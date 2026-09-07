# AdvertisementModule

NamelessMC module for managing site advertisements as **widgets**, **page headers**, and **page footers**.

Compatible with **NamelessMC 2.2.5**.

## Features

- Create, edit, enable/disable, and delete advertisements from StaffCP
- Placement per ad: widget (sidebar), header, or footer
- Global toggles for each placement type
- HTML content sanitised with NamelessMC's HTMLPurifier (scripts and unsafe markup are stripped)
- Impression and click tracking with daily buckets (StaffCP only)
- Click-through links are proxied through a safe redirect that only allows `http`/`https` (or site-relative) URLs present in the ad

## Install

1. Copy `modules/AdvertisementModule` into your Nameless `modules/` directory.
2. Copy `custom/panel_templates/Default/advertisement` into your Nameless `custom/panel_templates/Default/` directory (or your active panel template folder).
3. In StaffCP → Modules, install and enable **AdvertisementModule**.
4. Under StaffCP → Layout → Widgets, enable:
   - **Ads** (sidebar) — set location Left/Right
   - **Ads Header** — set location **Top**
   - **Ads Footer** — set location **Footer**
5. Assign permissions under StaffCP → Groups as needed:
   - `advertisement.create`
   - `advertisement.delete`
   - `advertisement.settings`
   - `advertisement.view_stats`

## Security notes

All advertisement HTML is treated as untrusted input and purified before storage. Third-party ad networks that require `<script>` tags will not work; use safe HTML (links, images, basic formatting) instead.

## Author

Originally started by [JaredScar](https://github.com/JaredScar) (Badger). Updated for NamelessMC 2.2.5.
