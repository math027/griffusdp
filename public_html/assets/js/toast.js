/* Toast Notification System */
const Toast = {
    init() {
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        Object.assign(this.container.style, {
            position: 'fixed',
            top: '20px',
            left: '20px',
            right: '20px',
            zIndex: '99999',
            display: 'flex',
            flexDirection: 'column',
            gap: '10px',
            pointerEvents: 'none'
        });
        document.body.appendChild(this.container);

        // Inject styles
        const style = document.createElement('style');
        style.textContent = `
            .toast {
                max-width: 500px;
                width: 100%;
                margin-left: auto;
                background: #fff;
                padding: 12px 16px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: flex-start;
                gap: 12px;
                animation: slideIn 0.3s ease-out forwards;
                opacity: 0;
                transform: translateX(100%);
                font-family: inherit;
                font-size: 0.9rem;
                border-left: 4px solid #333;
                pointer-events: auto;
                box-sizing: border-box;
                word-break: break-word;
            }
            .toast.success { border-left-color: #4caf50; }
            .toast.error { border-left-color: #f44336; }
            .toast.warning { border-left-color: #ff9800; }
            .toast.info { border-left-color: #2196f3; }
            
            .toast-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 2px; }
            .toast.success .toast-icon { color: #4caf50; }
            .toast.error .toast-icon { color: #f44336; }
            .toast.warning .toast-icon { color: #ff9800; }
            .toast.info .toast-icon { color: #2196f3; }

            .toast-message {
                color: #333;
                font-weight: 500;
                white-space: pre-line;
                flex: 1;
                min-width: 0;
            }

            @keyframes slideIn {
                to { opacity: 1; transform: translateX(0); }
            }
            @keyframes fadeOut {
                to { opacity: 0; transform: translateX(100%); }
            }

            @media (max-width: 360px) {
                .toast {
                    font-size: 0.8rem;
                    padding: 10px 12px;
                    gap: 8px;
                }
                .toast-icon {
                    font-size: 1rem;
                }
            }
        `;
        document.head.appendChild(style);
    },

    show(message, type = 'info', duration = 4000) {
        if (!this.container) this.init();

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;

        const icons = {
            success: '✔',
            error: '✖',
            warning: '⚠',
            info: 'ℹ'
        };

        toast.innerHTML = `
            <span class="toast-icon">${icons[type]}</span>
            <span class="toast-message">${message}</span>
        `;

        this.container.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-in forwards';
            toast.addEventListener('animationend', () => {
                toast.remove();
            });
        }, duration);
    },

    success(msg) { this.show(msg, 'success'); },
    error(msg) { this.show(msg, 'error'); },
    warning(msg) { this.show(msg, 'warning'); },
    info(msg) { this.show(msg, 'info'); }
};
