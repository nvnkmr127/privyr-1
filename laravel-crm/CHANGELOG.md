# CHANGELOG for 2.2

This changelog consists of the bug & security fixes and new features being included in the releases listed below.

## **v2.2.7 (Upcoming)**

* [feature] Added Moldable CRM workspaces with memberships, roles, teams, workspace settings, and access isolation primitives.

* [feature] Added a workspace field builder supporting typed custom fields, options, groups, ordering, required fields, and field configuration.

* [feature] Added saved CRM views with filters, columns, sorting, grouping, visibility, and default-view support.

* [feature] Added industry template installation with initial Interior Design and Real Estate CRM templates.

* [feature] Added workspace resource bindings for attaching existing CRM records to workspaces without replacing existing Lead business logic.

* [enhancement] Registered the Moldable package through Composer and Laravel package discovery.

## **v2.2.6 (Upcoming)**

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
