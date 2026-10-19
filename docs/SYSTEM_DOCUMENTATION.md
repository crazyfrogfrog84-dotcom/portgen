# PORTGEN

## Dynamic Online Portfolio Generator

**Project type:** School software system documentation

**Live URL:** Not deployed

**Target audience:** Students, freelancers, and job seekers

**Author:** PortGen project implementation

**Date:** 2026-10-19

**Version:** 0.1.0

PortGen is a Laravel application for building and publishing a personal portfolio from one structured form. This paper documents the implemented system, the evidence gathered from automated checks and expert inspection, and the Render deployment plan. The public source repository contains the application, design records, and HCI evidence. No classmates have taken part in the usability study yet.

## 1. System overview

### 1.1 Problem and solution

Students and early-career professionals often have information spread across documents and profile sites. A portfolio can be time-consuming to format and keep consistent. PortGen collects a person's introduction, education, work experience, skills, projects, and social links in one guided form. The person chooses a responsive presentation and receives a public portfolio URL plus a separate signed link for private management.

### 1.2 Objectives

1. Collect structured portfolio information in a guided, recoverable flow.
2. Render the same information with Simple, Modern, or Creative presentation styles.
3. Let a portfolio owner edit, change the template, and delete the portfolio without requiring an account.
4. Support keyboard operation, responsive layouts, clear feedback, and a dark color scheme.
5. Keep database constraints, request validation, automated browser checks, and HCI evidence in the project repository.

### 1.3 Scope and limits

The implementation includes portfolio creation, template selection, public viewing, signed-link management, editing, deletion with a ten-second undo window, friendly error pages, a demo portfolio, CI configuration, and Docker deployment configuration for Render. It does not include accounts, password recovery, image uploads, payments, analytics, or user research results. The public demo contains fictional information.

Laravel 11 was upgraded to Laravel 12.70.0 after its security-fix window ended; the upgraded dependency set currently passes `composer audit --locked`. The Render container configuration is prepared, but the Render service and a dedicated PortGen Supabase database still need creation. The deployment must run additive migrations before serving traffic. See section 8 for the release configuration and current status.

## 2. Users and needs

### 2.1 Personas and tasks

**Table 1. Target users and primary needs**

| Persona | Situation and tasks | Likely needs |
|---|---|---|
| Student | Building an early-career profile with limited work history; adds education, skills, and coursework projects, selects a template, and shares the page. | Examples for unfamiliar fields, useful empty states, and a way to show projects without paid experience. |
| Freelancer | Presents services and selected client work; edits the portfolio as work changes and shares it with prospects. | Fast editing, a work-first layout, and a private owner link that can be saved. |
| Job seeker | Prepares a focused profile for applications; selects a readable layout and revises details. | Straightforward form, keyboard access, clear completion feedback, and predictable editing and deletion. |

These are design personas and assumptions, not research findings. The main assumptions are that users can provide the information in one sitting if examples are available, that the public and private links are understandable, and that three initial templates provide enough variety. The study kit in `docs/usability-testing/` leaves participant outcomes open for classmates.

### 2.2 User-needs walkthrough

The student path permits empty education and work sections while requiring at least one skill. This supports users with limited employment history while ensuring that a portfolio has at least one concrete strength. The freelancer path supports repeatable project entries and an owner link for later changes. The job-seeker path uses familiar labels, inline validation, a completion stage, and a confirmation before deletion. These are expert walkthrough conclusions to validate with participants.

## 3. System architecture and technology stack

### 3.1 Architecture

The browser submits validated portfolio fields to Laravel. The controller and Eloquent model persist structured values in PostgreSQL JSONB columns. Blade renders the public page from the chosen template. A signed URL provides private owner access without an account. On Render, the browser's signed purge request and the Laravel scheduler remove a deleted record when its ten-second undo period ends.

<!-- PAGEBREAK -->

**Figure 1. Application architecture**

![Application architecture](diagrams/system-architecture.png)

The user-facing application is a modular Laravel monolith. The controller handles the portfolio workflow, Form Requests validate input, signed middleware protects owner routes, Blade templates render screens, and the database stores portfolio records. Render serves the app with Apache and supervises `schedule:work` alongside the web server.

### 3.2 Technology stack

**Table 2. Implemented and planned technology**

| Layer | Technology | Use and status |
|---|---|---|
| Server framework | Laravel 12.70.0, PHP 8.3 target | MVC routing, validation, ORM, migrations, signed URLs, and scheduled commands. The local workstation runs PHP 8.5. |
| Views and styling | Blade, Tailwind CSS 4, Vite 8 | Server-rendered views, design tokens, responsive styling, and production asset bundles. |
| Database | PostgreSQL 16 in CI; Supabase PostgreSQL planned for production | PostgreSQL is configured as a CI service. No production database is connected. Local tests use isolated SQLite. |
| Browser checks | Playwright 1.64 and axe-core 4.14 | Keyboard journey, responsive screenshots, touch target checks, and accessibility audits. |
| Container | Docker, PHP 8.3 Apache on Render | Multi-stage image configuration is committed; the Render image build runs in GitHub Actions. |
| Hosting | Render web service with Supabase PostgreSQL | Supabase stores portfolio data, sessions, and cache. |
| CI | GitHub Actions | Workflow defines PHP, PostgreSQL, build, browser, and HCI artifact steps. It has not run on GitHub. |

**Table 3. Main workflow stages**

| Stage | User action | System response |
|---:|---|---|
| 1 | Enter portfolio details | Form Request validates fields; the app stores a draft locally and shows inline feedback. |
| 2 | Compare and choose a template | The app previews the user's information and saves the selected supported template through a signed route. |
| 3 | Publish and share | The app displays the public URL and a private signed management URL with a copy action. |
| 4 | Edit or change a template | The owner opens the private signed link and updates the same portfolio record. |
| 5 | Delete or undo | The app soft-deletes the record, allows signed recovery for ten seconds, and sends a signed purge request when the undo period ends. |

## 4. Database schema and data dictionary

### 4.1 Portfolio entity

**Figure 2. Portfolio data model**

![Portfolio entity diagram](diagrams/portfolio-schema.png)

There is one application-owned `portfolios` entity. The structured content fields store arrays as JSONB in PostgreSQL and are cast to arrays by Eloquent. The table has no foreign-key relationship because portfolios do not require user accounts.

**Table 4. `portfolios` data dictionary**

| Column | PostgreSQL type | Constraint | Description |
|---|---|---|---|
| `id` | uuid | Primary key | Application-generated portfolio identifier. |
| `slug` | varchar(10) | Unique, required | Random ten-character route key used in public URLs. |
| `full_name` | varchar(120) | Required | Name displayed on the portfolio. |
| `headline` | varchar(180) | Required | Short professional tagline. |
| `about_me` | text | Required | Introduction, limited to 2,000 characters by request validation. |
| `education` | jsonb | Required array | Up to ten education entries with institution, qualification, and year. |
| `work_experience` | jsonb | Required array | Up to ten work entries with company, role, description, and month fields. |
| `skills` | jsonb | Required array, 1-30 entries | Skill names, up to 50 characters each. |
| `projects` | jsonb | Required array | Up to twenty entries with name, description, and optional HTTP/HTTPS URL. |
| `social_links` | jsonb | Required array | Up to twenty labeled HTTP/HTTPS links. |
| `template_choice` | varchar | Nullable; check constraint | Null before selection, otherwise `Simple`, `Modern`, or `Creative`. |
| `created_at` | timestamp | Nullable | Laravel creation timestamp. |
| `updated_at` | timestamp | Nullable | Laravel update timestamp. |
| `deleted_at` | timestamp | Nullable | Soft-delete marker; the record remains recoverable during the undo period. |
| `undo_until` | timestamp | Nullable | Deadline after which the scheduled purge may permanently delete the row. |

PostgreSQL enforces the template choice with a CHECK constraint. SQLite migration tests use insert/update triggers to enforce the same allowed values. `Portfolio` has an explicit `$fillable` list; IDs, slugs, timestamps, and deletion fields cannot be mass assigned from request input. The factory creates test data and the idempotent seeder creates the fictional `portgen001` demo.

## 5. Key routes and controller logic

### 5.1 Route map

**Table 5. Main HTTP routes**

| Method | URL | Purpose | Access |
|---|---|---|---|
| GET | `/` | Redirect to the portfolio form | Public |
| GET | `/portfolios/create` | Open the creation flow | Public |
| POST | `/store` | Validate and create the portfolio | Public, rate limited |
| GET | `/portfolio/{slug}/select-template` | Choose a presentation | Signed |
| POST | `/portfolio/{slug}/save-template` | Save an allowed template | Signed, rate limited |
| GET | `/p/{slug}` | Display the public portfolio | Public |
| GET | `/manage/{slug}` | Open owner controls | Signed |
| GET | `/manage/{slug}/edit` | Edit the portfolio | Signed |
| PUT | `/manage/{slug}` | Validate and update content | Signed, rate limited |
| DELETE | `/manage/{slug}` | Begin the delete and undo flow | Signed, rate limited |
| GET | `/manage/{slug}/deleted` | Display the deletion countdown | Signed |
| POST | `/manage/{slug}/undo` | Restore during the undo window | Signed, rate limited |
| DELETE | `/manage/{slug}/purge` | Purge after the undo period | Signed, rate limited |
| GET | `/help` | Read help and keyboard shortcuts | Public |
| GET | `/up` | Health check | Public health endpoint |

Error preview routes exist only in local and test environments. The production routes return friendly 403, 404, 419, 429, and 500 views. State-changing routes retain CSRF protection and use Laravel throttling.

### 5.2 Controller and validation

`PortfolioController` implements create, store, template selection, template save, public show, manage, edit, update, delete, deleted-state display, undo, and purge. `StorePortfolioRequest` and `UpdatePortfolioRequest` share portfolio rules. `SaveTemplateRequest` restricts template values. URLs accept only HTTP and HTTPS schemes. User content is rendered through escaped Blade expressions.

Owner management is a bearer-link model. Laravel signed URLs without an expiry protect owner routes; anyone who obtains the private link can manage that portfolio. The completion screen explains the link, shows the public URL separately, and provides a copy control. There is no account-based recovery if the owner loses the private link.

Security middleware adds Content Security Policy, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, and `Permissions-Policy`. `APP_DEBUG` is set to false in Render production configuration. Production also requires a real `APP_KEY` in the hosting secret store.

## 6. User interface design and HCI compliance

### 6.1 Interaction design

The creation flow uses three named stages and collapsible form sections. The browser stores recoverable draft fields in localStorage, excluding CSRF and method fields. A restore prompt appears on reload. Inline errors identify missing values; submit, copy, and deletion provide visible status. Users can operate the core flow with pointer controls or keyboard navigation and can open the shortcut dialog from the shared layout.

The three public templates group the same information differently. Simple uses a quiet neutral palette, Modern uses neighboring blue hues, and Creative uses warm cream with plum and magenta accents. All colors are defined as CSS custom properties in one token block with dark-mode overrides. The test computes contrast for configured text and interface pairs in both modes. It is a token-level regression test; it is not a manual audit of every rendered color combination.

### 6.2 Accessibility evidence

The Playwright suite tests the full keyboard create-and-publish journey, skip-link behavior, inline validation, local draft restoration, template changes, copy feedback, signed management, edit, delete and undo, help, and error screens. For each of 17 captured states it runs axe-core at 375 by 812 and 1280 by 800, checks visible control target dimensions, and checks the mobile layout at 320 pixels for horizontal overflow. The create screen is additionally inspected at 200% browser zoom. The automated suite reports no serious or critical axe violations. The suite does not claim complete WCAG conformance or replace assistive-technology user testing.

### 6.3 Screenshot evidence

Each of the 17 listed screen states has a mobile and desktop full-page image under `docs/screenshots/`. Captures use fictional profile content. The private signed owner URL is replaced in the saved screenshot with an invalid example value; the browser test still verifies that the actual link copies correctly before redaction. Each exported figure shows the 375 by 812 mobile viewport and 1280 by 800 desktop viewport crop, with the full-page captures retained in the repository. Each pair is annotated with its principal HCI evidence.

#### 6.3.1 Creation and template selection

The first three figures show the form, template previews, and completion screen. They provide evidence for staged task structure, field guidance, visible status, and preview-based recognition.

**Figure 3. Create form, step 1**

Mobile: `docs/screenshots/mobile/step-1-your-info.png`

Desktop: `docs/screenshots/desktop/step-1-your-info.png`

HCI evidence: S1, S5, S8, N1, N2, N3, N4, H2, H5, H8, U1, U3, U6, U7, V1, V2, V3, V7, V8.

**Figure 4. Template selection, step 2**

Mobile: `docs/screenshots/mobile/step-2-choose-template.png`

Desktop: `docs/screenshots/desktop/step-2-choose-template.png`

HCI evidence: N2, N5, N3, H6, U3, C1, V3.

**Figure 5. Completion and sharing, step 3**

Mobile: `docs/screenshots/mobile/step-3-done-share.png`

Desktop: `docs/screenshots/desktop/step-3-done-share.png`

HCI evidence: S3, S4, H1, U4, C4, V7. The owner link is redacted in the saved image.

#### 6.3.2 Public portfolio templates

**Figure 6. Public Simple template**

Mobile: `docs/screenshots/mobile/template-simple.png`

Desktop: `docs/screenshots/desktop/template-simple.png`

HCI evidence: S1, H4, H8, C1, C2, C3, V1, V2, V4, V5, V8.

**Figure 7. Public Modern template**

Mobile: `docs/screenshots/mobile/template-modern.png`

Desktop: `docs/screenshots/desktop/template-modern.png`

HCI evidence: H6, C1, C2, C3, V3, V5, V6.

**Figure 8. Public Creative template**

Mobile: `docs/screenshots/mobile/template-creative.png`

Desktop: `docs/screenshots/desktop/template-creative.png`

HCI evidence: H6, C1, C2, C3, V4, V6, V8.

**Figure 9. Creative template in dark mode**

Mobile: `docs/screenshots/mobile/dark-mode-creative.png`

Desktop: `docs/screenshots/desktop/dark-mode-creative.png`

HCI evidence: C2, C3, V5. The CSS contrast test verifies configured palette pairs in light and dark mode.

#### 6.3.3 Owner management and recovery

**Figure 10. Private manage screen**

Mobile: `docs/screenshots/mobile/manage.png`

Desktop: `docs/screenshots/desktop/manage.png`

HCI evidence: S1, S7, H1, H3, H4, N7, V1, V8.

**Figure 11. Edit screen**

Mobile: `docs/screenshots/mobile/edit.png`

Desktop: `docs/screenshots/desktop/edit.png`

HCI evidence: S1, N4, H3, U2, V2, V8.

**Figure 12. Delete and undo**

Mobile: `docs/screenshots/mobile/delete-undo.png`

Desktop: `docs/screenshots/desktop/delete-undo.png`

HCI evidence: S6, H3, H5, N6, U5. The undo window is ten seconds by requirement.

#### 6.3.4 Help and errors

**Figure 13. Help and FAQ**

Mobile: `docs/screenshots/mobile/help-faq.png`

Desktop: `docs/screenshots/desktop/help-faq.png`

HCI evidence: N1, H2, H10, U1.

**Figure 14. Keyboard shortcuts dialog**

Mobile: `docs/screenshots/mobile/keyboard-shortcuts.png`

Desktop: `docs/screenshots/desktop/keyboard-shortcuts.png`

HCI evidence: S2, S3, H7, H10, U2, U6.

**Figures 15-19. Friendly error states**

For each error, the mobile and desktop captures appear in the exported paper: 403 `docs/screenshots/{mobile,desktop}/error-403.png`; 404 `error-404.png`; 419 `error-419.png`; 429 `error-429.png`; 500 `error-500.png`. The pages provide plain-language feedback and a return path. HCI evidence: S3, S5, N6, H1, H9, U4, C4.

### 6.3.5 Complete screenshot gallery

The following paired figures embed every mobile and desktop screen capture. Labels name the state shown and the primary principles illustrated.

<!-- PAGEBREAK -->
**Figure 20. Create form, step 1**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Create form, step 1](screenshots/mobile/step-1-your-info.png) | ![Desktop screenshot, Create form, step 1](screenshots/desktop/step-1-your-info.png) |
**HCI principles:** S8 Reduce short-term memory load; N4 Get the mappings right; H5 Error prevention; U7 Size and space for approach and use; V3 Visual hierarchy.
<!-- PAGEBREAK -->
**Figure 21. Template selection, step 2**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Template selection, step 2](screenshots/mobile/step-2-choose-template.png) | ![Desktop screenshot, Template selection, step 2](screenshots/desktop/step-2-choose-template.png) |
**HCI principles:** N2 Simplify task structure; H6 Recognition rather than recall; C1 Deliberate color scheme per template; V3 Visual hierarchy.
<!-- PAGEBREAK -->
**Figure 22. Completion and sharing, step 3**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Completion and sharing, step 3](screenshots/mobile/step-3-done-share.png) | ![Desktop screenshot, Completion and sharing, step 3](screenshots/desktop/step-3-done-share.png) |
**HCI principles:** S3 Informative feedback; H1 Visibility of system status; C4 Never rely on color alone; V7 Dominance. the signed owner link is redacted in saved evidence
<!-- PAGEBREAK -->
**Figure 23. Modern portfolio preview**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Modern portfolio preview](screenshots/mobile/template-modern.png) | ![Desktop screenshot, Modern portfolio preview](screenshots/desktop/template-modern.png) |
**HCI principles:** H6 Recognition rather than recall; C1 Deliberate color scheme per template; C3 Sufficient value contrast, WCAG AA; V5 Contrast.
<!-- PAGEBREAK -->
**Figure 24. Public Simple portfolio**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Public Simple portfolio](screenshots/mobile/template-simple.png) | ![Desktop screenshot, Public Simple portfolio](screenshots/desktop/template-simple.png) |
**HCI principles:** H8 Aesthetic and minimalist design; C1 Deliberate color scheme per template; V2 Gestalt principles; V4 Balance.
<!-- PAGEBREAK -->
**Figure 25. Public Creative portfolio**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Public Creative portfolio](screenshots/mobile/template-creative.png) | ![Desktop screenshot, Public Creative portfolio](screenshots/desktop/template-creative.png) |
**HCI principles:** H6 Recognition rather than recall; C1 Deliberate color scheme per template; C2 Color temperature matches the template's message; V6 Scale.
<!-- PAGEBREAK -->
**Figure 26. Private portfolio management**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Private portfolio management](screenshots/mobile/manage.png) | ![Desktop screenshot, Private portfolio management](screenshots/desktop/manage.png) |
**HCI principles:** S7 Internal locus of control; H1 Visibility of system status; H3 User control and freedom; H4 Consistency and standards.
<!-- PAGEBREAK -->
**Figure 27. Edit portfolio**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Edit portfolio](screenshots/mobile/edit.png) | ![Desktop screenshot, Edit portfolio](screenshots/desktop/edit.png) |
**HCI principles:** H3 User control and freedom; N4 Get the mappings right; U2 Flexibility in use; V2 Gestalt principles.
<!-- PAGEBREAK -->
**Figure 28. Delete and undo**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Delete and undo](screenshots/mobile/delete-undo.png) | ![Desktop screenshot, Delete and undo](screenshots/desktop/delete-undo.png) |
**HCI principles:** S6 Easy reversal of actions; H3 User control and freedom; U5 Tolerance for error. ten-second undo state
<!-- PAGEBREAK -->
**Figure 29. Help and FAQ**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Help and FAQ](screenshots/mobile/help-faq.png) | ![Desktop screenshot, Help and FAQ](screenshots/desktop/help-faq.png) |
**HCI principles:** N1 Knowledge in the world and in the head; H10 Help and documentation; U1 Equitable use.
<!-- PAGEBREAK -->
**Figure 30. Keyboard shortcuts dialog**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Keyboard shortcuts dialog](screenshots/mobile/keyboard-shortcuts.png) | ![Desktop screenshot, Keyboard shortcuts dialog](screenshots/desktop/keyboard-shortcuts.png) |
**HCI principles:** S2 Shortcuts for frequent users; H7 Flexibility and efficiency of use; U2 Flexibility in use.
<!-- PAGEBREAK -->
**Figure 31. Invalid private link, 403**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Invalid private link, 403](screenshots/mobile/error-403.png) | ![Desktop screenshot, Invalid private link, 403](screenshots/desktop/error-403.png) |
**HCI principles:** S3 Informative feedback; H9 Help users recognize, diagnose, recover from errors; C4 Never rely on color alone.
<!-- PAGEBREAK -->
**Figure 32. Missing portfolio, 404**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Missing portfolio, 404](screenshots/mobile/error-404.png) | ![Desktop screenshot, Missing portfolio, 404](screenshots/desktop/error-404.png) |
**HCI principles:** S3 Informative feedback; H9 Help users recognize, diagnose, recover from errors; C4 Never rely on color alone.
<!-- PAGEBREAK -->
**Figure 33. Expired form, 419**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Expired form, 419](screenshots/mobile/error-419.png) | ![Desktop screenshot, Expired form, 419](screenshots/desktop/error-419.png) |
**HCI principles:** S3 Informative feedback; N6 Design for error; H9 Help users recognize, diagnose, recover from errors.
<!-- PAGEBREAK -->
**Figure 34. Rate limited, 429**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Rate limited, 429](screenshots/mobile/error-429.png) | ![Desktop screenshot, Rate limited, 429](screenshots/desktop/error-429.png) |
**HCI principles:** S3 Informative feedback; N6 Design for error; H9 Help users recognize, diagnose, recover from errors.
<!-- PAGEBREAK -->
**Figure 35. Server error, 500**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Server error, 500](screenshots/mobile/error-500.png) | ![Desktop screenshot, Server error, 500](screenshots/desktop/error-500.png) |
**HCI principles:** S3 Informative feedback; N6 Design for error; H9 Help users recognize, diagnose, recover from errors.
<!-- PAGEBREAK -->
**Figure 36. Creative portfolio in dark mode**
| Mobile view | Desktop view |
|---|---|
| ![Mobile screenshot, Creative portfolio in dark mode](screenshots/mobile/dark-mode-creative.png) | ![Desktop screenshot, Creative portfolio in dark mode](screenshots/desktop/dark-mode-creative.png) |
**HCI principles:** C2 Color temperature matches the template's message; C3 Sufficient value contrast, WCAG AA; V5 Contrast.
<!-- LANDSCAPE_START -->
### 6.4 Full HCI tracker

The tables below reproduce all 48 rows from `docs/HCI_TRACKER.md`. All 48 rows now have a named test or committed screenshot, and the `HCI_RELEASE=1` release test passes. `Verified` means the row cites implementation evidence; it does not mean every criterion has been tested with participants.

#### Shneiderman's 8 Golden Rules
**Table 6. Shneiderman's 8 Golden Rules HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| S1 | Consistency | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Shared layout, navigation, spacing, and repeated button styles. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/desktop/step-1-your-info.png | Verified |
| S2 | Shortcuts for frequent users | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Discoverable save and step navigation shortcuts. | docs/screenshots/mobile/keyboard-shortcuts.png; docs/screenshots/desktop/keyboard-shortcuts.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |
| S3 | Informative feedback | `resources/views/layouts/app.blade.php`; `resources/views/portfolios/show.blade.php` | Announce saved, published, copied-link, and error status in polite live regions. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/desktop/step-3-done-share.png; PortfolioRoutesTest::test_template_selection_uses_and_preserves_the_private_link_for_the_creator | Verified |
| S4 | Dialogs that yield closure | `resources/views/portfolios/manage.blade.php`; `resources/views/portfolios/show.blade.php` | Name the portfolio in the delete dialog and show the private owner link when creation finishes. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/desktop/step-3-done-share.png | Verified |
| S5 | Error prevention and simple error handling | `app/Http/Requests/Concerns/HasPortfolioRules.php`; `resources/views/portfolios/form.blade.php`; `resources/js/app.js` | Validate structured fields and safe URLs, then show specific inline recovery messages. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; docs/screenshots/mobile/step-1-your-info.png | Verified |
| S6 | Easy reversal of actions | `routes/web.php`; `resources/views/portfolios/deleted.blade.php`; `app/Http/Controllers/PortfolioController.php` | Offer a signed 10-second undo before permanent removal. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; docs/screenshots/mobile/delete-undo.png; docs/screenshots/desktop/delete-undo.png | Verified |
| S7 | Internal locus of control | `routes/web.php`; `app/Http/Controllers/PortfolioController.php` | Require a private signed URL for management and keep transitions user initiated. | PortfolioRoutesTest::test_signed_manage_link_opens_and_unsigned_link_is_forbidden; docs/screenshots/mobile/manage.png | Verified |
| S8 | Reduce short-term memory load | `resources/views/portfolios/form.blade.php`; `resources/js/app.js` | Group form fields, show completion labels, and offer local draft restoration. | docs/screenshots/mobile/step-1-your-info.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |

#### Norman's 7 Principles
**Table 7. Norman's 7 Principles HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| N1 | Knowledge in the world and in the head | `resources/views/portfolios/form.blade.php`; `resources/views/help.blade.php` | Use examples and plain-language help beside unfamiliar fields. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| N2 | Simplify task structure | `resources/views/portfolios/stepper.blade.php`; `resources/views/portfolios/create.blade.php`; `resources/views/portfolios/select-template.blade.php` | Divide creation into a visible three-step flow. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/desktop/step-2-choose-template.png | Verified |
| N3 | Make things visible | `resources/views/portfolios/stepper.blade.php`; `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Show current step, section completion, save status, and submit loading state. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/mobile/step-3-done-share.png | Verified |
| N4 | Get the mappings right | `resources/views/portfolios/form.blade.php`; `resources/views/portfolios/templates/sections.blade.php` | Use familiar field names and group entered information under matching headings. | docs/screenshots/mobile/step-1-your-info.png | Verified |
| N5 | Use constraints | `app/Http/Requests/SaveTemplateRequest.php`; `app/Http/Requests/Concerns/HasPortfolioRules.php` | Restrict template choice and URL schemes before storage. | PortfolioRoutesTest::test_template_choice_is_limited_to_the_three_supported_values; docs/screenshots/mobile/step-2-choose-template.png | Verified |
| N6 | Design for error | `resources/js/app.js`; `resources/views/portfolios/deleted.blade.php` | Autosave drafts, explain validation failures, and provide delete recovery. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window | Verified |
| N7 | When all else fails, standardize | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Reuse semantic controls, navigation, and status components. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/edit.png | Verified |

#### Nielsen's 10 Usability Heuristics
**Table 8. Nielsen's 10 Usability Heuristics HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| H1 | Visibility of system status | `resources/views/layouts/app.blade.php`; `resources/views/portfolios/show.blade.php` | Announce saved and published status and show the completion step. | docs/screenshots/mobile/step-3-done-share.png; PortfolioRoutesTest::test_template_selection_uses_and_preserves_the_private_link_for_the_creator | Verified |
| H2 | Match between system and real world | `resources/views/portfolios/form.blade.php`; `resources/views/help.blade.php` | Use plain labels such as Skills, Projects, Save, and Next, with concrete examples. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| H3 | User control and freedom | `routes/web.php`; `resources/views/portfolios/deleted.blade.php`; `resources/js/app.js` | Support back navigation, template changes, and deletion undo. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; docs/screenshots/mobile/delete-undo.png | Verified |
| H4 | Consistency and standards | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Keep page structure, focus states, and control roles consistent. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/template-simple.png | Verified |
| H5 | Error prevention | `app/Http/Requests/Concerns/HasPortfolioRules.php`; `app/Http/Requests/SaveTemplateRequest.php` | Validate required values, URL schemes, and supported templates before saving. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; PortfolioRoutesTest::test_template_choice_is_limited_to_the_three_supported_values | Verified |
| H6 | Recognition rather than recall | `resources/views/portfolios/select-template.blade.php`; `resources/views/portfolios/form.blade.php` | Preview each template with the person's own information and show field examples. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/mobile/template-modern.png | Verified |
| H7 | Flexibility and efficiency of use | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Provide keyboard shortcuts alongside standard pointer controls. | docs/screenshots/mobile/keyboard-shortcuts.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |
| H8 | Aesthetic and minimalist design | `resources/views/portfolios/form.blade.php`; `resources/css/app.css` | Show only the current creation task and group related information. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/template-simple.png | Verified |
| H9 | Help users recognize, diagnose, recover from errors | `resources/views/errors/status.blade.php`; `resources/js/app.js`; `app/Http/Requests/Concerns/HasPortfolioRules.php` | Show friendly server errors and specific inline field guidance. | docs/screenshots/mobile/error-403.png; docs/screenshots/mobile/error-500.png; PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls | Verified |
| H10 | Help and documentation | `resources/views/help.blade.php`; `resources/views/layouts/app.blade.php` | Link a help page and shortcut dialog from every screen. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |

#### Universal Design
**Table 9. Universal Design HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| U1 | Equitable use | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Provide keyboard and screen-reader access to core tasks. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |
| U2 | Flexibility in use | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Offer shortcuts and ordinary pointer controls. | docs/screenshots/mobile/keyboard-shortcuts.png; docs/screenshots/desktop/keyboard-shortcuts.png | Verified |
| U3 | Simple and intuitive use | `resources/views/portfolios/create.blade.php`; `resources/views/portfolios/select-template.blade.php`; `resources/views/portfolios/show.blade.php` | Label the path from portfolio information through template choice to sharing. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/step-2-choose-template.png | Verified |
| U4 | Perceptible information | `resources/css/app.css`; `resources/views/portfolios/form.blade.php` | Use contrast-tested text and pair status colors with readable labels. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/step-3-done-share.png | Verified |
| U5 | Tolerance for error | `routes/web.php`; `resources/views/portfolios/deleted.blade.php` | Keep deleted content recoverable for 10 seconds, then permanently remove it. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; PortfolioModelTest::test_purge_command_removes_portfolios_after_the_undo_period; docs/screenshots/mobile/delete-undo.png | Verified |
| U6 | Low physical effort | `resources/css/app.css`; `resources/js/app.js` | Support keyboard navigation and give controls generous spacing. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |
| U7 | Size and space for approach and use | `resources/css/app.css` | Give interactive controls at least 44 CSS pixels and adapt layouts for narrow screens. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/step-1-your-info.png | Verified |

#### Color theory
**Table 10. Color theory HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| C1 | Deliberate color scheme per template | `resources/css/app.css`; `docs/DECISIONS.md` | Use monochromatic Simple neutrals, analogous blue Modern hues, and complementary plum/magenta with warm cream for Creative. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-modern.png; docs/screenshots/mobile/template-creative.png | Verified |
| C2 | Color temperature matches the template's message | `resources/css/app.css`; `docs/DECISIONS.md` | Keep Simple restrained, Modern cool and calm, and Creative warm and expressive. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-modern.png; docs/screenshots/mobile/template-creative.png | Verified |
| C3 | Sufficient value contrast, WCAG AA | `resources/css/app.css`; `tests/Unit/ColorContrastTest.php` | Check text pairs at 4.5:1 and control/focus boundaries at 3:1 in light and dark palettes. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/dark-mode-creative.png | Verified |
| C4 | Never rely on color alone | `resources/views/portfolios/form.blade.php`; `resources/views/portfolios/deleted.blade.php`; `resources/css/app.css` | Pair completion, error, success, and undo states with visible text. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; docs/screenshots/mobile/error-429.png; docs/screenshots/mobile/step-3-done-share.png | Verified |
| C5 | Define all colors as design tokens in one place | `resources/css/app.css` | Define app and template palette values in one root token block with dark overrides. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; resources/css/app.css | Verified |

#### Visual design principles
**Table 11. Visual design principles HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| V1 | Unity | `resources/css/app.css`; `resources/views/layouts/app.blade.php` | Reuse typography, spacing, and component styles throughout the app. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/template-simple.png | Verified |
| V2 | Gestalt principles | `resources/css/app.css`; `resources/views/portfolios/form.blade.php` | Group related fields, repeat section styling, and align content. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/template-creative.png | Verified |
| V3 | Visual hierarchy | `resources/css/app.css`; `resources/views/portfolios/stepper.blade.php` | Use heading scale, step state, and spacing to guide the current task. | docs/screenshots/mobile/step-2-choose-template.png | Verified |
| V4 | Balance | `resources/css/app.css`; `resources/views/portfolios/templates/simple.blade.php` | Balance primary content, supporting hints, and whitespace at each viewport. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-creative.png | Verified |
| V5 | Contrast | `resources/css/app.css`; `tests/Unit/ColorContrastTest.php` | Separate text, controls, and focus states with measured contrast. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/dark-mode-creative.png | Verified |
| V6 | Scale | `resources/css/app.css`; `resources/views/portfolios/templates/creative.blade.php` | Use a consistent readable type and component scale with responsive headings. | docs/screenshots/mobile/template-creative.png; docs/screenshots/desktop/template-creative.png | Verified |
| V7 | Dominance | `resources/css/app.css`; `resources/views/portfolios/create.blade.php` | Make the current heading and primary action the clearest elements. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| V8 | Similarity | `resources/css/app.css`; `resources/views/layouts/app.blade.php` | Style controls with the same role consistently throughout the application. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/edit.png | Verified |

#### User needs
**Table 12. User needs HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| R1 | Documented personas and tasks | `docs/USERS_AND_NEEDS.md` | Describe student, freelancer, and job seeker contexts and tasks. | HciTrackerTest::test_documented_personas_and_core_tasks_cover_target_users; docs/USERS_AND_NEEDS.md | Verified |
| R2 | Documented assumptions to validate | `docs/USERS_AND_NEEDS.md` | Separate assumptions from measured user research. | HciTrackerTest::test_user_research_assumptions_are_labeled_as_unvalidated; docs/USERS_AND_NEEDS.md | Verified |
| R3 | Iteration log of changes from evaluation | `docs/USERS_AND_NEEDS.md` | Record evaluation evidence, findings, design changes, and follow-up work. | HciTrackerTest::test_iteration_log_records_evaluation_findings_and_design_changes; docs/USERS_AND_NEEDS.md; docs/COGNITIVE_WALKTHROUGH.md | Verified |

<!-- LANDSCAPE_END -->

## 7. Evaluation

### 7.1 Heuristic review

The single-reviewer expert inspection covers the 17 captured states against all ten Nielsen heuristics at mobile and desktop sizes. The screen-by-heuristic matrix, severity scale, findings, re-evaluation, and limitations are recorded in `docs/HEURISTIC_EVALUATION.md`. One minor finding remains: ten seconds is a short recovery period. A second cleanup finding was corrected by adding server-side scheduled purge and treating an already-purged record as completed. No severity 3 or 4 finding remains.

**Table 13. Expert review findings and disposition**

| Finding | Severity | Disposition |
|---|---:|---|
| The ten-second delete undo can be missed by a user who needs more time. | 2 | Retained to match the product requirement; ask classmates whether it is long enough. |
| Closing the delete screen could leave cleanup dependent on a later browser request. | 2 before fix | Resolved with ten-second server-side purge scheduling and already-purged response handling; covered by purge command and schedule tests. |

### 7.2 Cognitive walkthrough

This walkthrough applies the student, freelancer, and job-seeker personas to the implemented task path. It is an expert review based on the screens and route behavior, not observed participant behavior. The step-by-step record is in `docs/COGNITIVE_WALKTHROUGH.md`.

**Table 14. Expert cognitive walkthrough**

| Persona | Task path reviewed | Expected user questions | Inspection result and limitation |
|---|---|---|---|
| Student | Add name, headline, introduction, skills, and optional education or course projects; select a template; copy the public URL. | “What information is required?”, “Can I leave work experience empty?”, “How do I share?” | Field guidance, optional section labels, a required skill message, template previews, and a completion screen answer the main questions. A classmate should validate terminology and whether the required fields feel reasonable. |
| Freelancer | Add a specialty and projects, preview styles, publish, save the private management link, and later update a project. | “Will my work look professional?”, “Which link is public?”, “Can I edit after sharing?” | The three previews use the same profile data; public and private links have separate labels; the private link opens edit and template controls. The owner must retain the signed link because there is no account recovery. |
| Job seeker | Add experience and skills, use keyboard navigation, publish a focused page, and test deletion recovery. | “Where am I in the process?”, “Can I correct a mistake?”, “Can I undo a delete?” | The stepper, field validation, keyboard shortcuts, edit page, and ten-second undo provide visible controls. Ten seconds may be too brief for some users and is an explicit study question. |

### 7.3 Usability test plan

The kit in `docs/usability-testing/` contains a five-task moderator script, an observation sheet, a ten-item SUS questionnaire, and a results table. Participant count, task results, quotes, and SUS values remain marked “to be completed with classmates.” No user-study score is reported in this paper.

### 7.4 Iteration record

The R3 log in `docs/USERS_AND_NEEDS.md` records the draft restoration and repeat-row fixes observed during browser testing, and the server-side purge issue found during code inspection. The log distinguishes automation evidence from future classmate evaluation.

## 8. Deployment and infrastructure

### 8.1 Release configuration

`Dockerfile` builds Composer dependencies, Vite assets, and a PHP 8.3 Apache image. `docker/start.sh` binds Apache to Render's `PORT`, applies additive migrations, seeds the fictional demo, and builds Laravel's configuration, route, and view caches. It also supervises the sub-minute scheduler. Render's free plan does not support a pre-deploy command, so migration runs at service startup. Free web services sleep after inactivity and have ephemeral local storage; Supabase PostgreSQL retains application data, sessions, and cache. `.github/workflows/ci.yml` audits PHP dependencies and builds the Render image; `.github/workflows/smoke.yml` checks the configured live URL after CI.

**Figure 37. CI and deployment flow**

![CI and deployment flow](diagrams/ci-cd.png)

**Table 15. Deployment environment variables**

| Variable | Value source |
|---|---|
| `APP_ENV`, `APP_DEBUG`, `APP_TIMEZONE` | Render production environment; production, false, and Asia/Taipei. |
| `APP_KEY` | Generate once and store as a Render production secret. |
| `APP_URL` | HTTPS origin of the deployed Render service. |
| `DB_CONNECTION`, `DB_PORT`, `DB_DATABASE`, `DB_SSLMODE` | PostgreSQL, 5432, postgres (or configured DB name), and require. |
| `DB_HOST` | Supabase shared session-pooler host copied from its Connect screen. |
| `DB_USERNAME` | Supabase session-pooler username, typically `postgres.[PROJECT_REF]`. |
| `DB_PASSWORD` | Supabase database password stored as a Render production secret. |
| `SESSION_DRIVER`, `CACHE_STORE`, `QUEUE_CONNECTION` | Database, database, and sync for the stateless container. |
| `LOG_CHANNEL`, `SESSION_SECURE_COOKIE` | stderr and true for runtime logs and HTTPS session cookies. |
| `PORTGEN_LIVE_URL` | GitHub repository variable for the smoke workflow, set only after a real deployment. |

Supabase session pooler connectivity uses port 5432 and SSL mode `require`. Production migrations must be additive; `migrate:fresh` and `db:wipe` are reserved for the isolated CI database. The local test guard refuses a Supabase host. Render's free instances have ephemeral local storage, so the session and cache tables share state through PostgreSQL. The Render dashboard receives secret values; the repository contains only variable names.

### 8.2 Deployment status and blockers

The source repository is public at `https://github.com/crazyfrogfrog84-dotcom/portgen`; `main` and the eight phase branches are present. The deployment changes are prepared locally on `codex/render-deployment` but are not yet pushed. The Supabase organization contains the existing `StuDyIo Project`, which was not modified. No PortGen database has been created. The requested Render service name is `portgen-m1uh`; its live URL and production environment are pending service creation.

Laravel 12.70.0 replaces the unsupported Laravel 11 dependency set and `composer audit --locked` now reports no advisories. The Render container configuration is ready, but publication still depends on connecting the deployment branch, creating a dedicated Supabase database, configuring production secrets, and running the additive production migration. No production database migration has been attempted. The public source repository is separate from the still-pending live application.

## 9. Testing and technical achievements

### 9.1 Local command results

**Table 16. Verification executed in this workspace**

| Command | Result |
|---|---|
| `php artisan test --compact` | Exit code 0; 573 assertions; 37 deprecation-marked tests and 1 passing test in the compact PHP 8.5 output. |
| `./vendor/bin/pint --test` | Passed. |
| `npm run build` | Passed with Vite 8.3.4. |
| `npm audit --omit=optional` | Zero vulnerabilities. |
| `npm run test:e2e` | 1 Playwright test passed; captured 17 states at mobile and desktop sizes. |
| `php artisan schedule:list` | Shows `portfolios:purge-expired` every ten seconds. |
| `HCI_RELEASE=1 php artisan test --compact` | Exit code 0; 657 assertions; the tracker release requirement passes. PHP 8.5 reports 37 deprecation-marked tests and 1 passing test in the compact summary. |
| `composer audit --locked` | Exit code 0; no security vulnerability advisories found after upgrading to Laravel 12.70.0. |

The workstation uses PHP 8.5, not the required PHP 8.3 runtime, so PHPUnit reports deprecations. CI is configured to use PHP 8.3 and PostgreSQL 16 and to build the Render image. PostgreSQL migration rollback/reapply, GitHub CI for this branch, and the container image build remain pending.

### 9.2 Database, browser, and security checks

Feature tests cover portfolio validation, signed access, template constraints, escaping, rate limits, demo seeding, deletion/undo, and purge behavior. Model tests cover UUID and slug behavior, mass-assignment protection, uniqueness, JSON arrays, template constraints, seeder idempotence, and scheduled cleanup. Browser checks use a dedicated SQLite test database and test keyboard flow, draft state, responsive widths, dark mode, and error screens. CI declares an isolated PostgreSQL 16 database and an explicit test-only guard; these PostgreSQL jobs remain configured but unexecuted on the workstation.

The implementation includes CSRF, rate limits on state-changing routes, signed owner URLs, strict URL schemes, escaped user content, friendly error views, security headers, an explicit mass-assignment allowlist, and a repository secret scanner. The signed owner URL is a bearer credential and should be kept private. No live proxy response, including a 502, was available to inspect because no service is deployed; the smoke workflow is configured to check the service endpoints after a future deployment.

<!-- PAGEBREAK -->
## 10. User guide

### 10.1 Create and publish

1. Open `/portfolios/create` and enter your name, headline, and introduction.
2. Expand the optional Education, Experience, Projects, and Links sections as needed. Add at least one skill.
3. Use Next to compare previews populated with your information.
4. Choose Simple, Modern, or Creative, then finish the sharing step.
5. Copy the public URL to share your portfolio. Save the separate private management link in a secure place.

### 10.2 Manage and edit

Open the private signed link to view the portfolio controls. Choose Edit portfolio to update details or Change template to preview another style. Anyone who has the private link can manage the portfolio, so do not share it publicly.

### 10.3 Delete and undo

Choose Delete portfolio and review the named confirmation. The deletion screen provides a ten-second Undo control. After the window ends, the server scheduler permanently removes the record. The local copy in the browser is not a backup.

### 10.4 Keyboard shortcuts

Use Ctrl/Cmd+S to save a draft or submit the current step, Alt+Right/Left to move through steps, and `?` to open the shortcut dialog. The shared layout also links the Help and FAQ page.

## 11. Maintenance and future work

### 11.1 Run locally

1. Install PHP 8.3, Composer, Node.js 24, and npm.
2. Run `composer install` and `npm ci`.
3. Copy `.env.example` to `.env`, generate an application key with `php artisan key:generate`, and configure an isolated local database.
4. Run `php artisan migrate --seed` and `npm run build`.
5. Start the app with `php artisan serve` and the front-end watcher with `npm run dev`.
6. Run `php artisan test`, `./vendor/bin/pint --test`, `npm audit --omit=optional`, and `npm run test:e2e` for the local checks.

Never point test commands or `migrate:fresh` at Supabase. For a supported public launch, upgrade the framework dependency to a currently supported Laravel release and re-run the full CI gate.

### 11.2 Future work

- Keep Laravel 12 dependencies patched and plan the Laravel 13 upgrade before Laravel 12's security-fix date.
- Create the Render service `portgen-m1uh` under the crazyfrog account and record the live URL after the first healthy deploy.
- Create a dedicated PortGen Supabase project, set production secrets, apply additive migrations, and pass live smoke checks.
- Conduct the five-task classmate study and add real observation and SUS results.
- Consider profile photos and project images only after launch readiness; validate type, size, alt text, preview, storage, and deletion flows.
- Evaluate skill-logo suggestions with an explicit text fallback and no dependency on color or imagery.

## 12. Appendices

### Appendix A. Decisions

**Table 17. Dated implementation decisions**

| Date | Decision | Reason |
| --- | --- | --- |
| 2026-10-19 | Initially retain Laravel 11 to match the fixed stack in the brief; later superseded by the security upgrade decision below. | The framework's security-fix window had ended and Composer reported four advisories, so the initial decision was changed before deployment. |
| 2026-10-19 | Upgrade Laravel 11 to Laravel 12.70.0 and PHPUnit 11 before deployment. | Laravel 11's security-fix window ended on 2026-03-12 and the locked framework had four advisories. The official upgrade guide describes Laravel 12 as a low-impact update; the patched release removes the current Composer advisories while retaining PHP 8.3 compatibility. |
| 2026-10-19 | Set Composer's platform PHP version to 8.3. | The required CI and production runtime are PHP 8.3, while the workstation currently runs PHP 8.5. This keeps dependency resolution compatible with the requested runtime. |
| 2026-10-19 | Use Tailwind CSS 4 through its official Vite plugin and use Playwright with axe-core for browser checks. | These match the required build and browser-testing stack; the packages are dev dependencies and are needed for the requested UI and accessibility gates. |
| 2026-10-19 | Upgrade Vite and `laravel-vite-plugin` to versions compatible with Node 24 and Vite 8 after npm audit found vulnerable esbuild/Vite dependencies. | The existing Laravel 11 skeleton pinned Vite 5, which produced moderate and high npm audit findings. The current Laravel Vite plugin supports Vite 8; the updated dependency tree reports zero npm vulnerabilities. |
| 2026-10-19 | Use neutral monochrome tokens for Simple, cool neighboring blue hues for Modern, and warm cream with plum/magenta accents for Creative. | The palettes distinguish quiet, focused, and expressive presentation styles while keeping text and controls readable in both color schemes; `ColorContrastTest` measures the defined foreground/background pairs. |
| 2026-10-19 | Use a deterministic fictional public demo slug (`portgen001`) and an idempotent seeder for the live smoke check. | The deployment smoke workflow needs a stable public URL to verify that migrations, seeding, and public rendering work together without using real user data. |
| 2026-10-19 | Keep local error-preview routes available only in `local` and `testing` environments. | Playwright needs to inspect every friendly server-error screen, while production should expose only application routes. |
| 2026-10-19 | Run local Playwright with a dedicated temporary SQLite file and reserve PostgreSQL browser tests for the isolated CI service. | This machine has no Docker, PostgreSQL server, or GitHub runner access. The browser setup overrides DB connection settings and refuses a PostgreSQL target unless it is explicitly marked as the local `portgen_test` service. |
| 2026-10-19 | Use Render's Docker web service with Apache as the primary host. | The requested deployment target is Render; its Docker runtime supports the committed Apache image and uses the standard `PORT` variable. Supabase PostgreSQL stores persistent data, sessions, and cache. |
| 2026-10-19 | Name the requested Render service `portgen-m1uh` and deploy the `codex/render-deployment` branch after its CI checks pass. | This matches the requested live URL slug and keeps the public service on a branch with CI checks before it is promoted to `main`. |
| 2026-10-19 | Keep Laravel's ten-second deletion undo and run its scheduler in the Render container. | The owner may close the browser during the undo window; Laravel's `schedule:work` performs cleanup while the service is running, and the signed browser purge provides request-driven cleanup. |
| 2026-10-19 | Use SQLite in memory only for the initial scaffold checks; use the dedicated local Postgres 16 service for application database tests and CI. | This avoids any accidental connection to production and allows the scaffold gate to run before a Docker service is available. |
| 2026-10-19 | Run the required Postgres 16 migration, rollback, and feature gates in the CI service; run only SQLite-backed migration/model tests on this workstation. | Docker, `psql`, and a local PostgreSQL service are absent, and the current Windows account cannot install a PostgreSQL service. The CI job declares its own ephemeral Postgres 16 service. Local SQLite results do not count as the Postgres gate. |
| 2026-10-19 | Run expired portfolio cleanup every ten seconds in the Render container. | The required ten-second undo period must also finish when an owner closes the browser. Laravel supports sub-minute schedules through `schedule:work`; the signed purge request also completes cleanup when the owner remains on the page. |
| 2026-10-19 | Keep the usability study kit open for classmates and report no participant scores until sessions have been run. | The implementation has expert inspection and automated checks, but no real participant data. Blank observation and results fields prevent fabricated findings. |
| 2026-10-19 | Store Mermaid source and a neutral theme configuration next to rendered diagrams and use the diagrams in the system paper. | Source files keep architecture, data, and release flow editable; rendered PNGs allow diagrams to be embedded in the DOCX and PDF. Mermaid CLI is used only as an authoring tool and is not added to application dependencies. |

### Appendix B. Glossary

**Bearer link:** A private URL whose possession grants access to a resource.

**JSONB:** PostgreSQL's binary JSON data type for structured values.

**Signed URL:** A URL with a cryptographic signature that detects tampering.

**Soft delete:** Marking a row as deleted while retaining it for a recovery period.

**SUS:** System Usability Scale, a standardized ten-item questionnaire.

**WCAG:** Web Content Accessibility Guidelines published by W3C.

### Appendix C. References

1. Shneiderman, B., Plaisant, C., Cohen, M., Jacobs, S., and Elmqvist, N. *Designing the User Interface: Strategies for Effective Human-Computer Interaction*, 6th ed., Pearson, 2016. Summary of the Eight Golden Rules: [University of Maryland HCIL](https://www.cs.umd.edu/users/ben/goldenrules.html).
2. Norman, D. A. *The Design of Everyday Things: Revised and Expanded*. Basic Books, 2013. The design principles are summarized for course use at [California State University, Northridge](https://www.csun.edu/science/courses/671/bibliography/norman.html).
3. Nielsen, J. and Molich, R. “Heuristic Evaluation of User Interfaces.” *Proceedings of CHI 1990*, pp. 249-256. [doi:10.1145/97243.97281](https://doi.org/10.1145/97243.97281).
4. Nielsen, J. “10 Usability Heuristics for User Interface Design.” Nielsen Norman Group, updated 2020. [nngroup.com](https://www.nngroup.com/articles/ten-usability-heuristics/).
5. Center for Universal Design, North Carolina State University. *The Principles of Universal Design*, version 2.0, 1997. [NC State College of Design](https://design.ncsu.edu/research/center-for-universal-design/).
6. World Wide Web Consortium. *Web Content Accessibility Guidelines (WCAG) 2.2*, Recommendation, 2023. [W3C Recommendation](https://www.w3.org/TR/WCAG22/).
7. University of Delaware Library. “Color Schemes.” [Graphic Design Research Guide](https://guides.lib.udel.edu/design/color).
8. Laravel. “Laravel 12.x Task Scheduling.” [Official documentation](https://laravel.com/framework/docs/12.x/scheduling).
9. Laravel. “Release Notes.” [Official framework support table](https://laravel.com/framework/docs/12.x/releases).
10. Render. “Blueprint Specification Reference.” [Official documentation](https://render.com/docs/blueprint-spec).
11. Supabase. “Connect to Postgres.” [Official documentation](https://supabase.com/docs/guides/database/connecting-to-postgres).
12. Render. “Docker on Render.” [Official documentation](https://render.com/docs/docker).

<!-- LANDSCAPE_START -->
### Appendix D. HCI tracker copy

This copy matches the 48 implementation rows in section 6.4 and `docs/HCI_TRACKER.md`.

#### Shneiderman's 8 Golden Rules
**Table 18. Shneiderman's 8 Golden Rules HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| S1 | Consistency | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Shared layout, navigation, spacing, and repeated button styles. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/desktop/step-1-your-info.png | Verified |
| S2 | Shortcuts for frequent users | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Discoverable save and step navigation shortcuts. | docs/screenshots/mobile/keyboard-shortcuts.png; docs/screenshots/desktop/keyboard-shortcuts.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |
| S3 | Informative feedback | `resources/views/layouts/app.blade.php`; `resources/views/portfolios/show.blade.php` | Announce saved, published, copied-link, and error status in polite live regions. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/desktop/step-3-done-share.png; PortfolioRoutesTest::test_template_selection_uses_and_preserves_the_private_link_for_the_creator | Verified |
| S4 | Dialogs that yield closure | `resources/views/portfolios/manage.blade.php`; `resources/views/portfolios/show.blade.php` | Name the portfolio in the delete dialog and show the private owner link when creation finishes. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/desktop/step-3-done-share.png | Verified |
| S5 | Error prevention and simple error handling | `app/Http/Requests/Concerns/HasPortfolioRules.php`; `resources/views/portfolios/form.blade.php`; `resources/js/app.js` | Validate structured fields and safe URLs, then show specific inline recovery messages. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; docs/screenshots/mobile/step-1-your-info.png | Verified |
| S6 | Easy reversal of actions | `routes/web.php`; `resources/views/portfolios/deleted.blade.php`; `app/Http/Controllers/PortfolioController.php` | Offer a signed 10-second undo before permanent removal. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; docs/screenshots/mobile/delete-undo.png; docs/screenshots/desktop/delete-undo.png | Verified |
| S7 | Internal locus of control | `routes/web.php`; `app/Http/Controllers/PortfolioController.php` | Require a private signed URL for management and keep transitions user initiated. | PortfolioRoutesTest::test_signed_manage_link_opens_and_unsigned_link_is_forbidden; docs/screenshots/mobile/manage.png | Verified |
| S8 | Reduce short-term memory load | `resources/views/portfolios/form.blade.php`; `resources/js/app.js` | Group form fields, show completion labels, and offer local draft restoration. | docs/screenshots/mobile/step-1-your-info.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |

#### Norman's 7 Principles
**Table 19. Norman's 7 Principles HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| N1 | Knowledge in the world and in the head | `resources/views/portfolios/form.blade.php`; `resources/views/help.blade.php` | Use examples and plain-language help beside unfamiliar fields. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| N2 | Simplify task structure | `resources/views/portfolios/stepper.blade.php`; `resources/views/portfolios/create.blade.php`; `resources/views/portfolios/select-template.blade.php` | Divide creation into a visible three-step flow. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/desktop/step-2-choose-template.png | Verified |
| N3 | Make things visible | `resources/views/portfolios/stepper.blade.php`; `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Show current step, section completion, save status, and submit loading state. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/mobile/step-3-done-share.png | Verified |
| N4 | Get the mappings right | `resources/views/portfolios/form.blade.php`; `resources/views/portfolios/templates/sections.blade.php` | Use familiar field names and group entered information under matching headings. | docs/screenshots/mobile/step-1-your-info.png | Verified |
| N5 | Use constraints | `app/Http/Requests/SaveTemplateRequest.php`; `app/Http/Requests/Concerns/HasPortfolioRules.php` | Restrict template choice and URL schemes before storage. | PortfolioRoutesTest::test_template_choice_is_limited_to_the_three_supported_values; docs/screenshots/mobile/step-2-choose-template.png | Verified |
| N6 | Design for error | `resources/js/app.js`; `resources/views/portfolios/deleted.blade.php` | Autosave drafts, explain validation failures, and provide delete recovery. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window | Verified |
| N7 | When all else fails, standardize | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Reuse semantic controls, navigation, and status components. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/edit.png | Verified |

#### Nielsen's 10 Usability Heuristics
**Table 20. Nielsen's 10 Usability Heuristics HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| H1 | Visibility of system status | `resources/views/layouts/app.blade.php`; `resources/views/portfolios/show.blade.php` | Announce saved and published status and show the completion step. | docs/screenshots/mobile/step-3-done-share.png; PortfolioRoutesTest::test_template_selection_uses_and_preserves_the_private_link_for_the_creator | Verified |
| H2 | Match between system and real world | `resources/views/portfolios/form.blade.php`; `resources/views/help.blade.php` | Use plain labels such as Skills, Projects, Save, and Next, with concrete examples. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| H3 | User control and freedom | `routes/web.php`; `resources/views/portfolios/deleted.blade.php`; `resources/js/app.js` | Support back navigation, template changes, and deletion undo. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; docs/screenshots/mobile/delete-undo.png | Verified |
| H4 | Consistency and standards | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Keep page structure, focus states, and control roles consistent. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/template-simple.png | Verified |
| H5 | Error prevention | `app/Http/Requests/Concerns/HasPortfolioRules.php`; `app/Http/Requests/SaveTemplateRequest.php` | Validate required values, URL schemes, and supported templates before saving. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; PortfolioRoutesTest::test_template_choice_is_limited_to_the_three_supported_values | Verified |
| H6 | Recognition rather than recall | `resources/views/portfolios/select-template.blade.php`; `resources/views/portfolios/form.blade.php` | Preview each template with the person's own information and show field examples. | docs/screenshots/mobile/step-2-choose-template.png; docs/screenshots/mobile/template-modern.png | Verified |
| H7 | Flexibility and efficiency of use | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Provide keyboard shortcuts alongside standard pointer controls. | docs/screenshots/mobile/keyboard-shortcuts.png; tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible | Verified |
| H8 | Aesthetic and minimalist design | `resources/views/portfolios/form.blade.php`; `resources/css/app.css` | Show only the current creation task and group related information. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/template-simple.png | Verified |
| H9 | Help users recognize, diagnose, recover from errors | `resources/views/errors/status.blade.php`; `resources/js/app.js`; `app/Http/Requests/Concerns/HasPortfolioRules.php` | Show friendly server errors and specific inline field guidance. | docs/screenshots/mobile/error-403.png; docs/screenshots/mobile/error-500.png; PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls | Verified |
| H10 | Help and documentation | `resources/views/help.blade.php`; `resources/views/layouts/app.blade.php` | Link a help page and shortcut dialog from every screen. | docs/screenshots/mobile/help-faq.png; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |

#### Universal Design
**Table 21. Universal Design HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| U1 | Equitable use | `resources/views/layouts/app.blade.php`; `resources/css/app.css` | Provide keyboard and screen-reader access to core tasks. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |
| U2 | Flexibility in use | `resources/js/app.js`; `resources/views/layouts/app.blade.php` | Offer shortcuts and ordinary pointer controls. | docs/screenshots/mobile/keyboard-shortcuts.png; docs/screenshots/desktop/keyboard-shortcuts.png | Verified |
| U3 | Simple and intuitive use | `resources/views/portfolios/create.blade.php`; `resources/views/portfolios/select-template.blade.php`; `resources/views/portfolios/show.blade.php` | Label the path from portfolio information through template choice to sharing. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/step-2-choose-template.png | Verified |
| U4 | Perceptible information | `resources/css/app.css`; `resources/views/portfolios/form.blade.php` | Use contrast-tested text and pair status colors with readable labels. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/step-3-done-share.png | Verified |
| U5 | Tolerance for error | `routes/web.php`; `resources/views/portfolios/deleted.blade.php` | Keep deleted content recoverable for 10 seconds, then permanently remove it. | PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window; PortfolioModelTest::test_purge_command_removes_portfolios_after_the_undo_period; docs/screenshots/mobile/delete-undo.png | Verified |
| U6 | Low physical effort | `resources/css/app.css`; `resources/js/app.js` | Support keyboard navigation and give controls generous spacing. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/keyboard-shortcuts.png | Verified |
| U7 | Size and space for approach and use | `resources/css/app.css` | Give interactive controls at least 44 CSS pixels and adapt layouts for narrow screens. | tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible; docs/screenshots/mobile/step-1-your-info.png | Verified |

#### Color theory
**Table 22. Color theory HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| C1 | Deliberate color scheme per template | `resources/css/app.css`; `docs/DECISIONS.md` | Use monochromatic Simple neutrals, analogous blue Modern hues, and complementary plum/magenta with warm cream for Creative. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-modern.png; docs/screenshots/mobile/template-creative.png | Verified |
| C2 | Color temperature matches the template's message | `resources/css/app.css`; `docs/DECISIONS.md` | Keep Simple restrained, Modern cool and calm, and Creative warm and expressive. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-modern.png; docs/screenshots/mobile/template-creative.png | Verified |
| C3 | Sufficient value contrast, WCAG AA | `resources/css/app.css`; `tests/Unit/ColorContrastTest.php` | Check text pairs at 4.5:1 and control/focus boundaries at 3:1 in light and dark palettes. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/dark-mode-creative.png | Verified |
| C4 | Never rely on color alone | `resources/views/portfolios/form.blade.php`; `resources/views/portfolios/deleted.blade.php`; `resources/css/app.css` | Pair completion, error, success, and undo states with visible text. | PortfolioRoutesTest::test_store_requires_a_skill_and_rejects_unsafe_urls; docs/screenshots/mobile/error-429.png; docs/screenshots/mobile/step-3-done-share.png | Verified |
| C5 | Define all colors as design tokens in one place | `resources/css/app.css` | Define app and template palette values in one root token block with dark overrides. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; resources/css/app.css | Verified |

#### Visual design principles
**Table 23. Visual design principles HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| V1 | Unity | `resources/css/app.css`; `resources/views/layouts/app.blade.php` | Reuse typography, spacing, and component styles throughout the app. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/template-simple.png | Verified |
| V2 | Gestalt principles | `resources/css/app.css`; `resources/views/portfolios/form.blade.php` | Group related fields, repeat section styling, and align content. | docs/screenshots/mobile/step-1-your-info.png; docs/screenshots/mobile/template-creative.png | Verified |
| V3 | Visual hierarchy | `resources/css/app.css`; `resources/views/portfolios/stepper.blade.php` | Use heading scale, step state, and spacing to guide the current task. | docs/screenshots/mobile/step-2-choose-template.png | Verified |
| V4 | Balance | `resources/css/app.css`; `resources/views/portfolios/templates/simple.blade.php` | Balance primary content, supporting hints, and whitespace at each viewport. | docs/screenshots/mobile/template-simple.png; docs/screenshots/mobile/template-creative.png | Verified |
| V5 | Contrast | `resources/css/app.css`; `tests/Unit/ColorContrastTest.php` | Separate text, controls, and focus states with measured contrast. | ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds; docs/screenshots/mobile/dark-mode-creative.png | Verified |
| V6 | Scale | `resources/css/app.css`; `resources/views/portfolios/templates/creative.blade.php` | Use a consistent readable type and component scale with responsive headings. | docs/screenshots/mobile/template-creative.png; docs/screenshots/desktop/template-creative.png | Verified |
| V7 | Dominance | `resources/css/app.css`; `resources/views/portfolios/create.blade.php` | Make the current heading and primary action the clearest elements. | docs/screenshots/mobile/step-3-done-share.png; docs/screenshots/mobile/step-1-your-info.png | Verified |
| V8 | Similarity | `resources/css/app.css`; `resources/views/layouts/app.blade.php` | Style controls with the same role consistently throughout the application. | docs/screenshots/mobile/manage.png; docs/screenshots/mobile/edit.png | Verified |

#### User needs
**Table 24. User needs HCI tracker**
| ID | Principle | Where applied | How applied | Evidence | Status |
| --- | --- | --- | --- | --- | --- |
| R1 | Documented personas and tasks | `docs/USERS_AND_NEEDS.md` | Describe student, freelancer, and job seeker contexts and tasks. | HciTrackerTest::test_documented_personas_and_core_tasks_cover_target_users; docs/USERS_AND_NEEDS.md | Verified |
| R2 | Documented assumptions to validate | `docs/USERS_AND_NEEDS.md` | Separate assumptions from measured user research. | HciTrackerTest::test_user_research_assumptions_are_labeled_as_unvalidated; docs/USERS_AND_NEEDS.md | Verified |
| R3 | Iteration log of changes from evaluation | `docs/USERS_AND_NEEDS.md` | Record evaluation evidence, findings, design changes, and follow-up work. | HciTrackerTest::test_iteration_log_records_evaluation_findings_and_design_changes; docs/USERS_AND_NEEDS.md; docs/COGNITIVE_WALKTHROUGH.md | Verified |

<!-- LANDSCAPE_END -->
