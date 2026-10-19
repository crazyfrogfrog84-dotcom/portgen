# Heuristic evaluation

## Review scope

This is a single-reviewer expert inspection, not a participant study. The reviewer walked the primary create, publish, manage, edit, delete, help, template, and error states at mobile and desktop viewport sizes. The browser suite captured 17 screen states at 375 by 812 and 1280 by 800 CSS pixels, checked each mobile state at 320 pixels for horizontal overflow, and ran axe-core against each captured state. The create screen was also checked at 200% zoom. The full screen list and captures are in `docs/screenshots/`.

The review uses the severity scale below. It describes usability impact, not the priority labels of a software issue tracker.

| Severity | Meaning |
|---:|---|
| 0 | No usability problem found in this inspection. |
| 1 | Cosmetic issue; fix only when convenient. |
| 2 | Minor usability problem; a planned improvement is useful. |
| 3 | Major problem that should be fixed before release. |
| 4 | Usability catastrophe; the core task cannot be completed. |

## Screen inventory

The matrix evaluates each listed state against each of Nielsen's ten heuristics. Every state was reviewed in both viewport captures. A zero means no problem was found for that screen and heuristic in this expert inspection. `H3=2` records the deliberately short, ten-second undo window on the deletion screen; the product brief requires that window, so it remains a minor usability limitation. No severity 3 or 4 finding remains.

| Screen state | H1 | H2 | H3 | H4 | H5 | H6 | H7 | H8 | H9 | H10 |
|---|---:|---:|---:|---:|---:|---:|---:|---:|---:|---:|
| Create form, step 1 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Template selection, step 2 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Completion and sharing, step 3 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Public Simple template | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Public Modern template | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Public Creative template | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Private manage screen | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Edit screen | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Delete and undo screen | 0 | 0 | 2 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Help and FAQ | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Keyboard shortcuts dialog | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Invalid private link, 403 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Missing portfolio, 404 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Expired form, 419 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Rate limited, 429 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Server error, 500 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |
| Dark-mode Creative template | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 | 0 |

## Findings and re-evaluation

| Finding | Screen and heuristic | Severity | Change or disposition | Re-evaluation evidence |
|---|---|---:|---|---|
| The undo window is intentionally brief, so a person who misses the ten-second window cannot restore the deleted portfolio. | Delete and undo; H3, user control and freedom | 2 | Kept at ten seconds because the product requirements explicitly define that period. The screen shows the remaining time, offers a direct Undo action, and the server provides signed recovery while the window is open. Scheduled cleanup also works when the browser closes. | `PortfolioRoutesTest::test_delete_can_be_undone_during_the_ten_second_window`; `PortfolioRoutesTest::test_delete_is_permanent_after_the_undo_window`; `PortfolioModelTest::test_purge_command_removes_portfolios_after_the_undo_period`; `docs/screenshots/mobile/delete-undo.png`; `docs/screenshots/desktop/delete-undo.png` |
| An owner could close the deletion screen before client-side cleanup completed, leaving an expired soft-deleted row until another manual request. | Delete and undo; H1, visibility of system status | 2 | Added a ten-second Laravel scheduled purge and start the scheduler with Apache in the production container. When the server has already purged a record, the countdown treats the resulting 404 as successful completion instead of retrying indefinitely. | `PortfolioModelTest::test_purge_command_removes_portfolios_after_the_undo_period`; `docs/screenshots/mobile/delete-undo.png`; `docs/screenshots/desktop/delete-undo.png` |

The first finding is an accepted requirement tradeoff and remains severity 2. The second finding is resolved. Neither blocks the HCI release gate. The browser captures are implementation evidence, not evidence that classmates have completed the usability study.

## Screen-by-screen heuristic notes

The following notes apply the ten heuristics to the entire screen inventory above. Each statement is limited to the inspected implementation; participant performance has not been measured.

| Heuristic | Inspection note |
|---|---|
| H1 Visibility of system status | The stepper identifies the active stage; the completion screen announces the public and private links; the delete screen exposes the countdown and undo state; form submission and copy actions provide status text. |
| H2 Match between system and real world | The UI uses familiar terms such as education, work experience, skills, projects, save, share, and edit. Error messages explain the problem in plain language. |
| H3 User control and freedom | Back navigation, template changes, edit links, cancel paths, browser navigation, and the delete undo are present. The ten-second recovery window is the minor finding described above. |
| H4 Consistency and standards | Shared navigation, button styles, focus indicators, landmarks, labels, and form patterns are reused across the application screens. |
| H5 Error prevention | Required values and supported template choices are constrained; unsafe URL schemes are rejected; deletion requires a named confirmation dialog. |
| H6 Recognition rather than recall | Template previews use the user's own portfolio content. Examples, field labels, visible steps, and help links remain available in context. |
| H7 Flexibility and efficiency of use | Pointer actions remain available alongside save, next/back, and help shortcuts. Shortcut names are listed in a dialog reachable from the layout. |
| H8 Aesthetic and minimalist design | The form reveals labeled sections progressively. Public templates group the supplied information into corresponding sections. |
| H9 Help users recognize, diagnose, and recover from errors | Inline errors identify fields, friendly status pages explain common HTTP failures, and deletion has a clear undo action during its recovery period. |
| H10 Help and documentation | The Help and FAQ page and shortcut dialog are linked from the shared layout. |

## Limits

The review was completed by the implementer, so it can miss problems that a first-time user would encounter. It did not include assistive-technology testing with people who use screen readers, and an axe pass does not establish full WCAG conformance. Complete the usability script in `docs/usability-testing/test-script.md` with classmates and record observed task outcomes before making participant-based claims.
