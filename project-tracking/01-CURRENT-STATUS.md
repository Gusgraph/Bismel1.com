# Current status

## Working
- Global access rule fix is now the active task
- Customer access still comes from active account ownership or active membership
- Admin access now needs explicit admin-capable membership instead of plain account ownership
- New signups should enter as customer-only by default for cleaner real-user and demo-user behavior
- Repo contains unrelated dirty files and backup artifacts that must remain untouched during this task

## Recent fixes
- Removed plain account ownership as a source of admin access
- Changed signup-created membership from admin-capable owner role to customer-only member role
- Updated local auth demo seeder so `customer.local@gusgraph.test` is customer-only and `admin.local@gusgraph.test` is admin-capable
- Added focused tests for ownership-only users, signup-created users, and local auth demo users

## Current truth
- Live app path: /var/www/html/bismel1.com
- Repo path: /var/www/html/bismel1.com
- Remote: git@github.com:Gusgraph/Bismel1.com.git
- Current date for this task pass: 2026-04-03 UTC

## Important implementation note
- This pass does not change auth architecture, shared models, or database direction
- Admin access now requires an active membership role of `owner` or `admin`
- New signups still create a workspace owned by the user, but the linked membership is customer-only by default
- Middleware remains the enforcement layer; access maps are descriptive and inspection-oriented

## Next
- Validate signup, login, and access routing after the membership-role change
- Verify `customer.local@gusgraph.test` and `admin.local@gusgraph.test` in-browser after reseeding local auth users
- Decide later whether newly created workspace owners should ever be elevated into explicit admin membership through a separate admin flow
