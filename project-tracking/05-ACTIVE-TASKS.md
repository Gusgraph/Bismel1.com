# Active tasks

## Done
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
- Desk-check the finished authenticated customer area in both dark and light mode
- Confirm dashboard, billing, automation, broker, positions, orders, activity, and settings still read clearly after the lighter nested-surface pass
- Keep the current compact top-right shell menu, sidebar nav, and theme toggle behavior intact
- Avoid unrelated repo cleanup and do not touch guest or admin areas

## Next
- Validate dashboard and billing in dark and light after the finishing pass
- Review whether any specific customer page still needs targeted visual tightening after live desk-check
- Revisit DB-backed theme persistence only if an aligned existing preferences path is introduced later
- Keep unrelated dirty files and backup artifacts out of any later staging or commit


## Active Follow-up - 2026-04-04

Current next tasks:
1. final dashboard wording polish
2. final automation wording polish
3. final broker wording polish
4. final billing wording polish
5. positions / orders / activity shared-page wording polish
6. mobile/header scratch fixes
7. tracking cleanup and commit hygiene
8. repo cleanup of .bak files and unrelated dirty files

Working rule:
- continue page by page
- bash/manual fixes first for visible polish
- save larger Codex passes for broader structural changes only

