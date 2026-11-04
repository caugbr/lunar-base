
function switchTab(tabKey) {
    document.querySelectorAll('.settings-tab').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.tab === tabKey);
    });

    document.querySelectorAll('.tab-panel').forEach(panel => {
        panel.classList.toggle('active', panel.dataset.panel === tabKey);
    });

    const hidden = document.getElementById('_active_tab');
    if (hidden) hidden.value = tabKey;
}

function enableTabButtons() {
    document.querySelectorAll('.settings-tabs button').forEach(btn => {
        btn.disabled = false;
    });
}

function revertGroup(inputs, originalValue) {
    const first = inputs[0];

    if (first.type === 'checkbox' && inputs.length > 1) {
        const values = originalValue ? originalValue.split(',') : [];
        inputs.forEach(cb => {
            cb.checked = values.includes(cb.value);
        });
        first.dispatchEvent(new Event('change'));
        return;
    }

    if (first.type === 'checkbox') {
        first.checked = originalValue;
        first.dispatchEvent(new Event('change'));
        return;
    }

    if (first.type === 'radio') {
        inputs.forEach(r => {
            r.checked = (r.value === originalValue);
        });
        first.dispatchEvent(new Event('change'));
        return;
    }

    first.value = originalValue !== null ? originalValue : '';
    first.dispatchEvent(new Event('change'));
}

document.addEventListener('DOMContentLoaded', () => {
    const activeTab = document.getElementById('_active_tab')?.value;
    if (activeTab) {
        switchTab(activeTab);
    }

    const warnSelector = '.form-group[data-warn-on-change],.form-field[data-warn-on-change]';
    document.querySelectorAll(warnSelector).forEach(group => {
        const message = group.dataset.warnOnChange;
        const inputs = group.querySelectorAll('input:not([type="hidden"]), select, textarea');
        if (!inputs.length) return;

        const readValue = () => {
            const first = inputs[0];

            if (first.type === 'checkbox' && inputs.length > 1) {
                return Array.from(inputs)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value)
                    .sort()
                    .join(',');
            }

            if (first.type === 'checkbox') {
                return first.checked;
            }

            if (first.type === 'radio') {
                const checked = Array.from(inputs).find(r => r.checked);
                return checked ? checked.value : null;
            }

            return first.value;
        };

        let originalValue = readValue();

        inputs.forEach(input => {
            input.addEventListener('change', async () => {
                const newValue = readValue();

                if (newValue !== originalValue) {
                    if (window.Dialog && typeof Dialog.confirm === 'function') {
                        if (!(await Dialog.confirm(message))) {
                            revertGroup(inputs, originalValue);
                        } else {
                            originalValue = newValue;
                        }
                    } else if (!window.confirm(message)) {
                        revertGroup(inputs, originalValue);
                    } else {
                        originalValue = newValue;
                    }
                }
            });
        });
    });

    enableTabButtons();
});
