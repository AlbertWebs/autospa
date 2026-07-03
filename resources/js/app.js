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
        customers: config.customers ?? [],
        vehicles: config.vehicles ?? [],
        customerStoreUrl: config.customerStoreUrl ?? '/customers',
        vehicleStoreUrl: config.vehicleStoreUrl ?? '/vehicles',
        successMessage: config.successMessage ?? 'Job card created.',
        showCustomerModal: false,
        customerSaving: false,
        customerErrors: {},
        customerForm: {
            full_name: '',
            phone: '',
            email: '',
        },
        showVehicleModal: false,
        vehicleSaving: false,
        vehicleErrors: {},
        vehicleForm: {
            registration_number: '',
            make: '',
            model: '',
            color: '',
        },

        get filteredVehicles() {
            if (!this.customerId) {
                return this.vehicles;
            }

            return this.vehicles.filter(
                (vehicle) => String(vehicle.customer_id) === String(this.customerId),
            );
        },

        vehicleLabel(vehicle) {
            let label = vehicle.registration_number;
            if (vehicle.make) {
                label += ` · ${vehicle.make}`;
                if (vehicle.model) {
                    label += ` ${vehicle.model}`;
                }
            }
            return label;
        },

        init() {
            this.$watch('customerId', () => {
                const stillValid = this.filteredVehicles.some(
                    (vehicle) => String(vehicle.id) === String(this.vehicleId),
                );

                if (!stillValid) {
                    this.vehicleId = '';
                }
            });
        },

        openCustomerModal() {
            this.customerForm = { full_name: '', phone: '', email: '' };
            this.customerErrors = {};
            this.showCustomerModal = true;
            this.$nextTick(() => document.getElementById('quick_customer_full_name')?.focus());
        },

        closeCustomerModal() {
            if (this.customerSaving) {
                return;
            }
            this.showCustomerModal = false;
        },

        async createCustomer() {
            this.customerSaving = true;
            this.customerErrors = {};

            const formData = new FormData();
            formData.append('full_name', this.customerForm.full_name);
            formData.append('phone', this.customerForm.phone);
            if (this.customerForm.email) {
                formData.append('email', this.customerForm.email);
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrf) {
                formData.append('_token', csrf);
            }

            try {
                const response = await fetch(this.customerStoreUrl, {
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
                        this.customerErrors = data.errors;
                    } else {
                        Alpine.store('toast').show(data.message || 'Could not create customer.', 'error');
                    }
                    this.customerSaving = false;
                    return;
                }

                this.customers.push({
                    id: data.customer.id,
                    full_name: data.customer.full_name,
                });
                this.customers.sort((a, b) => a.full_name.localeCompare(b.full_name));
                this.customerId = String(data.customer.id);
                this.vehicleId = '';
                this.showCustomerModal = false;
                this.customerSaving = false;
                Alpine.store('toast').show(data.message ?? 'Customer created.', 'success');
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.customerSaving = false;
            }
        },

        openVehicleModal() {
            if (!this.customerId) {
                Alpine.store('toast').show('Select or create a customer first.', 'error');
                return;
            }

            this.vehicleForm = {
                registration_number: '',
                make: '',
                model: '',
                color: '',
            };
            this.vehicleErrors = {};
            this.showVehicleModal = true;
            this.$nextTick(() => document.getElementById('quick_vehicle_registration')?.focus());
        },

        closeVehicleModal() {
            if (this.vehicleSaving) {
                return;
            }
            this.showVehicleModal = false;
        },

        async createVehicle() {
            this.vehicleSaving = true;
            this.vehicleErrors = {};

            const formData = new FormData();
            formData.append('customer_id', this.customerId);
            formData.append('registration_number', this.vehicleForm.registration_number);
            if (this.vehicleForm.make) {
                formData.append('make', this.vehicleForm.make);
            }
            if (this.vehicleForm.model) {
                formData.append('model', this.vehicleForm.model);
            }
            if (this.vehicleForm.color) {
                formData.append('color', this.vehicleForm.color);
            }

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrf) {
                formData.append('_token', csrf);
            }

            try {
                const response = await fetch(this.vehicleStoreUrl, {
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
                        this.vehicleErrors = data.errors;
                    } else {
                        Alpine.store('toast').show(data.message || 'Could not register vehicle.', 'error');
                    }
                    this.vehicleSaving = false;
                    return;
                }

                this.vehicles.push(data.vehicle);
                this.vehicleId = String(data.vehicle.id);
                this.showVehicleModal = false;
                this.vehicleSaving = false;
                Alpine.store('toast').show(data.message ?? 'Vehicle registered.', 'success');
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.vehicleSaving = false;
            }
        },

        async submit(event) {
            event.preventDefault();
            this.loading = true;
            this.errors = {};

            const form = event.target;
            const formData = new FormData(form);

            if (form.customer_id?.value) {
                formData.set('customer_id', form.customer_id.value);
            } else if (this.customerId) {
                formData.set('customer_id', this.customerId);
            }
            if (form.vehicle_id?.value) {
                formData.set('vehicle_id', form.vehicle_id.value);
            } else if (this.vehicleId) {
                formData.set('vehicle_id', this.vehicleId);
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

    Alpine.data('posCheckout', (config = {}) => ({
        cart: [],
        customerId: config.defaultCustomerId ?? '',
        paymentMethodId: '',
        catalogTab: 'all',
        search: '',
        services: config.services ?? [],
        products: config.products ?? [],
        paymentMethods: config.paymentMethods ?? [],

        get filteredItems() {
            let items = [];

            if (this.catalogTab === 'all' || this.catalogTab === 'service') {
                items = items.concat(
                    this.services.map((service) => ({ ...service, itemType: 'service' })),
                );
            }

            if (this.catalogTab === 'all' || this.catalogTab === 'product') {
                items = items.concat(
                    this.products.map((product) => ({ ...product, itemType: 'product' })),
                );
            }

            if (this.search.trim()) {
                const query = this.search.trim().toLowerCase();
                items = items.filter((item) => item.name.toLowerCase().includes(query));
            }

            return items;
        },

        get itemCount() {
            return this.cart.reduce((sum, item) => sum + item.qty, 0);
        },

        get subtotal() {
            return this.cart.reduce((sum, item) => sum + item.price * item.qty, 0);
        },

        get total() {
            return this.subtotal;
        },

        get selectedMethodSlug() {
            const method = this.paymentMethods.find(
                (entry) => String(entry.id) === String(this.paymentMethodId),
            );

            return method?.slug ?? '';
        },

        get canCheckout() {
            return this.cart.length > 0 && this.customerId && this.paymentMethodId;
        },

        formatMoney(amount) {
            return Number(amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        addItem(item) {
            const existing = this.cart.find(
                (entry) => entry.id === item.id && entry.itemType === item.itemType,
            );

            if (existing) {
                existing.qty++;
                return;
            }

            this.cart.push({
                id: item.id,
                itemType: item.itemType,
                name: item.name,
                price: parseFloat(item.price),
                qty: 1,
            });
        },

        incrementItem(index) {
            if (this.cart[index]) {
                this.cart[index].qty++;
            }
        },

        decrementItem(index) {
            if (!this.cart[index]) {
                return;
            }

            if (this.cart[index].qty <= 1) {
                this.cart.splice(index, 1);
                return;
            }

            this.cart[index].qty--;
        },

        removeItem(index) {
            this.cart.splice(index, 1);
        },

        clearCart() {
            this.cart = [];
        },
    }));
});

Alpine.start();
