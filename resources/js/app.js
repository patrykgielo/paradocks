import './bootstrap';
import Alpine from 'alpinejs';

// Alpine.js global components and data
Alpine.data('bookingWizard', () => ({
    step: 1,
    totalSteps: 4,
    service: null,
    date: null,
    timeSlot: null,
    customer: {
        first_name: '',
        last_name: '',
        phone_e164: '',
        street_name: '',
        street_number: '',
        city: '',
        postal_code: '',
        access_notes: '',
        notes: ''
    },
    loading: false,
    errors: {},
    availableSlots: [],

    init() {
        // Initialize component
        console.log('Booking wizard initialized - automatic staff assignment enabled');
    },

    // Calculate minimum booking date (24 hours advance requirement)
    // Conservative approach: Always block next 2 full days to ensure 24h requirement
    // This avoids edge cases with business hours (9-18) and timezone issues
    minDate() {
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 2);
        minDate.setHours(0, 0, 0, 0);
        return minDate.toISOString().split('T')[0];
    },

    nextStep() {
        if (this.validateStep()) {
            this.step++;
            this.scrollToTop();
        }
    },

    prevStep() {
        if (this.step > 1) {
            this.step--;
            this.scrollToTop();
        }
    },

    goToStep(stepNumber) {
        if (stepNumber <= this.step || this.validateStepsUpTo(stepNumber - 1)) {
            this.step = stepNumber;
            this.scrollToTop();
        }
    },

    validateStep() {
        this.errors = {};

        switch(this.step) {
            case 1:
                if (!this.service) {
                    this.errors.service = 'Wybierz usługę';
                    return false;
                }
                break;
            case 2:
                if (!this.date) {
                    this.errors.date = 'Wybierz datę';
                    return false;
                }
                if (!this.timeSlot) {
                    this.errors.timeSlot = 'Wybierz godzinę';
                    return false;
                }
                break;
            case 3:
                return this.validateStep3();
        }

        return true;
    },

    validateStep3() {
        this.errors = {};
        let valid = true;

        // Required fields
        if (!this.customer.first_name || this.customer.first_name.trim() === '') {
            this.errors.first_name = 'Imię jest wymagane';
            valid = false;
        }

        if (!this.customer.last_name || this.customer.last_name.trim() === '') {
            this.errors.last_name = 'Nazwisko jest wymagane';
            valid = false;
        }

        if (!this.customer.phone_e164 || this.customer.phone_e164.trim() === '') {
            this.errors.phone_e164 = 'Telefon jest wymagany';
            valid = false;
        } else if (!/^\+\d{1,3}\d{6,14}$/.test(this.customer.phone_e164)) {
            this.errors.phone_e164 = 'Nieprawidłowy format telefonu (wymagany: +48501234567)';
            valid = false;
        }

        // Optional postal code validation
        if (this.customer.postal_code && !/^\d{2}-\d{3}$/.test(this.customer.postal_code)) {
            this.errors.postal_code = 'Nieprawidłowy format kodu pocztowego (wymagany: 00-000)';
            valid = false;
        }

        return valid;
    },

    validateStepsUpTo(stepNumber) {
        const currentStep = this.step;
        let valid = true;

        for (let i = 1; i <= stepNumber; i++) {
            this.step = i;
            if (!this.validateStep()) {
                valid = false;
                break;
            }
        }

        this.step = currentStep;
        return valid;
    },

    async fetchAvailableSlots() {
        if (!this.date) return;

        this.loading = true;
        this.availableSlots = [];
        this.timeSlot = null;
        this.errors.slots = null;

        try {
            const response = await fetch('/api/available-slots', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    service_id: this.service.id,
                    date: this.date
                })
            });

            if (!response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Błąd serwera');
                } else {
                    throw new Error(`Błąd ${response.status}: Sprawdź konsolę przeglądarki`);
                }
            }

            const data = await response.json();
            this.availableSlots = data.slots || [];

            if (this.availableSlots.length === 0) {
                this.errors.slots = 'Brak dostępnych terminów w tym dniu';
            }
        } catch (error) {
            console.error('API Error:', error);
            this.errors.slots = error.message || 'Nie udało się pobrać dostępnych terminów';
        } finally {
            this.loading = false;
        }
    },

    selectTimeSlot(slot) {
        this.timeSlot = slot;
        this.errors.timeSlot = null;
    },

    scrollToTop() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    get progressPercentage() {
        return (this.step / this.totalSteps) * 100;
    },

    get canProceed() {
        return Object.keys(this.errors).length === 0;
    }
}));

Alpine.data('serviceCard', () => ({
    hover: false,
    expanded: false,

    toggleDetails() {
        this.expanded = !this.expanded;
    }
}));

Alpine.data('toast', () => ({
    visible: false,
    message: '',
    type: 'success',

    show(message, type = 'success') {
        this.message = message;
        this.type = type;
        this.visible = true;

        setTimeout(() => {
            this.hide();
        }, 5000);
    },

    hide() {
        this.visible = false;
    }
}));

// Start Alpine
window.Alpine = Alpine;
Alpine.start();
