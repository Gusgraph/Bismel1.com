# Active tasks

## Done
- Inspect customer routes and live 500 causes for dashboard, onboarding, and reports
- Confirm all three customer 500s come from the same missing Firestore credentials exception path
- Add a controller-local Firestore fallback for the three broken customer pages
- Remove the public `Home` left-nav group from the customer workspace shell
- Add a focused regression test for missing Firestore credentials on the broken customer pages
- Update tracking files for the customer workspace QA task

## Current
- Refit the customer dashboard into the exact trading-first control-surface structure without changing architecture
- Keep the top strip focused on `Equity`, `Buying Power`, `Runtime State`, `Broker Sync`, and `Automation State`
- Keep the main blocks focused on `Positions Preview`, `Open Orders Preview`, `Latest Activity`, `Latest Signals`, and `Action Needed`
- Keep the side panel focused on broker, plan, bot, market, and sync checks using real current data where available

## Next
- Validate the dashboard view and focused customer dashboard tests
- Note any data points that still fall back to honest not-yet-synced wording
- Keep unrelated dirty files and backup artifacts out of any later staging or commit
