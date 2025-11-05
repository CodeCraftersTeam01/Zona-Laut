export class ToastSystem {
            constructor() {
                this.container = document.getElementById('toastContainer');
                this.toasts = new Set();
            }

            show(type, title, message, duration = 5000) {
                const toast = this.createToast(type, title, message, duration);
                this.container.appendChild(toast);
                this.toasts.add(toast);

                requestAnimationFrame(() => {
                    toast.classList.add('show');
                });

                if (duration > 0) {
                    setTimeout(() => {
                        this.hide(toast);
                    }, duration);
                }

                return toast;
            }

            createToast(type, title, message, duration) {
                const toast = document.createElement('div');
                toast.className = `toast bg-white rounded-xl p-0 shadow-lg border border-[#e5e5e7] overflow-hidden ${type === 'success' ? 'border-l-4 border-l-success' : 'border-l-4 border-l-danger'}`;

                const icons = {
                    success: 'fa-check',
                    error: 'fa-exclamation-triangle'
                };

                toast.innerHTML = `
                    <div class="toast-header flex items-center justify-between p-4 bg-white/95">
                        <div class="toast-title flex items-center gap-2 font-semibold text-sm text-text-dark">
                            <div class="toast-icon w-5 h-5 flex items-center justify-center rounded-full ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white">
                                <i class="fas ${icons[type]}"></i>
                            </div>
                            ${title}
                        </div>
                        <button class="toast-close bg-none border-none text-text-light cursor-pointer p-1 rounded transition-all duration-200 hover:bg-black/5 hover:text-text-dark">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="toast-body px-5 pb-4 text-text-dark">
                        <p class="toast-message text-sm leading-relaxed text-[#515154] m-0">${message}</p>
                    </div>
                `;

                const closeBtn = toast.querySelector('.toast-close');
                closeBtn.addEventListener('click', () => this.hide(toast));

                return toast;
            }

            hide(toast) {
                if (this.toasts.has(toast)) {
                    toast.classList.remove('show');
                    toast.classList.add('hide');

                    setTimeout(() => {
                        if (toast.parentNode) {
                            toast.parentNode.removeChild(toast);
                        }
                        this.toasts.delete(toast);
                    }, 400);
                }
            }

            success(title, message, duration = 5000) {
                return this.show('success', title, message, duration);
            }

            error(title, message, duration = 5000) {
                return this.show('error', title, message, duration);
            }
        }