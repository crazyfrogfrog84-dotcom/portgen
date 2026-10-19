# HCI compliance tracker

Status describes implementation evidence: `Planned` means the behavior is not implemented; `Implemented` means the code exists; `Verified` requires a named test or committed screenshot.

Phase 2 adds the structured portfolio schema and database integrity tests. User-facing HCI behaviors remain `Planned` until their screens and interactions are implemented and verified.

| ID | Principle | Where applied (route/view/file paths) | How it is applied | Evidence (test name or screenshot path) | Status |
|---|---|---|---|---|---|
| S1 | Consistency | `resources/views/welcome.blade.php`; `resources/css/app.css` | Shared layout, repeated button styles, and stable navigation placement. | — | Planned |
| S2 | Shortcuts for frequent users | `resources/js/app.js`; `resources/views/welcome.blade.php` | Discoverable save and step navigation keyboard shortcuts. | — | Planned |
| S3 | Informative feedback | `resources/js/app.js`; `resources/views/welcome.blade.php` | Live save state and polite success/error announcements. | — | Planned |
| S4 | Dialogs that yield closure | `resources/views/welcome.blade.php`; `routes/web.php` | Completion confirmation and a clear explanation of the owner link. | — | Planned |
| S5 | Error prevention and simple error handling | `routes/web.php`; `resources/views/welcome.blade.php` | Prevent invalid submissions and explain field-specific recovery. | — | Planned |
| S6 | Easy reversal of actions | `routes/web.php`; `resources/js/app.js` | Back navigation preserves data; deletion offers a timed undo. | — | Planned |
| S7 | Internal locus of control | `routes/web.php`; `resources/views/welcome.blade.php` | Users choose every transition and can manage only through their private signed link. | — | Planned |
| S8 | Reduce short-term memory load | `resources/js/app.js`; `resources/views/welcome.blade.php` | Sectioned form, visible progress, and draft restoration. | — | Planned |
| N1 | Knowledge in the world and in the head | `resources/views/welcome.blade.php`; `docs/USERS_AND_NEEDS.md` | Field examples and visible template descriptions reduce recall. | — | Planned |
| N2 | Simplify task structure | `resources/views/welcome.blade.php` | Divide creation into a clear three-step process and labeled sections. | — | Planned |
| N3 | Make things visible | `resources/views/welcome.blade.php`; `resources/js/app.js` | Stepper, completion checks, loading state, and save status stay visible. | — | Planned |
| N4 | Get the mappings right | `resources/views/welcome.blade.php` | Familiar labels and controls correspond to the intended portfolio content. | — | Planned |
| N5 | Use constraints | `routes/web.php`; `resources/views/welcome.blade.php` | Constrain template choices and validate fields before save. | — | Planned |
| N6 | Design for error | `resources/js/app.js`; `routes/web.php` | Autosave, validation guidance, and reversible deletion limit error cost. | — | Planned |
| N7 | When all else fails, standardize | `resources/views/welcome.blade.php`; `resources/css/app.css` | Reuse standard form, link, button, and navigation patterns. | — | Planned |
| H1 | Visibility of system status | `resources/js/app.js`; `resources/views/welcome.blade.php` | Announce progress, saving, submission, and completion. | — | Planned |
| H2 | Match between system and real world | `resources/views/welcome.blade.php` | Use plain language for familiar portfolio concepts and actions. | — | Planned |
| H3 | User control and freedom | `routes/web.php`; `resources/js/app.js` | Back navigation and delete undo provide escape and recovery. | — | Planned |
| H4 | Consistency and standards | `resources/views/welcome.blade.php`; `resources/css/app.css` | Keep controls and page structure consistent across the flow. | — | Planned |
| H5 | Error prevention | `routes/web.php`; `resources/views/welcome.blade.php` | Validate required values and safe URL schemes before saving. | — | Planned |
| H6 | Recognition rather than recall | `resources/views/welcome.blade.php` | Inline examples and previews show available choices and expected content. | — | Planned |
| H7 | Flexibility and efficiency of use | `resources/js/app.js` | Provide keyboard shortcuts alongside standard controls. | — | Planned |
| H8 | Aesthetic and minimalist design | `resources/views/welcome.blade.php`; `resources/css/app.css` | Show only the current task and organize portfolio content into sections. | — | Planned |
| H9 | Help users recognize, diagnose, recover from errors | `resources/views/welcome.blade.php`; `routes/web.php` | State a specific problem and the action needed to fix it. | — | Planned |
| H10 | Help and documentation | `resources/views/welcome.blade.php`; `docs/USERS_AND_NEEDS.md` | Provide contextual help and an accessible FAQ/help view. | — | Planned |
| U1 | Equitable use | `resources/views/welcome.blade.php`; `resources/css/app.css` | Provide keyboard and screen-reader access to the same core tasks. | — | Planned |
| U2 | Flexibility in use | `resources/js/app.js`; `resources/views/welcome.blade.php` | Offer keyboard shortcuts and ordinary pointer controls. | — | Planned |
| U3 | Simple and intuitive use | `resources/views/welcome.blade.php` | Use a visible start, middle, and completion step with plain language. | — | Planned |
| U4 | Perceptible information | `resources/css/app.css`; `resources/views/welcome.blade.php` | Use text and icons with sufficient contrast; never rely on color alone. | — | Planned |
| U5 | Tolerance for error | `resources/js/app.js`; `routes/web.php` | Preserve drafts and offer undo after delete. | — | Planned |
| U6 | Low physical effort | `resources/css/app.css`; `resources/views/welcome.blade.php` | Support keyboard navigation and generous interactive controls. | — | Planned |
| U7 | Size and space for approach and use | `resources/css/app.css` | Give interactive controls at least 44 by 44 CSS pixels and responsive spacing. | — | Planned |
| C1 | Deliberate color scheme per template | `resources/css/app.css`; `resources/views/welcome.blade.php` | Define monochromatic Simple, analogous Modern, and complementary Creative palettes. | — | Planned |
| C2 | Color temperature matches the template's message | `resources/css/app.css` | Use restrained cool neutrals for Simple, calm blue/teal for Modern, and warm energetic accents for Creative. | — | Planned |
| C3 | Sufficient value contrast, WCAG AA | `resources/css/app.css` | Verify body text at 4.5:1 and large text/UI at 3:1 in light and dark palettes. | — | Planned |
| C4 | Never rely on color alone | `resources/views/welcome.blade.php`; `resources/css/app.css` | Pair status colors with text labels, icons, or shape changes. | — | Planned |
| C5 | Define all colors as design tokens in one place | `resources/css/app.css` | Keep palette and semantic colors in one token block. | — | Planned |
| V1 | Unity | `resources/css/app.css`; `resources/views/welcome.blade.php` | Reuse typography, spacing, and component tokens across the application. | — | Planned |
| V2 | Gestalt principles | `resources/css/app.css`; `resources/views/welcome.blade.php` | Group related fields, use consistent section styling, and align content for continuity. | — | Planned |
| V3 | Visual hierarchy | `resources/css/app.css`; `resources/views/welcome.blade.php` | Use heading scale and spacing to guide the current task. | — | Planned |
| V4 | Balance | `resources/css/app.css`; `resources/views/welcome.blade.php` | Balance primary content, supporting hints, and whitespace at each viewport. | — | Planned |
| V5 | Contrast | `resources/css/app.css` | Separate text, controls, and focus states with tested value contrast. | — | Planned |
| V6 | Scale | `resources/css/app.css` | Use a consistent type and component scale that remains readable when enlarged. | — | Planned |
| V7 | Dominance | `resources/css/app.css`; `resources/views/welcome.blade.php` | Make the current step's heading and primary action the most prominent elements. | — | Planned |
| V8 | Similarity | `resources/css/app.css`; `resources/views/welcome.blade.php` | Style controls with the same role consistently throughout the flow. | — | Planned |
| R1 | Documented personas and tasks | `docs/USERS_AND_NEEDS.md` | Describe student, freelancer, and job seeker contexts and core tasks. | — | Planned |
| R2 | Documented assumptions to validate | `docs/USERS_AND_NEEDS.md` | Separate design assumptions from measured user research. | — | Planned |
| R3 | Iteration log of changes from evaluation | `docs/USERS_AND_NEEDS.md` | Record evidence, findings, resulting design changes, and follow-up work. | — | Planned |
