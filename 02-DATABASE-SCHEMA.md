# Bluespond -- Database Schema & ERD

**Project:** Bluespond
**Client:** Darell Parker
**Prepared by:** Mandeep Singh
**Phase:** 1 -- Discovery & Architecture

---

## 1. Schema Overview

The database extends SaaSykit's existing tables and adds Bluespond-specific tables. SaaSykit tables are marked with [S]. New Bluespond tables are marked with [NEW].

**Total tables:** ~43 (15 existing from SaaSykit, ~9 removed, ~28 new for Bluespond)

**Tenancy model:** Single-database, row-based isolation. All business-owned resources include a `tenant_id` foreign key.

---

## 2. Entity Relationship Diagram (Text-Based)

```
                                    PLATFORM LAYER (SaaSykit)
    +------------------+     +------------------+     +------------------+
    |     users [S]    |---->|  subscriptions[S]|---->|    plans [S]     |
    |  (all user types)|     +------------------+     +------------------+
    +--------+---------+                                      |
             |                                                v
             |                                    +------------------+
             |                                    |   products [S]   |
             |                                    +------------------+
             v
    +------------------+
    |   tenants [S]    |  = businesses (extended)
    | (= businesses)   |
    +--------+---------+
             |
    +--------+--------+----------------------------------------+
    |                 |                    |                    |
    v                 v                    v                    v
+----------+  +-------------+  +------------------+  +------------------+
| business |  |   staff     |  |    services      |  |   customers      |
| _profiles|  | _members    |  |                  |  |   (per-business) |
|  [NEW]   |  |   [NEW]     |  |     [NEW]        |  |     [NEW]        |
+----------+  +------+------+  +--------+---------+  +--------+---------+
                     |                   |                     |
                     v                   v                     |
              +-------------+  +------------------+            |
              | staff       |  | service_staff    |            |
              | _schedules  |  | (pivot) [NEW]    |            |
              |   [NEW]     |  +------------------+            |
              +-------------+                                  |
                     |                                         |
    +----------------+----------------+------------------------+
    |                                 |
    v                                 v
+------------------+        +------------------+
|    bookings      |        |  booking_reviews |
|     [NEW]        |        |     [NEW]        |
+--------+---------+        +------------------+
         |
         v
+------------------+
| booking          |
| _status_history  |
|     [NEW]        |
+------------------+


                    REVENUE RECOVERY LAYER
    +------------------+     +------------------+
    |   campaigns      |---->| campaign         |
    |     [NEW]        |     | _messages [NEW]  |
    +--------+---------+     +------------------+
             |
             v
    +------------------+     +------------------+
    | campaign         |     | campaign         |
    | _recipients[NEW] |     | _analytics [NEW] |
    +------------------+     +------------------+


                    AI & COMMUNICATIONS LAYER
    +------------------+     +------------------+
    | ai_generations   |     | notifications    |
    |     [NEW]        |     |     [NEW]        |
    +------------------+     +------------------+

    +------------------+     +------------------+
    | sms_messages     |     | usage_records    |
    |     [NEW]        |     |     [NEW]        |
    +------------------+     +------------------+


                    MARKETPLACE LAYER
    +------------------+     +------------------+
    | business         |     | business         |
    | _categories[NEW] |     | _social_links    |
    +------------------+     |     [NEW]        |
                             +------------------+
    +------------------+
    |    waitlist      |
    |  _entries [NEW]  |
    +------------------+
```

---

## 3. Detailed Table Definitions

### 3.1 SaaSykit Existing Tables (Extended)

#### users [S - EXTEND]
```sql
-- SaaSykit existing fields preserved
-- Additional Bluespond fields:
ALTER TABLE users ADD COLUMN user_type ENUM('business_owner', 'staff', 'customer', 'admin') DEFAULT 'customer';
ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN avatar VARCHAR(500) NULL;
ALTER TABLE users ADD COLUMN notification_preferences JSON NULL;
-- notification_preferences: {"sms": true, "email": true, "push": false}
```

#### tenants [S - EXTEND]
```sql
-- SaaSykit existing fields preserved (id, name, slug, uuid, domain, etc.)
-- Additional Bluespond fields added via business_profiles table (1:1 relationship)
-- Keeps SaaSykit tenant model clean; Bluespond-specific data in separate table
```

---

### 3.2 Business & Staff Tables [NEW]

#### business_profiles
```sql
CREATE TABLE business_profiles (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id           BIGINT UNSIGNED NOT NULL UNIQUE,  -- 1:1 with tenants
    business_name       VARCHAR(255) NOT NULL,
    slug                VARCHAR(255) NOT NULL UNIQUE,      -- public URL: bluespond.com/b/{slug}
    category_id         BIGINT UNSIGNED NULL,              -- FK -> business_categories
    description         TEXT NULL,
    phone               VARCHAR(20) NULL,
    email               VARCHAR(255) NULL,
    address_line_1      VARCHAR(255) NULL,
    address_line_2      VARCHAR(255) NULL,
    city                VARCHAR(100) NULL,
    state               VARCHAR(100) NULL,
    zip_code            VARCHAR(20) NULL,
    country             VARCHAR(2) DEFAULT 'US',
    latitude            DECIMAL(10, 8) NULL,
    longitude           DECIMAL(11, 8) NULL,
    timezone            VARCHAR(50) DEFAULT 'America/New_York',
    currency            VARCHAR(3) DEFAULT 'USD',
    logo                VARCHAR(500) NULL,
    cover_image         VARCHAR(500) NULL,
    is_published        BOOLEAN DEFAULT FALSE,
    is_featured         BOOLEAN DEFAULT FALSE,
    average_rating      DECIMAL(2, 1) DEFAULT 0.0,
    total_reviews       INT UNSIGNED DEFAULT 0,
    total_bookings      INT UNSIGNED DEFAULT 0,
    vertical            ENUM('appointments', 'skilled_trades') DEFAULT 'appointments',
    settings            JSON NULL,
    -- settings: {"booking_buffer_minutes": 15, "max_advance_booking_days": 60,
    --            "cancellation_policy_hours": 24, "allow_guest_booking": true,
    --            "require_deposit": false, "deposit_percentage": 0,
    --            "auto_confirm_bookings": true, "reminder_hours_before": 24}
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES business_categories(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category_id),
    INDEX idx_city_state (city, state),
    INDEX idx_published (is_published),
    INDEX idx_location (latitude, longitude)
);
```

#### business_categories
```sql
CREATE TABLE business_categories (
    id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL UNIQUE,
    icon        VARCHAR(50) NULL,
    parent_id   BIGINT UNSIGNED NULL,           -- allows subcategories
    vertical    ENUM('appointments', 'skilled_trades') DEFAULT 'appointments',
    sort_order  INT DEFAULT 0,
    is_active   BOOLEAN DEFAULT TRUE,
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    FOREIGN KEY (parent_id) REFERENCES business_categories(id) ON DELETE SET NULL
);

-- Seed data:
-- Appointments: Barber Shop, Hair Salon, Med Spa, Nail Salon, Esthetician, Massage, Tattoo, etc.
-- Skilled Trades: Plumber, Electrician, HVAC, Handyman, Cleaning, Landscaping, etc.
```

#### business_social_links
```sql
CREATE TABLE business_social_links (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    business_profile_id BIGINT UNSIGNED NOT NULL,
    platform            ENUM('instagram', 'facebook', 'tiktok', 'twitter', 'youtube', 'linkedin', 'website', 'yelp', 'google_business') NOT NULL,
    url                 VARCHAR(500) NOT NULL,
    sort_order          INT DEFAULT 0,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (business_profile_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_business (business_profile_id)
);
```

#### business_hours
```sql
CREATE TABLE business_hours (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    business_profile_id BIGINT UNSIGNED NOT NULL,
    day_of_week         TINYINT NOT NULL,           -- 0=Sunday, 6=Saturday
    open_time           TIME NULL,
    close_time          TIME NULL,
    is_closed           BOOLEAN DEFAULT FALSE,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (business_profile_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_business_day (business_profile_id, day_of_week)
);
```

#### business_blocked_dates
```sql
CREATE TABLE business_blocked_dates (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    business_profile_id BIGINT UNSIGNED NOT NULL,
    date                DATE NOT NULL,
    reason              VARCHAR(255) NULL,           -- "Holiday", "Vacation", etc.
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (business_profile_id) REFERENCES business_profiles(id) ON DELETE CASCADE,
    INDEX idx_business_date (business_profile_id, date)
);
```

#### staff_members
```sql
CREATE TABLE staff_members (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    user_id             BIGINT UNSIGNED NULL,        -- linked user account (nullable for staff without login)
    name                VARCHAR(255) NOT NULL,
    email               VARCHAR(255) NULL,
    phone               VARCHAR(20) NULL,
    avatar              VARCHAR(500) NULL,
    title               VARCHAR(100) NULL,           -- "Senior Stylist", "Barber", etc.
    bio                 TEXT NULL,
    is_active           BOOLEAN DEFAULT TRUE,
    is_bookable         BOOLEAN DEFAULT TRUE,
    sort_order          INT DEFAULT 0,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_active (tenant_id, is_active)
);
```

#### staff_schedules
```sql
CREATE TABLE staff_schedules (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_member_id BIGINT UNSIGNED NOT NULL,
    day_of_week     TINYINT NOT NULL,               -- 0=Sunday, 6=Saturday
    start_time      TIME NULL,
    end_time        TIME NULL,
    is_available    BOOLEAN DEFAULT TRUE,
    break_start     TIME NULL,
    break_end       TIME NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE CASCADE,
    INDEX idx_staff_day (staff_member_id, day_of_week)
);
```

#### staff_blocked_times
```sql
CREATE TABLE staff_blocked_times (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_member_id BIGINT UNSIGNED NOT NULL,
    start_datetime  DATETIME NOT NULL,
    end_datetime    DATETIME NOT NULL,
    reason          VARCHAR(255) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE CASCADE,
    INDEX idx_staff_range (staff_member_id, start_datetime, end_datetime)
);
```

---

### 3.3 Service Tables [NEW]

#### services
```sql
CREATE TABLE services (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    slug            VARCHAR(255) NOT NULL,
    description     TEXT NULL,
    duration_minutes INT NOT NULL DEFAULT 30,
    buffer_minutes  INT DEFAULT 0,                   -- gap after service before next booking
    price           DECIMAL(10, 2) NOT NULL DEFAULT 0,
    deposit_amount  DECIMAL(10, 2) DEFAULT 0,
    category        VARCHAR(100) NULL,               -- internal grouping: "Haircuts", "Color", "Treatments"
    is_active       BOOLEAN DEFAULT TRUE,
    is_public       BOOLEAN DEFAULT TRUE,            -- visible on public booking page
    max_per_day     INT NULL,                        -- limit bookings per day (null = unlimited)
    sort_order      INT DEFAULT 0,
    image           VARCHAR(500) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_active (tenant_id, is_active),
    UNIQUE KEY idx_tenant_slug (tenant_id, slug)
);
```

#### service_staff (pivot)
```sql
CREATE TABLE service_staff (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    service_id      BIGINT UNSIGNED NOT NULL,
    staff_member_id BIGINT UNSIGNED NOT NULL,
    custom_price    DECIMAL(10, 2) NULL,             -- override service price for this staff
    custom_duration INT NULL,                        -- override duration for this staff
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE CASCADE,
    UNIQUE KEY idx_service_staff (service_id, staff_member_id)
);
```

---

### 3.4 Customer Tables [NEW]

#### customers
```sql
CREATE TABLE customers (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NULL,            -- linked user account (nullable for guest customers)
    first_name      VARCHAR(100) NOT NULL,
    last_name       VARCHAR(100) NULL,
    email           VARCHAR(255) NULL,
    phone           VARCHAR(20) NULL,
    avatar          VARCHAR(500) NULL,
    notes           TEXT NULL,                       -- internal notes by business
    tags            JSON NULL,                       -- ["VIP", "frequent", "sensitive"]
    source          ENUM('booking', 'import', 'manual', 'campaign', 'referral') DEFAULT 'booking',
    status          ENUM('active', 'inactive', 'lost') DEFAULT 'active',
    last_visit_at   TIMESTAMP NULL,
    total_visits    INT UNSIGNED DEFAULT 0,
    total_spent     DECIMAL(12, 2) DEFAULT 0,
    total_no_shows  INT UNSIGNED DEFAULT 0,
    lifetime_value  DECIMAL(12, 2) DEFAULT 0,
    inactive_since  TIMESTAMP NULL,                  -- set when detected as inactive
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_last_visit (tenant_id, last_visit_at),
    INDEX idx_email (tenant_id, email),
    INDEX idx_phone (tenant_id, phone)
);
```

#### customer_imports
```sql
CREATE TABLE customer_imports (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    uploaded_by     BIGINT UNSIGNED NOT NULL,         -- user_id who uploaded
    file_name       VARCHAR(255) NOT NULL,
    file_path       VARCHAR(500) NOT NULL,
    total_rows      INT UNSIGNED DEFAULT 0,
    imported_count  INT UNSIGNED DEFAULT 0,
    skipped_count   INT UNSIGNED DEFAULT 0,
    error_count     INT UNSIGNED DEFAULT 0,
    status          ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
    error_log       JSON NULL,
    column_mapping  JSON NULL,                       -- maps CSV columns to customer fields
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);
```

---

### 3.5 Booking Tables [NEW]

#### bookings
```sql
CREATE TABLE bookings (
    id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id           BIGINT UNSIGNED NOT NULL,
    customer_id         BIGINT UNSIGNED NOT NULL,
    staff_member_id     BIGINT UNSIGNED NULL,
    service_id          BIGINT UNSIGNED NOT NULL,
    booking_reference   VARCHAR(20) NOT NULL UNIQUE,  -- human-readable: "BLU-A1B2C3"
    status              ENUM('pending', 'confirmed', 'in_progress', 'completed', 'cancelled', 'no_show', 'rescheduled') DEFAULT 'pending',
    start_time          DATETIME NOT NULL,
    end_time            DATETIME NOT NULL,
    duration_minutes    INT NOT NULL,
    price               DECIMAL(10, 2) NOT NULL,
    deposit_amount      DECIMAL(10, 2) DEFAULT 0,
    deposit_paid        BOOLEAN DEFAULT FALSE,
    source              ENUM('online', 'phone', 'walk_in', 'recovery', 'waitlist') DEFAULT 'online',
    notes               TEXT NULL,                    -- customer notes
    internal_notes      TEXT NULL,                    -- staff notes
    cancelled_at        TIMESTAMP NULL,
    cancellation_reason VARCHAR(500) NULL,
    cancelled_by        ENUM('customer', 'business') NULL,
    rescheduled_from    BIGINT UNSIGNED NULL,         -- FK -> bookings.id if rescheduled
    reminder_sent       BOOLEAN DEFAULT FALSE,
    reminder_sent_at    TIMESTAMP NULL,
    created_at          TIMESTAMP NULL,
    updated_at          TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE SET NULL,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (rescheduled_from) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_tenant_date (tenant_id, start_time),
    INDEX idx_staff_date (staff_member_id, start_time),
    INDEX idx_customer (customer_id),
    INDEX idx_status (tenant_id, status),
    INDEX idx_reference (booking_reference)
);
```

#### booking_status_history
```sql
CREATE TABLE booking_status_history (
    id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    booking_id  BIGINT UNSIGNED NOT NULL,
    from_status VARCHAR(20) NULL,
    to_status   VARCHAR(20) NOT NULL,
    changed_by  BIGINT UNSIGNED NULL,               -- user_id
    reason      VARCHAR(500) NULL,
    created_at  TIMESTAMP NULL,

    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id)
);
```

---

### 3.6 Waitlist Tables [NEW]

#### waitlist_entries
```sql
CREATE TABLE waitlist_entries (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    customer_id     BIGINT UNSIGNED NOT NULL,
    service_id      BIGINT UNSIGNED NULL,
    staff_member_id BIGINT UNSIGNED NULL,            -- preferred staff (optional)
    preferred_date  DATE NULL,
    preferred_time_start TIME NULL,
    preferred_time_end   TIME NULL,
    status          ENUM('waiting', 'notified', 'booked', 'expired', 'cancelled') DEFAULT 'waiting',
    notified_at     TIMESTAMP NULL,
    expires_at      TIMESTAMP NULL,                  -- auto-expire after X days
    booking_id      BIGINT UNSIGNED NULL,            -- linked booking if converted
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE SET NULL,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_preferred_date (tenant_id, preferred_date)
);
```

---

### 3.7 Revenue Recovery & Campaign Tables [NEW]

#### campaigns
```sql
CREATE TABLE campaigns (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(255) NOT NULL,
    type            ENUM('reconnect', 'recover', 'refill', 'custom', 'social') NOT NULL,
    -- reconnect: inactive customer reactivation
    -- recover: missed booking / cancellation follow-up
    -- refill: open slot / capacity filling
    -- custom: manual campaign
    -- social: social media copy generation
    status          ENUM('draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled') DEFAULT 'draft',
    channel         ENUM('sms', 'email', 'both') DEFAULT 'both',
    audience_filter JSON NULL,
    -- {"status": "inactive", "inactive_days_min": 30, "inactive_days_max": 90,
    --  "min_visits": 2, "tags": ["VIP"], "services": [1, 3]}
    schedule_type   ENUM('immediate', 'scheduled', 'automated') DEFAULT 'immediate',
    scheduled_at    TIMESTAMP NULL,
    -- For automated campaigns:
    trigger_type    ENUM('inactive_days', 'missed_booking', 'cancellation', 'open_slot', 'no_show') NULL,
    trigger_config  JSON NULL,
    -- {"inactive_days": 30, "follow_up_delay_hours": 24, "max_attempts": 3}
    ai_generated    BOOLEAN DEFAULT FALSE,
    total_recipients INT UNSIGNED DEFAULT 0,
    total_sent      INT UNSIGNED DEFAULT 0,
    total_delivered  INT UNSIGNED DEFAULT 0,
    total_opened    INT UNSIGNED DEFAULT 0,
    total_clicked   INT UNSIGNED DEFAULT 0,
    total_converted INT UNSIGNED DEFAULT 0,          -- resulted in a booking
    estimated_revenue_recovered DECIMAL(12, 2) DEFAULT 0,
    started_at      TIMESTAMP NULL,
    completed_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_type_status (tenant_id, type, status)
);
```

#### campaign_messages
```sql
CREATE TABLE campaign_messages (
    id          BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    campaign_id BIGINT UNSIGNED NOT NULL,
    channel     ENUM('sms', 'email') NOT NULL,
    subject     VARCHAR(255) NULL,                   -- email subject
    body        TEXT NOT NULL,
    is_ai_generated BOOLEAN DEFAULT FALSE,
    -- Supports variables: {{first_name}}, {{business_name}}, {{service_name}},
    -- {{last_visit_date}}, {{booking_link}}, {{offer_details}}
    step_number INT DEFAULT 1,                       -- for multi-step sequences
    delay_hours INT DEFAULT 0,                       -- delay from previous step
    created_at  TIMESTAMP NULL,
    updated_at  TIMESTAMP NULL,

    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    INDEX idx_campaign_step (campaign_id, step_number)
);
```

#### campaign_recipients
```sql
CREATE TABLE campaign_recipients (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    campaign_id     BIGINT UNSIGNED NOT NULL,
    customer_id     BIGINT UNSIGNED NOT NULL,
    status          ENUM('pending', 'sent', 'delivered', 'opened', 'clicked', 'converted', 'failed', 'opted_out') DEFAULT 'pending',
    channel         ENUM('sms', 'email') NOT NULL,
    sent_at         TIMESTAMP NULL,
    delivered_at    TIMESTAMP NULL,
    opened_at       TIMESTAMP NULL,
    clicked_at      TIMESTAMP NULL,
    converted_at    TIMESTAMP NULL,                  -- when they booked
    booking_id      BIGINT UNSIGNED NULL,            -- the resulting booking
    current_step    INT DEFAULT 1,
    error_message   VARCHAR(500) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (campaign_id) REFERENCES campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_campaign_status (campaign_id, status),
    INDEX idx_customer (customer_id)
);
```

---

### 3.8 Reviews Table [NEW]

#### reviews
```sql
CREATE TABLE reviews (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    customer_id     BIGINT UNSIGNED NOT NULL,
    booking_id      BIGINT UNSIGNED NULL,            -- optionally linked to a booking
    staff_member_id BIGINT UNSIGNED NULL,
    rating          TINYINT NOT NULL,                -- 1-5
    title           VARCHAR(255) NULL,
    comment         TEXT NULL,
    is_published    BOOLEAN DEFAULT TRUE,
    is_flagged      BOOLEAN DEFAULT FALSE,
    business_reply  TEXT NULL,
    business_replied_at TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    FOREIGN KEY (staff_member_id) REFERENCES staff_members(id) ON DELETE SET NULL,
    INDEX idx_tenant (tenant_id),
    INDEX idx_rating (tenant_id, rating),
    INDEX idx_published (tenant_id, is_published)
);
```

---

### 3.9 Leads Table [NEW] (for Recover Module)

#### leads
```sql
CREATE TABLE leads (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    first_name      VARCHAR(100) NULL,
    last_name       VARCHAR(100) NULL,
    email           VARCHAR(255) NULL,
    phone           VARCHAR(20) NULL,
    service_id      BIGINT UNSIGNED NULL,            -- service they inquired about
    source          ENUM('website', 'phone', 'walk_in', 'social', 'referral', 'other') DEFAULT 'website',
    status          ENUM('new', 'contacted', 'follow_up', 'converted', 'lost') DEFAULT 'new',
    notes           TEXT NULL,
    converted_to_customer_id BIGINT UNSIGNED NULL,   -- if they became a customer
    converted_to_booking_id  BIGINT UNSIGNED NULL,   -- the resulting booking
    last_contacted_at TIMESTAMP NULL,
    follow_up_at    TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE SET NULL,
    FOREIGN KEY (converted_to_customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    FOREIGN KEY (converted_to_booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
    INDEX idx_tenant_status (tenant_id, status),
    INDEX idx_follow_up (tenant_id, follow_up_at)
);
```

---

### 3.10 AI & Communication Tables [NEW]

#### ai_generations
```sql
CREATE TABLE ai_generations (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    user_id         BIGINT UNSIGNED NOT NULL,         -- who triggered it
    type            ENUM('campaign_message', 'reply_suggestion', 'social_post', 'rewrite', 'recommendation') NOT NULL,
    model           VARCHAR(50) NOT NULL,             -- "gpt-4o-mini", "gpt-4o"
    prompt          TEXT NOT NULL,
    response        TEXT NOT NULL,
    tokens_used     INT UNSIGNED DEFAULT 0,
    input_tokens    INT UNSIGNED DEFAULT 0,
    output_tokens   INT UNSIGNED DEFAULT 0,
    cost_cents      INT UNSIGNED DEFAULT 0,           -- estimated cost in cents
    context         JSON NULL,                        -- any additional context sent
    created_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_tenant_type (tenant_id, type),
    INDEX idx_created (tenant_id, created_at)
);
```

#### notifications
```sql
CREATE TABLE notifications (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NULL,
    user_id         BIGINT UNSIGNED NULL,             -- recipient user
    customer_id     BIGINT UNSIGNED NULL,             -- recipient customer
    type            ENUM('booking_confirmation', 'booking_reminder', 'booking_cancellation', 'booking_rescheduled', 'waitlist_available', 'campaign', 'review_request', 'staff_assignment', 'system') NOT NULL,
    channel         ENUM('sms', 'email', 'in_app') NOT NULL,
    title           VARCHAR(255) NULL,
    body            TEXT NOT NULL,
    reference_type  VARCHAR(50) NULL,                -- 'booking', 'campaign', 'waitlist'
    reference_id    BIGINT UNSIGNED NULL,
    status          ENUM('pending', 'sent', 'delivered', 'failed') DEFAULT 'pending',
    sent_at         TIMESTAMP NULL,
    read_at         TIMESTAMP NULL,
    error_message   VARCHAR(500) NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL,
    INDEX idx_user (user_id, read_at),
    INDEX idx_customer (customer_id),
    INDEX idx_status (status)
);
```

#### sms_messages
```sql
CREATE TABLE sms_messages (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    to_number       VARCHAR(20) NOT NULL,
    from_number     VARCHAR(20) NULL,
    body            TEXT NOT NULL,
    direction       ENUM('outbound', 'inbound') DEFAULT 'outbound',
    status          ENUM('queued', 'sent', 'delivered', 'failed', 'received') DEFAULT 'queued',
    twilio_sid      VARCHAR(50) NULL,
    segments        INT UNSIGNED DEFAULT 1,
    cost_cents      INT UNSIGNED DEFAULT 0,
    reference_type  VARCHAR(50) NULL,                -- 'campaign', 'notification', 'booking'
    reference_id    BIGINT UNSIGNED NULL,
    error_code      VARCHAR(20) NULL,
    error_message   VARCHAR(500) NULL,
    sent_at         TIMESTAMP NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status),
    INDEX idx_twilio_sid (twilio_sid)
);
```

---

### 3.12 Message Templates Table [NEW]

#### message_templates
```sql
CREATE TABLE message_templates (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NULL,            -- NULL = system default template
    type            ENUM('booking_confirmation', 'booking_reminder', 'booking_cancellation',
                         'booking_rescheduled', 'review_request', 'waitlist_available',
                         'reconnect', 'recover', 'refill', 'welcome', 'staff_invite') NOT NULL,
    channel         ENUM('sms', 'email') NOT NULL,
    name            VARCHAR(255) NOT NULL,
    subject         VARCHAR(255) NULL,               -- email subject only
    body            TEXT NOT NULL,                    -- supports variables: {{first_name}}, {{business_name}}, etc.
    is_active       BOOLEAN DEFAULT TRUE,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_type (tenant_id, type, channel)
);

-- System default templates are seeded with tenant_id = NULL
-- Businesses can override by creating their own template for the same type
```

---

### 3.13 Usage Tracking Table [NEW]

#### usage_records
```sql
CREATE TABLE usage_records (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    tenant_id       BIGINT UNSIGNED NOT NULL,
    subscription_id BIGINT UNSIGNED NULL,
    type            ENUM('ai_generation', 'sms_sent', 'email_sent', 'campaign_run', 'customer_import') NOT NULL,
    quantity        INT UNSIGNED DEFAULT 1,
    billing_period_start DATE NOT NULL,
    billing_period_end   DATE NOT NULL,
    created_at      TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_period (tenant_id, billing_period_start, billing_period_end),
    INDEX idx_tenant_type (tenant_id, type)
);

-- Aggregate view for quick limit checks:
-- SELECT type, SUM(quantity) as total
-- FROM usage_records
-- WHERE tenant_id = ? AND billing_period_start = ? AND billing_period_end = ?
-- GROUP BY type;
```

---

### 3.14 Subscription Plan Limits [NEW]

#### plan_limits
```sql
CREATE TABLE plan_limits (
    id              BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    plan_id         BIGINT UNSIGNED NOT NULL,         -- FK -> SaaSykit plans table
    feature_key     VARCHAR(50) NOT NULL,
    -- Feature keys: 'ai_generations', 'sms_per_month', 'campaigns_per_month',
    --              'staff_members', 'services', 'customers', 'custom_branding',
    --              'social_post_generation', 'advanced_analytics', 'priority_support'
    limit_value     INT NULL,                         -- NULL = unlimited
    is_enabled      BOOLEAN DEFAULT TRUE,             -- for boolean features (custom_branding, etc.)
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (plan_id) REFERENCES plans(id) ON DELETE CASCADE,
    UNIQUE KEY idx_plan_feature (plan_id, feature_key)
);

-- Example data:
-- Starter ($49/mo): ai_generations=100, sms_per_month=200, campaigns=3, staff=2, customers=500
-- Growth ($99/mo):  ai_generations=500, sms_per_month=1000, campaigns=10, staff=10, customers=2000
-- Pro ($199/mo):    ai_generations=NULL, sms_per_month=5000, campaigns=NULL, staff=NULL, customers=NULL
```

---
