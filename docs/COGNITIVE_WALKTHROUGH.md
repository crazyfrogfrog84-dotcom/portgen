# Cognitive walkthrough

## Scope

This is an expert cognitive walkthrough of the implemented screens using the three target personas. It is not a session with real users. For each task, the reviewer considered whether the persona would know what to do, notice the control, understand the feedback, and recognize progress toward the goal.

## Student

**Goal:** Publish a first portfolio despite limited professional experience.

1. Opens `/portfolios/create`. The heading and stepper identify the first stage. The skip link and familiar field labels make the form's entry point visible.
2. Enters a name, headline, short introduction, and one skill. The field examples clarify expected content, and the skill requirement is specific. Education and experience are labeled optional, so the student can continue without inventing work history.
3. Reviews education, projects, and social links as needed. Collapsible sections reduce the amount visible at once. Add-row controls expose structured entries.
4. Chooses Next. Inline errors identify incomplete required values instead of silently disabling the action.
5. Compares template cards populated with the entered profile and selects one. Preview content supports recognition rather than recall.
6. Reaches the completion step. The public URL and private owner link have separate explanations and copy feedback. The private link must be kept confidential.

**Expert conclusion:** The path provides cues for a first-time student and allows optional work sections. A classmate study should check whether the student recognizes the private link's bearer-access implications and whether the required fields feel appropriate.

## Simple portfolio owner

**Goal:** Check a portfolio's appearance, keep control of its template and theme, and reopen its management page.

1. Opens the public page using the signed guest link or while signed in as the portfolio's account owner. The shared header exposes template and Manage controls only to that owner; a visitor or another account sees the saved portfolio and theme control only.
2. Chooses a template. The page shows a live query-string preview and the status “Not saved yet”; Save persists it, while Cancel restores the saved template. With JavaScript unavailable, ordinary links and the Save form still work.
3. Opens Manage and chooses Edit portfolio or Manage page. Account owners use My portfolios; guest owners copy their private link and receive a live confirmation without the long URL being printed inline.
4. Uses the theme control and, on narrow screens, the template popover. Arrow keys move among desktop choices; Escape closes the mobile list and returns focus to its button.

**Expert conclusion:** The owner actions are grouped in the same header as navigation and the theme control, while the public view stays free of owner-only actions. The guest link remains a bearer secret and should not be shared. The review checked the Simple page with minimal, rich, and stress data, but has not measured first-time recognition with participants.

## Freelancer

**Goal:** Create a work-focused public page and revise it after a new project.

1. Enters a specialty in the headline and adds representative projects using project name, description, and HTTP/HTTPS URL fields.
2. Reviews the three live previews using the actual project details and chooses a style.
3. Copies the public URL for a prospect and stores the private management link separately.
4. Reopens the signed management link later, selects Edit portfolio, updates project details, and saves.
5. Uses Change template to compare another style if the work focus changes.

**Expert conclusion:** The same structured project data appears in all three visual styles, and editing is separate from public viewing. The owner link is not recoverable through an account, which is a known limitation to explain and validate with freelancers.

## Job seeker

**Goal:** Prepare a focused application portfolio, navigate without a mouse, and recover from a deletion mistake.

1. Uses Tab and the skip link to reach the form. Visible focus styles and bound labels provide keyboard orientation.
2. Enters profile details and uses Alt+Right to continue. If validation fails, specific inline feedback directs correction.
3. Opens the keyboard-shortcut dialog with `?` and reviews the available controls.
4. Selects a template and completes publishing. The completion state confirms the result and provides separate links.
5. Opens the private link, chooses Delete portfolio, reads the named confirmation, and chooses to proceed.
6. Uses Undo on the deletion screen before the ten-second window expires.

**Expert conclusion:** Keyboard actions and the recovery control are visible in the tested journey. The ten-second undo period is brief; the test script asks participants whether it is sufficient. The developer's automated keyboard run is implementation evidence, not a job-seeker outcome.

## Follow-up

Run the five tasks in `docs/usability-testing/test-script.md` with classmates. Record pauses, errors, unassisted completion, and comments on the observation sheets. Keep actual participant results in `docs/usability-testing/results.md` without names or private portfolio data.
