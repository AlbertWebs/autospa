import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import flatpickr from 'flatpickr';
import TomSelect from 'tom-select';

import 'flatpickr/dist/flatpickr.min.css';
import 'tom-select/dist/css/tom-select.css';

window.Alpine = Alpine;
window.Chart = Chart;
window.flatpickr = flatpickr;
window.TomSelect = TomSelect;

Alpine.store('toast', {
    items: [],
    show(message, type = 'success') {
        const id = Date.now();
        this.items.push({ id, message, type });
        setTimeout(() => this.dismiss(id), 5000);
    },
    dismiss(id) {
        this.items = this.items.filter((item) => item.id !== id);
    },
});

Alpine.store('theme', {
    dark: localStorage.getItem('darkMode') === 'true',

    toggle() {
        this.dark = !this.dark;
        this.sync();
    },

    sync() {
        localStorage.setItem('darkMode', this.dark ? 'true' : 'false');
        document.documentElement.classList.toggle('dark', this.dark);
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.store('theme').sync();

    Alpine.data('ajaxForm', (config = {}) => ({
        loading: false,
        errors: {},
        successMessage: config.successMessage ?? 'Saved successfully.',

        async submit(event) {
            event.preventDefault();
            this.loading = true;
            this.errors = {};

            const form = event.target;
            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let data = {};
                const contentType = response.headers.get('content-type');
                if (contentType?.includes('application/json')) {
                    data = await response.json();
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        Alpine.store('toast').show(data.message || 'Something went wrong.', 'error');
                    }
                    this.loading = false;
                    return;
                }

                Alpine.store('toast').show(data.message ?? this.successMessage, 'success');

                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 400);
                    return;
                }

                this.loading = false;
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.loading = false;
            }
        },
    }));

    Alpine.data('jobCardCreateForm', (config = {}) => ({
        loading: false,
        errors: {},
        customerId: config.customerId ?? '',
        vehicleId: config.vehicleId ?? '',
        successMessage: config.successMessage ?? 'Job card created.',

        init() {
            this.$watch('customerId', () => {
                const vehicleSelect = document.getElementById('vehicle_id');
                if (!vehicleSelect) {
                    return;
                }

                const selected = vehicleSelect.options[vehicleSelect.selectedIndex];
                if (selected?.disabled) {
                    vehicleSelect.value = '';
                    this.vehicleId = '';
                }
            });
        },

        async submit(event) {
            event.preventDefault();
            this.loading = true;
            this.errors = {};

            const form = event.target;
            const formData = new FormData(form);

            if (form.customer_id?.value) {
                formData.set('customer_id', form.customer_id.value);
            }
            if (form.vehicle_id?.value) {
                formData.set('vehicle_id', form.vehicle_id.value);
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                let data = {};
                const contentType = response.headers.get('content-type');
                if (contentType?.includes('application/json')) {
                    data = await response.json();
                }

                if (!response.ok) {
                    if (response.status === 422 && data.errors) {
                        this.errors = data.errors;
                    } else {
                        Alpine.store('toast').show(data.message || 'Something went wrong.', 'error');
                    }
                    this.loading = false;
                    return;
                }

                Alpine.store('toast').show(data.message ?? this.successMessage, 'success');

                if (data.redirect) {
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 400);
                    return;
                }

                this.loading = false;
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.loading = false;
            }
        },
    }));
});

Alpine.start();
