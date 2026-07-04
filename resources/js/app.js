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

Alpine.store('fullscreen', {
    supported: typeof document !== 'undefined'
        && document.fullscreenEnabled
        && typeof document.documentElement.requestFullscreen === 'function',
    active: false,

    init() {
        this.sync();
        document.addEventListener('fullscreenchange', () => this.sync());
    },

    sync() {
        this.active = !!document.fullscreenElement;
    },

    async toggle() {
        if (!this.supported) {
            return;
        }

        try {
            if (document.fullscreenElement) {
                await document.exitFullscreen();
            } else {
                await document.documentElement.requestFullscreen();
            }
        } catch {
            Alpine.store('toast').show('Could not toggle full screen.', 'error');
        } finally {
            this.sync();
        }
    },
});

Alpine.store('navMode', {
    mode: localStorage.getItem('navMode') === 'minimalist' ? 'minimalist' : 'beast',

    toggle() {
        this.mode = this.mode === 'beast' ? 'minimalist' : 'beast';
        localStorage.setItem('navMode', this.mode);
    },

    isMinimalist() {
        return this.mode === 'minimalist';
    },

    visible(minimalist, minimalistOnly = false) {
        if (this.mode === 'beast' && minimalistOnly) {
            return false;
        }

        return this.mode === 'beast' || minimalist;
    },
});

Alpine.store('pwa', {
    canInstall: false,
    installed: false,
    deferredPrompt: null,

    init() {
        this.installed = window.matchMedia('(display-mode: standalone)').matches
            || window.navigator.standalone === true;

        window.addEventListener('beforeinstallprompt', (event) => {
            event.preventDefault();
            this.deferredPrompt = event;
            this.canInstall = true;
        });

        window.addEventListener('appinstalled', () => {
            this.installed = true;
            this.canInstall = false;
            this.deferredPrompt = null;
            Alpine.store('toast').show('AutoSpa installed successfully.', 'success');
        });

        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {
                // Service worker registration is optional for local development.
            });
        }
    },

    async install() {
        if (!this.deferredPrompt) {
            Alpine.store('toast').show('Install is not available in this browser yet.', 'error');
            return;
        }

        this.deferredPrompt.prompt();

        const choice = await this.deferredPrompt.userChoice;
        this.deferredPrompt = null;
        this.canInstall = false;

        if (choice.outcome === 'accepted') {
            this.installed = true;
        }
    },
});

Alpine.store('onboarding', {
    active: false,
    step: 0,
    steps: [],
    highlightStyle: '',
    cardStyle: '',

    get currentStep() {
        return this.steps[this.step] ?? null;
    },

    get isLastStep() {
        return this.step >= this.steps.length - 1;
    },

    start(steps) {
        this.steps = steps ?? [];
        this.step = 0;
        this.active = this.steps.length > 0;
        requestAnimationFrame(() => this.position());
    },

    restart(steps) {
        this.start(steps);
    },

    position() {
        const step = this.currentStep;
        const padding = 8;
        const cardWidth = 'min(28rem, calc(100vw - 2rem))';

        if (!step?.target) {
            this.highlightStyle = '';
            this.cardStyle = `position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:${cardWidth};`;

            return;
        }

        const element = document.querySelector(`[data-tour="${step.target}"]`);

        if (!element) {
            this.highlightStyle = '';
            this.cardStyle = `position:fixed;left:50%;top:50%;transform:translate(-50%,-50%);width:${cardWidth};`;

            return;
        }

        const rect = element.getBoundingClientRect();
        this.highlightStyle = `position:fixed;top:${Math.max(rect.top - padding, 8)}px;left:${Math.max(rect.left - padding, 8)}px;width:${rect.width + (padding * 2)}px;height:${rect.height + (padding * 2)}px;`;

        const cardTop = rect.bottom + 16;

        if (cardTop + 280 > window.innerHeight) {
            this.cardStyle = `position:fixed;left:50%;bottom:1.5rem;transform:translateX(-50%);width:${cardWidth};`;
        } else {
            this.cardStyle = `position:fixed;left:50%;top:${cardTop}px;transform:translateX(-50%);width:${cardWidth};`;
        }
    },

    next() {
        if (this.isLastStep) {
            this.complete();

            return;
        }

        this.step += 1;
        requestAnimationFrame(() => this.position());
    },

    previous() {
        if (this.step === 0) {
            return;
        }

        this.step -= 1;
        requestAnimationFrame(() => this.position());
    },

    skip() {
        this.complete();
    },

    async complete() {
        this.active = false;

        try {
            await fetch('/onboarding/complete', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
        } catch {
            // Ignore network failures; tour is still dismissed locally.
        }
    },
});

document.addEventListener('alpine:init', () => {
    Alpine.store('theme').sync();
    Alpine.store('fullscreen').init();
    Alpine.store('pwa').init();

    Alpine.data('onboardingTour', () => ({
        init() {
            this.$watch('$store.onboarding.step', () => {
                requestAnimationFrame(() => Alpine.store('onboarding').position());
            });

            window.addEventListener('resize', () => {
                if (Alpine.store('onboarding').active) {
                    Alpine.store('onboarding').position();
                }
            });
        },
    }));

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
            registration_number: '',
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
            this.customerForm = { full_name: '', phone: '', email: '', registration_number: '' };
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
            if (this.customerForm.registration_number) {
                formData.append('registration_number', this.customerForm.registration_number);
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
                if (data.vehicle) {
                    this.vehicles.push(data.vehicle);
                    this.vehicleId = String(data.vehicle.id);
                } else {
                    this.vehicleId = '';
                }
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

    Alpine.data('customerVehicleLinkForm', (config = {}) => ({
        customerId: config.customerId ?? '',
        vehicleId: config.vehicleId ?? '',
        customers: config.customers ?? [],
        vehicles: config.vehicles ?? [],

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

        syncCustomerFromVehicle() {
            if (!this.vehicleId) {
                return;
            }

            const vehicle = this.vehicles.find(
                (entry) => String(entry.id) === String(this.vehicleId),
            );

            if (vehicle && String(this.customerId) !== String(vehicle.customer_id)) {
                this.customerId = String(vehicle.customer_id);
            }
        },

        init() {
            this.syncCustomerFromVehicle();

            this.$watch('customerId', () => {
                const stillValid = this.filteredVehicles.some(
                    (vehicle) => String(vehicle.id) === String(this.vehicleId),
                );

                if (!stillValid) {
                    this.vehicleId = '';
                }
            });

            this.$watch('vehicleId', () => {
                this.syncCustomerFromVehicle();
            });
        },
    }));

    Alpine.data('liveJobBoard', (config = {}) => ({
        jobCards: config.jobCards ?? [],
        canManage: config.canManage ?? false,
        updating: {},
        statusConfig: {
            open: { label: 'Open', progress: 25, color: 'amber' },
            in_progress: { label: 'In Progress', progress: 70, color: 'sky' },
            completed: { label: 'Completed', progress: 100, color: 'green' },
            cancelled: { label: 'Cancelled', progress: 0, color: 'slate' },
        },
        actionButtonClasses: {
            open: 'asp-btn asp-btn-ghost !px-3 !py-1.5 text-xs',
            in_progress: 'asp-btn asp-btn-secondary !px-3 !py-1.5 text-xs',
            completed: 'asp-btn asp-btn-primary !px-3 !py-1.5 text-xs',
        },

        countByStatus(status) {
            return this.jobCards.filter((jobCard) => jobCard.status === status).length;
        },

        progressFor(status) {
            return this.statusConfig[status]?.progress ?? 0;
        },

        colorFor(status) {
            return this.statusConfig[status]?.color ?? 'slate';
        },

        labelFor(status) {
            return this.statusConfig[status]?.label ?? 'Open';
        },

        startedLabel(jobCard) {
            return jobCard.started_at_human
                ? `Started ${jobCard.started_at_human}`
                : 'Awaiting wash start';
        },

        buttonClass(status) {
            return this.actionButtonClasses[status] ?? this.actionButtonClasses.open;
        },

        isUpdating(jobCardId) {
            return !!this.updating[jobCardId];
        },

        async updateStatus(jobCardId, status) {
            const jobCard = this.jobCards.find((entry) => entry.id === jobCardId);

            if (!jobCard || this.isUpdating(jobCardId) || jobCard.status === status) {
                return;
            }

            this.updating = {
                ...this.updating,
                [jobCardId]: true,
            };

            const formData = new FormData();
            formData.append('_method', 'PATCH');
            formData.append('status', status);

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrf) {
                formData.append('_token', csrf);
            }

            try {
                const response = await fetch(jobCard.update_url, {
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
                    Alpine.store('toast').show(data.message || 'Could not update washing status.', 'error');
                    return;
                }

                if (data.remove_from_live) {
                    this.jobCards = this.jobCards.filter((entry) => entry.id !== jobCardId);
                } else {
                    this.jobCards = this.jobCards.map((entry) => entry.id === jobCardId
                        ? { ...entry, ...data.job_card }
                        : entry);
                }

                Alpine.store('toast').show(data.message ?? 'Car washing status updated.', 'success');
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
            } finally {
                const updating = { ...this.updating };
                delete updating[jobCardId];
                this.updating = updating;
            }
        },
    }));

    Alpine.data('posCheckout', (config = {}) => ({
        cart: [],
        customerId: config.defaultCustomerId ?? '',
        paymentMethodId: '',
        showCheckoutGuide: true,
        catalogTab: 'all',
        search: '',
        customers: config.customers ?? [],
        customerStoreUrl: config.customerStoreUrl ?? '/customers',
        stkPushUrl: config.stkPushUrl ?? '/pos/stk-push',
        services: config.services ?? [],
        products: config.products ?? [],
        paymentMethods: config.paymentMethods ?? [],
        showCustomerModal: false,
        customerSaving: false,
        customerErrors: {},
        customerForm: {
            full_name: '',
            phone: '',
            email: '',
            registration_number: '',
        },
        showStkModal: false,
        stkPushLoading: false,
        stkPushErrors: {},
        stkPhoneDraft: '',
        stkPhone: '',
        stkReference: '',
        stkStatus: '',
        checkoutSubmitting: false,
        checkoutForm: null,

        init() {
            this.showCheckoutGuide = !this.checkoutGuideDismissed();

            if (Array.isArray(config.oldItems) && config.oldItems.length > 0) {
                this.cart = config.oldItems.map((item) => ({
                    id: item.item_id,
                    itemType: item.item_type,
                    name: item.description,
                    price: parseFloat(item.unit_price),
                    qty: parseFloat(item.quantity),
                }));
            }

            if (config.oldPaymentMethodId) {
                this.paymentMethodId = String(config.oldPaymentMethodId);
            }

            if (config.oldCustomerId) {
                this.customerId = String(config.oldCustomerId);
            }

            this.$watch('customerId', () => {
                this.clearStkState();
            });

            this.$watch('paymentMethodId', () => {
                this.clearStkState();
            });
        },

        checkoutGuideDismissed() {
            try {
                return localStorage.getItem('posCheckoutGuideDismissed') === 'true';
            } catch {
                return false;
            }
        },

        dismissCheckoutGuide() {
            this.showCheckoutGuide = false;

            try {
                localStorage.setItem('posCheckoutGuideDismissed', 'true');
            } catch {
                // Ignore storage failures and just hide for this session.
            }
        },

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

        get selectedCustomer() {
            return this.customers.find(
                (entry) => String(entry.id) === String(this.customerId),
            ) ?? null;
        },

        get selectedCustomerVehicle() {
            return this.selectedCustomer?.vehicle_summary ?? '';
        },

        get selectedCustomerPhone() {
            return this.selectedCustomer?.phone ?? '';
        },

        get isMpesaSelected() {
            return this.selectedMethodSlug === 'mpesa';
        },

        get isCashSelected() {
            return this.selectedMethodSlug === 'cash';
        },

        get canCheckout() {
            return this.cart.length > 0 && this.customerId && this.paymentMethodId;
        },

        buildCustomerOption(customer) {
            const displayName = customer.display_name ?? customer.full_name ?? null;
            const vehicleSummary = customer.vehicle_summary ?? null;

            return {
                id: customer.id,
                display_name: displayName,
                phone: customer.phone ?? '',
                vehicle_summary: vehicleSummary,
                option_label: customer.option_label ?? (displayName
                    ? `${displayName}${vehicleSummary ? ` · ${vehicleSummary}` : ''}`
                    : (vehicleSummary ?? 'Unnamed customer')),
            };
        },

        sortCustomers() {
            this.customers.sort((a, b) => a.option_label.localeCompare(b.option_label));
        },

        clearStkState() {
            this.stkPhone = '';
            this.stkReference = '';
            this.stkStatus = '';
            this.stkPushErrors = {};
            this.showStkModal = false;
        },

        formatMoney(amount) {
            return Number(amount).toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
        },

        openCustomerModal() {
            this.customerForm = { full_name: '', phone: '', email: '', registration_number: '' };
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
            if (this.customerForm.registration_number) {
                formData.append('registration_number', this.customerForm.registration_number);
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

                this.customers.push(this.buildCustomerOption(data.customer));
                this.sortCustomers();
                this.customerId = String(data.customer.id);
                this.showCustomerModal = false;
                this.customerSaving = false;
                Alpine.store('toast').show(data.message ?? 'Customer created.', 'success');
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.customerSaving = false;
            }
        },

        openStkModal(form) {
            this.checkoutForm = form;
            this.stkPhoneDraft = this.selectedCustomerPhone;
            this.stkPushErrors = {};
            this.showStkModal = true;
            this.$nextTick(() => document.getElementById('stk_phone')?.focus());
        },

        closeStkModal() {
            if (this.stkPushLoading) {
                return;
            }

            this.showStkModal = false;
        },

        confirmCashReceived() {
            return window.confirm(
                `Have you received the cash payment of KES ${this.formatMoney(this.total)} from the customer?`,
            );
        },

        handleCheckout(event) {
            const form = event.target;

            if (!this.canCheckout) {
                return;
            }

            if (this.isMpesaSelected) {
                this.openStkModal(form);
                return;
            }

            if (this.isCashSelected && !this.confirmCashReceived()) {
                return;
            }

            this.submitCheckoutForm(form);
        },

        async confirmStkPush() {
            this.stkPushLoading = true;
            this.stkPushErrors = {};

            const phone = this.stkPhoneDraft.trim();
            if (!phone) {
                this.stkPushErrors = {
                    phone: ['Phone number is required for STK push.'],
                };
                this.stkPushLoading = false;
                return;
            }

            const formData = new FormData();
            formData.append('customer_id', this.customerId);
            formData.append('payment_method_id', this.paymentMethodId);
            formData.append('method', this.selectedMethodSlug);
            formData.append('phone', phone);
            formData.append('amount', this.total.toFixed(2));

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            if (csrf) {
                formData.append('_token', csrf);
            }

            try {
                const response = await fetch(this.stkPushUrl, {
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
                        this.stkPushErrors = data.errors;
                    } else {
                        Alpine.store('toast').show(data.message || 'Could not initiate STK push.', 'error');
                    }
                    this.stkPushLoading = false;
                    return;
                }

                this.stkPhone = phone;
                this.stkReference = data.transaction_id ?? '';
                this.stkStatus = data.status ?? 'pending';
                this.showStkModal = false;
                this.stkPushLoading = false;
                Alpine.store('toast').show(data.message ?? 'STK push initiated.', 'success');

                if (this.checkoutForm) {
                    this.submitCheckoutForm(this.checkoutForm);
                }
            } catch {
                Alpine.store('toast').show('Network error. Please try again.', 'error');
                this.stkPushLoading = false;
            }
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

        syncCheckoutFields(form) {
            form.querySelectorAll('[data-pos-cart-item], input[name^="items["]').forEach((element) => element.remove());

            const fields = {
                customer_id: this.customerId,
                payment_method_id: this.paymentMethodId,
                method: this.selectedMethodSlug,
                subtotal: this.subtotal.toFixed(2),
                total_amount: this.total.toFixed(2),
            };

            Object.entries(fields).forEach(([name, value]) => {
                let input = form.querySelector(`input[type="hidden"][name="${name}"]`);

                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    form.appendChild(input);
                }

                input.value = value ?? '';
            });

            this.cart.forEach((item, index) => {
                const itemFields = {
                    item_type: item.itemType,
                    item_id: item.id,
                    description: item.name,
                    quantity: item.qty,
                    unit_price: item.price.toFixed(2),
                    total: (item.price * item.qty).toFixed(2),
                };

                Object.entries(itemFields).forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `items[${index}][${key}]`;
                    input.value = value ?? '';
                    input.dataset.posCartItem = 'true';
                    form.appendChild(input);
                });
            });
        },

        submitCheckoutForm(form) {
            this.syncCheckoutFields(form);
            this.checkoutSubmitting = true;

            this.$nextTick(() => {
                form.submit();
            });
        },
    }));
});

Alpine.start();
