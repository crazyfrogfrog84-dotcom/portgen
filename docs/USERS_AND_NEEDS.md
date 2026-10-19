# Users and needs

## Personas and primary tasks

| Persona | Situation | Primary tasks | Likely needs |
|---|---|---|---|
| Student | Building an early career profile with limited work history. | Add education, skills, coursework projects, choose a template, share a portfolio. | Examples for unfamiliar fields, helpful empty states, an easy way to show projects without paid experience. |
| Freelancer | Presenting services and selected client work. | Describe a specialty, add projects and social links, update the page after new work, share it with prospects. | Fast editing, a clear work-first layout, a private owner link that remains available. |
| Job seeker | Preparing a focused profile for applications. | Add experience and skills, select a readable template, send the public link, revise details later. | A straightforward form, keyboard access, clear completion feedback, predictable editing and deletion. |

## Assumptions to validate with classmates

- People can supply portfolio information in one sitting, but need examples and can return to unfinished work.
- A shareable public page and a separate private management link are understandable when explained in plain language.
- Three named template choices provide enough visual variety for an initial course project.
- Students, freelancers, and job seekers can use the same structured information model with optional sections.
- A confirmation step and a 10-second delete undo make consequential actions understandable and recoverable.

These are design assumptions, not findings from real user research. The real-user study sections remain open for classmates.

## Iteration log (R3)

| Date | Evaluation evidence | Finding | Change made | Follow-up |
|---|---|---|---|---|
| 2026-10-19 | Initial expert review planned; no participants have been studied. | Baseline assumptions and persona tasks need validation. | Recorded the three personas, tasks, and assumptions before implementation. | Complete the documented cognitive walkthrough and invite classmates to perform the usability script; record actual findings here. |
| 2026-10-19 | `tests/e2e/portfolio-accessibility.spec.js::keyboard-first create, publish, manage, template, and delete-undo flows stay accessible`; `ColorContrastTest::test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds`; mobile and desktop screenshots. | The browser run showed that a saved draft must recreate added rows after refresh, the snapshot should not store CSRF tokens, and removing an interior repeat row must preserve the remaining names and values. | Restoring a draft now recreates repeat rows, snapshots omit CSRF/method fields, saved drafts clear after server submission, and remaining row IDs/names reindex after removal. | Run the five-task usability session with classmates; no real-user findings are claimed yet. |
