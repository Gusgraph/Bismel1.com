# Current status

## Working
- Homepage renders correctly with no 500 errors
- Domain folder remains the live source repo
- Guest homepage layout is stable again after shared CSS cleanup
- Products page is redesigned and live
- Public products page supports affiliate-aware display pricing when referral tracking is active
- Prime, Execution, roadmap products, add-ons, and Demo Access are all structured on the products page
- Affiliate notice is visible at the top only when affiliate pricing is active
- Products page and homepage desktop widths were tightened selectively without changing the final two homepage sections
- Header label was changed from Plans to Products
- Git push to Bismel1.com repo works

## Recent fixes
- Rebuilt products page structure around real products instead of generic pricing tiers
- Added affiliate-aware pricing notice with pink-red glow styling
- Added discounted label and previous-price strike display for affiliate pricing state
- Changed status language from Ready to Live
- Added green live-state glow styling
- Tightened products page desktop widths
- Restored homepage shared guest styling after products-page CSS bleed
- Adjusted homepage desktop width so main sections are tighter while Infrastructure Behind the Bot and Live Market Feed stay full width
- Fixed mobile header nav button sizing
- Added stronger product copy for Prime, Execution, Demo Access, and add-ons
- Confirmed affiliate pricing works in a clean browser state

## Current truth
- Live app path: /var/www/html/bismel1.com
- Repo path: /var/www/html/bismel1.com
- Remote: git@github.com:Gusgraph/Bismel1.com.git
- Latest pushed commit should be checked with git log when needed

## Important implementation note
- Prime Stocks Bot Trader on the products page uses its own hardcoded bullet list in resources/views/plans.blade.php
- Changes to HomeController product item arrays do not affect that flagship bullet list

## Next
- Clean app.css further to reduce stacked products-page additions and keep shared guest rules cleaner
- Review homepage and products page side by side for final visual polish
- Decide whether affiliate pricing should persist from referral capture or be URL-only on the public products page
- Clean leftover local backup files and stray working-tree changes when ready
