class ProfileCompletionManager {
    constructor(config) {
        this.form = document.querySelector(config.elements.form);
        this.progressBar = document.querySelector(config.elements.progressBar);
        this.updateUrl = config.updateUrl;
        this.requiredFields = [
            'artist_name',
            'genre',
            'bio',
            'profile_image',
            'cover_image'
        ];
        
        this.init();
    }

    init() {
        this.calculateCompletion();
        this.attachEventListeners();
    }

    calculateCompletion() {
        const completedFields = this.requiredFields.filter(field => {
            const element = this.form.querySelector(`[name="${field}"]`);
            return element && element.value;
        });

        const percentage = (completedFields.length / this.requiredFields.length) * 100;
        this.updateProgressBar(percentage);
    }

    updateProgressBar(percentage) {
        this.progressBar.style.width = `${percentage}%`;
        this.progressBar.setAttribute('aria-valuenow', percentage);
        this.progressBar.textContent = `${Math.round(percentage)}%`;
    }

    attachEventListeners() {
        this.form.addEventListener('change', () => this.calculateCompletion());
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
    }

    async handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this.form);
        
        try {
            const response = await fetch(this.updateUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            if (!response.ok) throw new Error('Profile update failed');

            const data = await response.json();
            this.showNotification('Profile updated successfully', 'success');
            
        } catch (error) {
            this.showNotification(error.message, 'error');
        }
    }

    showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} fixed top-4 right-4 z-50`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 3000);
    }
}
