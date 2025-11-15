// Admin Authentication & Dashboard Management for Laravel

class AdminApp {
    constructor() {
        this.currentUser = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadCurrentUser();
    }

    setupEventListeners() {
        // Modal handlers
        const closeModal = document.getElementById('closeModal');
        if (closeModal) {
            closeModal.addEventListener('click', () => this.closeEventModal());
        }

        // Event form submit
        const eventForm = document.getElementById('eventForm');
        if (eventForm) {
            eventForm.addEventListener('submit', (e) => this.handleEventSubmit(e));
        }

        // Cancel button
        const cancelBtn = document.getElementById('cancelBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => this.closeEventModal());
        }

        // Close modal when clicking outside
        const modal = document.getElementById('eventModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeEventModal();
                }
            });
        }
    }

    loadCurrentUser() {
        // Get user from page data attribute or API
        const userEl = document.querySelector('[data-user]');
        if (userEl) {
            this.currentUser = JSON.parse(userEl.dataset.user);
        }
    }

    openEventModal() {
        const modal = document.getElementById('eventModal');
        if (modal) {
            modal.classList.add('active');
            document.getElementById('modalTitle').textContent = 'Tambah Event Baru';
            document.getElementById('eventForm').reset();
            this.currentEventId = null;
        }
    }

    closeEventModal() {
        const modal = document.getElementById('eventModal');
        if (modal) {
            modal.classList.remove('active');
        }
    }

    handleEventSubmit(e) {
        e.preventDefault();
        // Form submission handled by Laravel form
        e.target.submit();
    }
}

// Initialize Admin App
document.addEventListener('DOMContentLoaded', () => {
    window.adminApp = new AdminApp();
});
