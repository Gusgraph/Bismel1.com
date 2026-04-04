# Active tasks

## Done
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
- Desk-check the integrated Prime Stocks module on the existing Automation page in both dark and light mode
- Keep the current customer shell, compact top-right menu, sidebar nav, and theme toggle behavior intact
- Do not wire live Python runtime, browser polling, or browser-run bot logic in this phase
- Avoid unrelated repo cleanup and do not touch guest or admin areas

## Next
- Wire the Prime Stocks Automation module to real Cloud Run-backed runtime and strategy status data after the visual surface is approved
- Review whether the Automation-embedded Prime Stocks section needs only targeted polish after live desk-check
- Keep unrelated dirty files and backup artifacts out of any later staging or commit


## Active Follow-up - 2026-04-04

Current next tasks:
1. desk-check the Prime Stocks section on the Automation page in dark and light
2. collect visual review feedback only
3. wire real Cloud Run-backed runtime/status data later inside Automation after approval
4. keep unrelated dirty files and `.bak` artifacts out of staging

Working rule:
- work only inside the approved Automation surface for Prime Stocks in this phase
- keep the architecture unchanged: Laravel shell on VM, Python runtime separate, Cloud Run server-side
- use demo/static data until the later backend hookup pass
