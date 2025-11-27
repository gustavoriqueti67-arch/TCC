// Newsletter Subscription Handler
document.addEventListener('DOMContentLoaded', () => {
    const newsletterForm = document.getElementById('newsletter-form');
    
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = this.querySelector('.newsletter-input');
            const submitBtn = this.querySelector('.newsletter-btn');
            const email = emailInput.value.trim();
            
            if (!email || !email.includes('@')) {
                showToast('Por favor, insira um e-mail válido', 'error');
                return;
            }
            
            // Salvar estado original
            const originalText = submitBtn.textContent;
            submitBtn.textContent = 'Inscrito! ✓';
            submitBtn.style.background = '#10b981';
            submitBtn.disabled = true;
            
            // Aqui você pode adicionar uma chamada AJAX real ao backend
            // Por enquanto, simulamos sucesso
            setTimeout(() => {
                showToast('Inscrição realizada com sucesso! 🎉', 'success');
                emailInput.value = '';
                submitBtn.textContent = originalText;
                submitBtn.style.background = '';
                submitBtn.disabled = false;
            }, 1500);
        });
    }
});

// Toast notification function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${getToastIcon(type)}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Adiciona estilos se não existirem
    if (!document.querySelector('#toast-styles')) {
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast {
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--bg-glass);
                backdrop-filter: blur(10px);
                border: 1px solid var(--border-primary);
                border-radius: 12px;
                padding: 1rem 1.5rem;
                box-shadow: var(--elevation-3);
                z-index: var(--z-toast);
                transform: translateX(120%);
                transition: transform 0.3s var(--transition-curve);
                max-width: 350px;
            }
            
            .toast.show {
                transform: translateX(0);
            }
            
            .toast-content {
                display: flex;
                align-items: center;
                gap: 0.75rem;
                color: var(--text-primary);
                font-size: var(--text-sm);
                font-weight: 500;
            }
            
            .toast i {
                font-size: var(--text-lg);
            }
            
            .toast-success {
                border-left: 4px solid #10b981;
                background: rgba(16, 185, 129, 0.1);
            }
            
            .toast-error {
                border-left: 4px solid #ef4444;
                background: rgba(239, 68, 68, 0.1);
            }
            
            .toast-warning {
                border-left: 4px solid #f59e0b;
                background: rgba(245, 158, 11, 0.1);
            }
            
            .toast-info {
                border-left: 4px solid var(--primary);
                background: rgba(0, 170, 255, 0.1);
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(toast);
    
    // Anima entrada
    setTimeout(() => toast.classList.add('show'), 100);
    
    // Remove após 4 segundos
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function getToastIcon(type) {
    const icons = {
        success: 'check-circle',
        error: 'exclamation-circle',
        warning: 'exclamation-triangle',
        info: 'info-circle'
    };
    return icons[type] || 'info-circle';
}

