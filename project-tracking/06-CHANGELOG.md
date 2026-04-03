# Changelog

## 2026-04-03
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
