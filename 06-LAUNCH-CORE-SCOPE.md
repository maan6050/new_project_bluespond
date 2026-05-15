# Bluespond -- Launch Core Scope Definition

**Project:** Bluespond
**Client:** Darell Parker
**Prepared by:** Mandeep Singh
**Phase:** 1 -- Discovery & Architecture

---

## 1. What is Launch Core?

Launch Core is the MVP version of Bluespond -- the minimum set of features needed to launch a working product that businesses can sign up for, use to manage bookings, and benefit from revenue recovery. Everything listed here must work end-to-end before launch.

---

## 2. Launch Core Feature List

### Module 1: Platform Foundation
| Feature | Priority | Status |
|---------|----------|--------|
| SaaSykit setup and configuration | Must Have | Exists (extend) |
| Business registration + onboarding wizard | Must Have | Build |
| Authentication (email, social login, 2FA) | Must Have | Exists (keep) |
| Role-based access (Owner, Manager, Staff) | Must Have | Exists (extend) |
| Subscription billing (Stripe + Apple Pay + Google Pay) | Must Have | Exists (configure) |
| Three pricing tiers (Starter/Growth/Pro, admin-editable) | Must Have | Exists (configure) |
| Usage tracking + plan limit enforcement | Must Have | Build |
| Admin panel (platform management) | Must Have | Exists (extend) |

### Module 2: Business Profile & Setup
| Feature | Priority | Status |
|---------|----------|--------|
| Business profile creation & editing | Must Have | Build |
| Business hours management (weekly schedule) | Must Have | Build |
| Blocked dates / holidays | Must Have | Build |
| Logo & cover image upload | Must Have | Build |
| Social media links | Must Have | Build |
| Public business profile page | Must Have | Build |
| Business category system | Must Have | Build |
| Timezone support | Must Have | Build |

### Module 3: Service & Staff Management
| Feature | Priority | Status |
|---------|----------|--------|
| Service CRUD (name, duration, price, description) | Must Have | Build |
| Service categories (grouping) | Should Have | Build |
| Staff member CRUD | Must Have | Build |
| Staff schedule management (per-day availability) | Must Have | Build |
| Staff break times | Must Have | Build |
| Staff blocked times (time off) | Must Have | Build |
| Service-to-staff assignment | Must Have | Build |
| Staff invitation via email | Should Have | Build |

### Module 4: Booking & Scheduling Engine
| Feature | Priority | Status |
|---------|----------|--------|
| Time slot calculation algorithm | Must Have | Build |
| Customer-facing booking flow (5-step) | Must Have | Build |
| Guest checkout (no account required) | Must Have | Build |
| Booking conflict prevention (DB locking) | Must Have | Build |
| Booking confirmation (SMS + email) | Must Have | Build |
| Booking reminders (24h before) | Must Have | Build |
| Booking management (business side calendar + list view) | Must Have | Build |
| Day / Week view calendar | Must Have | Build |
| Walk-in booking creation | Must Have | Build |
| Booking status workflow (pending > confirmed > completed) | Must Have | Build |
| Booking cancellation (customer + business) | Must Have | Build |
| Booking rescheduling | Must Have | Build |
| No-show marking | Must Have | Build |
| Booking reference numbers | Must Have | Build |
| Customer booking management link (token-based) | Must Have | Build |
| Deposit/prepayment collection | Nice to Have | Build |
| Add to Calendar (Google/Apple/.ics) | Should Have | Build |

### Module 5: Customer Management
| Feature | Priority | Status |
|---------|----------|--------|
| Customer records (auto-created on booking) | Must Have | Build |
| Customer list with search and filter | Must Have | Build |
| Customer detail view (history, notes, value) | Must Have | Build |
| Customer status tracking (active/inactive/lost) | Must Have | Build |
| Inactive customer auto-detection | Must Have | Build |
| CSV customer import | Must Have | Build |
| Manual customer creation | Must Have | Build |
| Customer tags | Should Have | Build |
| Lifetime value calculation | Should Have | Build |

### Module 6: Waitlist
| Feature | Priority | Status |
|---------|----------|--------|
| Join waitlist when no slots available | Must Have | Build |
| Auto-notify when slot opens | Must Have | Build |
| Time-limited hold on waitlist offers | Should Have | Build |
| Waitlist management (business side) | Must Have | Build |

### Module 7: Revenue Recovery -- Reconnect
| Feature | Priority | Status |
|---------|----------|--------|
| Inactive customer detection (configurable threshold) | Must Have | Build |
| Automated reconnect campaign creation | Must Have | Build |
| Multi-step message sequence | Must Have | Build |
| SMS + email delivery | Must Have | Build |
| Auto-stop on customer conversion | Must Have | Build |
| Conversion tracking (campaign -> booking) | Must Have | Build |

### Module 8: Revenue Recovery -- Recover
| Feature | Priority | Status |
|---------|----------|--------|
| Cancellation follow-up (automated) | Must Have | Build |
| No-show follow-up (automated) | Must Have | Build |
| Lead pipeline (inquired but didn't book) | Must Have | Build |
| Lead status tracking (new, contacted, converted, lost) | Must Have | Build |
| Configurable delay before outreach | Must Have | Build |
| Multi-step recovery sequence | Should Have | Build |

### Module 9: Revenue Recovery -- Refill
| Feature | Priority | Status |
|---------|----------|--------|
| Open slot detection on cancellation | Must Have | Build |
| Targeted outreach to fill slots | Must Have | Build |
| Waitlist integration (notify waitlist first) | Must Have | Build |
| Time-sensitive messaging (within 48h window) | Must Have | Build |

### Module 10: Campaign Tools
| Feature | Priority | Status |
|---------|----------|--------|
| Campaign builder (type, audience, message, schedule) | Must Have | Build |
| Audience filtering (status, visits, tags, services) | Must Have | Build |
| SMS campaign sending (via Twilio) | Must Have | Build |
| Email campaign sending | Must Have | Build |
| Campaign analytics (sent, delivered, converted) | Must Have | Build |
| Automated campaigns (trigger-based) | Must Have | Build |
| Manual/custom campaigns | Must Have | Build |
| Message templates with variables | Must Have | Build |
| Campaign scheduling (future send) | Should Have | Build |

### Module 11: AI-Assisted Messaging
| Feature | Priority | Status |
|---------|----------|--------|
| AI message generation for campaigns (OpenAI) | Must Have | Build |
| Multiple message variations (2-3 options) | Must Have | Build |
| Context-aware prompts (business, customer, service) | Must Have | Build |
| Social media post copy generation | Must Have | Build |
| AI usage tracking per business per billing cycle | Must Have | Build |
| Plan-based AI limit enforcement | Must Have | Build |

### Module 12: Ratings & Reviews
| Feature | Priority | Status |
|---------|----------|--------|
| Automated review request (post-booking) | Must Have | Build |
| Customer review submission (star + text) | Must Have | Build |
| Review display on public profile | Must Have | Build |
| Business reply to reviews | Must Have | Build |
| Average rating calculation + display | Must Have | Build |
| Review moderation (flag inappropriate) | Should Have | Build |

### Module 13: Dashboard & Reporting
| Feature | Priority | Status |
|---------|----------|--------|
| Business dashboard with key metrics | Must Have | Build |
| Revenue recovered KPI (primary metric) | Must Have | Build |
| Bookings over time chart | Must Have | Build |
| Revenue over time chart | Must Have | Build |
| Customer status breakdown | Must Have | Build |
| Recovery insights & alerts | Must Have | Build |
| Campaign performance reports | Must Have | Build |
| Booking reports (by date, service, staff) | Should Have | Build |

### Module 14: Marketplace
| Feature | Priority | Status |
|---------|----------|--------|
| Business directory / browse page | Must Have | Build |
| Category-based browsing | Must Have | Build |
| Search by name / location | Must Have | Build |
| Featured businesses (admin-controlled) | Should Have | Build |
| SEO-optimized business pages | Should Have | Build |

### Module 15: Customer Portal
| Feature | Priority | Status |
|---------|----------|--------|
| Customer account creation | Must Have | Build |
| View upcoming & past bookings | Must Have | Build |
| Reschedule / cancel from portal | Must Have | Build |
| Rebook from past bookings | Should Have | Build |
| Favorite businesses | Nice to Have | Build |
| Notification preferences | Should Have | Build |

### Module 16: Notifications & Communications
| Feature | Priority | Status |
|---------|----------|--------|
| Twilio SMS integration (booking + campaigns) | Must Have | Build |
| Email transactional (booking confirmations, etc.) | Must Have | Build |
| Opt-out / unsubscribe handling (TCPA compliance) | Must Have | Build |
| SMS delivery status tracking | Must Have | Build |
| Communication log | Should Have | Build |

### Module 17: Marketing Site
| Feature | Priority | Status |
|---------|----------|--------|
| Landing page (hero, features, pricing, CTA) | Must Have | Extend (SaaSykit) |
| Pricing page | Must Have | Extend (SaaSykit) |
| Blog (SEO content) | Should Have | Exists (keep) |
| "Recover lost revenue automatically" messaging | Must Have | Build |

---

## 3. Explicitly OUT OF SCOPE for Launch Core

| Feature | Reason | Phase |
|---------|--------|-------|
| Gift cards | Client deferred | Post-launch |
| Stripe Connect (marketplace payouts to businesses) | Client deferred | Phase 3+ |
| Mobile app / PWA | Post-MVP | Phase 3+ |
| Google Calendar sync | Integration phase | Phase 3 |
| Direct integrations (Booksy, theCut, Square, etc.) | Integration phase | Phase 3+ |
| Zapier / Make integration | Integration phase | Phase 3 |
| Skilled trades vertical UI customization | Schema supports it; no UI yet | Phase 3 |
| A/B testing for campaigns | Advanced feature | Phase 3 |
| Multi-language support | Post-launch | Phase 3+ |
| Payment processing for businesses (accepting customer payments) | Complex (Stripe Connect) | Phase 3 |
| Video consultations | Not in scope | TBD |
| Loyalty / rewards program | Not in scope | TBD |
| Advanced AI recommendations | Enhancement | Phase 3 |

---

## 4. Launch Core Success Criteria

The MVP is ready for launch when:

1. A business can sign up, choose a plan, and complete onboarding in under 10 minutes
2. A customer can discover a business, book an appointment, and receive confirmation
3. The business can view and manage bookings from a calendar view
4. Inactive customers are auto-detected and reactivation campaigns can be launched
5. Cancelled bookings trigger recovery follow-ups
6. Open slots from cancellations trigger refill outreach
7. The "Revenue Recovered" metric is calculated and displayed on the dashboard
8. AI generates campaign messages with one click
9. Ratings and reviews are collected and displayed on public profiles
10. Usage limits are enforced per subscription tier
11. The platform works on mobile and desktop
12. The marketplace allows customers to browse and discover businesses

---
