# Current status

## Working
- Customer dashboard trading-surface polish is now the active task
- Customer dashboard structure still needs to match the intended top strip, five main blocks, and desk-check side panel more closely
- Repo contains unrelated dirty files and backup artifacts that must remain untouched during this task

## Recent fixes
- Added a controller-scoped Firestore fallback for the broken customer summary pages so missing credentials no longer throw 500s
- Removed the public `Home` group from the customer workspace left nav while leaving guest/public navigation untouched
- Added a focused regression test covering the exact missing-Firestore-credentials customer-page failure mode

## Current truth
- Live app path: /var/www/html/bismel1.com
- Repo path: /var/www/html/bismel1.com
- Remote: git@github.com:Gusgraph/Bismel1.com.git
- Current date for this task pass: 2026-04-03 UTC

## Important implementation note
- This pass does not change auth architecture, role logic, shared models, or database direction
- This pass keeps customer route access rules and existing customer route names intact while refitting only the dashboard surface
- Guest/public pages and unrelated customer pages remain untouched; only dashboard composition and copy are adjusted where needed

## Next
- Verify the customer dashboard renders with the exact trading-first structure: top strip, five main blocks, and desk-check side panel
- Confirm any remaining missing values are shown with honest real-data fallback wording
- Leave unrelated dirty files and backup artifacts out of any later staging or commit
