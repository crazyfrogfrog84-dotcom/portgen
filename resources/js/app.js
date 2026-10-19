import './bootstrap';

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
        .filter(([name]) => !['_token', '_method'].includes(name))
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

const deleteForm = document.querySelector('[data-delete-form]');
const deleteDialog = document.querySelector('[data-delete-confirmation]');
let pendingDelete = false;
deleteForm?.addEventListener('submit', (event) => {
    if (pendingDelete) return;
    event.preventDefault();
    if (deleteDialog?.showModal) deleteDialog.showModal();
    else if (window.confirm('Delete this portfolio? You will have 10 seconds to undo.')) performDelete();
});
deleteDialog?.querySelector('[data-cancel-delete]')?.addEventListener('click', () => deleteDialog.close());
deleteDialog?.querySelector('[data-confirm-delete]')?.addEventListener('click', () => {
    deleteDialog.close();
    performDelete();
});

async function performDelete() {
    if (!deleteForm || pendingDelete) return;
    pendingDelete = true;
    const button = deleteDialog?.querySelector('[data-confirm-delete]');
    if (button) {
        button.disabled = true;
        button.textContent = 'Deleting…';
    }
    try {
        const response = await fetch(deleteForm.action, {
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
            if (!response.ok) throw new Error('Purge request failed');
            const message = (await response.json()).message;
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
