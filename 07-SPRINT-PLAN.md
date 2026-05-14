# Bluespond -- Sprint-by-Sprint Phase 2 Build Plan

**Project:** Bluespond
**Client:** Darell Parker
**Prepared by:** Mandeep Singh
**Phase:** 1 -- Discovery & Architecture

---

## 1. Build Overview

| Metric | Value |
|--------|-------|
| Hourly rate | $30/hour |
| Max hours per week | 40 hours |
| Weekly cost (max) | $1,200/week |
| Total sprints | 7 sprints (2 weeks each) |
| Total duration | 14 weeks (~3.5 months) |
| Sprint duration | 2 weeks each |
| Total estimated hours | 467 -- 533 hours |
| **Total estimated cost** | **$14,000 -- $16,000** |
| Payment structure | Weekly billing (timesheet-based) |
| Weekly demo calls | 15-20 min every Friday |

---

## 2. Sprint Breakdown

### Sprint 1: Foundation & Business Setup (Weeks 1-2)
**Milestone 1 -- "Business Can Sign Up and Set Up"**

| Task | Details |
|------|---------|
| SaaSykit environment setup | Local dev, AWS staging, database, Redis, S3 |
| Database migrations | All new tables from schema document |
| Extend User model | user_type, phone, avatar fields |
| Extend Tenant model | Business profile 1:1 relationship |
| Business categories | Model, migration, admin CRUD, seed data |
| Business profile CRUD | Name, address, phone, timezone, logo, cover image, social links |
| Business hours management | Weekly schedule, closed days |
| Blocked dates management | Holiday/vacation blocking |
| Subscription plan configuration | Starter/Growth/Pro with limits in admin panel |
| Plan limits system | plan_limits table, UsageService foundation |
| Business onboarding wizard | 4-step wizard: info, services, hours, publish |

**Deliverable:** A business owner can sign up, pay via Stripe, complete the onboarding wizard, and have a configured business profile.

**Estimated hours:** 60 -- 70 hours
**Estimated cost:** $1,800 -- $2,100

---

### Sprint 2: Services, Staff & Scheduling Engine (Weeks 3-4)
**Milestone 2 -- "Calendar Works"**

| Task | Details |
|------|---------|
| Service CRUD | Name, duration, price, description, category, image |
| Staff member CRUD | Name, title, email, avatar, bio |
| Staff schedule management | Per-day availability, breaks |
| Staff blocked times | Time-off management |
| Service-to-staff assignment | Pivot table, custom price/duration per staff |
| Staff invitation system | Email invite, account creation, role assignment |
| Time slot calculation algorithm | Core scheduling logic: business hours + staff schedules + existing bookings + buffers |
| Business calendar view | Day view and week view with color-coded bookings |
| Walk-in booking creation | Quick-add from calendar |
| Block time feature | Mark slots as unavailable |

**Deliverable:** Business can manage services and staff, view a working calendar, and create walk-in bookings. Time slot calculation engine works correctly.

**Estimated hours:** 65 -- 75 hours
**Estimated cost:** $1,950 -- $2,250

---

### Sprint 3: Customer Booking Flow & Customer Management (Weeks 5-6)

| Task | Details |
|------|---------|
| Public business profile page | Full public page with services, hours, social links, "Book Now" |
| Customer booking flow (5-step) | Service -> Staff -> Date -> Time -> Confirm |
| Guest checkout | Book without creating account |
| Booking conflict prevention | Database locking, 5-min hold |
| Booking reference numbers | Human-readable codes (BLU-XXXXXX) |
| Booking status workflow | Pending -> confirmed -> in progress -> completed / cancelled / no-show |
| Booking management (business side) | List view with filters, detail panel with actions |
| Customer records auto-creation | Create customer record on first booking |
| Customer list with search/filter | Filterable table: status, visits, tags |
| Customer detail view | History, notes, lifetime value, tags |
| Manual customer creation | Add customer from dashboard |
| CSV customer import | Upload, column mapping, validation, background processing |
| Token-based booking management link | Customer reschedule/cancel without login |

**Deliverable:** A customer can find a business, book an appointment through the full flow, receive confirmation, and manage their booking. Business sees the booking on their calendar and customer list.

**Estimated hours:** 75 -- 80 hours
**Estimated cost:** $2,250 -- $2,400

---

### Sprint 4: Notifications, Waitlist & Twilio Integration (Weeks 7-8)
**Milestone 3 -- "Bookings Work End-to-End"**

| Task | Details |
|------|---------|
| Twilio SMS integration | Send/receive, delivery tracking, webhook handling |
| Email notification system | Transactional emails via SES/Mailgun |
| Booking confirmation (SMS + email) | Sent on booking creation |
| Booking reminder (24h before) | Scheduled job, checks every 15 min |
| Booking cancellation notifications | To customer and business |
| Rescheduling notifications | To customer and business |
| Opt-out / TCPA compliance | STOP keyword handling, opt-out sync |
| SMS message logging | All messages stored with status tracking |
| Waitlist system | Join waitlist, auto-notify on slot open, time-limited hold |
| Waitlist management (business side) | View, manage waitlist entries |
| Customer rescheduling flow | From management link, select new date/time |
| Customer cancellation flow | With cancellation policy enforcement |
| No-show handling | Mark no-show, auto-update customer stats |
| Add to Calendar buttons | Google Calendar, Apple Calendar, .ics download |

**Deliverable:** Full booking lifecycle works including notifications. Waitlist operational. SMS delivery confirmed and tracked.

**Estimated hours:** 65 -- 75 hours
**Estimated cost:** $1,950 -- $2,250

---

### Sprint 5: Revenue Recovery Engine & Campaigns (Weeks 9-10)
**Milestone 4 -- "Revenue Recovery Works"**

| Task | Details |
|------|---------|
| Inactive customer detection | Daily cron job, configurable threshold |
| Reconnect campaign automation | Auto-create campaigns for inactive customers |
| Multi-step message sequences | Step 1, delay, Step 2, delay, Step 3 |
| Recover: cancellation follow-up | Triggered on booking cancellation |
| Recover: no-show follow-up | Triggered on no-show marking |
| Refill: open slot detection | Triggered on cancellation within 48h window |
| Refill: waitlist-first outreach | Notify waitlist before general outreach |
| Campaign builder UI | Type, audience filter, message, schedule |
| Audience filtering | Status, visits, tags, services, date ranges |
| Campaign scheduling (immediate + future) | Send now or schedule for later |
| Campaign analytics tracking | Sent, delivered, opened, clicked, converted |
| Conversion tracking | Link campaign recipient to resulting booking |
| Revenue recovered calculation | Sum of recovered booking prices |
| Custom/manual campaigns | Business creates ad-hoc campaigns |
| Campaign message templates with variables | {{first_name}}, {{business_name}}, {{booking_link}} |
| Usage tracking: SMS count | Per business per billing period |
| Usage tracking: campaign count | Per business per billing period |

**Deliverable:** All three recovery modules (Reconnect, Recover, Refill) operational. Campaign builder works. Revenue recovered metric calculated.

**Estimated hours:** 75 -- 80 hours
**Estimated cost:** $2,250 -- $2,400

---

### Sprint 6: AI Integration, Reviews & Social Posts (Weeks 11-12)

| Task | Details |
|------|---------|
| OpenAI integration | Service class, API connection, error handling |
| AI campaign message generation | Generate 2-3 variations based on context |
| AI social media post generation | Platform-specific: Instagram, Facebook, TikTok, Twitter |
| Context-aware AI prompts | Business info, customer data, campaign type fed to prompt |
| AI usage tracking | Per business per billing period |
| AI plan limit enforcement | Check limits before generation, show upgrade prompt |
| Review request system | Automated review request 2h after completed booking |
| Customer review submission | Token-secured page, star rating + text |
| Review display on public profile | Star average, review list, business replies |
| Business reply to reviews | From dashboard |
| Review moderation / flagging | Flag inappropriate reviews |
| Average rating calculation + caching | Update on new review, cache in business_profiles |
| Social media copy generation UI | Select type, context, platforms, generate, copy to clipboard |
| Usage limit warnings | Notify at 80% usage |

**Deliverable:** AI generates campaign messages and social posts. Reviews collected and displayed. Usage tracking enforced across all features.

**Estimated hours:** 60 -- 70 hours
**Estimated cost:** $1,800 -- $2,100

---

### Sprint 7: Dashboard, Marketplace, Polish & Launch (Weeks 13-14)
**Milestone 5 -- "Launch Ready"**

| Task | Details |
|------|---------|
| Business dashboard | Key metrics, charts, recovery insights, activity feed |
| Revenue Recovered KPI display | Primary metric, prominent placement |
| Bookings over time chart | Line chart, 30-day default |
| Revenue over time chart | Line chart with recovery overlay |
| Customer status breakdown chart | Active/inactive/lost |
| Campaign performance reports | Conversion funnel, per-campaign stats |
| Reports page (overview, recovery, bookings, customers) | Tabbed reports with date range filters |
| Marketplace / explore page | Browse businesses by category |
| Business search | By name, location, category |
| Category-based browsing | Category pages with business cards |
| Customer portal (bluespond.com/my) | View bookings, reschedule, cancel, rebook, reviews |
| Customer account management | Profile editing, notification preferences |
| Landing page customization | Hero, features, pricing, CTA with Bluespond branding |
| SEO setup | Meta tags, Open Graph, sitemap, structured data |
| Mobile responsiveness pass | All screens tested and fixed for mobile |
| Admin panel extensions | Platform metrics, business management, category management |
| Performance optimization | Query optimization, eager loading, cache warming |
| Security review | Input validation, rate limiting, tenant isolation audit |
| Bug fixes from demo feedback | Address all issues found during weekly demos |

**Deliverable:** Complete platform ready for production deployment and public launch.

**Estimated hours:** 67 -- 80 hours
**Estimated cost:** $2,000 -- $2,400

---
