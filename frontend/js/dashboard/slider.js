// ===============================
// SMOOTH CONTROL PANEL SLIDER WITH APPLE-LIKE ANIMATION
// ===============================

export class ControlPanelSlider {
    constructor() {
        this.slider = document.getElementById('slider');
        this.controlPanel = document.getElementById('controlPanel');
        this.isDragging = false;
        this.startY = 0;
        this.startHeight = 0;
        this.maxHeight = 0;
        this.snapThreshold = 0.3; // 30% threshold untuk snap ke 70%

        this.init();
    }

    init() {
        // Set initial height dan style
        this.controlPanel.style.height = 'auto';
        this.maxHeight = window.innerHeight * 0.7;

        // Add Apple-like blur effect
        this.controlPanel.style.backdropFilter = 'blur(20px) saturate(180%)';
        this.controlPanel.style.backgroundColor = 'rgba(255, 255, 255, 0.8)';
        this.controlPanel.style.border = '1px solid rgba(255, 255, 255, 0.2)';

        // Add event listeners
        this.slider.addEventListener('mousedown', this.startDrag.bind(this));
        this.slider.addEventListener('touchstart', this.startDrag.bind(this));

        document.addEventListener('mousemove', this.drag.bind(this));
        document.addEventListener('touchmove', this.drag.bind(this));

        document.addEventListener('mouseup', this.stopDrag.bind(this));
        document.addEventListener('touchend', this.stopDrag.bind(this));

        // Handle window resize
        window.addEventListener('resize', this.handleResize.bind(this));

        // console.log('🎯 Control Panel Slider initialized with Apple-like animation');
    }

    startDrag(e) {
        e.preventDefault();
        this.isDragging = true;

        // Get initial position
        const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;
        this.startY = clientY;
        this.startHeight = this.controlPanel.offsetHeight;

        // Add dragging styles dengan animasi halus
        this.slider.style.cursor = 'grabbing';
        this.controlPanel.style.transition = 'none';
        this.controlPanel.style.willChange = 'height';
        document.body.style.userSelect = 'none';
        document.body.style.cursor = 'grabbing';

        // Add haptic feedback untuk mobile (jika tersedia)
        if (navigator.vibrate) {
            navigator.vibrate(10);
        }
    }

    drag(e) {
        if (!this.isDragging) return;

        e.preventDefault();
        const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;
        const deltaY = this.startY - clientY;

        // Calculate new height dengan easing natural
        let newHeight = this.startHeight + deltaY;

        // Apply constraints dengan smooth clamping
        newHeight = Math.max(80, newHeight); // Minimum 80px
        newHeight = Math.min(this.maxHeight, newHeight); // Maximum 70% of screen

        // Apply new height dengan spring-like effect
        this.controlPanel.style.height = `${newHeight}px`;

        // Update visual feedback berdasarkan progress drag
        this.updateDragFeedback(newHeight);
    }

    stopDrag() {
        if (!this.isDragging) return;

        this.isDragging = false;

        // Calculate final height dan tentukan snap position
        const currentHeight = this.controlPanel.offsetHeight;
        const dragProgress = (currentHeight - 80) / (this.maxHeight - 80);

        let targetHeight;

        // Smart snapping: jika drag > 30%, snap ke 70%, else kembali ke compact
        if (dragProgress > this.snapThreshold) {
            targetHeight = this.maxHeight;
            // console.log('🎯 Snapping to expanded state (70%)');
        } else {
            targetHeight = 80;
            // console.log('🎯 Snapping to compact state');
        }

        // Apply smooth snap animation dengan easing curve Apple-like
        this.animateToHeight(targetHeight);

        // Cleanup styles
        this.slider.style.cursor = 'grab';
        document.body.style.userSelect = '';
        document.body.style.cursor = '';
        this.controlPanel.style.willChange = 'auto';

        // Haptic feedback untuk snap
        if (navigator.vibrate) {
            navigator.vibrate(5);
        }
    }

    animateToHeight(targetHeight) {
        const currentHeight = this.controlPanel.offsetHeight;
        const duration = 400; // ms - durasi medium untuk feel natural
        const startTime = performance.now();

        // Apple-like easing function (similar to cubic-bezier(0.25, 0.1, 0.25, 1))
        const easeOut = (t) => {
            return 1 - Math.pow(1 - t, 3);
        };

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easedProgress = easeOut(progress);

            const newHeight = currentHeight + (targetHeight - currentHeight) * easedProgress;
            this.controlPanel.style.height = `${newHeight}px`;
            this.controlPanel.style.transition = 'none'; // Tetap non-transition selama animasi

            // Update appearance selama animasi
            this.updatePanelAppearance(newHeight);

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                // Setelah animasi selesai, apply final styles
                this.controlPanel.style.transition = 'height 0.4s cubic-bezier(0.25, 0.1, 0.25, 1)';
                this.updatePanelAppearance(targetHeight);
            }
        };

        requestAnimationFrame(animate);
    }

    updateDragFeedback(height) {
        const progress = (height - 80) / (this.maxHeight - 80);

        // Update slider opacity berdasarkan progress
        this.slider.style.opacity = 0.6 + (progress * 0.4);

        // Smooth background color transition selama drag
        const bgOpacity = 0.7 + (progress * 0.2);
        this.controlPanel.style.backgroundColor = `rgba(255, 255, 255, ${bgOpacity})`;
    }

    updatePanelAppearance(height) {
        const progress = (height - 80) / (this.maxHeight - 80);
        const isExpanded = progress > 0.5;

        // Smooth radius transition
        const borderRadius = isExpanded ? 24 : 12;
        this.controlPanel.style.borderRadius = `${borderRadius}px`;

        if (isExpanded) {
            // Expanded state - full width dengan animasi smooth
            this.controlPanel.style.width = '100%';
            this.controlPanel.style.bottom = '0';
            this.controlPanel.style.right = '0';
            this.controlPanel.style.left = '0';
            this.controlPanel.style.margin = '0';

            // Enhanced blur effect ketika expanded
            document.querySelector('#vesselsPanelMobile').style.marginTop = '80px';
            document.querySelector('#vesselsPanelMobile').style.filter = 'blur(10px)';
            document.querySelector('#vesselsPanelMobile').style.opacity = '0';
            document.querySelector('#vesselsPanelMobile').style.transition = '1s all !important';
            setTimeout(() => {
                document.querySelector('#vesselsPanelMobile').style.display = 'block';
                document.querySelector('#vesselsPanelMobile').style.filter = 'blur(0px)';
                document.querySelector('#vesselsPanelMobile').style.opacity = '1';
            }, 10);
            
            this.controlPanel.style.backdropFilter = 'blur(30px) saturate(200%)';

        } else {
            // Compact state - auto width
            this.controlPanel.style.width = '90%';
            this.controlPanel.style.bottom = '16px';
            this.controlPanel.style.right = '16px';
            this.controlPanel.style.left = 'auto';
            this.controlPanel.style.margin = '0';
            document.querySelector('.vesselsPanelMobile').style.display = 'none';
            document.querySelector('.vesselsPanelMobile').style.marginTop = '15px';

            // Normal blur effect
            this.controlPanel.style.backdropFilter = 'blur(20px) saturate(180%)';
        }

        // Update content layout dengan animasi smooth
        this.updateContentLayout(height, isExpanded);

        // Smooth shadow transition
        const shadowIntensity = isExpanded ? 0.3 : 0.15;
        this.controlPanel.style.boxShadow = `0 -10px 30px rgba(0, 0, 0, ${shadowIntensity})`;
    }

    updateContentLayout(height, isExpanded) {
        const content = this.controlPanel.querySelector('.flex.items-center.space-x-4');
        if (!content) return;

        const progress = (height - 80) / (this.maxHeight - 80);

        if (isExpanded && window.innerWidth < 1024) {
            // Smooth transition ke vertical layout
            content.style.flexDirection = 'column';
            content.style.alignItems = 'stretch';
            content.style.gap = `${12 + (progress * 8)}px`; // Gap bertambah secara smooth
            content.style.width = '100%';
            content.style.padding = `${8 + (progress * 8)}px 0`; // Padding bertambah smooth

            // Smooth button expansion
            const button = content.querySelector('#toggleMovement');
            const coords = content.querySelector('.coordinates');

            if (button) {
                button.style.width = '100%';
                button.style.justifyContent = 'center';
                button.style.padding = `${10 + (progress * 4)}px ${16 + (progress * 8)}px`;
                button.style.fontSize = `${14 + (progress * 2)}px`;
            }

            if (coords) {
                coords.style.width = '100%';
                coords.style.textAlign = 'center';
                coords.style.padding = `${8 + (progress * 4)}px ${12 + (progress * 8)}px`;
                coords.style.fontSize = `${12 + (progress * 2)}px`;
            }

        } else {
            // Smooth transition ke horizontal layout
            content.style.flexDirection = 'row';
            content.style.alignItems = 'center';
            content.style.gap = `${8 + (progress * 8)}px`;
            content.style.width = 'auto';
            content.style.padding = '0';

            // Reset button styles dengan animasi
            const button = content.querySelector('#toggleMovement');
            const coords = content.querySelector('.coordinates');

            if (button) {
                button.style.width = 'auto';
                button.style.justifyContent = 'flex-start';
                button.style.padding = '8px 16px';
                button.style.fontSize = '14px';
            }

            if (coords) {
                coords.style.width = 'auto';
                coords.style.textAlign = 'left';
                coords.style.padding = '6px 12px';
                coords.style.fontSize = '12px';
            }
        }
    }

    handleResize() {
        this.maxHeight = window.innerHeight * 0.7;

        // Reset to appropriate height based on current state
        if (!this.isDragging) {
            const currentHeight = this.controlPanel.offsetHeight;
            const isExpanded = currentHeight > this.maxHeight * 0.5;

            if (isExpanded) {
                this.controlPanel.style.height = `${this.maxHeight}px`;
            } else {
                this.controlPanel.style.height = 'auto';
            }

            this.updatePanelAppearance(this.controlPanel.offsetHeight);
        }
    }

    // Quick toggle method dengan animasi smooth
    toggle() {
        const currentHeight = this.controlPanel.offsetHeight;
        const isCompact = currentHeight <= 150;

        if (isCompact) {
            this.animateToHeight(this.maxHeight);
        } else {
            this.animateToHeight(80);
        }
    }
}

// Enhanced CSS untuk Apple-like design
const sliderStyles = document.createElement('style');
sliderStyles.textContent = `
    
    
    #controlPanel {
        transition: all 0.4s cubic-bezier(0.25, 0.1, 0.25, 1);
        resize: none;
        min-height: 80px;
        backdrop-filter: blur(20px) saturate(180%);
        background: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.15);
        position: fixed !important;
        z-index: 30;
    }
    
    /* Mobile optimizations dengan animasi smooth */
    @media (max-width: 768px) {
        #controlPanel {
            right: 16px;
            left: auto;
            bottom: 16px;
            width: 90%;
            border-radius: 16px;
        }
        
        #controlPanel.expanded {
            width: 100% !important;
            right: 0 !important;
            left: 0 !important;
            bottom: 0 !important;
            border-radius: 24px 24px 0 0 !important;
        }
    }
    
    /* Hide slider on desktop */
    @media (min-width: 1025px) {
        #slider {
            display: none;
        }
        
        #controlPanel {
            backdrop-filter: none;
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.1);
        }
    }
    
    /* Smooth transitions untuk semua elemen */
    #controlPanel * {
        transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1);
    }
    
    /* Enhanced button styles */
    #toggleMovement {
        transition: all 0.3s cubic-bezier(0.25, 0.1, 0.25, 1) !important;
    }
    
    #toggleMovement:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 113, 227, 0.3);
    }
`;
document.head.appendChild(sliderStyles);