# Mathlin Booking System

A comprehensive WordPress venue booking plugin built for Needham Market Scout Group, with Home Assistant integration.

**Current Version:** 3.0.0  
**Requires WordPress:** 5.0+  
**Requires PHP:** 7.4+  
**Tested with WordPress:** 6.7  
**License:** GPL-2.0+

---

## Features

### Public Booking System
- Interactive availability calendar with blocked date indicators
- Booking form with real-time cost calculation (tier-aware)
- Multi-day and full-day booking support
- Recurring weekly bookings (up to 52 weeks)
- Conflict detection prevents double bookings (including parent/child space bundling)
- Configurable minimum notice period
- Custom form fields (admin-configurable)
- Terms & Conditions checkbox
- Public/private event visibility toggle
- Scout Use (free) bookings for volunteers
- Honeypot spam prevention on booking form
- Mobile-optimised responsive design
- Deposit amount shown on booking form when applicable
- Venue info page with configurable booking notice and facilities

### Customer Accounts (Hirer Portal)
- Self-service registration and login (`[mathlin_portal]`)
- Dashboard showing all bookings, invoices, payment status
- Pre-filled booking form for returning hirers
- Quick account creation after first booking
- Booking status lookup (`[mathlin_status]`)
- Modification and cancellation requests

### Admin Dashboard
- Bookings list with search, filter, and CSV export
- Editable booking details with live cost recalculation
- Custom price override for ad-hoc arrangements
- Calendar view with day-by-day breakdown
- Recurring bookings grouped as collapsible series
- Booking analytics with Chart.js visualisations
- Occupancy/utilisation reports per space
- Audit log tracking all actions with user and timestamp
- Dashboard widget on wp-admin home
- Financial balance indicator (Balance Due / Refund Due) on booking detail
- Mark Balance Paid / Mark Refunded action buttons

### Financial
- Configurable spaces and pricing (hourly + daily rates)
- **Tiered pricing** (Standard, Community, Commercial — configurable multipliers)
- **Deposit management** (configurable percentage, balance due window)
- Kitchen add-on pricing
- WooCommerce payment integration (Stripe, PayPal, etc.)
- Deposit-aware WooCommerce checkout (pays deposit or full balance)
- Invoice generation with HTML email attachment
- Automatic payment chasing (3 escalating levels + deposit balance reminders)
- Accounting export (Xero, Sage, QuickBooks CSV)
- Bank transfer details on invoices and modification emails
- Financial year revenue tracking (April–March)

### Modification & Cancellation Requests
- Bookers can request changes via secure link
- Proper approval queue with pending count badge
- Admin approve/reject with one click
- Auto-applies changes on approval (with conflict check)
- **Auto-confirm on approval** (no drop to pending)
- Smart status transition: keeps `paid` if cost decreased, sets `confirmed` if cost increased
- Approval email includes BACS details + Pay Now button when balance due
- Updated invoice attached to all modification approval emails
- Rejection emails with optional reason

### Email System
- 14 editable email templates with placeholder tags
- Configurable organisation name, address, logo
- Email queue with automatic retry on failure
- Booking reminders (configurable hours before)
- Multi-admin notifications
- Recurring booking summary emails
- Booking notice preserves line breaks (nl2br)

### Space Bundling (Parent/Child)
- Spaces can be defined as parent or child
- Booking a parent space blocks all child spaces
- Booking a child space blocks the parent space
- Configured via "Parent Space" column in admin settings

### Integrations
- Home Assistant: webhook on confirmation + REST API polling
- WooCommerce: Pay Now button in emails, auto-status update, deposit support
- iCal: downloadable .ics files + subscribable calendar feed
- GitHub: auto-update from private repository releases
- OSM (Online Scout Manager): push financial records on payment

### Security & Privacy
- GDPR right-to-erasure (WordPress Privacy tools integration)
- PII anonymisation preserving financial audit trail
- WooCommerce refund detection and status revert
- IP address logging without trusting proxy headers
- Email queue auto-cleanup of stalled PII-containing entries
- Role-based access control (hirer, booking manager, admin)

---

## Installation

1. Upload `mathlin-booking/` to `/wp-content/plugins/`
2. Activate in wp-admin → Plugins
3. Database tables are created automatically

## Shortcodes

| Shortcode | Description |
|---|---|
| `[mathlin_booking]` | Full calendar + booking form |
| `[mathlin_calendar]` | Calendar only (no form) |
| `[mathlin_status]` | Booking status lookup + modification form |
| `[mathlin_portal]` | Hirer login/register + dashboard |
| `[mathlin_terms]` | Terms & Conditions page |
| `[mathlin_venue_info]` | Venue information, pricing, facilities |

## REST API

Base: `/wp-json/mathlin/v1/`

| Endpoint | Auth | Description |
|---|---|---|
| `GET /bookings/today` | None | Today's confirmed bookings |
| `GET /bookings/upcoming` | None | Next 30 days |
| `GET /bookings/calendar?year=&month=` | None | Booked dates |
| `GET /bookings/{ref}/ical` | None | iCal download |
| `GET /bookings/ical` | None | iCal feed |
| `GET /bookings` | Admin | All bookings |
| `POST /bookings/{ref}/status` | Admin | Update status |
| `GET /bookings/{ref}/payment-url` | Admin | Generate payment URL |

---

## Changelog

### v3.0.0 (Major Feature Release)
- **New: Deposit Management** — configurable deposit percentage (default 25%), balance due window (default 7 days before event), new `deposit_paid` status, WooCommerce deposit-aware checkout, balance chase reminders
- **New: Tiered Pricing** — Standard/Community/Commercial tiers with configurable multipliers, tier-specific rates per space, user profile tier assignment, all 4 cost calculators updated
- **New: Space Bundling** — parent/child space relationships, booking a parent blocks children and vice versa, configured via settings UI
- New DB columns: `deposit_paid`, `pricing_tier`
- Admin settings: Deposit Management card, Pricing Tiers card, Parent Space column on spaces table
- User profile: Pricing Tier dropdown (admin-editable)

### v2.15.3
- **Fix:** TinyMCE triggerSave before collecting form values — booking notice and facilities now persist on save
- **Fix:** Version bump ensures browser cache-busting of admin.js
- **Fix:** Booking notice preserves line breaks (nl2br) on venue info page and booking form

### v2.15.2
- **New:** Configurable Booking Notice field (displayed on booking form + venue info page)
- **New:** Configurable Facilities WYSIWYG field (displayed on venue info page)
- **New:** Make a Booking / Book the Hall Now buttons on venue info page
- **Fix:** Default venue capacity changed from 80 to 100

### v2.15.1
- Full 32-point Terms & Conditions, T&C in emails and admin view

### v2.15.0
- **New:** Venue & Legal settings section
- **New:** `[mathlin_terms]` shortcode for Terms & Conditions page
- **New:** `[mathlin_venue_info]` shortcode for venue information page

### v2.14.0
- **New:** Read-only calendar mode for `[mathlin_calendar]` shortcode
- **New:** Unified `[mathlin_manage]` shortcode

### v2.13.2
- Booking managers now see "My Hall Bookings" tab in WooCommerce My Account

### v2.13.1
- **Fix:** Booking managers no longer blocked from wp-admin by WooCommerce
- **Fix:** Manager login redirects to admin bookings page (not frontend portal)
- **Fix:** /my-account/hall-bookings/ 404 resolved — endpoint auto-registers on update

### v2.13.0
- **New:** WooCommerce UX integration for hirers
- Smart login redirect: hirers → portal, managers → admin, others → default
- "My Hall Bookings" tab in WooCommerce My Account menu

### v2.12.0 (Security & Performance Release)
- WooCommerce refund handler, GDPR right-to-erasure, email queue cleanup
- Hirer portal security, payment for cancelled booking warning
- Database indexes for performance

### v2.11.0
- OSM (Online Scout Manager) integration

### v2.10.x
- WooCommerce payment fixes, calendar refresh, admin AJAX fixes

### v2.9.x
- Timezone safety, honeypot spam prevention, mobile responsive design
- Pending bookings badge, PHP 8.x compatibility

### v2.8.x
- Booking Manager role, bulk actions, delete restriction

### v2.7.0
- Custom admin price override, README + AI-CONTEXT.md

### v2.0–2.6
- Email queue, custom fields, modification requests, analytics
- Scout Use, recurring bookings, hirer portal, accounting export
- Overnight booking fix, multi-day hourly fix

### v1.x
- Core booking system, calendar, invoicing, Home Assistant integration
- Conflict detection, recurring bookings, payment chasing
- WooCommerce payments, auto-archive, email templates
