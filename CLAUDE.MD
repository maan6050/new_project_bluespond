# Bluespond Project — Claude Code Instructions

You are working on **Bluespond**, a multi-tenant SaaS for **booking + revenue recovery**, built on top of **SaaSyKit**. These instructions apply to every session in this project.

---

## 1. Project Context (Read First)

- **Foundation:** SaaSyKit (multi-tenant Laravel SaaS starter)
- **Stack:** Laravel 13, PHP 8.4, Filament 5, Livewire 4, Tailwind CSS, DaisyUI, MySQL, Redis
- **Payments:** Stripe (primary), Paddle, Lemon Squeezy
- **Other integrations:** Twilio (SMS/WhatsApp), AWS S3, Resend/Mailgun/Postmark, Socialite, Spatie Permissions, Filament Breezy
- **Async/Ops:** Horizon (queues), Telescope (debugging)
- **Local dev:** Laragon — site runs at `http://saasykit-tenancy.test`, DB `saasykit_tenancy`
- **Source of truth:** numbered Markdown docs in repo root (e.g., `02-DATABASE-SCHEMA.md`, `07-SPRINT-PLAN.md`). **Always consult these before writing code.**
- **Architecture & current code conventions:** see `AGENTS.md` — it documents what actually exists today.

---

## 2. Core Coding Principles

Apply these to **every** file you write or modify:

### Reusable

- Build for a category of problems, not a single use case
- Extract logic appearing 2+ times into Services or Traits
- Name things by what they do, not where they're used
  - Good: `BookingAvailabilityService`, `formatCurrency()`
  - Bad: `DashboardBookingHelper`, `formatCartPagePrice()`

### Easy to Modify

- One class / method = one responsibility
- Methods under ~30 lines
- No magic numbers/strings — use config, enums, or named constants
- Comments explain *why*, not *what*
- Code should read like prose; avoid clever one-liners

### Flexible

- Accept config via constructor injection, method params, or `config/*.php`
- Provide sensible defaults
- Depend on **interfaces/contracts**, not concrete classes
- Design for extension (new payment provider, new notification channel) without rewriting existing code

### AI working agreement

For every code change in this project:

1. Follow the project's coding principles (Reusable, Easy to Modify, Flexible).
2. Before writing new code, check if existing utilities can be reused or extended.
3. After writing, review: can this be made more generic without over-engineering?
4. Flag any duplication you notice and suggest refactoring.
5. Explain design decisions briefly when introducing new patterns.

---

## Branding & Design Tokens (locked — use everywhere)

Every UI surface in this project — admin panel, owner dashboard, customer portal, public booking pages, marketing site, email templates — uses the Bluespond palette. **Never substitute SaaSykit purple, Filament default Amber, or any color outside this table.**

| Role | Hex | Tailwind | Where |
| --- | --- | --- | --- |
| Primary button / brand logo | `#1e3a8a` | `bg-blue-900` (hover `bg-blue-800`) | CTAs, header logo (deeper shade matches the Bluespond logo at button scale) |
| Primary link / accent | `#2563eb` | `text-blue-600` | Links, focus borders, info accents |
| Secondary / success | `#14b8a6` | `teal-500` (hover `teal-600`) | Step indicators, required asterisks, totals, success states |
| Sky accent | `#38bdf8` | `sky-400` | Highlights, decorative |
| Body text | `#1e293b` | `text-slate-800` | All body copy |
| Heading text | `#0f172a` | `text-slate-900` | h1–h3 |
| Muted text | — | `text-slate-500` | Helper text, captions |
| Card surface | `#ffffff` | `bg-white` | Cards, form backgrounds |
| Page background | `#f8fafc` | `bg-slate-50` | App / page background |
| Subtle / disabled bg | `#f1f5f9` | `bg-slate-100` | Dividers, disabled fields |
| Border | — | `border-slate-200` / `border-slate-300` | Default component borders |

**Typography:** Inter (Google Fonts) — `font-family: 'Inter', system-ui, -apple-system, sans-serif;`. Weights 400, 500, 600, 700.

**Aesthetic target:** Polished consumer app (think iPhone / Amazon checkout), not generic SaaS dashboard. Clean cards, generous whitespace, `rounded-lg` on form fields and `rounded-2xl` on cards, soft `shadow-sm` default.

**Forbidden:** SaaSykit purple `#6f27e5`, Filament default Amber, pure black for primary buttons, ad-hoc colors not in this table.

If a new token is genuinely needed, propose adding it here first — don't introduce one-off colors in component code.

---

## 3. Laravel + SaaSyKit Conventions

### Where things live

- **Models:** `app/Models/` — slim; only relationships, casts, scopes, small domain methods (e.g., `BusinessProfile::canPublish()`)
- **Services:** `app/Services/` — business logic (e.g., `TenantService`, `TenantCreationService`, `TenantPermissionService`). Bluespond domain services (Booking, Customer, Campaign, etc.) come in Sprint 2+ — when introduced, follow the same pattern.
- **Filament Resources:** `app/Filament/` — admin/tenant panel CRUD; reusable schema fragments live in `app/Filament/Schemas/`
- **Livewire Components:** `app/Livewire/` — interactive UI
- **Model Concerns / Traits:** `app/Models/Concerns/` — cross-model reusable behavior (e.g., `HasUniqueSlug`)
- **Migrations:** `database/migrations/` — never edit old migrations; always add new ones
- **Config:** `config/*.php` — all tunable values go here, not hardcoded
- *(`app/Actions/` — not currently used. If introduced, follow Laravel's invokable action pattern via `__invoke()`.)*

### Multi-tenancy is sacred

- **Every** tenant-scoped query MUST be tenant-aware. Filament resources do this via `getEloquentQuery()`; direct queries must filter by `tenant_id`.
- Never assume a model is global — check whether `tenant_id` exists.
- Use existing services: `TenantService`, `TenantCreationService`, `TenantPermissionService`, `TenantSubscriptionService`.

### Database

- **MySQL** for dev/prod; **SQLite** for tests (per `.env.testing`) — do not introduce other engines
- Migrations: descriptive names, always reversible (`down()` method)
- Always add FK constraints with appropriate `onDelete` behavior
- Indexes on FKs and frequently-queried columns
- Soft-delete is the project default for business-data tables (no hard delete) — use Laravel's `SoftDeletes` trait

### Code style

- Follow **PSR-12** + Laravel Pint defaults
- Type-hint everything (params, return types, properties)
- Use enums for fixed value sets (PHP 8.1+ backed enums)
- Validation:
  - **Form Requests** for controller endpoints
  - **Livewire `#[Validate]` attributes** for Livewire components (project's primary input pattern)
- Use policies for authorization, not inline checks

---

## 4. Before Writing Any Code, Check

1. **Does the relevant MD doc define this?** (`02-DATABASE-SCHEMA.md`, `07-SPRINT-PLAN.md`, etc.) — follow it.
2. **Does SaaSyKit already provide it?** Don't rebuild auth, billing, tenant scoping, etc.
3. **Does a service / trait / schema fragment already exist?** Reuse or extend instead of duplicating.
4. **Is this tenant-scoped?** If yes, ensure tenant isolation is enforced.
5. **Will this need to change per environment?** → config/env, not hardcoded.

---

## 5. Workflow Rules

- **Sprint discipline:** Confirm which sprint task this maps to before coding. If unclear, ask.
- **Migrations:** Run `php artisan migrate` after creating; show what changed.
- **No destructive commands without confirmation:** never run `migrate:fresh`, `db:wipe`, or delete files without explicit user OK.
- **Git:** Don't commit unless asked. When asked, write clear conventional commits (`feat:`, `fix:`, `refactor:`, etc.).
- **Testing:** When adding a feature with business logic, also write a feature/unit test in `tests/`.

---

## 6. Communication Style

The user prefers:

- **Concise, point-form answers** for status updates and reports
- **Tables** for comparisons (e.g., "what's done vs. what's left")
- **Step-by-step lists** for setup/testing flows
- **Clickable file paths** (e.g., `app/Services/BookingService.php:42`) when referencing code
- Short summaries suitable for forwarding to a senior (no fluff)

When the user asks "what's next?" → give one concrete next step, not a wall of options.

---

## 7. Avoid

- ❌ Copy-pasting logic between Filament Resources or Livewire components
- ❌ Hardcoding URLs, prices, plan IDs, feature flags
- ❌ Bypassing the tenant scope ("just this once")
- ❌ Editing existing migrations (always add a new one)
- ❌ Premature abstraction — follow the **Rule of Three**: refactor on the third repetition, not the first
- ❌ Adding new packages without checking if SaaSyKit/Laravel already ships the capability
- ❌ Deeply nested conditionals — use early returns and guard clauses
- ❌ God services — split when a service exceeds ~5 public methods or ~300 lines
- ❌ Hard-deleting business data — soft-delete is the default

---

## 8. When Suggesting Changes

- Flag duplication when you spot it, even if not asked to refactor
- Briefly explain design decisions (1–2 sentences)
- Prefer small, focused PRs over sweeping rewrites
- If a request conflicts with these principles, say so and propose the cleaner alternative

---

## 9. Useful Project Commands

```bash
# Local dev (Laragon)
php artisan serve                    # if not using Laragon's auto-host
php artisan migrate                  # apply pending migrations
php artisan migrate:status           # see what's run
php artisan tinker                   # REPL
php artisan horizon                  # queue worker dashboard
php artisan telescope:prune          # clean debug logs
npm run dev                          # Vite dev server
vendor/bin/pint --dirty              # format only changed files
```

---

**Remember:** Reusable. Easy to modify. Flexible. Every file, every time.
