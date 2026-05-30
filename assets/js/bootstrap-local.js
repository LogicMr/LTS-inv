/* Bootstrap 5.3.0 - Local JavaScript Fallback */
/* Minimal Bootstrap functionality when CDN is blocked */

// Bootstrap Modal Class
class Modal {
    constructor(element) {
        this._element = element;
        this._backdrop = null;
        this._isShown = false;
    }

    show() {
        if (this._isShown) return;
        
        this._isShown = true;
        this._element.style.display = 'block';
        this._element.classList.add('show');
        this._createBackdrop();
        this._showBackdrop();
    }

    hide() {
        if (!this._isShown) return;
        
        this._isShown = false;
        this._element.classList.remove('show');
        this._hideBackdrop();
        
        setTimeout(() => {
            this._element.style.display = 'none';
        }, 150);
    }

    _createBackdrop() {
        if (this._backdrop) return;
        
        this._backdrop = document.createElement('div');
        this._backdrop.className = 'modal-backdrop fade';
        document.body.appendChild(this._backdrop);
    }

    _showBackdrop() {
        if (this._backdrop) {
            setTimeout(() => {
                this._backdrop.classList.add('show');
            }, 10);
        }
    }

    _hideBackdrop() {
        if (this._backdrop) {
            this._backdrop.classList.remove('show');
            setTimeout(() => {
                if (this._backdrop && this._backdrop.parentNode) {
                    this._backdrop.parentNode.removeChild(this._backdrop);
                    this._backdrop = null;
                }
            }, 150);
        }
    }
}

// Global Bootstrap object
window.bootstrap = {
    Modal: Modal
};

// Auto-initialize modals
document.addEventListener('DOMContentLoaded', function() {
    // Find all modal triggers
    const modalTriggers = document.querySelectorAll('[data-bs-toggle="modal"]');
    
    modalTriggers.forEach(trigger => {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-bs-target');
            const targetModal = document.querySelector(targetId);
            
            if (targetModal) {
                const modal = new window.bootstrap.Modal(targetModal);
                modal.show();
            }
        });
    });

    // Handle close buttons
    const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');
    closeButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const modal = this.closest('.modal');
            if (modal) {
                const modalInstance = new window.bootstrap.Modal(modal);
                modalInstance.hide();
            }
        });
    });

    // Handle backdrop clicks
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal-backdrop')) {
            const modals = document.querySelectorAll('.modal.show');
            modals.forEach(modal => {
                const modalInstance = new window.bootstrap.Modal(modal);
                modalInstance.hide();
            });
        }
    });
});

console.log('Local Bootstrap fallback loaded');
