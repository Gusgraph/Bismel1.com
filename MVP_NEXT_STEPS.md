# MVP Next Steps

## Known Safe Next Steps

- Add more factories and small seed variants for targeted scenarios instead of overloading the main demo seeder.
- Expand feature coverage around validation errors, failed writes, and edge-case empty states.
- Add more small reusable UI partials for repeated panel patterns in admin/customer detail pages.
- Introduce a small domain-level service layer for write flows if the controllers start to grow further.
- Add pagination or record windows if any list pages begin to accumulate enough local data to feel crowded.

## What Remains

- No background jobs, external broker integrations, or real license verification flows are active.
- No role-specific authorization matrix beyond the current app assumptions has been implemented.
- Reporting remains intentionally lightweight and descriptive, not analytical.
- Admin/customer routing is functional, but authentication and policy hardening for production deployment still need a later pass.
- There is no dedicated browser/E2E test layer yet.

## Recommended Order

1. Keep future changes inside the existing customer/admin test groupings.
2. Add targeted validation and failure-path coverage before expanding scope.
3. Only after that, start any post-MVP integration work.
