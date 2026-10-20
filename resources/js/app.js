import './bootstrap';

const authErrorSummary = document.querySelector('[data-auth-error-summary]');
if (authErrorSummary) window.requestAnimationFrame(() => authErrorSummary.focus());

for (const button of document.querySelectorAll('[data-password-toggle]')) {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordToggle);
        if (!(input instanceof HTMLInputElement)) return;
        const shown = input.type === 'password';
        input.type = shown ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(shown));
        button.setAttribute('aria-label', `${shown ? 'Hide' : 'Show'} password`);
        button.textContent = shown ? 'Hide' : 'Show';
        input.focus();
    });
}

for (const input of document.querySelectorAll('[data-caps-lock-input]')) {
    const warning = input.closest('.grid')?.querySelector('[data-caps-lock-warning]');
    const updateCapsLockWarning = (event) => {
        if (warning) warning.hidden = !event.getModifierState('CapsLock');
    };
    input.addEventListener('keydown', updateCapsLockWarning);
    input.addEventListener('keyup', updateCapsLockWarning);
    input.addEventListener('blur', () => { if (warning) warning.hidden = true; });
}

for (const input of document.querySelectorAll('[data-password-requirement-input]')) {
    const hint = document.querySelector('[data-password-requirement]');
    input.addEventListener('input', () => {
        if (hint) hint.textContent = input.value.length >= 8
            ? 'Password length requirement met.'
            : 'Use at least 8 characters.';
    });
}

for (const form of document.querySelectorAll('[data-loading-form]')) {
    form.addEventListener('submit', () => {
        const button = form.querySelector('[data-loading-label]');
        if (!button) return;
        button.disabled = true;
        button.textContent = button.dataset.loadingLabel;
    });
}

for (const countdown of document.querySelectorAll('[data-lockout-countdown]')) {
    const seconds = countdown.querySelector('span');
    let remaining = Number(countdown.dataset.seconds || 0);
    window.setInterval(() => {
        remaining = Math.max(0, remaining - 1);
        if (seconds) seconds.textContent = String(remaining);
    }, 1000);
}

if (document.body.hasAttribute('data-clear-draft')) {
    try { localStorage.removeItem('portgen:portfolio-draft'); } catch { /* Storage may be disabled. */ }
}

const toast = document.querySelector('[data-toast]');
let toastTimer;

function announce(message) {
    if (!toast) return;
    toast.textContent = message;
    toast.hidden = false;
    window.clearTimeout(toastTimer);
    toastTimer = window.setTimeout(() => { toast.hidden = true; }, 5000);
}

function isEditingText(target) {
    return target instanceof HTMLElement && (target.isContentEditable || target.matches('input, textarea, select'));
}

function formSnapshot(form) {
    return Array.from(new FormData(form).entries())
        .filter(([name, value]) => !['_token', '_method'].includes(name)
            && !(value instanceof File)
            && !/^(?:profile_image$|profile_image_remove$|project_images(?:\[|$)|project_image_(?:ids|remove)(?:\[|$)|project_image_controls_present$)/.test(name))
        .map(([name, value]) => [name, String(value)]);
}

function applySnapshot(form, entries) {
    const values = new Map();
    for (const [name, value] of entries) {
        if (!values.has(name)) values.set(name, []);
        values.get(name).push(value);
    }

    const desiredRows = new Map();
    for (const [name, fieldValues] of values) {
        if (name === 'skills[]') desiredRows.set('skills', fieldValues.length);
        const indexedGroup = name.match(/^(education|work_experience|projects|social_links)\[(\d+)\]/);
        if (indexedGroup) {
            const indexes = desiredRows.get(indexedGroup[1]) || new Set();
            indexes.add(indexedGroup[2]);
            desiredRows.set(indexedGroup[1], indexes);
        }
    }
    for (const [name, count] of desiredRows) {
        const desired = count instanceof Set ? count.size : count;
        const group = form.querySelector(`[data-repeat-group="${CSS.escape(name)}"]`);
        while (group && group.querySelectorAll(':scope > [data-repeat-row]').length < desired) addRepeatRow(form, name);
    }

    for (const field of form.elements) {
        if (!(field instanceof HTMLElement) || !('name' in field) || !field.name || !values.has(field.name)) continue;
        const candidates = values.get(field.name);
        if (field instanceof HTMLInputElement && ['checkbox', 'radio'].includes(field.type)) {
            field.checked = candidates.includes(field.value);
        } else if ('value' in field) {
            const occurrence = Array.from(form.elements).filter((item) => item.name === field.name).indexOf(field);
            field.value = candidates[occurrence] ?? candidates[0];
        }
    }
}

function showFieldError(field, message) {
    field.setAttribute('aria-invalid', 'true');
    let error = field.parentElement?.querySelector(`[data-inline-error-for="${CSS.escape(field.id)}"]`);
    if (!error) {
        error = document.createElement('span');
        error.dataset.inlineErrorFor = field.id;
        error.dataset.inlineError = '';
        error.id = `${field.id}-client-error`;
        error.setAttribute('role', 'alert');
        field.insertAdjacentElement('afterend', error);
    }
    error.textContent = message;
    const describedBy = new Set((field.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
    describedBy.add(error.id);
    field.setAttribute('aria-describedby', Array.from(describedBy).join(' '));
}

function clearFieldError(field) {
    field.removeAttribute('aria-invalid');
    field.parentElement?.querySelector(`[data-inline-error-for="${CSS.escape(field.id)}"]`)?.remove();
    const describedBy = (field.getAttribute('aria-describedby') || '').split(/\s+/).filter((id) => id && id !== `${field.id}-client-error`);
    if (describedBy.length) field.setAttribute('aria-describedby', describedBy.join(' '));
    else field.removeAttribute('aria-describedby');
}

function validateField(field) {
    if (!(field instanceof HTMLInputElement || field instanceof HTMLTextAreaElement || field instanceof HTMLSelectElement)) return true;
    const value = field.value.trim();
    let message = '';

    if (field.required && !value) {
        message = field.dataset.errorMessage || 'Complete this field to continue.';
    } else if (value && field.hasAttribute('data-http-url')) {
        try {
            const url = new URL(value);
            if (!['http:', 'https:'].includes(url.protocol)) message = field.dataset.errorMessage || 'Use a complete http or https link.';
        } catch {
            message = field.dataset.errorMessage || 'Enter a complete web address.';
        }
    } else if (value && !field.validity.valid) {
        message = field.dataset.errorMessage || 'Check the format of this value.';
    }

    if (message) showFieldError(field, message);
    else clearFieldError(field);
    return !message;
}

function validateForm(form) {
    const fields = Array.from(form.querySelectorAll('input, textarea, select'));
    let valid = fields.map(validateField).every(Boolean);

    const skills = form.querySelector('[data-repeat-group="skills"]');
    if (skills) {
        const hasSkill = Array.from(skills.querySelectorAll('input')).some((input) => input.value.trim());
        if (!hasSkill) {
            const firstSkill = skills.querySelector('input');
            if (firstSkill) {
                showFieldError(firstSkill, 'Add at least one skill.');
                valid = false;
            }
        }
    }

    if (!valid) {
        const firstInvalid = form.querySelector('[aria-invalid="true"]');
        firstInvalid?.focus();
        firstInvalid?.closest('[data-section]')?.setAttribute('open', '');
        announce('Some details need attention. Review the message beside each field.');
    }
    return valid;
}

function updateSections(form) {
    for (const section of form.querySelectorAll('[data-section]')) {
        const output = section.querySelector('[data-section-completion]');
        if (!output) continue;
        const fields = Array.from(section.querySelectorAll('input, textarea, select'));
        const hasValue = fields.some((field) => field.value.trim());
        const requiredFields = fields.filter((field) => field.required);
        const skills = section.querySelector('[data-repeat-group="skills"]');
        const hasSkill = !skills || Array.from(skills.querySelectorAll('input')).some((input) => input.value.trim());
        const isComplete = requiredFields.every((field) => field.value.trim() && field.validity.valid) && hasSkill;
        const isRequired = section.hasAttribute('data-section-required');

        output.textContent = isRequired ? (isComplete ? 'Complete' : 'In progress') : (hasValue ? 'Added' : 'Optional');
        output.setAttribute('aria-label', `${section.querySelector('summary > span')?.textContent.trim() || 'Section'}: ${output.textContent}`);
    }
}

function renumberRows(group) {
    const rows = Array.from(group.querySelectorAll(':scope > [data-repeat-row]'));
    rows.forEach((row, index) => {
        const ids = new Map();
        for (const element of row.querySelectorAll('[id]')) {
            const nextId = element.id.replace(/-(\d+)(?=-|$)/, `-${index}`);
            ids.set(element.id, nextId);
        }
        for (const element of row.querySelectorAll('[for]')) {
            if (ids.has(element.htmlFor)) element.htmlFor = ids.get(element.htmlFor);
        }
        for (const element of row.querySelectorAll('[aria-describedby], [aria-labelledby]')) {
            for (const attribute of ['aria-describedby', 'aria-labelledby']) {
                if (!element.hasAttribute(attribute)) continue;
                element.setAttribute(attribute, element.getAttribute(attribute).split(/\s+/).map((id) => ids.get(id) || id).join(' '));
            }
        }
        for (const element of row.querySelectorAll('[data-inline-error-for]')) {
            if (ids.has(element.dataset.inlineErrorFor)) element.dataset.inlineErrorFor = ids.get(element.dataset.inlineErrorFor);
        }
        for (const element of row.querySelectorAll('[name]')) {
            element.name = element.name.replace(/\[\d+\]/, `[${index}]`);
        }
        for (const element of row.querySelectorAll('[id]')) {
            if (ids.has(element.id)) element.id = ids.get(element.id);
        }
        const remove = row.querySelector('[data-remove-row]');
        if (remove) remove.hidden = rows.length <= 1;
        const legend = row.querySelector('legend');
        if (legend) legend.textContent = legend.textContent.replace(/\b(item|link|skill) \d+$/i, `$1 ${index + 1}`);
        for (const label of row.querySelectorAll('label')) {
            label.textContent = label.textContent.replace(/^Skill \d+$/, `Skill ${index + 1}`);
        }
    });
}

function addRepeatRow(form, name) {
    const group = form.querySelector(`[data-repeat-group="${CSS.escape(name)}"]`);
    const template = form.querySelector(`[data-row-template="${CSS.escape(name)}"]`);
    if (!group || !template) return;
    const existingIndexes = Array.from(group.querySelectorAll('[id]'))
        .map((element) => Number(element.id.match(/-(\d+)(?:-|$)/)?.[1] ?? -1));
    const nextIndex = Math.max(-1, ...existingIndexes) + 1;
    const fragment = template.content.cloneNode(true);
    const wrapper = document.createElement('div');
    wrapper.append(fragment);
    const markup = wrapper.innerHTML.replaceAll('__INDEX__', String(nextIndex)).replaceAll('__INDEX_LABEL__', String(group.querySelectorAll(':scope > [data-repeat-row]').length + 1));
    const holder = document.createElement('template');
    holder.innerHTML = markup;
    group.append(holder.content);
    renumberRows(group);
    group.closest('[data-section]')?.setAttribute('open', '');
    group.querySelector(':scope > [data-repeat-row]:last-child input')?.focus();
}

for (const form of document.querySelectorAll('[data-portfolio-form]')) {
    const draftForm = form.hasAttribute('data-draft-form');
    const draftKey = form.dataset.draftKey;
    const draftStatus = form.querySelector('[data-draft-status]');
    const restoreDialog = document.querySelector('[data-draft-restore-dialog]');
    let restorePending = false;
    let draftTimer;

    if (draftForm && document.body.hasAttribute('data-clear-draft')) {
        try { localStorage.removeItem(draftKey); } catch { /* Storage may be disabled. */ }
    } else if (draftForm) {
        try {
            const saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
            if (saved?.entries?.length && restoreDialog?.showModal) {
                restorePending = true;
                restoreDialog.showModal();
            }
        } catch {
            if (draftStatus) draftStatus.textContent = 'Draft storage is unavailable in this browser.';
        }
    }

    const saveDraft = (announceSaved = false) => {
        if (!draftForm || restorePending) return;
        try {
            localStorage.setItem(draftKey, JSON.stringify({ savedAt: Date.now(), entries: formSnapshot(form) }));
            if (draftStatus) draftStatus.textContent = 'Draft saved on this device.';
            if (announceSaved) announce('Draft saved on this device.');
        } catch {
            if (draftStatus) draftStatus.textContent = 'Draft could not be saved in this browser.';
        }
    };

    if (restoreDialog) {
        restoreDialog.querySelector('[data-restore-draft]')?.addEventListener('click', () => {
            try {
                const saved = JSON.parse(localStorage.getItem(draftKey) || 'null');
                if (saved?.entries) applySnapshot(form, saved.entries);
                restorePending = false;
                restoreDialog.close();
                updateSections(form);
                announce('Draft restored.');
            } catch {
                restorePending = false;
                restoreDialog.close();
                announce('The saved draft could not be restored.');
            }
        });
        restoreDialog.querySelector('[data-discard-draft]')?.addEventListener('click', () => {
            try { localStorage.removeItem(draftKey); } catch { /* Storage may be disabled. */ }
            restorePending = false;
            restoreDialog.close();
            saveDraft();
            announce('Starting a new portfolio.');
        });
    }

    for (const group of form.querySelectorAll('[data-repeat-group]')) renumberRows(group);

    form.addEventListener('input', (event) => {
        if (event.target instanceof HTMLElement) {
            validateField(event.target);
            updateSections(form);
        }
        if (draftForm) {
            window.clearTimeout(draftTimer);
            draftTimer = window.setTimeout(() => saveDraft(), 350);
        }
    });
    form.addEventListener('change', (event) => {
        if (event.target instanceof HTMLElement) {
            validateField(event.target);
            updateSections(form);
        }
        if (draftForm) saveDraft();
    });
    form.addEventListener('focusout', (event) => {
        if (event.target instanceof HTMLElement) validateField(event.target);
    });
    form.addEventListener('submit', (event) => {
        if (!validateForm(form)) {
            event.preventDefault();
            return;
        }
        const submit = form.querySelector('[data-submit-button]');
        if (submit) {
            submit.disabled = true;
            submit.setAttribute('aria-busy', 'true');
            submit.dataset.originalText = submit.textContent;
            submit.textContent = 'Saving…';
        }
        form.setAttribute('aria-busy', 'true');
    });

    form.addEventListener('click', (event) => {
        const add = event.target.closest('[data-add-row]');
        const remove = event.target.closest('[data-remove-row]');
        if (add) {
            const name = add.dataset.addRow;
            addRepeatRow(form, name);
            updateSections(form);
            if (draftForm) saveDraft();
        } else if (remove) {
            const row = remove.closest('[data-repeat-row]');
            const group = row?.parentElement;
            if (!row || !group || group.querySelectorAll(':scope > [data-repeat-row]').length <= 1) return;
            const focusTarget = group.querySelector('[data-add-row]') || group.querySelector(':scope > [data-repeat-row] input');
            row.remove();
            renumberRows(group);
            updateSections(form);
            focusTarget?.focus();
            if (draftForm) saveDraft();
        }
    });

    updateSections(form);
}

for (const section of document.querySelectorAll('[data-section]')) {
    section.addEventListener('toggle', () => {
        if (!section.open) return;
        for (const other of document.querySelectorAll('[data-section]')) {
            if (other !== section) other.open = false;
        }
    });
}

const templateForm = document.querySelector('[data-template-form]');
function showTemplateError() {
    let error = templateForm?.querySelector('[data-template-error]');
    if (!templateForm) return;
    if (!error) {
        error = document.createElement('p');
        error.className = 'status-error lg:col-span-3';
        error.dataset.templateError = '';
        error.setAttribute('role', 'alert');
        templateForm.prepend(error);
    }
    error.textContent = 'Choose a template to continue.';
    templateForm.querySelector('input[name="template_choice"]')?.focus();
    announce('Choose a template to continue.');
}
templateForm?.addEventListener('invalid', (event) => {
    if (event.target instanceof HTMLInputElement && event.target.name === 'template_choice') {
        event.preventDefault();
        showTemplateError();
    }
}, true);
templateForm?.addEventListener('submit', (event) => {
    const selected = templateForm.querySelector('input[name="template_choice"]:checked');
    let error = templateForm.querySelector('[data-template-error]');
    if (!selected) {
        event.preventDefault();
        showTemplateError();
        return;
    }
    error?.remove();
    const submit = templateForm.querySelector('[data-submit-button]');
    if (submit) {
        submit.disabled = true;
        submit.setAttribute('aria-busy', 'true');
        submit.textContent = 'Saving…';
    }
    templateForm.setAttribute('aria-busy', 'true');
});
templateForm?.addEventListener('change', () => templateForm.querySelector('[data-template-error]')?.remove());

for (const button of document.querySelectorAll('[data-dialog-open]')) {
    button.addEventListener('click', () => document.getElementById(button.dataset.dialogOpen)?.showModal());
}
for (const button of document.querySelectorAll('[data-dialog-close]')) {
    button.addEventListener('click', () => button.closest('dialog')?.close());
}
for (const dialog of document.querySelectorAll('dialog')) {
    dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
    });
}

document.addEventListener('click', async (event) => {
    const copyButton = event.target.closest('[data-copy-target]');
    if (!copyButton) return;
    const field = document.getElementById(copyButton.dataset.copyTarget);
    const status = document.querySelector('[data-copy-status]');
    if (!field) return;
    try {
        await navigator.clipboard.writeText(field.value);
    } catch {
        field.focus();
        field.select();
        if (!document.execCommand('copy')) {
            if (status) status.textContent = 'Copy was unavailable. Select and copy the private link above.';
            announce('Copy was unavailable. Select and copy the private link.');
            return;
        }
    }
    if (status) status.textContent = 'Private link copied to the clipboard.';
    announce('Private link copied. Keep it private; anyone with it can manage this portfolio.');
});

const deleteForms = Array.from(document.querySelectorAll('[data-delete-form]'));
const deleteDialog = document.querySelector('[data-delete-confirmation]');
let pendingDelete = false;
let activeDeleteForm = null;
deleteForms.forEach((form) => form.addEventListener('submit', (event) => {
    if (pendingDelete) return;
    event.preventDefault();
    activeDeleteForm = form;
    if (deleteDialog?.showModal) deleteDialog.showModal();
    else if (window.confirm('Delete this portfolio? You will have 10 seconds to undo.')) performDelete(form);
}));
deleteDialog?.querySelector('[data-cancel-delete]')?.addEventListener('click', () => deleteDialog.close());
deleteDialog?.querySelector('[data-confirm-delete]')?.addEventListener('click', () => {
    deleteDialog.close();
    performDelete(activeDeleteForm);
});

async function performDelete(form) {
    if (!form || pendingDelete) return;
    pendingDelete = true;
    const button = deleteDialog?.querySelector('[data-confirm-delete]');
    if (button) {
        button.disabled = true;
        button.textContent = 'Deleting…';
    }
    try {
        const response = await fetch(form.action, {
            method: 'DELETE',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        if (!response.ok) throw new Error('Delete request failed');
        const result = await response.json();
        window.location.assign(result.deletedUrl);
    } catch {
        pendingDelete = false;
        if (button) {
            button.disabled = false;
            button.textContent = 'Yes, delete this portfolio';
        }
        announce('The portfolio could not be deleted. Please try again.');
    }
}

const deletedScreen = document.querySelector('[data-deleted-screen]');
if (deletedScreen) {
    const countdown = deletedScreen.querySelector('[data-undo-countdown]');
    const status = deletedScreen.querySelector('[data-delete-status]');
    let remaining = Number(deletedScreen.dataset.undoSeconds || 0);
    const purge = async () => {
        try {
            const response = await fetch(deletedScreen.dataset.purgeUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!response.ok && response.status !== 404) throw new Error('Purge request failed');
            const message = response.status === 404
                ? 'Portfolio permanently deleted.'
                : (await response.json()).message;
            if (status) status.textContent = message;
            deletedScreen.querySelector('[data-undo-form]')?.remove();
            deletedScreen.querySelector('[data-undo-countdown]')?.closest('p')?.replaceWith(Object.assign(document.createElement('p'), { textContent: message }));
        } catch {
            if (status) status.textContent = 'Permanent deletion is delayed. This page will retry shortly.';
            window.setTimeout(purge, 2000);
        }
    };

    let countdownTimer;
    if (countdown) {
        countdownTimer = window.setInterval(() => {
            remaining = Math.max(0, remaining - 1);
            countdown.textContent = String(remaining);
            if (remaining === 0) {
                window.clearInterval(countdownTimer);
                purge();
            }
        }, 1000);
    } else if (remaining <= 0) {
        purge();
    }

    const undoForm = deletedScreen.querySelector('[data-undo-form]');
    undoForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = undoForm.querySelector('[type="submit"]');
        if (submit) { submit.disabled = true; submit.textContent = 'Restoring…'; }
        try {
            const response = await fetch(undoForm.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!response.ok) throw new Error('Undo request failed');
            const result = await response.json();
            window.clearInterval(countdownTimer);
            window.location.assign(result.manageUrl);
        } catch {
            if (status) status.textContent = 'The portfolio could not be restored. Please try again.';
            if (submit) { submit.disabled = false; submit.textContent = 'Undo deletion'; }
        }
    });
}

document.addEventListener('keydown', (event) => {
    const questionMark = event.key === '?' || (event.key === '/' && event.shiftKey);
    if (questionMark && !isEditingText(event.target)) {
        event.preventDefault();
        document.querySelector('[data-help-dialog]')?.showModal();
        return;
    }
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 's') {
        event.preventDefault();
        const draftForm = document.querySelector('[data-draft-form]');
        if (draftForm) {
            try {
                localStorage.setItem(draftForm.dataset.draftKey, JSON.stringify({ savedAt: Date.now(), entries: formSnapshot(draftForm) }));
                const status = draftForm.querySelector('[data-draft-status]');
                if (status) status.textContent = 'Draft saved on this device.';
                announce('Draft saved on this device.');
            } catch { announce('Draft storage is unavailable in this browser.'); }
        } else {
            const form = document.querySelector('main form');
            if (form?.requestSubmit) form.requestSubmit();
        }
        return;
    }
    if (event.altKey && event.key === 'ArrowRight') {
        const form = document.querySelector('main form');
        if (form?.requestSubmit) { event.preventDefault(); form.requestSubmit(); }
        return;
    }
    if (event.altKey && event.key === 'ArrowLeft') {
        const back = document.querySelector('[data-step-back]');
        if (back) { event.preventDefault(); back.click(); }
    }
});

const publicTemplates = document.querySelectorAll('[data-template][data-theme]');
const themeOptions = document.querySelectorAll('[data-theme-toggle] input[name="portfolio-theme"]');
const validThemes = new Set(['system', 'light', 'dark']);

if (publicTemplates.length && themeOptions.length) {
    let selectedTheme = 'system';
    try {
        const savedTheme = localStorage.getItem('portgen:theme');
        if (validThemes.has(savedTheme)) selectedTheme = savedTheme;
    } catch {
        // Theme selection still works for this page when browser storage is unavailable.
    }

    const applyTheme = (theme) => {
        if (!validThemes.has(theme)) return;
        for (const template of publicTemplates) template.dataset.theme = theme;
        for (const option of themeOptions) option.checked = option.value === theme;
        selectedTheme = theme;
    };

    applyTheme(selectedTheme);
    for (const option of themeOptions) {
        option.addEventListener('change', () => {
            if (!option.checked) return;
            applyTheme(option.value);
            try {
                localStorage.setItem('portgen:theme', selectedTheme);
            } catch {
                announce('Theme changed for this page. Browser storage is unavailable, so it will not be remembered.');
            }
        });
    }
}

const sectionNav = document.querySelector('[data-section-nav]');
if (sectionNav && 'IntersectionObserver' in window) {
    const sectionLinks = [...sectionNav.querySelectorAll('a[href^="#"]')];
    const sectionTargets = sectionLinks
        .map((link) => document.getElementById(decodeURIComponent(link.hash.slice(1))))
        .filter(Boolean);

    const setCurrentSection = (sectionId) => {
        for (const link of sectionLinks) {
            const current = link.hash === `#${sectionId}`;
            if (current) link.setAttribute('aria-current', 'true');
            else link.removeAttribute('aria-current');
            link.classList.toggle('surface-inset', current);
        }
    };

    const updateFromViewport = () => {
        const readingLine = window.innerHeight * 0.3;
        const currentSection = sectionTargets
            .filter((section) => section.getBoundingClientRect().top <= readingLine)
            .at(-1) || sectionTargets[0];
        if (currentSection) setCurrentSection(currentSection.id);
    };

    const sectionObserver = new IntersectionObserver((entries) => {
        if (entries.length) updateFromViewport();
    }, { rootMargin: '0px 0px -25% 0px', threshold: 0 });

    for (const section of sectionTargets) sectionObserver.observe(section);
    let scrollUpdatePending = false;
    window.addEventListener('scroll', () => {
        if (scrollUpdatePending) return;
        scrollUpdatePending = true;
        requestAnimationFrame(() => {
            updateFromViewport();
            scrollUpdatePending = false;
        });
    }, { passive: true });
    sectionNav.addEventListener('click', (event) => {
        const link = event.target.closest('a[href^="#"]');
        if (link?.hash) setCurrentSection(decodeURIComponent(link.hash.slice(1)));
    });
}

for (const radioGroup of document.querySelectorAll('[data-template-radiogroup]')) {
    const options = [...radioGroup.querySelectorAll('[data-template-option]')];
    const selected = options.find((option) => option.getAttribute('aria-checked') === 'true');
    options.forEach((option) => option.setAttribute('tabindex', option === selected ? '0' : '-1'));

    radioGroup.addEventListener('keydown', (event) => {
        if (!['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown'].includes(event.key)) return;
        const current = options.indexOf(event.target.closest('[data-template-option]'));
        if (current < 0 || options.length < 2) return;
        event.preventDefault();
        const offset = ['ArrowRight', 'ArrowDown'].includes(event.key) ? 1 : -1;
        const next = options[(current + offset + options.length) % options.length];
        next.focus();
        next.click();
    });
}

for (const disclosure of document.querySelectorAll('[data-owner-disclosure]')) {
    const button = disclosure.querySelector('[data-disclosure-button]');
    const syncExpanded = () => button?.setAttribute('aria-expanded', String(disclosure.open));
    disclosure.addEventListener('toggle', syncExpanded);
    syncExpanded();
    disclosure.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || !disclosure.open) return;
        event.preventDefault();
        disclosure.open = false;
        button?.focus();
    });
}

for (const banner of document.querySelectorAll('[data-ready-banner]')) {
    const portfolioSlug = document.querySelector('.pf-page')?.dataset.portfolioSlug;
    const key = portfolioSlug ? `portgen:ready-dismissed:${portfolioSlug}` : null;
    try {
        if (key && sessionStorage.getItem(key) === '1') banner.hidden = true;
    } catch { /* The server only shows this banner on the creation redirect. */ }
    banner.querySelector('[data-dismiss-ready]')?.addEventListener('click', () => {
        banner.hidden = true;
        try { if (key) sessionStorage.setItem(key, '1'); } catch { /* Dismissal still applies for this page. */ }
    });
}

document.addEventListener('click', async (event) => {
    const copyButton = event.target.closest('[data-copy-link]');
    if (!copyButton) return;
    const status = copyButton.closest('[data-copy-context]')?.querySelector('[data-copy-status]');
    const value = copyButton.dataset.copyLink;
    if (!value) return;

    try {
        await navigator.clipboard.writeText(value);
    } catch {
        const fallback = document.createElement('textarea');
        fallback.value = value;
        fallback.setAttribute('aria-hidden', 'true');
        fallback.tabIndex = -1;
        fallback.style.position = 'fixed';
        fallback.style.opacity = '0';
        document.body.append(fallback);
        fallback.select();
        const copied = document.execCommand('copy');
        fallback.remove();
        if (!copied) {
            if (status) status.textContent = 'Copy is unavailable in this browser.';
            announce('Copy is unavailable in this browser.');
            return;
        }
    }
    if (status) status.textContent = 'Link copied';
    announce('Link copied');
});
