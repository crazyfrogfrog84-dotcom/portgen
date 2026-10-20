# Simple template visual review

This is an expert visual inspection of the Simple template, not a participant study. The reference images guide layout, hierarchy, and mood only; all fixture copy and artwork are fictional and locally generated.

## Rubric

Scores use the requested 1–5 scale. A final score below 4 would require another revision.

| Round | Visual hierarchy | Alignment and grid consistency | Spacing rhythm | Contrast and legibility | Density | Elevation polish | Reference mood | Change or finding |
|---|---:|---:|---:|---:|---:|---:|---:|---|
| 0 — main baseline | 3 | 3 | 2 | 4 | 2 | 2 | 3 | Rounded navigation and owner controls competed with content; spacing/dividers and surface depth felt repetitive. |
| 1 — first Simple pass | 4 | 4 | 4 | 5 | 3 | 4 | 4 | Typography and layout improved, but the no-photo monogram was 240 × 300px and felt too tall. |
| 2 — final | 4 | 4 | 4 | 5 | 4 | 4 | 4 | Changed monograms to a 240px square and raised the heading minimum and compact control labels to the specified sizes. All scores meet the threshold. |

Round 0 captures are in `docs/screenshots/simple/before/`. Final captures are in `docs/screenshots/simple/after/`. Each set contains 60 screenshots: three fixtures × five viewport widths (320, 375, 768, 1280, 1920) × two color modes × owner and visitor views. The final 320px captures also pass the no-horizontal-scroll assertion. The rich fixture includes a generated portrait and six generated project covers; the minimal fixture has one skill and no photo; the stress fixture covers long Unicode and escaped content, 30 skills, and 20 projects.

## Final visual decisions

- Use the reference's restrained header, aligned content edge, two-column hero, open sections, and three-column rich project grid, with a single green material palette.
- Keep owner controls in the shared header. At narrow widths they wrap into a second compact row; visitors retain only the theme toggle.
- Use a patterned monogram square when no portrait exists; uploaded portraits retain their 4:5 crop.
- Keep the neumorphic effect in token-driven shadows, with borders and solid selected/focus states carrying the actual information.

## Evidence

- Full screenshot, axe, and overflow matrix: `tests/e2e/simple-review-visual.spec.js`.
- Owner, keyboard, contrast, reduced-motion, and print checks: `tests/e2e/simple-template.spec.js` and `tests/e2e/owner-controls-simple.spec.js`.
- Increased-contrast screenshot: `docs/screenshots/simple/demo-rich-1280-contrast-more.png`.
- The minimal and rich owner captures at 375px/1280px in light/dark mode are committed under `docs/screenshots/simple/after/`.
- The final browser run passed 6 checked-in and Simple-focused tests, skipped the live-only smoke test, and completed 60 axe/overflow audits; `php artisan test` and the strict HCI release gate exited successfully.

The review is based on rendered screenshots and automated checks. It does not claim measured usability or accessibility outcomes from participants.
