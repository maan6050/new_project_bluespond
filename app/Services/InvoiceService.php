<?php

namespace App\Services;

use App\Constants\InvoiceStatus;
use App\Constants\PlanPriceType;
use App\Constants\PlanType;
use App\Constants\TransactionStatus;
use App\Models\Invoice as InvoiceEntity;
use App\Models\PlanPrice;
use App\Models\Tenant;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Invoice;
use Parfaitementweb\FilamentCountryField\CountryResolver;

class InvoiceService
{
    public function __construct(
        private CountryResolver $countryResolver,
    ) {}

    public function generate(Transaction $transaction, bool $regenerate = false)
    {
        if (! $this->canGenerateInvoices($transaction)) {
            return null;
        }

        $invoiceEntity = $this->findOrCreateInvoiceForTransaction($transaction);

        if (! $regenerate && $invoiceEntity->status === InvoiceStatus::RENDERED->value) {
            $storage = Storage::disk(config('invoices.disk'));

            if (! $storage->exists($invoiceEntity->filename)) {
                throw new Exception(sprintf('Invoice file not found for transaction %s', $transaction->id));
            }

            return response()->file($storage->path($invoiceEntity->filename));
        }

        $orderNumber = $transaction?->order?->uuid;
        $subscriptionNumber = $transaction?->subscription?->uuid;
        $customFields = [
            'email' => $transaction->user->email,
        ];

        $orderItems = [];
        if ($orderNumber) {
            $customFields[__('Order')] = $orderNumber;

            foreach ($transaction->order->items as $item) {
                $orderItems[] = InvoiceItem::make($item->oneTimeProduct->name)
                    ->quantity($item->quantity)
                    ->formattedPricePerUnit(
                        money($item->price_per_unit, $item->currency->code)
                    );
            }
        } elseif ($subscriptionNumber) {
            $customFields[__('subscription')] = $subscriptionNumber;

            $orderItems = $this->buildSubscriptionInvoiceItems($transaction);
        }

        $customFields = $this->addAddressInfo($transaction->tenant, $customFields);

        $customer = new Buyer([
            'name' => $transaction->user->name,
            'custom_fields' => $customFields,
        ]);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->formattedTotalAmount(
                money($transaction->amount, $transaction->currency->code)
            )
            ->sequence($invoiceEntity->id)
            ->date($invoiceEntity->created_at)
            ->dateFormat(config('app.date_format'))
            ->logo(public_path(config('app.logo.dark')));

        if ($transaction->total_tax > 0) {
            $invoice->formattedTotalTaxes(
                money($transaction->total_tax, $transaction->currency->code)
            );
        }

        if ($transaction->total_discount > 0) {
            $invoice->formattedTotalDiscount(
                money($transaction->total_discount, $transaction->currency->code)
            );
        }

        if (count($orderItems) > 0) {
            $invoice->addItems($orderItems);
        }

        $dateCreated = Carbon::parse($invoiceEntity->created_at);
        $filename = sprintf('%s/%s/%s', config('invoices.path'), $dateCreated->format('Y/m'), $invoiceEntity->uuid);

        $invoice->filename($filename);
        $invoice->save();

        $invoiceEntity->update([
            'status' => InvoiceStatus::RENDERED->value,
            'filename' => $filename.'.pdf',
        ]);

        return $invoice->stream();
    }

    public function buildSubscriptionInvoiceItems(Transaction $transaction): array
    {
        $subscription = $transaction->subscription;
        $items = [];

        $itemName = $subscription->plan->name;

        if ($subscription->plan->has_trial) {
            $itemName .= ' - '.$subscription->plan->trial_interval_count.' '.$subscription->plan->trialInterval()->firstOrFail()->name.' '.__('free trial included');
        }

        $planPrice = PlanPrice::where('plan_id', $subscription->plan_id)
            ->where('currency_id', $subscription->currency_id)
            ->first();

        if ($subscription->plan->type === PlanType::SEAT_BASED->value
            && $planPrice
            && $planPrice->type === PlanPriceType::SEAT_BASED_WITH_INCLUDED_SEATS->value
        ) {
            $items[] = InvoiceItem::make($itemName.' - '.__('Base Price (:count seats included)', ['count' => $planPrice->included_seats]))
                ->quantity(1)
                ->formattedPricePerUnit(
                    money($planPrice->price, $subscription->currency->code)
                );

            $setupFeeAmount = 0;
            if (($planPrice->setup_fee ?? 0) > 0) {
                $isFirstTransaction = ! $subscription->transactions()
                    ->where('id', '<', $transaction->id)
                    ->where('status', TransactionStatus::SUCCESS->value)
                    ->exists();

                if ($isFirstTransaction) {
                    $setupFeeAmount = $planPrice->setup_fee;
                }
            }

            $extraSeatsAmount = $transaction->amount - $planPrice->price - $setupFeeAmount - $transaction->total_discount - $transaction->total_tax;
            if ($extraSeatsAmount > 0) {
                $items[] = InvoiceItem::make(__('Extra Seats'))
                    ->quantity(1)
                    ->formattedPricePerUnit(
                        money($extraSeatsAmount, $subscription->currency->code)
                    );
            }
        } else {
            $items[] = InvoiceItem::make($itemName)
                ->quantity(1)
                ->formattedPricePerUnit(
                    money($subscription->price, $subscription->currency->code)
                );
        }

        if ($planPrice && ($planPrice->setup_fee ?? 0) > 0) {
            $isFirstTransaction = ! $subscription->transactions()
                ->where('id', '<', $transaction->id)
                ->where('status', TransactionStatus::SUCCESS->value)
                ->exists();

            if ($isFirstTransaction) {
                $items[] = InvoiceItem::make(__('Setup Fee'))
                    ->quantity(1)
                    ->formattedPricePerUnit(
                        money($planPrice->setup_fee, $subscription->currency->code)
                    );
            }
        }

        return $items;
    }

    private function addAddressInfo(Tenant $tenant, array $customFields): array
    {
        $address = $this->resolveInvoiceAddress($tenant);

        if ($address === null) {
            return $customFields;
        }

        $addressPieces = array_filter([
            $address['line_1'] ?? null,
            $address['line_2'] ?? null,
            $address['city'] ?? null,
            $address['state'] ?? null,
            $address['zip'] ?? null,
            ! empty($address['country_code'])
                ? $this->countryResolver->resolveCountryFromCode($address['country_code'])
                : null,
        ]);

        if (count($addressPieces) > 0) {
            $customFields[__('address')] = implode(', ', $addressPieces);
        }

        if (! empty($address['tax_number'])) {
            $customFields[__('tax number')] = $address['tax_number'];
        }

        return $customFields;
    }

    /**
     * Resolve the address printed on subscription invoices.
     *
     * Bluespond uses the BusinessProfile as the single source of truth for an
     * org's address (entered during onboarding / Business Settings). The legacy
     * tenant_address record is consulted only as a fallback for tenants whose
     * data predates that consolidation, or whose BusinessProfile is empty.
     *
     * Returns a normalized array keyed by line_1/line_2/city/state/zip/country_code/tax_number,
     * or null when neither source has any address data.
     */
    private function resolveInvoiceAddress(Tenant $tenant): ?array
    {
        $profile = $tenant->businessProfile;

        if ($profile && ($profile->address_line_1 || $profile->city || $profile->zip_code)) {
            return [
                'line_1' => $profile->address_line_1,
                'line_2' => $profile->address_line_2,
                'city' => $profile->city,
                'state' => $profile->state,
                'zip' => $profile->zip_code,
                'country_code' => $profile->country,
                'tax_number' => null, // BusinessProfile has no tax_number yet — add later if needed
            ];
        }

        $legacy = $tenant->address()->first();

        if ($legacy) {
            return [
                'line_1' => $legacy->address_line_1,
                'line_2' => $legacy->address_line_2,
                'city' => $legacy->city,
                'state' => $legacy->state,
                'zip' => $legacy->zip,
                'country_code' => $legacy->country_code,
                'tax_number' => $legacy->tax_number,
            ];
        }

        return null;
    }

    public function canGenerateInvoices(Transaction $transaction): bool
    {
        if (config('invoices.enabled', false) !== true) {
            return false;
        }

        if ($transaction->status != TransactionStatus::SUCCESS->value) {
            return false;
        }

        return true;
    }

    public function addInvoicePlaceholderForTransaction(Transaction $transaction)
    {
        $this->findOrCreateInvoiceForTransaction($transaction);
    }

    private function findOrCreateInvoiceForTransaction(Transaction $transaction): InvoiceEntity
    {
        return InvoiceEntity::firstOrCreate([
            'transaction_id' => $transaction->id,
        ], [
            'uuid' => Str::uuid(),
            'status' => InvoiceStatus::UNRENEDERED->value,
            'transaction_id' => $transaction->id,
        ]);
    }
}
