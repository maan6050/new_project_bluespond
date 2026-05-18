<?php

namespace App\Constants;

/**
 * Days of the week, backed by the integer convention used across the booking
 * domain (0 = Sunday … 6 = Saturday) — matches the `day_of_week` columns on
 * `business_hours` and `staff_schedules`.
 */
enum DayOfWeek: int
{
    case SUNDAY = 0;
    case MONDAY = 1;
    case TUESDAY = 2;
    case WEDNESDAY = 3;
    case THURSDAY = 4;
    case FRIDAY = 5;
    case SATURDAY = 6;

    /**
     * Human-readable, translatable day name for display in UIs.
     */
    public function label(): string
    {
        return __(ucfirst(strtolower($this->name)));
    }

    public function isWeekend(): bool
    {
        return $this === self::SATURDAY || $this === self::SUNDAY;
    }
}
