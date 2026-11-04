class DependencyManager {
    constructor() {
        this.dependents = [];
    }

    init() {
        const elements = document.querySelectorAll('[data-depends-on]');
        if (!elements.length) return;

        elements.forEach(el => {
            try {
                const rule = JSON.parse(el.dataset.dependsOn);
                const childKey = this.findFieldKey(el);
                if (!childKey) return;

                this.dependents.push({ element: el, childKey, rule });

                this.extractParents(rule).forEach(parentKey => {
                    this.bindParent(parentKey);
                });
            } catch (e) {
                console.error('Erro ao processar dependência:', el.dataset.dependsOn, e);
            }
        });

        this.evaluateAll();
    }

    findFieldKey(groupEl) {
        const input = groupEl.querySelector('input[name]:not([type="hidden"]), select[name], textarea[name]');
        return input?.name?.replace(/\[\]$/, '');
    }

    findGroup(key) {
        const checkbox = document.querySelector(`[name="${key}"][type="checkbox"]`);
        if (checkbox) return checkbox.closest('.form-group, .form-field');

        const el = document.querySelector(`[name="${key}"]`)
                || document.querySelector(`[name="${key}[]"]`);
        return el?.closest('.form-group, .form-field');
    }

    bindParent(parentKey) {
        const group = this.findGroup(parentKey);
        if (!group) return;

        const checkbox = group.querySelector(`[name="${parentKey}"][type="checkbox"]`);
        const select = group.querySelector(`[name="${parentKey}"]`);
        const input = checkbox || select;

        if (!input) return;

        input.addEventListener('change', () => this.evaluateAll());

        const switchTrack = group.querySelector('.switch-track');
        if (switchTrack) {
            switchTrack.addEventListener('click', () => {
                requestAnimationFrame(() => this.evaluateAll());
            });
        }
    }

    extractParents(rule) {
        if (typeof rule === 'string') return [rule];
        if (rule.field) return [rule.field];
        return [];
    }

    evaluateAll() {
        this.dependents.forEach(({ element, rule }) => {
            const enabled = this.evaluate(rule);
            this.setState(element, enabled);
        });
    }

    evaluate(rule) {
        if (typeof rule === 'string') {
            return this.isTruthy(rule);
        }
        if (rule.field && rule.operator && 'value' in rule) {
            const actual = this.getValue(rule.field);
            return this.compare(actual, rule.operator, rule.value);
        }
        return true;
    }

    getValue(key) {
        const checkbox = document.querySelector(`[name="${key}"][type="checkbox"]`);
        if (checkbox) return checkbox.checked;

        const radio = document.querySelector(`[name="${key}"][type="radio"]:checked`);
        if (radio) return radio.value;

        const el = document.querySelector(`[name="${key}"]`)
                || document.querySelector(`[name="${key}[]"]`);

        if (!el) return null;

        if (el.tagName === 'SELECT') {
            const val = el.value;
            if (val === 'true' || val === '1' || val === 'on') return true;
            if (val === 'false' || val === '0' || val === '') return false;
            return val;
        }

        return el.value;
    }

    isTruthy(key) {
        const v = this.getValue(key);
        if (typeof v === 'boolean') return v;
        if (typeof v === 'number') return v !== 0;
        return v !== '' && v !== null && v !== undefined && v !== false;
    }

    compare(actual, op, expected) {
        const toBool = (v) => {
            if (v === true || v === 'true' || v === '1' || v === 1 || v === 'on') return true;
            if (v === false || v === 'false' || v === '0' || v === 0 || v === '' || v === null || v === undefined) return false;
            return v;
        };

        if (typeof expected === 'boolean' || typeof actual === 'boolean') {
            return toBool(actual) === toBool(expected);
        }

        switch (op) {
            case '==': return actual == expected;
            case '===': return actual === expected;
            case '!=': return actual != expected;
            case '!==': return actual !== expected;
            case '>': return actual > expected;
            case '<': return actual < expected;
            default: return Boolean(actual);
        }
    }

    setState(groupEl, enabled) {
        groupEl.classList.toggle('field-disabled', !enabled);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.querySelectorAll('[data-depends-on]').length > 0) {
        window.depManager = new DependencyManager();
        window.depManager.init();
    }
});
