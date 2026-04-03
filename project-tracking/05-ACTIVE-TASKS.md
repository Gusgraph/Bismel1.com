# Active tasks

## Done
- Inspect current customer/admin qualification rules in `User`, signup flow, and local auth seeder
- Remove plain account ownership as implicit admin access
- Change signup-created membership to customer-only default behavior
- Update local auth demo seeder so customer and admin demo accounts reflect real intended roles
- Add focused tests for ownership-only users, signup users, and local auth demo users
- Update tracking files to follow the global access-rule fix task

## Current
- Run focused auth/login/access validation after the global rule change
- Review for any blocker in the signup-to-customer-only path

## Next
- Verify both local demo accounts in-browser after reseeding auth fixtures
- Review whether any older fixtures or setup docs still assume new signups are admin-capable
- Keep unrelated dirty files and backup artifacts out of any later staging or commit
