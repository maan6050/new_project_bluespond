# Bluespond -- Technical Specification Document

**Project:** Bluespond
**Client:** Darell Parker
**Prepared by:** Mandeep Singh
**Phase:** 1 -- Discovery & Architecture
**Version:** 1.0

---

## 1. System Architecture Overview

```
                    +---------------------+
                    |   CUSTOMER BROWSER  |
                    | (Public booking pages|
                    |  Customer portal)   |
                    +----------+----------+
                               |
                    +----------v----------+
                    |    LOAD BALANCER    |
                    |                     |
                    +----------+----------+
                               |
              +----------------+----------------+
              |                                 |
    +---------v---------+             +---------v---------+
    |  WEB APPLICATION  |             |  BUSINESS DASHBOARD|
    | (Blade + Livewire)|             | (Filament Panels)  |
    +---+--------+------+             +---+--------+------+
        |        |                        |        |
        |   +----v----+                   |   +----v----+
        |   | QUEUE   |                   |   | ADMIN   |
        |   | WORKERS |                   |   | PANEL   |
        |   +---------+                   |   +---------+
        |                                 |
    +---v---------------------------------v---+
    |          LARAVEL APPLICATION             |
    |  (Services, Repositories, Models)       |
    +---+-------+-------+-------+-------+---+
        |       |       |       |       |
   +----v--+ +--v---+ +-v----+ +v-----+ +v--------+
   |MySQL/ | |Redis | |S3    | |Twilio| |OpenAI   |
   |Postgre| |Cache | |Media | |SMS   | |API      |
   +-------+ +------+ +------+ +------+ +---------+
                                   |
                              +----v----+
                              | Email   |
                              | Provider|
                              +---------+
```

### Panel Architecture

| Panel | URL | Framework | Users |
|-------|-----|-----------|-------|
| Admin Panel | `/admin` | Filament | Bluespond admins |
| Business Dashboard | `/dashboard` | Filament (tenant-scoped) | Business owners, managers, staff |
| Customer Portal | `/my` | Blade + Livewire | Customers |
| Public Pages | `/b/{slug}`, `/explore` | Blade + Livewire | Anyone (public) |
| Marketing Site | `/` | Blade + Livewire | Anyone (public) |

Note: Above is just an example, can be changed to any preferred slug if any.
---

## 2. Tech Stack

### Core
| Component | Technology | Version | Rationale |
|-----------|------------|---------|-----------|
| Backend | Laravel | 13 | SaaSykit foundation |
| PHP | PHP | 8.4+ | SaaSykit requirement |
| Admin/Dashboard | Filament PHP | Latest | SaaSykit foundation, rapid CRUD |
| Frontend | Livewire + Alpine.js | Latest | TALL stack, SaaSykit native |
| CSS | Tailwind CSS + DaisyUI | Latest | SaaSykit native |
| Build | Vite | Latest | SaaSykit native |
| Database | MySQL 8.0 or PostgreSQL 15 | | Row-based tenancy, production-ready |
| Cache | Redis | 7+ | Sessions, cache, queues |
| File Storage | AWS S3 | | Media uploads (logos, images) |
| Search | Laravel Scout + Meilisearch | | Marketplace business search (Phase 2 enhancement) |

### Third-Party Services
| Service | Provider | Purpose |
|---------|----------|---------|
| Payments | Stripe | Subscription billing, Apple Pay, Google Pay |
| SMS | Twilio | Booking notifications, campaign messages, 2FA |
| Email | Amazon SES or Mailgun | Transactional + campaign emails |
| AI | OpenAI (GPT-4o-mini / GPT-4o) (OR any other preferred model) | Message generation, recommendations |
| Calendar | Google Calendar API | Sync bookings (future enhancement) |
| Analytics | Laravel-native | No third-party dependency at launch |

---

## 3. Multi-Tenancy Architecture

### Approach: Single-Database, Row-Based Isolation

```
tenants (SaaSykit) = Businesses on Bluespond
  |
  +-- business_profiles (1:1 extension)
  +-- staff_members (scoped by tenant_id)
  +-- services (scoped by tenant_id)
  +-- customers (scoped by tenant_id)
  +-- bookings (scoped by tenant_id)
  +-- campaigns (scoped by tenant_id)
  +-- reviews (scoped by tenant_id)
  +-- ... all business data scoped by tenant_id
```
---

## 4. Authentication & Authorization

### User Types
```
users.user_type = ENUM('business_owner', 'staff', 'customer', 'admin')
```

### Authentication Flow

| User Type | Auth Method | Panel Access |
|-----------|-------------|--------------|
| Admin | Email/password, 2FA | `/admin` |
| Business Owner | Email/password, Social login, 2FA | `/dashboard` |
| Staff | Email/password (invited) | `/dashboard` (limited) |
| Customer | Email/password, Social login, Guest checkout | `/my`, Public booking pages |

### Role-Based Permissions (Per Tenant)

| Permission | Owner | Manager | Staff |
|------------|-------|---------|-------|
| View dashboard | Yes | Yes | Yes (own bookings) |
| Manage services | Yes | Yes | No |
| Manage staff | Yes | Yes | No |
| Manage bookings | Yes | Yes | Yes (own only) |
| View all bookings | Yes | Yes | No |
| Manage customers | Yes | Yes | View only |
| Create campaigns | Yes | Yes | No |
| View analytics | Yes | Yes | No |
| Manage billing | Yes | No | No |
| Manage settings | Yes | Limited | No |
| Reply to reviews | Yes | Yes | No |
| Use AI generation | Yes | Yes | Yes (limited) |

---

## 5. API Architecture

### Internal API (Livewire / Filament)
Primary data flow uses Livewire components and Filament resources -- no separate REST API needed for the web application.

### Public Booking API (Future: Extension / Mobile)
RESTful endpoints for potential mobile app or browser extension integration:

```
POST   /api/v1/bookings                    Create booking
GET    /api/v1/bookings/{reference}         Get booking by reference
PATCH  /api/v1/bookings/{reference}/cancel  Cancel booking
PATCH  /api/v1/bookings/{reference}/reschedule  Reschedule booking

GET    /api/v1/businesses/{slug}            Public business profile
GET    /api/v1/businesses/{slug}/services   Available services
GET    /api/v1/businesses/{slug}/availability  Available time slots
GET    /api/v1/businesses/{slug}/reviews    Business reviews

POST   /api/v1/webhooks/stripe             Stripe webhooks
POST   /api/v1/webhooks/twilio             Twilio status callbacks
```

### Webhook Endpoints
```
POST   /api/payments-providers/stripe/webhook   (SaaSykit existing)
POST   /api/webhooks/twilio/status              SMS delivery status
POST   /api/webhooks/twilio/inbound             Inbound SMS (future)
```

---

## 6. Booking & Scheduling Engine

### Time Slot Calculation Algorithm

```
function getAvailableSlots(business, service, staff, date):

    1. Get business hours for the day_of_week
       - If business is closed that day -> return empty
       - Check business_blocked_dates -> if blocked, return empty

    2. Get staff schedule for the day_of_week
       - If specific staff selected: use their schedule
       - If "any staff": get union of all assigned staff schedules

    3. Get existing bookings for the staff on that date
       - Include buffer times

    4. Get staff blocked times for that date

    5. Calculate available windows:
       available = staff_schedule - existing_bookings - blocked_times - breaks

    6. Slice available windows into slots based on service duration + buffer:
       for each window in available:
           slot_start = window.start
           while slot_start + service.duration + service.buffer <= window.end:
               slots.add(slot_start)
               slot_start += interval (default 15 min increments)

    7. Filter out slots in the past (if date is today)

    8. Return sorted list of available start times
```


## 7. Revenue Recovery Engine

### Architecture: Event-Driven + Scheduled

```
+------------------+     +------------------+     +------------------+
|  EVENT TRIGGERS  |     | RECOVERY ENGINE  |     |  COMMUNICATION   |
|                  |     |                  |     |     LAYER        |
| - Booking cancel |---->| - Match rules    |---->| - Twilio SMS     |
| - No-show detect |     | - Find audience  |     | - Email          |
| - Inactive detect|     | - Queue messages |     | - Track delivery |
| - Slot opens     |     | - Enforce limits |     | - Track response |
+------------------+     +--------+---------+     +------------------+
                                  |
                         +--------v---------+
                         |  TRACKING &      |
                         |  ANALYTICS       |
                         | - Conversions    |
                         | - Revenue calc   |
                         | - ROI reporting  |
                         +------------------+
```

## 8. AI Integration (OpenAI)

### Service Architecture

### Model Recommendation (Can be changed to any preferred model)

| Use Case | Model | Reason |
|----------|-------|--------|
| Campaign SMS messages | GPT-4o-mini | Short, templated, high volume, cost-sensitive |
| Campaign email messages | GPT-4o-mini | Good quality, cost-effective |
| Social media posts | GPT-4o | Needs creativity, public-facing, lower volume |
| Personalized recommendations | GPT-4o | Complex context, needs nuance |
| Message rewrites/edits | GPT-4o-mini | Simple transformation task |

---

## 9. Communication System

### SMS (Twilio)

### Email
- Transactional emails via Laravel Mail (SaaSykit existing infrastructure)
- Campaign emails via queued bulk sender
- Email templates stored in database (editable per campaign)
- Supports variables: {{first_name}}, {{business_name}}, etc.

### Notification Types & Channels

| Notification | SMS | Email | In-App |
|-------------|-----|-------|--------|
| Booking confirmation | Yes | Yes | - |
| Booking reminder (24h before) | Yes | Yes | - |
| Booking cancellation | Yes | Yes | - |
| Waitlist slot available | Yes | Yes | - |
| Review request | Yes | Yes | - |
| Recovery campaign message | Config | Config | - |
| Staff: new booking assigned | - | Yes | Yes |
| Business: new review | - | Yes | Yes |
| Usage limit warning (80%) | - | Yes | Yes |
| Usage limit reached | - | Yes | Yes |

### Email Templates to Build (~13)
1. Welcome email (new business signup)
2. Booking confirmation
3. Booking reminder (24h before)
4. Booking cancellation
5. Booking rescheduled
6. Review request (post-appointment)
7. Waitlist slot available
8. Staff invitation
9. Reconnect campaign (inactive customer)
10. Recover campaign (missed booking)
11. Refill campaign (open slot)
12. Usage limit warning (80%)
13. Usage limit reached

### SMS Templates to Build (~7)
1. Booking confirmation
2. Booking reminder (24h before)
3. Booking cancellation
4. Waitlist slot available
5. Reconnect campaign
6. Recover campaign
7. Refill campaig
---

## 10. Subscription & Usage Tracking

### Plan Configuration

| Feature | Starter ($49) | Growth ($99) | Pro ($199) |
|---------|--------------|--------------|------------|
| Staff members | 2 | 10 | Unlimited |
| Services | 10 | 50 | Unlimited |
| Customers | 500 | 2,000 | Unlimited |
| AI generations/mo | 100 | 500 | Unlimited |
| SMS messages/mo | 200 | 1,000 | 5,000 |
| Campaigns/mo | 3 | 10 | Unlimited |
| Social post generation | No | Yes | Yes |
| Custom branding | No | Yes | Yes |
| Advanced analytics | No | No | Yes |
| Priority support | No | No | Yes |
---

## 11. Marketplace & Public Pages

### Public Business Profile Page
**URL:** `bluespond.com/b/{slug}`

Publicly accessible page showing:
- Business info, logo, cover image
- Services with prices and durations
- Business hours
- Star rating + review count
- Recent reviews
- Social media links
- "Book Now" CTA button
- Staff members (optional display)
- Location map (if address provided)

### Marketplace / Directory
**URL:** `bluespond.com/explore`

- Browse businesses by category
- Search by name, location, service
- Filter by: category, rating, location
- Sort by: rating, distance, newest
- Featured businesses (admin-controlled)
- SEO-optimized category pages: `bluespond.com/explore/barber-shops`

### SEO Strategy
- Each business profile: unique meta title, description, Open Graph tags
- Category pages with structured data (LocalBusiness schema)
- Sitemap auto-generation (SaaSykit blog system pattern extended)
- Clean URLs with business slugs

---

## 12. Ratings & Reviews

### Review Collection
- Automated review request 2 hours after booking completion
- Token-secured review page (no login required)
- 1-5 star rating + optional text
- One review per booking per customer
- Business can reply to reviews

### Review Display
- Average rating calculated and cached on `business_profiles.average_rating`
- Displayed on public profile and marketplace listings
- Sorted by newest by default
- Filterable by rating on business profile
- Review count cached on `business_profiles.total_reviews`

### Moderation
- Reviews published immediately by default
- Business can flag inappropriate reviews
- Admin can moderate flagged reviews
- No editing reviews after 7 days

---

## 13. File Storage & Media

### AWS S3 Structure (Or any other preferred storage)

### Image Processing
- Resize on upload: logo (200x200), cover (1200x400), service (600x400)
- WebP conversion for performance
- Use SaaSykit's image-pipeline package

---

## 14. Caching Strategy

| Data | Cache Driver | TTL | Invalidation |
|------|-------------|-----|-------------|
| Business profile (public) | Redis | 1 hour | On profile update |
| Available time slots | Redis | 5 minutes | On booking create/cancel |
| Plan limits | Redis | 24 hours | On plan update |
| Usage counts | Redis | 15 minutes | On usage record create |
| Category list | Redis | 24 hours | On category update |
| Review averages | Redis | 1 hour | On review create/update |
| Marketplace listings | Redis | 30 minutes | On profile update |

---

## 15. Queue & Background Jobs

### Queue Configuration

### Key Background Jobs

| Job | Queue | Schedule |
|-----|-------|----------|
| `DetectInactiveCustomers` | low | Daily at 2:00 AM UTC |
| `ProcessReconnectCampaigns` | default | Daily at 8:00 AM business local time |
| `SendBookingReminders` | high | Every 15 minutes (check for bookings 24h away) |
| `ProcessCampaignStep` | default | Every 5 minutes (send next message in sequence) |
| `CalculateRevenueMetrics` | low | Daily at 3:00 AM UTC |
| `CheckUsageWarnings` | low | Daily at 9:00 AM UTC |
| `CleanupExpiredWaitlist` | low | Daily at 4:00 AM UTC |
| `UpdateBusinessRatings` | low | On review create (dispatched) |
| `ProcessCsvImport` | low | On upload (dispatched) |
| `SendReviewRequests` | default | Every 30 minutes (check completed bookings >2h ago) |

---

## 16. Security Considerations

### Data Protection
- All PII encrypted at rest (Laravel's `encrypted` cast for sensitive fields)
- HTTPS enforced everywhere
- CSRF protection on all forms (Laravel default)
- Rate limiting on public API endpoints and booking forms
- SQL injection prevention (Eloquent ORM parameterized queries)
- XSS prevention (Blade auto-escaping)

### Booking Security
- Token-based booking management links (no login required, unique per booking)
- Token expiry after 30 days
- Rate limit: max 10 bookings per IP per hour (prevent spam)

### API Security
- Stripe webhook signature verification (SaaSykit existing)
- Twilio request validation
- OpenAI API key stored in environment variables, never exposed to frontend
- CORS configuration for public API endpoints

---

## 17. Performance & Scalability

### Database Optimization
- Indexes on all foreign keys and frequent query columns (see Schema document)
- Composite indexes for common filter combinations
- Booking queries optimized with date-range indexes
- Customer search with indexed email/phone columns

### Application Performance
- Livewire lazy loading for dashboard components
- Pagination on all list views (20-50 items per page)
- Eager loading to prevent N+1 queries
- Image lazy loading on public pages
---
