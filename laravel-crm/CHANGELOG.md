# CHANGELOG for 2.2

This changelog consists of the bug & security fixes and new features being included in the releases listed below.

## **v2.2.7 (Upcoming)**

* [feature] Added Moldable CRM workspaces with memberships, roles, teams, workspace settings, and access isolation primitives.

* [feature] Rebuilt the Moldable Field Builder on top of the existing Attribute module instead of introducing duplicate custom field storage.

* [feature] Exposed the existing Attribute capabilities through the Moldable builder, including entity assignment, field types, options, validation, required state, unique state, quick-add state, and ordering.

* [feature] Added saved CRM views with filters, columns, sorting, grouping, visibility, and default-view support.

* [feature] Added industry template installation using the existing Attribute and AttributeOption models.

* [feature] Added workspace resource bindings for attaching existing CRM records to workspaces without replacing existing Lead business logic.

* [enhancement] Removed the duplicate Moldable CustomField model and storage table.

* [enhancement] Registered the Moldable package through Composer and Laravel package discovery.

## **v2.2.6 (Upcoming)**

* #2612[feature] Redesigned Lead Profile mobile experience with 1-tap quick actions and unified chronological activity timeline.

* [enhancement] Removed non-English translation packages (ar, es, fa, ko, pt_BR, tr, vi, zh_CN), keeping English (en) as the sole application locale.

* #2611[feature] Added Universal Lead Capture Engine with automated incoming data field mapping and multi-platform webhook integrations (Meta, Google, IndiaMART, JustDial, 99acres, MagicBricks, Housing, Sulekha, QR, Webhooks, API, Zapier).

* #2610[feature] Added Unified Lead Inbox primary mobile experience with touch swipe actions, smart filter presets, and bulk operations.
