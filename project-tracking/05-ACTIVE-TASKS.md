# Active tasks

## Done
- Inspect customer routes and live 500 causes for dashboard, onboarding, and reports
- Confirm all three customer 500s come from the same missing Firestore credentials exception path
- Add a controller-local Firestore fallback for the three broken customer pages
- Remove the public `Home` left-nav group from the customer workspace shell
- Add a focused regression test for missing Firestore credentials on the broken customer pages
- Update tracking files for the customer workspace QA task

## Current
- Run focused validation for `customer.dashboard`, `customer.onboarding.index`, and `customer.reports.index`
- Confirm no second customer-page failure remains after the Firestore fallback

## Next
- Verify customer left-menu structure in-browser after the nav cleanup
- Keep unrelated dirty files and backup artifacts out of any later staging or commit
