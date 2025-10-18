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
        notes: ''
    },
    loading: false,
    errors: {},
    availableSlots: [],

    init() {
        // Initialize component
        console.log('Booking wizard initialized - automatic staff assignment enabled');
    },

    // Calculate minimum booking date (24 hours from now)
    minDate() {
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        return tomorrow.toISOString().split('T')[0];
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
        }

        return true;
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
