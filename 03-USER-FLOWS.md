# Bluespond -- Core User Flow Documentation

**Project:** Bluespond
**Client:** Darell Parker
**Prepared by:** Mandeep Singh
**Phase:** 1 -- Discovery & Architecture

---

## Table of Contents

1. Business Onboarding Flow
2. Service & Staff Setup Flow
3. Customer Booking Flow (Public)
4. Booking Management Flow (Business Side)
5. Rescheduling & Cancellation Flow
6. Waitlist Flow
7. Customer Import Flow
8. Revenue Recovery: Reconnect Flow
9. Revenue Recovery: Recover Flow
10. Revenue Recovery: Refill Flow
11. Campaign Creation Flow
12. AI Message Generation Flow
13. Social Media Copy Generation Flow
14. Ratings & Reviews Flow
15. Dashboard & Reporting Flow
16. Customer Account Flow

---

## 1. Business Onboarding Flow

**Actor:** Business Owner
**Goal:** Sign up, set up business, and go live on Bluespond

```
START
  |
  v
[1] Visit bluespond.com -> Click "Get Started"
  |
  v
[2] Select subscription plan (Starter $49 / Growth $99 / Pro $199)
  |
  v
[3] Create account
    - Email + password OR social login (Google/Facebook)
    - First name, last name
    - Phone number
  |
  v
[4] Stripe payment flow
    - Enter card / Apple Pay / Google Pay
    - Trial period starts (if configured)
  |
  v
[5] Business Setup Wizard (4 steps)
    |
    +---> Step 1: Business Info
    |     - Business name
    |     - Category (Barber, Salon, Med Spa, etc.)
    |     - Address
    |     - Phone
    |     - Timezone
    |
    +---> Step 2: Services
    |     - Pre-filled service templates based on category selected in Step 1
    |       (e.g., "Barber" pre-fills: Haircut $25/30min, Fade $35/45min, Beard Trim $15/15min)
    |     - Business can edit, remove, or add more services
    |     - Must have at least 1 active service
    |
    +---> Step 3: Availability
    |     - Set weekly business hours
    |     - Mon-Sun: open/close times or "Closed"
    |
    +---> Step 4: Public Profile
          - Add logo (optional)
          - Add description (optional)
          - Review public booking URL: bluespond.com/b/{slug}
          - Toggle "Publish" to go live
  |
  v
[6] Dashboard -> "Your business is live!" confirmation
  |
  v
[7] Guided next steps:
    - "Add staff members"
    - "Import existing customers"
    - "Set up your first campaign"
    - "Add social media links"
  |
  v
END
```

**Edge cases:**
- User abandons wizard mid-flow: Save progress, resume on next login
- Business name already taken as slug: Auto-append number (e.g., "joes-barbershop-2")
- No payment method: Allow limited trial setup, prompt for payment before going live

---

## 2. Service & Staff Setup Flow

**Actor:** Business Owner / Manager
**Goal:** Configure services offered and assign staff

```
SERVICES:
  |
  v
[1] Dashboard -> "Services" tab
  |
  v
[2] Click "Add Service"
    - Service name (e.g., "Men's Haircut")
    - Duration (minutes)
    - Price
    - Description (optional)
    - Category grouping (optional: "Haircuts", "Color", "Treatments")
    - Buffer time after service (optional)
    - Image (optional)
  |
  v
[3] Assign staff to service
    - Select which staff members can perform this service
    - Optionally set custom price/duration per staff member
  |
  v
[4] Toggle "Active" and "Public" (visible on booking page)
  |
  v
[5] Service appears on public booking page


STAFF:
  |
  v
[1] Dashboard -> "Staff" tab
  |
  v
[2] Click "Add Staff Member"
    - Name
    - Email (optional -- sends invitation to create account)
    - Phone (optional)
    - Title (e.g., "Senior Stylist")
    - Avatar (optional)
  |
  v
[3] Set staff schedule
    - Weekly availability (per day: start time, end time, breaks)
    - Different from business hours (staff can have individual schedules)
  |
  v
[4] Assign services this staff member performs
  |
  v
[5] If email provided -> send invitation to join as Staff role
    - Staff member creates account
    - Gets access to their schedule and assigned bookings
  |
  v
END
```

---

## 3. Customer Booking Flow (Public)

**Actor:** Customer (end user)
**Goal:** Book an appointment with a business

```
START
  |
  v
[1] Customer arrives at public business page
    - Via direct link: bluespond.com/b/{business-slug}
    - Via marketplace search/browse
    - Via link shared by business (social media, SMS, email)
  |
  v
[2] View business profile
    - Business name, description, rating, photos
    - Hours, location, social links
    - Reviews
  |
  v
[3] Click "Book Now"
  |
  v
[4] Select Service
    - Browse available services with name, duration, price
    - If multiple categories, shown in grouped tabs
  |
  v
[5] Select Staff (optional)
    - "Any available" (default) OR pick a specific staff member
    - Only shows staff assigned to the selected service
  |
  v
[6] Select Date
    - Calendar view showing available dates
    - Dates with no availability are grayed out
    - Respects business hours, staff schedules, blocked dates, existing bookings
  |
  v
[7] Select Time Slot
    - Available time slots for selected date
    - Calculated based on: service duration + buffer, staff availability,
      existing bookings, business hours
    - If "Any staff" selected, shows union of all staff availability
  |
  v
[8] Review & Confirm
    - Service, staff, date, time, price summary
    - Add notes (optional)
  |
  v
[9] Customer Identity
    - IF logged in: pre-fill info, proceed
    - IF guest: enter name, email, phone
    - IF new: option to create account for future bookings
  |
  v
[10] Deposit payment (if required by business)
     - Stripe Checkout: card / Apple Pay / Google Pay
     - If no deposit required: skip
  |
  v
[11] Booking Confirmed
     - Confirmation screen with reference number (BLU-A1B2C3)
     - SMS confirmation sent (if phone provided)
     - Email confirmation sent (if email provided)
     - "Add to Calendar" button (Google Calendar / Apple Calendar / .ics)
  |
  v
END
```

**Edge cases:**
- Slot taken between selection and confirmation: Show error, reload available slots
- Business requires deposit but customer abandons payment: Hold slot for 5 minutes, then release
- Customer books then immediately cancels: Respect business cancellation policy
- Business auto-confirm OFF: Booking goes to "pending" status, business must confirm

---

## 4. Booking Management Flow (Business Side)

**Actor:** Business Owner / Manager / Staff
**Goal:** View, manage, and act on bookings

```
[1] Dashboard -> "Bookings" tab (default view)
  |
  v
[2] Calendar View (primary)
    - Day view / Week view / Month view toggle
    - Color-coded by status: confirmed (blue), pending (yellow), completed (green), cancelled (gray)
    - Click any booking to view details
    - Click empty slot to create walk-in booking
  |
  v
[3] List View (secondary)
    - Filterable table: date range, status, staff, service
    - Search by customer name, phone, reference number
    - Bulk actions: confirm multiple, export
  |
  v
[4] Booking Detail Panel
    - Customer info (name, phone, email, visit history)
    - Service, staff, date/time, price
    - Status with action buttons:
      - Pending -> Confirm / Decline
      - Confirmed -> Mark In Progress / Cancel / Reschedule
      - In Progress -> Mark Complete / Mark No-Show
      - Completed -> (no further status change; prompt review request)
    - Notes (customer + internal)
    - Status history timeline
  |
  v
[5] Quick Actions
    - "Add Walk-In" -> fast booking creation for in-person customers
    - "Block Time" -> mark time as unavailable
    - "Send Reminder" -> manual reminder to customer
  |
  v
END
```

---

## 5. Rescheduling & Cancellation Flow

**Actor:** Customer OR Business
**Goal:** Reschedule or cancel an existing booking

```
CUSTOMER-INITIATED RESCHEDULE:
  |
  v
[1] Customer receives booking confirmation SMS/email with "Manage Booking" link
  |
  v
[2] Click link -> Booking management page (no login required, secured by token)
  |
  v
[3] Click "Reschedule"
  |
  v
[4] Select new date and time (same flow as original booking, step 6-7)
  |
  v
[5] Confirm reschedule
    - Original booking marked as "rescheduled"
    - New booking created linked to original
    - Notifications sent to business and customer
  |
  v
END


CUSTOMER-INITIATED CANCELLATION:
  |
  v
[1] "Manage Booking" link -> Click "Cancel"
  |
  v
[2] Check cancellation policy
    - IF within allowed window: proceed
    - IF outside window (e.g., <24 hours before): show warning
      "Cancellation within 24 hours may forfeit your deposit"
  |
  v
[3] Select cancellation reason (optional)
  |
  v
[4] Confirm cancellation
    - Booking status -> "cancelled"
    - Notifications sent
    - Slot freed up (triggers Refill recovery check)
    - Deposit refund per business policy
  |
  v
END


BUSINESS-INITIATED:
  |
  v
[1] Business clicks booking -> "Cancel" or "Reschedule"
  |
  v
[2] Enter reason
  |
  v
[3] Customer notified via SMS/email with options:
    - Rebook at a new time
    - Acknowledge cancellation
  |
  v
END
```

---

## 6. Waitlist Flow

**Actor:** Customer
**Goal:** Join a waitlist when no slots are available

```
[1] Customer on booking flow -> selected date has no available slots
  |
  v
[2] System shows: "No slots available for this date. Join the waitlist?"
  |
  v
[3] Customer clicks "Join Waitlist"
    - Optionally select preferred time range
    - Optionally select preferred staff
  |
  v
[4] Waitlist entry created (status: "waiting")
  |
  v
[5] TRIGGER: When a booking is cancelled or rescheduled for that date
    |
    v
[6] System finds matching waitlist entries
    - Matches by: date, service, preferred time range, preferred staff
    - Ordered by: creation date (first come, first served)
  |
  v
[7] Top matching customer receives notification:
    "A slot has opened up on [date] at [time] for [service]. Book now!"
    - Link to instant booking (slot held for 15 minutes)
  |
  v
[8a] Customer books -> waitlist entry status: "booked", linked to new booking
[8b] Customer doesn't respond within 15 min -> notify next person on waitlist
[8c] Customer declines -> waitlist entry status: "cancelled"
  |
  v
END
```

---

## 7. Customer Import Flow

**Actor:** Business Owner
**Goal:** Import existing customer list from CSV

```
[1] Dashboard -> "Customers" -> "Import"
  |
  v
[2] Upload CSV file
    - Drag & drop or file picker
    - Accepts: .csv, .xlsx
    - Max file size: 10MB
  |
  v
[3] Column Mapping
    - System auto-detects common column names
    - User confirms/adjusts mapping:
      - First Name -> first_name
      - Last Name -> last_name
      - Email -> email
      - Phone -> phone
      - Last Visit Date -> last_visit_at
      - Notes -> notes
    - Preview first 5 rows
  |
  v
[4] Validation & Preview
    - Show: total rows, valid rows, rows with issues
    - Issues: missing required fields, invalid email/phone format, duplicates
    - Option to skip or fix rows with issues
  |
  v
[5] Confirm Import
  |
  v
[6] Background processing (queue job)
    - Progress bar on screen
    - Creates customer records
    - Deduplicates by email/phone
    - Sets source: "import"
  |
  v
[7] Import Complete
    - Summary: imported, skipped, errors
    - Download error report if any
    - Auto-detect inactive customers based on last_visit_at
  |
  v
END
```

---

## 8. Revenue Recovery: Reconnect Flow

**Actor:** System (automated) + Business Owner (configuration)
**Goal:** Re-engage inactive customers automatically

```
SETUP (one-time):
  |
  v
[1] Dashboard -> "Recovery" -> "Reconnect"
  |
  v
[2] Configure inactivity threshold
    - Default: customer inactive for 30+ days
    - Business can adjust: 14, 30, 60, 90 days
  |
  v
[3] Set up reconnect sequence:
    - Message 1 (Day 0): "Hi {{first_name}}, we miss you at {{business_name}}! Book your next appointment: {{booking_link}}"
    - Message 2 (Day 3): Follow-up if no response
    - Message 3 (Day 7): Special offer / last attempt
    - Business can customize messages or use AI-generated ones
  |
  v
[4] Select channel: SMS, Email, or Both
  |
  v
[5] Enable automated campaign


AUTOMATED EXECUTION:
  |
  v
[A] Daily cron job scans customers table
  |
  v
[B] Identifies customers where:
    - last_visit_at < (now - inactivity_threshold)
    - status = 'active' (not already marked inactive)
    - Not already in an active reconnect campaign
    - Not opted out of communications
  |
  v
[C] Updates customer status to 'inactive'
  |
  v
[D] Adds to reconnect campaign queue
  |
  v
[E] Sends Message 1 via configured channel
  |
  v
[F] Tracks: delivered, opened, clicked, converted (booked)
  |
  v
[G] If no conversion after Message 1 -> wait delay -> send Message 2
  |
  v
[H] If customer books at any point -> stop sequence, mark converted
    - Calculate revenue recovered (service price)
  |
  v
END
```

---

## 9. Revenue Recovery: Recover Flow

**Actor:** System (automated)
**Goal:** Follow up on missed bookings, cancellations, and no-shows

```
TRIGGERS:
  - Booking cancelled by customer
  - Booking marked as no-show
  - Customer viewed booking page but didn't complete booking (future enhancement)

FLOW:
  |
  v
[1] TRIGGER EVENT: Booking cancelled or no-show detected
  |
  v
[2] System waits configured delay (default: 2 hours for cancellation, 24 hours for no-show)
  |
  v
[3] Sends recovery message:
    - Cancellation: "We noticed you cancelled your {{service_name}} appointment. Would you like to rebook? {{booking_link}}"
    - No-show: "We missed you today! Would you like to reschedule your {{service_name}}? {{booking_link}}"
  |
  v
[4] If customer rebooks -> mark as "recovered"
    - Calculate recovered revenue
  |
  v
[5] If no response after Message 1 -> send Message 2 (Day 3)
    - Include a special offer or incentive (if configured by business)
  |
  v
[6] Max 2-3 attempts, then stop
  |
  v
END
```

---

## 10. Revenue Recovery: Refill Flow

**Actor:** System (automated)
**Goal:** Fill open slots from cancellations or unused capacity

```
TRIGGER: Booking cancelled, creating an open slot
  |
  v
[1] System detects open slot
    - Checks: date, time, service, staff
    - Only triggers if slot is within next 48 hours (configurable)
  |
  v
[2] Find candidates to fill slot:
    - Waitlist entries matching date/service/staff -> Priority 1
    - Customers who previously booked this service -> Priority 2
    - Nearby customers (by last visit recency) -> Priority 3
  |
  v
[3] Send targeted message:
    - "Great news! A slot just opened up on {{date}} at {{time}} for {{service_name}}. Book it now: {{booking_link}}"
    - Can include a last-minute discount (if configured)
  |
  v
[4] First customer to book gets the slot
  |
  v
[5] Track: slot filled = revenue recovered
  |
  v
END
```

---

## 11. Campaign Creation Flow

**Actor:** Business Owner / Manager
**Goal:** Create and send a targeted marketing campaign

```
[1] Dashboard -> "Campaigns" -> "Create Campaign"
  |
  v
[2] Select campaign type:
    - Reconnect (inactive customers)
    - Recover (missed bookings)
    - Refill (open slots)
    - Custom (manual targeting)
    - Social Media (generate social posts -- see Flow 13)
  |
  v
[3] Define audience:
    - Pre-built filters: inactive X days, visited Y times, specific service, tags
    - Preview audience count: "This campaign will reach ~45 customers"
  |
  v
[4] Create message:
    - Write manually OR
    - Click "Generate with AI" -> system generates message based on:
      - Campaign type
      - Business info
      - Audience characteristics
      - Service details
    - Support for variables: {{first_name}}, {{business_name}}, {{service_name}}, {{booking_link}}
    - Preview how message looks as SMS and email
  |
  v
[5] Select channel: SMS, Email, or Both
  |
  v
[6] Schedule:
    - Send immediately
    - Schedule for specific date/time
    - Set up as automated (recurring based on trigger)
  |
  v
[7] Review & Confirm
    - Audience count
    - Message preview
    - Channel
    - Schedule
    - Estimated cost (SMS segments)
    - Usage limit check (campaigns remaining in billing period)
  |
  v
[8] Launch Campaign
  |
  v
[9] Track performance in real-time:
    - Sent, delivered, opened, clicked, converted
    - Revenue recovered estimate
  |
  v
END
```

---

## 12. AI Message Generation Flow

**Actor:** Business Owner / Manager / Staff
**Goal:** Generate AI-powered messages for campaigns, replies, and recommendations

```
[1] User triggers AI generation from:
    - Campaign builder ("Generate with AI" button)
    - Customer detail page ("Suggest follow-up message")
    - Recovery setup ("Generate recovery sequence")
  |
  v
[2] System constructs prompt with context:
    - Business info (name, category, services, tone)
    - Customer info (name, visit history, last service, preferences)
    - Campaign type and goal
    - Channel constraints (SMS: 160 chars, Email: longer)
  |
  v
[3] Check usage limits:
    - Query current billing period AI generation count
    - IF under limit: proceed
    - IF at limit: show "AI generation limit reached. Upgrade your plan for more."
  |
  v
[4] Call OpenAI API:
    - Model: GPT-4o-mini for simple messages, GPT-4o for complex/personalized
    - Return 2-3 variations
  |
  v
[5] Display options to user:
    - Option A: [casual tone message]
    - Option B: [professional tone message]
    - Option C: [offer-focused message]
    - Edit / Regenerate / Pick one
  |
  v
[6] User selects or edits -> message saved
  |
  v
[7] Log generation in ai_generations table
    - Track tokens used, cost, type
  |
  v
END
```

**OpenAI Model Recommendation:**
- **GPT-4o-mini** for: campaign messages, SMS copy, simple suggestions (fast, cheap)
- **GPT-4o** for: complex personalization, multi-message sequences, social media posts (higher quality)
- Start with GPT-4o-mini for everything, upgrade specific use cases to GPT-4o based on quality feedback

---

## 13. Social Media Campaign Copy Generation Flow

**Actor:** Business Owner
**Goal:** Generate ready-to-post social media content for business marketing

```
[1] Dashboard -> "Campaigns" -> "Social Posts" OR "Create Campaign" -> "Social Media"
  |
  v
[2] Select post type:
    - Promotion / Offer
    - New service announcement
    - Seasonal / holiday
    - Customer spotlight (with permission)
    - General engagement / tip
  |
  v
[3] Provide context:
    - What to promote (service, offer, event)
    - Target platforms: Instagram, Facebook, TikTok, Twitter
    - Tone preference: casual, professional, fun, trendy
  |
  v
[4] AI generates platform-specific posts:
    - Instagram: visual caption + hashtags
    - Facebook: longer format + call-to-action
    - TikTok: short, trendy caption
    - Twitter: concise version with link
  |
  v
[5] User reviews, edits, copies to clipboard
    - "Copy" button per platform
    - NOT auto-posted (business posts manually to maintain control)
  |
  v
[6] Track: AI generation logged, usage counted
  |
  v
END
```

---

## 14. Ratings & Reviews Flow

**Actor:** Customer
**Goal:** Leave a review for a business after a completed booking

```
REQUEST FLOW:
  |
  v
[1] Booking marked as "completed"
  |
  v
[2] System waits 2 hours (configurable)
  |
  v
[3] Send review request:
    - SMS: "How was your visit to {{business_name}}? Leave a quick review: {{review_link}}"
    - Email: same with nicer formatting
  |
  v
[4] Customer clicks review link


REVIEW SUBMISSION:
  |
  v
[5] Review page (no login required, token-secured):
    - Star rating (1-5, required)
    - Optional title
    - Optional comment
    - Staff member rating (optional, if applicable)
  |
  v
[6] Submit review
  |
  v
[7] Review published immediately (or held for moderation based on business setting)
  |
  v
[8] Business notified of new review
  |
  v
[9] Business can reply to review from dashboard


BUSINESS SIDE:
  |
  v
[10] Dashboard -> "Reviews"
     - List all reviews: rating, customer, date, comment
     - Filter by: rating, staff, date range
     - Reply to reviews
     - Flag inappropriate reviews
     - Average rating displayed on public profile
  |
  v
END
```

---

## 15. Dashboard & Reporting Flow

**Actor:** Business Owner / Manager
**Goal:** View business performance, bookings, and revenue recovery metrics

```
[1] Login -> Dashboard (default landing page)
  |
  v
[2] Overview Cards (top row):
    - Today's Bookings: count + next upcoming
    - Revenue This Month: total from completed bookings
    - Revenue Recovered: from recovery campaigns
    - Active Customers: count
    - Pending Reviews: count
  |
  v
[3] Quick Actions Bar:
    - "Add Booking" | "New Campaign" | "Import Customers" | "View Calendar"
  |
  v
[4] Charts Section:
    - Bookings over time (line chart, 30-day)
    - Revenue over time (line chart, 30-day)
    - Revenue recovered by campaign type (bar chart)
    - Customer status breakdown (pie chart: active / inactive / lost)
  |
  v
[5] Upcoming Bookings (today/tomorrow list)
  |
  v
[6] Recovery Insights:
    - "23 inactive customers detected"
    - "5 cancellations this week"
    - "3 open slots tomorrow"
    - Action buttons to launch relevant campaigns
  |
  v
[7] Recent Activity Feed:
    - New bookings, cancellations, reviews, campaign results
  |
  v
[8] Navigation to detailed reports:
    - Bookings Report (date range, service, staff breakdown)
    - Revenue Report (monthly, by service, by staff)
    - Recovery Report (by campaign type, conversion rates, revenue recovered)
    - Customer Report (new, active, inactive, lost trends)
    - Staff Performance (bookings per staff, ratings per staff)
  |
  v
END
```

---

## 16. Customer Account Flow

**Actor:** Customer (end user)
**Goal:** Manage bookings and profile from a customer account

```
SIGNUP:
  |
  v
[1] Customer creates account (during first booking or standalone)
    - Email + password OR social login
    - Name, phone
  |
  v
[2] Email verification


CUSTOMER PORTAL (bluespond.com/my):
  |
  v
[3] My Bookings
    - Upcoming: with reschedule/cancel options
    - Past: with rebooking and review options
    - Filter by business (if customer uses multiple businesses)
  |
  v
[4] My Profile
    - Edit name, email, phone, avatar
    - Notification preferences (SMS on/off, email on/off)
    - Communication opt-out
  |
  v
[5] My Reviews
    - Reviews customer has left
    - Edit or delete own reviews
  |
  v
[6] Favorite Businesses
    - Saved businesses for quick rebooking
  |
  v
END
```

---

## 17. Platform Admin Flow

**Actor:** Bluespond Admin (Darell / team)
**Goal:** Manage the overall platform

```
[1] Admin Panel (bluespond.com/admin) -- Filament-powered
  |
  v
[2] Platform Dashboard:
    - Total businesses (active/trial/churned)
    - Total bookings (today/week/month)
    - Total revenue recovered across platform
    - MRR (Monthly Recurring Revenue)
    - Churn rate
  |
  v
[3] Business Management:
    - List all businesses
    - View details, subscription status, usage
    - Impersonate business owner
    - Feature/unfeature businesses in marketplace
    - Suspend/ban businesses
  |
  v
[4] User Management (from SaaSykit)
  |
  v
[5] Subscription & Plan Management:
    - Edit plan names, prices, limits
    - Manage discounts/coupons
  |
  v
[6] Category Management:
    - Add/edit/reorder business categories
    - Manage verticals (appointments, skilled trades)
  |
  v
[7] Content Management:
    - Blog posts
    - Landing page settings
    - Announcements
  |
  v
[8] Settings:
    - Platform-wide defaults
    - SMS/Email provider configuration
    - OpenAI API configuration
    - Payment provider configuration
  |
  v
END
```
