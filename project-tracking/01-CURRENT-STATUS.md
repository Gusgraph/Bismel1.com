# Current status

## Working
- Final authenticated customer finishing pass is active across the shared customer shell, page-intro treatment, dashboard bands, and billing-adjacent module surfaces
- Customer shell theme switching remains repaired while spacing, intro hierarchy, icon consistency, and border/glow weight are being unified across the customer area
- Prime Stocks customer-side production testing surface is now active in the customer workspace as a demo-only visual control and monitoring preview
- Repo contains unrelated dirty files and backup artifacts that must remain untouched during this task

## Recent fixes
- Added a dedicated customer `Prime Stocks Test Console` page using demo/static data only for visual product review in the existing customer shell
- Added a customer navigation link for the new Prime Stocks test surface so it can be opened directly from the workspace
- Made the Prime Stocks customer messaging explicit that Cloud Run is the bot runtime target, the page is control/monitoring only, and trading does not require the browser to stay open
- Aggressively neutralized all `.app-topbar` card-shaped styling for customer routes (padding, background, border, radius, shadow, and backdrop-filter) to ensure only the standalone 3-dots menu button remains visually unframed.
- Removed the surrounding frame/container for the top-right 3-dots menu on authenticated customer pages, leaving only the standalone button.
- Removed redundant top-left title block from authenticated customer pages, as the information was duplicated by the main page intro. This block is now conditionally hidden for customer routes using `@unless ($isCustomerRoute)` while remaining visible on public and admin pages.
- Moved customer summary/hero copy for automation, broker, trading, billing, settings, and related customer forms into the shared page-intro surface so those pages no longer stack a second heavy top card above the real content
- Softened authenticated customer breadcrumbs, section-nav bands, inner cards, list rows, summary tiles, and topbar/menu in both dark and light mode so the shell remains strongest and nested modules read lighter
- Tightened authenticated customer icon sizing, heading alignment, nav spacing, and flagship intro layout so dashboard and billing stay inside the same premium product family
- Fixed the customer shell theme selector mismatch so the `bismel1.app-theme` toggle now visibly flips dark and light modes again
- Restored the intended top-right menu border/glow treatment inside the customer shell
- Kept the existing compact top-right menu behavior and `localStorage` theme persistence intact
- Replaced the authenticated left-rail placeholder mark with the real `images/logo.png` asset already used by the app
- Removed the hard-coded dark customer intro gradient that kept hero/header panels visually dark in light mode
- Reworked shared authenticated icon surfaces so dark and light mode both derive icon contrast from theme tokens instead of fixed light-only gradient stops

## Current truth
- Live app path: /var/www/html/bismel1.com
- Repo path: /var/www/html/bismel1.com
- Remote: git@github.com:Gusgraph/Bismel1.com.git
- Current date for this task pass: 2026-04-03 UTC

## Important implementation note
- This pass does not change auth architecture, role logic, shared models, or database direction
- This pass keeps customer route access rules and existing customer route names intact while cleaning only the authenticated customer shell theme system
- This pass adds a visual Prime Stocks customer surface only; it does not wire live Python runtime status, browser polling, or browser-run bot logic
- Guest/public pages and admin pages remain untouched; only authenticated customer shell visuals, shared icons, and related hero/header surfaces are adjusted where needed

## Next
- Desk-check the `Prime Stocks Test Console` in the customer workspace in both dark and light mode
- After visual approval, wire the Prime Stocks test console to real Cloud Run-backed runtime and strategy status data without moving runtime ownership into the browser
- Leave any non-critical page-specific polish outside the approved customer surface family for a later targeted pass only if it survives visual review
- Leave unrelated dirty files and backup artifacts out of any later staging or commit


## Session Update - 2026-04-04

Customer area polish session completed in controlled passes across shell, dashboard, automation, broker, activity, and billing.

Completed in this session:
- customer shell cleanup continued
- duplicate customer top shell title copy removed
- top customer header card wrapper removed so page intro is the first real surface
- customer dashboard top clutter removed:
  - System Notices
  - Runtime Mode
  - Operational Review
  - breadcrumbs
  - Side Panel box
  - redundant Main blocks header layer
- light/dark theme behavior confirmed working after rebuild
- customer wording cleanup advanced page by page:
  - Automation
  - Broker
  - Activity
  - Billing
- shared customer page hierarchy and intro surfaces were tightened earlier and then refined manually in bash
- CSS build break during topbar work was corrected by removing the bad duplicate block from resources/css/app.css and rebuilding successfully

Current state:
- customer area is cleaner visually and structurally
- customer pages are now being refined one by one with bash for tighter control
- remaining work is mostly wording polish, mobile scratches, and repo cleanup/good git hygiene


## Session Update - 2026-04-04 - Prime Stocks visual testing phase

Completed in this session:
- inspected current customer routes, navigation, strategy surface, and tracking state before editing
- added a customer route for `customer.strategy.prime-stocks`
- added a customer navigation entry for `Prime Stocks`
- added a dedicated `Prime Stocks Test Console` customer page with demo/static status values
- made the runtime boundary explicit in visible customer copy:
  - Cloud Run is the bot runtime target
  - the page is control / monitoring only
  - trading does not require the page to stay open
- validated the new surface with PHP lint, route listing, and Blade view cache rebuild

Current state:
- the customer workspace now includes a Prime Stocks visual testing surface that can be opened from the left navigation
- Prime Stocks defaults now read clearly in the customer area:
  - stocks-only
  - 1H execution
  - 1D trend
  - pullback window 5
- runtime messaging remains aligned with the approved architecture: Laravel UI shell on the VM, Python runtime separate, Cloud Run server-side target
