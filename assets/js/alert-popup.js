// Custom Alert Popup System
class AlertPopup {
    constructor() {
        this.createAlertModal();
    }

    createAlertModal() {
        // Remove existing modal if any
        const existingModal = document.getElementById('alertModal');
        if (existingModal) {
            existingModal.remove();
        }

        // Create modal HTML
        const modalHTML = `
        <div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0" id="alertModalHeader">
                        <h5 class="modal-title d-flex align-items-center" id="alertModalLabel">
                            <i id="alertIcon" class="me-2"></i>
                            <span id="alertTitle">แจ้งเตือน</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="alertModalBody">
                        <p id="alertMessage" class="mb-0"></p>
                    </div>
                    <div class="modal-footer border-0" id="alertModalFooter">
                        <button type="button" class="btn" id="alertOkButton" data-bs-dismiss="modal">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>`;

        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
    }

    show(type, title, message, callback = null) {
        const modal = document.getElementById('alertModal');
        const header = document.getElementById('alertModalHeader');
        const icon = document.getElementById('alertIcon');
        const titleElement = document.getElementById('alertTitle');
        const messageElement = document.getElementById('alertMessage');
        const okButton = document.getElementById('alertOkButton');

        // Reset classes
        header.className = 'modal-header border-0';
        icon.className = 'me-2';
        okButton.className = 'btn';

        // Set content based on type
        switch (type) {
            case 'success':
                header.classList.add('bg-success', 'text-white');
                icon.classList.add('fa-solid', 'fa-check-circle');
                okButton.classList.add('btn-success');
                titleElement.textContent = title || 'สำเร็จ';
                break;
            case 'error':
                header.classList.add('bg-danger', 'text-white');
                icon.classList.add('fa-solid', 'fa-exclamation-triangle');
                okButton.classList.add('btn-danger');
                titleElement.textContent = title || 'เกิดข้อผิดพลาด';
                break;
            case 'warning':
                header.classList.add('bg-warning', 'text-dark');
                icon.classList.add('fa-solid', 'fa-exclamation-circle');
                okButton.classList.add('btn-warning');
                titleElement.textContent = title || 'คำเตือน';
                break;
            case 'info':
                header.classList.add('bg-info', 'text-white');
                icon.classList.add('fa-solid', 'fa-info-circle');
                okButton.classList.add('btn-info');
                titleElement.textContent = title || 'ข้อมูล';
                break;
            default:
                header.classList.add('bg-primary', 'text-white');
                icon.classList.add('fa-solid', 'fa-bell');
                okButton.classList.add('btn-primary');
                titleElement.textContent = title || 'แจ้งเตือน';
        }

        messageElement.textContent = message;

        // Handle callback
        if (callback) {
            okButton.onclick = () => {
                callback();
                bootstrap.Modal.getInstance(modal).hide();
            };
        } else {
            okButton.onclick = null;
        }

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    success(message, title = null, callback = null) {
        this.show('success', title, message, callback);
    }

    error(message, title = null, callback = null) {
        this.show('error', title, message, callback);
    }

    warning(message, title = null, callback = null) {
        this.show('warning', title, message, callback);
    }

    info(message, title = null, callback = null) {
        this.show('info', title, message, callback);
    }

    confirm(message, title = 'ยืนยัน', onConfirm = null, onCancel = null) {
        // Remove existing confirm modal if any
        const existingModal = document.getElementById('confirmModal');
        if (existingModal) {
            existingModal.remove();
        }

        const confirmModalHTML = `
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header border-0 bg-warning text-dark">
                        <h5 class="modal-title d-flex align-items-center" id="confirmModalLabel">
                            <i class="fa-solid fa-question-circle me-2"></i>
                            <span>${title}</span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">${message}</p>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" id="confirmCancelButton" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="button" class="btn btn-warning" id="confirmOkButton">ยืนยัน</button>
                    </div>
                </div>
            </div>
        </div>`;

        document.body.insertAdjacentHTML('beforeend', confirmModalHTML);

        const modal = document.getElementById('confirmModal');
        const okButton = document.getElementById('confirmOkButton');
        const cancelButton = document.getElementById('confirmCancelButton');

        okButton.onclick = () => {
            if (onConfirm) onConfirm();
            bootstrap.Modal.getInstance(modal).hide();
        };

        cancelButton.onclick = () => {
            if (onCancel) onCancel();
        };

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Clean up after modal is hidden
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }
}

// Initialize global alert instance
const Alert = new AlertPopup();

// Override default alert function
window.alert = function(message) {
    Alert.info(message);
};

// Override default confirm function
window.confirm = function(message) {
    return new Promise((resolve) => {
        Alert.confirm(message, 'ยืนยัน', 
            () => resolve(true), 
            () => resolve(false)
        );
    });
};

// Add custom methods to window for easy access
window.showSuccess = (message, title, callback) => Alert.success(message, title, callback);
window.showError = (message, title, callback) => Alert.error(message, title, callback);
window.showWarning = (message, title, callback) => Alert.warning(message, title, callback);
window.showInfo = (message, title, callback) => Alert.info(message, title, callback);
window.showConfirm = (message, title, onConfirm, onCancel) => Alert.confirm(message, title, onConfirm, onCancel);