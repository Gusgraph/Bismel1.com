# Changelog

## 2026-04-03
- Aggressively neutralized all `.app-topbar` card-shaped styling for customer routes (padding, background, border, radius, shadow, and backdrop-filter), ensuring only the standalone 3-dots menu button remains visually unframed.
- Removed the top-right topbar frame/container on customer pages, leaving only the standalone 3-dots menu button.
- Removed redundant top-left title block from authenticated customer pages, conditionally hidden on customer routes using `@unless ($isCustomerRoute)`.
- Ran a final authenticated customer finishing pass across the shared shell and customer page hierarchy without changing auth, routing, controllers, or data flow
- Moved repeated customer summary/hero copy for automation, broker, trading, billing, settings, and related customer forms into the shared page-intro surface so the top of those pages no longer stacks multiple heavy bars
- Added a shared page-intro summary slot and responsive flagship layout so customer page intros can carry the main summary cleanly in both dark and light mode
- Rebalanced authenticated customer visual hierarchy so shell surfaces stay strongest, page intros stay flagship, module cards sit at medium weight, and list rows/readiness items use softer supporting surfaces
- Reduced over-framing by replacing repeated 3px inset treatment on many customer-only surfaces with lighter border, inset, and shadow combinations
- Softened customer breadcrumbs and section navigation into support bands instead of treating them like primary cards
- Tightened customer icon sizing, heading alignment, left-nav spacing, and topbar/menu treatment so billing and dashboard stay in the same premium operator-grade family
- Kept the current customer sidebar nav, compact top-right menu, and `bismel1.app-theme` light/dark toggle behavior intact while refining the finish layer
- Verified the Blade/view layer still compiles by running `php artisan view:clear` and `php artisan view:cache`
- Ran a focused authenticated customer theme cleanup pass after the theme-toggle regression fix
- Replaced the top-left authenticated left-rail placeholder mark with the real `images/logo.png` app logo
- Kept the nearby hidden symbol decorative but pushed it further into the background and made it fully non-interactive
- Fixed the customer page intro / hero header surface so light mode now uses the light flagship surface tokens instead of a hard-coded dark gradient
- Reworked shared authenticated icon surfaces to use theme-derived foreground and background variables instead of fixed pale gradient stops that weakened dark-mode contrast
- Normalized the remaining customer-shell glow, inset, focus-ring, and flagship shadow treatments onto dark/light customer tokens to reduce mixed-theme leftovers
- Fixed the authenticated shell theme regression by aligning the customer dark/light token overrides with the same root `data-app-theme` selector used by the `bismel1.app-theme` toggle
- Restored the top-right customer menu trigger and panel border/glow treatment after the recent customer visual-system pass
- Ran one unified visual styling pass across the authenticated customer area using the existing theme-switch foundation
- Added customer-route shell classes so premium customer styling stays scoped away from guest and admin areas
- Reworked the customer shell, topbar, left nav, breadcrumbs, page intro, cards, forms, list rows, summary tiles, and dashboard panels into one coherent operator-grade visual system
- Pushed dark mode toward the intended near-black/deep-navy premium AI trading feel with restrained electric-cyan glow accents and consistent 3px border treatment
- Kept light mode functional and clean using the same customer visual system tokens instead of one-off dark-only overrides
- Upgraded the customer dashboard top strip, main blocks, readiness side panel, and action links so the dashboard reads as the flagship operator surface
- Brought customer automation, broker, trading pages, billing, and settings into the same visual family without changing route structure or business logic
- Reworked the billing plan catalog away from inline styling onto reusable customer visual classes and existing button/input components
- Added a real authenticated app-shell theme foundation with dark mode as the default premium AI trading look and a clean light fallback
- Added a compact theme switch inside the top-right authenticated shell menu
- Persisted the authenticated shell theme choice with `localStorage` so theme state survives refresh without changing DB direction
- Reworked shared authenticated shell colors onto reusable theme tokens for the sidebar, topbar, cards, form surfaces, and dashboard-adjacent panels
- Cleaned stale customer feature coverage to match the approved current customer route gating, broker form contract, and current customer page copy without reshaping the UI
- Polished the customer dashboard to align more closely with the intended trading-first control surface
- Moved `Latest Activity` into the five main dashboard blocks and trimmed the sidebar down to the real desk-check rail
- Tightened customer dashboard wording so the top strip, main blocks, and side panel read as a live trading desk instead of a generic workspace summary
- Tightened the authenticated app shell by reducing the sidebar brand block to a compact mark and removing the sidebar helper copy/footer logout section
- Added a minimal top-right three-dots menu in the shared shell and moved `Profile`, `Billing`, `Settings`, and `Logout` into it for customer pages
- Kept the cleaned customer left nav unchanged while making the shared shell more compact and premium
- Cleaned the customer left nav down to the intended final menu order: `Dashboard`, `Automation`, `Broker`, `Positions`, `Orders`, `Activity`, `Plans & Billing`, and `Settings`
- Removed legacy customer left-nav items for `Account`, `Strategy`, `License`, `Onboarding`, `Invoices`, and `Reports` without deleting their routes or pages
- Updated the collapsed customer rail in the shared app shell so it no longer exposes `Account` and now uses `Plans & Billing`
- Fixed customer `Dashboard`, `Onboarding`, and `Reports` 500s by catching missing Firestore credential failures in the three customer controllers and returning an unavailable runtime-summary card instead of crashing
- Removed the public `Home` left-nav group from the customer workspace shell so customer navigation stays inside the workspace
- Added a focused customer regression test for the missing-Firestore-credentials failure path seen in production QA
- Fixed the global access rule so plain account ownership no longer grants admin access
- Changed signup-created users to receive customer-only membership by default while still creating their workspace
- Updated `LocalAuthUsersSeeder` so `customer.local@gusgraph.test` is customer-only and `admin.local@gusgraph.test` is explicitly admin-capable
- Added focused auth tests covering ownership-only users, default signup behavior, and local auth demo-user behavior
- Audited current customer/admin access boundaries across middleware, route groups, navigation, and user access helpers
- Expanded `CustomerAccessMap` and `AdminAccessMap` so they reflect the real current route sets
- Tightened shared app navigation visibility so customer-only users no longer see admin workspace links
- Added focused access tests covering customer-only access, admin blocking for customer-only users, and current admin-capable crossover into customer routes
- Refit the customer dashboard into a trading-first control surface without changing app architecture or module boundaries
- Replaced generic dashboard stats with top-strip visibility for equity, buying power, runtime state, broker sync, and automation state
- Reworked dashboard main blocks into positions preview, open orders preview, latest signals, latest activity, and action-needed panels
- Moved broker, plan, bot, market-window, and last-sync checks into the dashboard side rail
- Updated customer dashboard copy to remove generic workspace/admin phrasing and focus on trading operations
- Updated project tracking files so active work now reflects the customer dashboard task instead of homepage/products follow-up

## 2026-04-02
- Redesigned the public products page away from generic pricing-grid structure into real product lanes
- Added Prime Stocks Bot Trader as the flagship product card
- Added Execution, Add-ons, Demo Access, and roadmap product sections
- Added affiliate-aware display pricing behavior on the public products page
- Added affiliate notice banner copy and styling
- Added discounted labels with prior-price strike display for affiliate pricing state
- Changed public page state label wording from Ready to Live
- Added green live-state glow styling
- Tightened products page desktop width
- Tightened homepage desktop width while keeping Infrastructure Behind the Bot and Live Market Feed full width
- Restored homepage shared styles after products-page CSS bleed from broad guest CSS changes
- Fixed mobile header nav button sizing
- Noted that the Prime Stocks Bot Trader flagship bullet list is hardcoded in resources/views/plans.blade.php and does not use HomeController item arrays


## 2026-04-04 - Customer area polish session

Added / fixed:
- customer shell refinement continued
- duplicate customer top shell title copy removed
- customer top header card wrapper removed
- dashboard clutter reduced by removing notices, runtime/review strips, breadcrumbs, side-panel label, and redundant main-block intro layer
- automation wording and structure reduced
- broker wording and structure reduced
- activity wording and structure reduced
- billing wording and structure reduced
- customer alerts placeholder notes removed from billing
- CSS build issue fixed by removing a bad duplicate customer topbar block from resources/css/app.css

Files worked during session included:
- resources/views/layouts/app.blade.php
- resources/css/app.css
- resources/views/customer/dashboard.blade.php
- resources/views/customer/automation/index.blade.php
- resources/views/customer/broker/index.blade.php
- resources/views/customer/trading/index.blade.php
- resources/views/customer/billing/index.blade.php
- app/Support/ViewData/AutomationPageData.php
- app/Support/ViewData/BrokerPageData.php
- app/Support/ViewData/Bismel1CustomerTradingPageData.php
- app/Support/ViewData/BillingPageData.php
- app/Support/Notifications/CustomerAlerts.php

## 2026-04-04 - Prime Stocks customer testing surface

- Inspected the existing customer routes, navigation, strategy page, trading pages, and tracking docs before implementation
- Added customer route `customer.strategy.prime-stocks` at `customer/strategy/prime-stocks`
- Added a new `Prime Stocks` item to the customer workspace navigation
- Added `resources/views/customer/strategy/prime-stocks.blade.php` as a dedicated Prime Stocks visual testing surface
- Used demo/static customer-facing status values only for runtime, state, timeframe, pullback, tier, action candidate, and signal timing
- Added visible Prime Stocks concept sections for:
  - strategy name
  - stocks-only label
  - 1H decides when
  - 1D helps decide whether
  - pullback window 5
  - reclaim model summary
  - FirstLot behavior summary
  - MULTI behavior summary
  - pauseNewBasket status concept
  - pauseAdds status concept
  - ATR trail exit concept
  - regime fail behavior summary
- Made the server-side runtime ownership explicit in customer-visible copy:
  - `Cloud Run runs the bot while this page stays a customer control and monitoring surface.`
  - `Prime Stocks executes on Cloud Run server-side with demo-only status values shown here. Trading does not require this page to stay open.`
  - `Bot runtime target: Cloud Run serverless. User page role: control / monitoring only. Trading does not require the page to stay open.`
- Validated the new surface with:
  - `php -l app/Http/Controllers/Customer/StrategyController.php`
  - `php -l routes/customer.php`
  - `php artisan route:list --name=customer.strategy.prime-stocks`
  - `php artisan view:clear`
  - `php artisan view:cache`

## 2026-04-04 - Prime Stocks moved into Automation

- Removed the standalone `customer.strategy.prime-stocks` route from the customer route file
- Removed the standalone `Prime Stocks` customer navigation item
- Removed the standalone Prime Stocks Blade page so no separate product page remains for this phase
- Integrated Prime Stocks into the existing Automation controller/view/data flow only
- Added an Automation-embedded Prime Stocks module with demo/static data for:
  - Demo Access product framing
  - stocks-only scope
  - 1H decides when
  - 1D helps decide whether
  - pullback window 5
  - Cloud Run runtime target
  - control / monitoring only page role
  - no stay-open requirement
  - reclaim / FirstLot / MULTI / pauseNewBasket / pauseAdds / ATR trail exit / regime fail concept copy
- Structured the Automation wording so the later subscribed/live presentation name can become `Prime Stocks Bot Trader` without changing the page structure
- Validated the integrated Automation path with:
  - `php -l app/Http/Controllers/Customer/AutomationController.php`
  - `php -l app/Support/ViewData/AutomationPageData.php`
  - `php -l app/Http/Controllers/Customer/StrategyController.php`
  - `php -l routes/customer.php`
  - `php artisan route:list --name=customer.strategy.prime-stocks` returning no matching route
  - `php artisan view:clear`
  - `php artisan view:cache`

## 2026-04-04 - Automation page made product/subscription-driven

- Refocused the existing Automation page away from generic placeholder overview/readiness sections and toward real product/subscription access state
- Removed or reduced placeholder-heavy visible sections including:
  - generic `Automation overview`
  - generic `Current Automation State`
  - generic `Run Window`
  - generic `Recent Activity` support card
  - generic `System linkage`
  - generic sidebar `Automation Notes` placeholder feed
- Added product-driven Automation rendering for three states:
  - `No active product`
  - `Demo Access product`
  - `Active subscribed product`
- Added Prime Stocks product presentation rules inside Automation:
  - subscribed/live naming: `Prime Stocks Bot Trader`
  - demo/local fallback naming: `Demo Access product`
- Added visible product-driven fields for:
  - current automation access
  - product name
  - product state / status
  - upgrade / subscribe / manage state
  - asset class
  - execution timeframe
  - trend timeframe
  - pullback window
  - runtime target
  - browser stay-open requirement
  - last action candidate or demo signal state
- Added explicit visible runtime wording:
  - `Cloud Run Serverless Bot`
  - `Cloud Run runs the Serverless Bot server-side`
  - `Control / monitoring zone`
  - `Trading does not require the page to stay open`
- Added local full-access Automation rendering for:
  - `customer.local@gusgraph.test`
  - `admin.local@gusgraph.test`
  until Stripe-backed subscriptions are built later
- Validated the updated Automation page with:
  - `php -l app/Http/Controllers/Customer/AutomationController.php`
  - `php -l app/Support/ViewData/AutomationPageData.php`
  - `php artisan view:clear`
  - `php artisan view:cache`
