# PortGen template redesign audit (T0)

**Audit date:** 2026-10-20
**Baseline:** `origin/main` at `e717d257979247db63ac23ddf0c71035f3d2da38`
**Scope:** Existing application structure and gaps against redesign prompt sections 3–5.

## Baseline and branch state

- No `codex/phase-9-auth` branch exists locally, on `origin`, or in GitHub branch search, and there is no open PortGen pull request. The login/accounts precondition is therefore not pending. The Laravel starter migration creates the default `users` and password-reset tables, but there is no accounts workflow, no portfolio-to-user relationship, and no `Portfolio` policy.
- The audit is based on the updated `main`, as the phase plan requires. [PR #1](https://github.com/crazyfrogfrog84-dotcom/portgen/pull/1) merged `codex/render-deployment` into `main` at `e717d257`; its two GitHub CI runs passed. `render.yaml` now configures the Render service to deploy from `main`, and the merged baseline includes the Render Docker service and dedicated Free PostgreSQL database.
- `composer.json` now requires Laravel `^12.0` and PHP `^8.3`, matching the redesign prompt's framework version. The CSS and browser stack is Tailwind 4, Vite 8, Playwright, and axe-core. The deployment branch also added a strict test database guard: tests allow only in-memory SQLite or the local PostgreSQL `portgen_test` database and reject `DB_URL`.
- The deployment merge made some documentation stale: `docs/SYSTEM_DOCUMENTATION.md` still says the Render resources and live checks are pending and describes the deployment branch CI as pending. The phase T9 documentation refresh should reconcile those statements with the merged, live deployment.

## What is shared today

### Content and rendering

`Portfolio` is the common content model. It stores `full_name`, `headline`, `about_me`, JSON arrays for `education`, `work_experience`, `skills`, `projects`, and `social_links`, and `template_choice`. `projects` currently has name, description, and URL fields. The model has an explicit `$fillable` list that excludes identifiers and route slugs. The shared Form Request rules validate entered fields and constrain links to HTTP/HTTPS. Blade output uses escaped expressions.

All three public views include `resources/views/portfolios/templates/sections.blade.php`. That shared partial renders About, Education, Experience, Skills, Projects, and Links in the same order; Education, Experience, and Links are conditional. The views are currently short wrappers with different introductory labels, project/skill counts in Modern, and outer styling. They are variations on one layout, rather than three different arrangements of shared components.

The public page is wrapped by the common `layouts.app` shell, which supplies the document landmarks, skip link, generic PortGen header/navigation, main region, footer, and help dialog. The public portfolio itself has no section navigation. The shell navigation is the same app navigation on every route, not the portfolio-specific Home/About/Skills/Projects/Experience/Education/Contact model.

### Styling and app shell

`resources/css/app.css` currently owns both public-template colors and neutral `--ui-*` controls. It has light values plus `prefers-color-scheme: dark` overrides, shared control styling, a visible keyboard focus outline, 44 px minimum control heights, and reduced-motion handling. Simple is neutral monochrome, Modern is blue, and Creative is warm cream with plum/magenta accents. These are not the requested green, indigo/violet/blue, and cream/yellow-on-cool-charcoal palettes.

The three public palettes are applied with `.theme-simple`, `.theme-modern`, and `.theme-creative`. There is no `tokens.css`, explicit `[data-template][data-theme]` palette selection, visible System/Light/Dark control, print stylesheet, or self-hosted font package. The app shell already uses a set of neutral `--ui-*` values and common button/input/status classes, but those values live beside the portfolio palette definitions in `app.css`; there is no shared PortGen Blade component library under `resources/views/components/pf/`.

### Existing behavior worth preserving

- The creation form has progressive-disclosure sections, inline validation, local draft recovery, repeatable education/experience/skill/project/link rows, and a three-step creation flow.
- Signed owner URLs protect edit, template-change, delete, and undo routes; rate limits, URL validation, escaped output, an explicit fillable list, and the existing HCI tracker/release test are established contracts. GitHub CI now runs the PHP suite against PostgreSQL 16 and includes the browser accessibility workflow; tests are guarded from production database URLs.
- The CSS has a shared visible focus treatment, large controls, responsive layout rules, and a `prefers-reduced-motion` rule. The Playwright flow checks keyboard operation, 320 px overflow, and axe findings for the current set of screens.

## Gaps against the redesign contract

| Area | Current baseline | Required work in later phases |
|---|---|---|
| Sections and outline | Shared order is About, Education, Experience, Skills, Projects, Links. There are no `#home`, `#skills`, etc. IDs or portfolio anchor navigation. Projects still render a “No projects shared yet” message. | Render the same ordered Home, About, Skills, Projects, Experience, Education, Contact sections in every template; omit a section and its navigation entry when it has no owner data; preserve one name `<h1>`, section `<h2>` headings, landmarks, and the first-position skip link. |
| Navigation | The shell has generic Help/Create links; public templates have no section navigation or active-section behavior. | Add the shared ordered section navigation, template-specific placement, plain-anchor fallback, and IntersectionObserver highlighting with `aria-current="true"`. |
| Template identity | Simple, Modern, and Creative all include the same section partial; differences are primarily wrapper, labels, and palette. | Build three distinct layouts from one content model and reusable `pf` Blade components. |
| Theme and controls | Dark mode follows the operating-system preference only. No visible theme control or remembered viewer choice exists. | Add the same System/Light/Dark toggle, localStorage persistence with failure handling, keyboard/focus behavior, minimum target sizing, same-tab project navigation, labelled external-link icons, reduced-motion support, and a shared print stylesheet. Add Creative Calm view and its automatic accessibility fallbacks. |
| Tokens and typography | Colors are centralized in `app.css`, but use per-template variable names and a media query rather than six explicit semantic palettes. Typography is the system sans stack. No print token set or self-hosted font packages are present. | Move semantic tokens, six explicit palettes, type/spacing/radius/shadow/motion values into `tokens.css`; use only self-hosted fonts and keep the neutral app-shell tokens separate from template palettes. |
| Template content | The public templates show the existing fields. There are no owner-provided location, availability, contact email, motto, interests, or project tags. | Add these nullable fields and form guidance while keeping all output owner-entered; derive optional skill groupings from a catalog without inventing claims. |
| Images | No portfolio image table, upload controls, image-serving route, processing pipeline, or GD setup exists. The current design works without images. | Add the additive image table and secure processing/serving path, while preserving monogram and generated-cover fallbacks. The Dockerfile currently has no GD installation. |
| Template selection | The chooser uses native required radios and previews snippets of the user's name, headline, skills, and first project. It already says the choice can be changed later. Previews are not the real rendered templates and are not marked `inert`/`aria-hidden`; no full-page preview link, Back action from a preview, or “Best for” description is present. | Render real inert previews from the selected user's data, show the fit and current selection, support the specified keyboard flow, and offer a full-page preview with a same-tab return path. Show the chosen template on completion. |
| Consistency and evidence | Existing shared markup and CSS provide some common behavior, but there are no cross-template parity tests. `HciTrackerTest` currently expects S/N/H/U/C/V/R rows and requires every row to be Verified with a path and evidence during an HCI release. It has no T1–T8 rows. | Extend the strict tracker contract and add parity, six-palette contrast, image, stress, viewport/theme, print, and performance coverage. Use named tests or committed screenshots as evidence; do not mark unfinished criteria Verified. |

## Template-by-template findings

- **Simple:** calm monochrome wrapper with name/headline, then the shared section list. It is not yet the requested green, two-column résumé layout, definition-list facts, or three-column project-card baseline.
- **Modern:** blue wrapper with a derived project/skill count and the shared section list. It has no sticky portfolio navigation, glow/portrait treatment, highlights card, skill-logo tiles, featured-card hierarchy, timeline, or contact bar.
- **Creative:** cream/plum wrapper with a different greeting and the shared section list. It has no navigation rail, scrapbook decoration, paper project band, owner-provided note, or Calm view. The supplied scrapbook mockup must guide layout only; its pictured people, stock imagery, and fictional text are not product content.

Current project and social links use `target="_blank"` and `rel="noopener noreferrer"`; the redesign contract calls for same-tab project navigation and a visible external-link cue with an accessible name.

## Reference pack

The three supplied PNGs are stored under `docs/design-references/` as design-only references. Their people, stock images, and fictional copy are not to be extracted or reused. The product's existing fictional seed remains the source for demo content.

- `docs/design-references/simple-green.png` — calm green, résumé-like hierarchy.
- `docs/design-references/modern-indigo.png` — structured indigo/blue cards and skill tiles.
- `docs/design-references/creative-scrapbook.png` — paper, rail navigation, and handwritten decoration, with essential information kept on solid surfaces.

## T0 outcome

The updated `main` has the Render deployment changes, but the portfolio UI still has a shared data model, app shell, and public section markup that do not meet the new three-layout, theme, media, or cross-template parity contract. T0 adds this audit, the reference pack, and the decision-log entries; it does not change runtime behavior, schema, or HCI verification status.
