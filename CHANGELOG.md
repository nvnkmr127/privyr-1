# CHANGELOG for 2.2

This changelog consists of the bug & security fixes and new features being included in the releases listed below.

## **v2.2.6 (Upcoming)**

* [enhancement] Centralized Moldable custom-field system (`/admin/moldable/builder`) across the workspace with full support for Leads, Contacts, Organizations, Products, Quotes, Lead Capture mapping, dynamic Web Forms, DataGrid exports, cascade deletion, and multi-tenant isolation.
* [enhancement] Overhauled Moldable Field Builder UI/UX with high-density compact field rows, multi-axis sticky filter toolbar, interactive presentation group manager with field previews, 6-step drawer with categorized field types and sticky footer, and accessible custom confirmation dialogs.
* [enhancement] Redesigned Moldable Create/Edit Field drawer into a clear enterprise 6-step workflow with completed/active/upcoming step states, micro-progress indicator, clean entity context badges, immutable field-code lock indicator, dynamic per-type configuration, inline validation, and unsaved changes protection.
* [enhancement] Standardized visual typography hierarchy across CRM admin views (page headings, breadcrumbs, buttons, section headers, badges, and drawers) using `/admin/settings` as the visual reference.
* [enhancement] Standardized icon system across the CRM with consistent sizing matrix (30px card icons, 24px action/checkbox icons, 18-20px sidebar/nav icons, 16px button/sort icons) and unified container paddings and radii (`rounded-md` / `rounded-lg`).
* [enhancement] Consolidated repeated UI patterns across CRM modules, replacing ad-hoc button and badge styles with shared primitives (`.primary-button`, `.secondary-button`, `.transparent-button`, `.label-active`, `.label-inactive`).
* [enhancement] Redesigned Lead Details page with a compact summary header, 2-column property grids with progressive disclosure for large field collections, sticky desktop sidebar, real-time activity timeline filters, and responsive 35%/65% layout.


* [feature] Added instant new-lead push notifications to assigned agents' devices via the Firebase Cloud Messaging HTTP v1 API (service-account OAuth), with device-token registration APIs, automatic pruning of dead tokens, and a WhatsApp fallback when no device is registered.

* #2612[feature] Redesigned Lead Profile mobile experience with 1-tap quick actions and unified chronological activity timeline.

* [enhancement] Removed non-English translation packages (ar, es, fa, ko, pt_BR, tr, vi, zh_CN), keeping English (en) as the sole application locale.

* #2611[feature] Added Universal Lead Capture Engine with automated incoming data field mapping and multi-platform webhook integrations (Meta, Google, IndiaMART, JustDial, 99acres, MagicBricks, Housing, Sulekha, QR, Webhooks, API, Zapier).

* #2610[feature] Added Unified Lead Inbox primary mobile experience with touch swipe actions, smart filter presets, and bulk operations.

## **v2.2.5 (4th of Aug 2026)**

* [feature] Added Chinese (Simplified) `zh_CN` translation for the Admin, Installer, DataTransfer, WebForm and Core packages.

* [feature] Added a configurable default dashboard date range — 1 month, 3 months, 9 months, 1 year, 2 years or a custom number of days — under Configuration > General > Settings > Dashboard Configurations.

* [fixed] Fixed menu item names set in Configuration not applying to section pages, breadcrumbs and the mobile sidebar. Previously only the desktop sidebar reflected a rename.

* [fixed] Fixed renaming the "Mail" and "Contacts" menu items having no effect anywhere, as their configuration fields did not match the actual menu keys.

* [fixed] Fixed the dashboard date range label omitting the year on ranges spanning more than one calendar year, which rendered as "30 Jul - 30 Jul".

* [fixed] Fixed Arabic DataTransfer translations never loading, as the file was named `ar/ar.php` instead of `ar/app.php`.

* [fixed] Fixed the missing Korean translation for the "None" input validation option on the create and edit attribute forms.

* [enhancement] Moved the Core and DataTransfer package translations into the Admin package. Only packages that ship their own Blade views now carry a `Resources/lang` directory.

* [enhancement] Reduced database queries on every admin page by loading the configured menu names in a single query instead of one per menu item.

* [enhancement] Documented the localization convention in the `crm-package-development` agent skill and in AGENTS.md.
