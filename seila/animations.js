// ==========================================
// SISTEMA DE ANIMAÇÕES E TRANSIÇÕES
// ==========================================

class AnimationManager {
    constructor() {
        this.init();
    }

    init() {
        this.createPageTransition();
        this.initScrollReveal();
        this.initMicroInteractions();
        this.initParallax();
        this.initPageTransitions();
    }

    // Cria a tela de transição entre páginas
    createPageTransition() {
        const transitionHTML = `
            <div class="page-transition" id="page-transition">
                <div class="logo">
                    <i class="fas fa-paw"></i> Adote um Amigo
                </div>
                <div class="loading-paws">
                    <div class="paw"></div>
                    <div class="paw"></div>
                    <div class="paw"></div>
                </div>
                <div class="loading-text">Carregando...</div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', transitionHTML);
    }

    // Mostra a tela de transição
    showPageTransition() {
        const transition = document.getElementById('page-transition');
        if (transition) {
            transition.classList.add('active');
        }
    }

    // Esconde a tela de transição
    hidePageTransition() {
        const transition = document.getElementById('page-transition');
        if (transition) {
            transition.classList.remove('active');
        }
    }

    // Inicializa scroll reveal
    initScrollReveal() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        // Observa elementos com classe reveal
        document.querySelectorAll('.reveal').forEach(el => {
            observer.observe(el);
        });

        // Adiciona animações escalonadas aos cards
        document.querySelectorAll('.animal-card').forEach((card, index) => {
            card.style.animationDelay = `${index * 0.1}s`;
        });
    }

    // Inicializa micro-interações
    initMicroInteractions() {
        // Botões com efeito ripple
        document.querySelectorAll('.btn-micro').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = this.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;
                
                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple');
                
                this.appendChild(ripple);
                
                setTimeout(() => {
                    ripple.remove();
                }, 600);
            });
        });

        // Hover effects para cards
        document.querySelectorAll('.animal-card, .related-card').forEach(card => {
            card.classList.add('hover-lift');
        });

        // Efeito de glow para elementos importantes
        document.querySelectorAll('.btn-register, .logo').forEach(el => {
            el.classList.add('hover-glow');
        });
    }

    // Inicializa parallax
    initParallax() {
        const parallaxElements = document.querySelectorAll('.parallax-element');
        
        if (parallaxElements.length > 0) {
            window.addEventListener('scroll', () => {
                const scrolled = window.pageYOffset;
                const rate = scrolled * -0.5;
                
                parallaxElements.forEach(el => {
                    el.style.transform = `translateY(${rate}px)`;
                });
            });
        }
    }

    // Inicializa transições entre páginas
    initPageTransitions() {
        // Intercepta cliques em links internos
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a[href]');
            if (link && this.isInternalLink(link.href)) {
                e.preventDefault();
                this.navigateToPage(link.href);
            }
        });

        // Intercepta navegação do browser
        window.addEventListener('beforeunload', () => {
            this.showPageTransition();
        });
    }

    // Verifica se é link interno
    isInternalLink(href) {
        try {
            const url = new URL(href, window.location.origin);
            return url.origin === window.location.origin;
        } catch {
            return false;
        }
    }

    // Navega para uma página com transição
    navigateToPage(url) {
        this.showPageTransition();
        
        setTimeout(() => {
            window.location.href = url;
        }, 800);
    }

    // Anima elementos quando entram na tela
    animateOnScroll() {
        const elements = document.querySelectorAll('.animate-on-scroll');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        });

        elements.forEach(el => observer.observe(el));
    }

    // Adiciona loading state a formulários
    addLoadingState(form) {
        const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (submitBtn) {
            const originalText = submitBtn.textContent;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
            submitBtn.disabled = true;
            
            return () => {
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
            };
        }
    }

    // Cria notificação toast
    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <div class="toast-content">
                <i class="fas fa-${this.getToastIcon(type)}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Anima entrada
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Remove após 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            error: 'exclamation-circle',
            warning: 'exclamation-triangle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    }
}

// CSS para toast notifications
const toastStyles = `
<style>
.toast {
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--card-background);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem;
    box-shadow: var(--shadow-lg);
    z-index: 10000;
    transform: translateX(100%);
    transition: transform 0.3s ease;
}

.toast.show {
    transform: translateX(0);
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--white);
}

.toast-success { border-left: 4px solid #10b981; }
.toast-error { border-left: 4px solid #ef4444; }
.toast-warning { border-left: 4px solid #f59e0b; }
.toast-info { border-left: 4px solid var(--primary-accent); }

.ripple {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transform: scale(0);
    animation: ripple-animation 0.6s linear;
    pointer-events: none;
}

@keyframes ripple-animation {
    to {
        transform: scale(4);
        opacity: 0;
    }
}
</style>
`;

// Adiciona estilos do toast ao head
document.head.insertAdjacentHTML('beforeend', toastStyles);

// Inicializa o gerenciador de animações quando o DOM carrega
document.addEventListener('DOMContentLoaded', () => {
    window.animationManager = new AnimationManager();
});

// Esconde a tela de transição quando a página carrega
window.addEventListener('load', () => {
    setTimeout(() => {
        if (window.animationManager) {
            window.animationManager.hidePageTransition();
        }
    }, 500);
});
