# Active tasks

## Done
- Integrate real Prime Stocks runtime read data into the existing Automation page from Firestore-backed runtime state
- Read the existing Prime Stocks Firestore/runtime paths from Laravel in read-only mode only
- Replace current static/demo Prime Stocks runtime fields in Automation with real runtime values where records exist
- Keep graceful Automation fallback messaging when Firestore runtime data is missing or unavailable
- Desk-check the active-plan Prime Stocks Automation page in both dark and light mode
- Clean the Automation page wording/state model so Prime Stocks reads as the active plan product for local testing instead of a demo product
- Remove mixed Demo Access vs subscribed wording from the active Prime Stocks Automation surface
- Present Prime Stocks as `Prime Stocks Bot Trader` with active plan access in a local full-access test state until Stripe subscription wiring is completed
- Clean the existing Automation page and convert it from placeholder-driven UI into product/subscription-driven UI inside the existing Automation system
- Remove or reduce placeholder-heavy Automation sections that do not map to real product or subscription access state
- Center Automation on real access outcomes:
  - no active product
  - Demo Access product
  - active subscribed product
- Keep Prime Stocks inside Automation and render subscribed/live naming as `Prime Stocks Bot Trader`
- Add explicit Serverless Bot, control / monitoring zone, and no stay-open requirement wording inside Automation
- Undo the standalone Prime Stocks page approach and integrate Prime Stocks into the existing Automation page/module using demo/static data only
- Remove the standalone Prime Stocks route/nav path and keep Prime Stocks presentation inside Automation
- Frame Prime Stocks inside Automation as a `Demo Access product` now, ready for later subscribed/live naming as `Prime Stocks Bot Trader`
- Inspect current customer pages/nav and add a Prime Stocks customer-side visual testing surface for production review using demo/static data only
- Link the Prime Stocks testing surface into the customer workspace navigation
- Make the customer-facing runtime boundary explicit: Cloud Run server-side runtime, control/monitoring page only, browser does not need to stay open
- Inspect customer routes and live 500 causes for dashboard, onboarding, and reports
- Confirm all three customer 500s come from the same missing Firestore credentials exception path
- Add a controller-local Firestore fallback for the three broken customer pages
- Remove the public `Home` left-nav group from the customer workspace shell
- Add a focused regression test for missing Firestore credentials on the broken customer pages
- Update tracking files for the customer workspace QA task
- Add a customer-scoped premium shell and visual-system pass using the existing light/dark theme foundation
- Unify customer cards, dashboard panels, nav states, forms, list rows, and plan catalog styling across the customer workspace
- Keep dark mode as the premium target while leaving light mode clean and readable
- Repair the authenticated shell theme toggle so it visibly switches customer pages again
- Restore the intended top-right customer menu border/glow treatment without changing menu behavior
- Run the final authenticated customer finishing pass across page-intro hierarchy, shell spacing, icon consistency, and nested surface weight
- Aggressively neutralized all `.app-topbar` card-shaped styling for customer routes (padding, background, border, radius, shadow, and backdrop-filter), ensuring only the standalone 3-dots menu button remains visually unframed.
- Removed the top-right topbar frame/container on customer pages, leaving only the standalone 3-dots menu button.
- Removed the redundant top-left shell title block from authenticated customer pages, conditionally hiding it on customer routes.
- Collapse repeated customer top-of-page framing by moving page summaries into the shared page-intro treatment where appropriate
- Soften customer rows, module cards, and nav support bands without changing route structure or business logic

## Current
- Keep the current customer shell, compact top-right menu, sidebar nav, and theme toggle behavior intact
- Do not wire live Python runtime, browser polling, or browser-run bot logic in this phase
- Avoid unrelated repo cleanup and do not touch guest or admin areas

## Next
- Wire the active-plan Prime Stocks Automation product access state to real entitlement/subscription data after the runtime read integration
- Review whether the cleaned active-plan Automation page needs only targeted polish after live desk-check
- Keep unrelated dirty files and backup artifacts out of any later staging or commit


## Active Follow-up - 2026-04-04

Current next tasks:
1. collect visual review feedback only
2. wire real Cloud Run-backed entitlement/subscription data later inside Automation after approval
3. keep unrelated dirty files and `.bak` artifacts out of staging

Working rule:
- work only inside the approved Automation surface in this phase
- keep the architecture unchanged: Laravel shell on VM, Python runtime separate, Cloud Run server-side
- use demo/static data until the later backend hookup pass
