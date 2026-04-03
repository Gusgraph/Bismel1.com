# Current status

## Working
- Customer workspace QA fix is now the active task
- Dashboard, Onboarding, and Reports were failing because the customer controllers hard-failed on a missing Firestore service-account file
- Customer workspace left nav still exposed a public `Home` exit path through the shared app shell
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
- The Firestore fix is isolated to the three broken customer page controllers instead of changing shared Firestore behavior globally
- Guest/public pages remain untouched; only the customer workspace shell stops showing the public `Home` left-nav group

## Next
- Run focused customer page validation for dashboard, onboarding, and reports
- Verify in-browser that customer left nav no longer exposes the public `Home` path
- Leave unrelated dirty files and backup artifacts out of any later staging or commit
