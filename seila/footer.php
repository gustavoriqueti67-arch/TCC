<style>
    .footer {
        background: linear-gradient(180deg, rgba(15, 15, 15, 0.95) 0%, rgba(10, 10, 10, 0.98) 100%);
        border-top: 1px solid rgba(0, 196, 154, 0.2);
        padding: 4rem 0 1.5rem;
        margin-top: 5rem;
        position: relative;
    }

    .footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, var(--primary-accent), transparent);
    }

    .footer-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .footer-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 3rem;
        margin-bottom: 3rem;
    }

    .footer-col {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .footer-col h3 {
        color: var(--white);
        font-size: 1.3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-col h3::after {
        content: '';
        flex: 1;
        height: 2px;
        background: linear-gradient(90deg, var(--primary-accent), transparent);
        margin-left: 1rem;
        max-width: 60px;
    }

    .footer-brand {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 1.4rem;
        color: var(--primary-accent);
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .footer-description {
        color: rgba(255, 255, 255, 0.6);
        line-height: 1.6;
        font-size: 0.95rem;
    }

    .footer-links {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .footer-links li a {
        color: rgba(255, 255, 255, 0.7);
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.95rem;
    }

    .footer-links li a::before {
        content: '→';
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
        color: var(--primary-accent);
    }

    .footer-links li a:hover {
        color: var(--primary-accent);
        transform: translateX(5px);
    }

    .footer-links li a:hover::before {
        opacity: 1;
        transform: translateX(0);
    }

    .footer-contact-info {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .footer-contact-item {
        color: rgba(255, 255, 255, 0.7);
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        font-size: 0.95rem;
        line-height: 1.5;
    }

    .footer-contact-icon {
        font-size: 1.2rem;
        color: var(--primary-accent);
        flex-shrink: 0;
        margin-top: 0.1rem;
    }

    .social-links {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
    }

    .social-link {
        width: 45px;
        height: 45px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255, 255, 255, 0.7);
        font-size: 1.3rem;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .social-link::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, var(--primary-accent), #00a67e);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .social-link:hover {
        transform: translateY(-5px);
        border-color: var(--primary-accent);
        box-shadow: 0 8px 20px rgba(0, 196, 154, 0.3);
    }

    .social-link:hover::before {
        opacity: 1;
    }

    .social-link span {
        position: relative;
        z-index: 1;
    }

    .social-link:hover span {
        color: var(--dark-bg);
    }

    .footer-bottom {
        padding-top: 2rem;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-copyright {
        color: rgba(255, 255, 255, 0.5);
        font-size: 0.9rem;
    }

    .footer-credits {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-credits .heart {
        color: #e74c3c;
        animation: heartbeat 1.5s ease-in-out infinite;
    }

    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.1); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .footer {
            padding: 3rem 0 1.5rem;
        }

        .footer-container {
            padding: 0 1rem;
        }

        .footer-grid {
            grid-template-columns: 1fr;
            gap: 2.5rem;
        }

        .footer-bottom {
            flex-direction: column;
            text-align: center;
        }
    }
</style>

<footer class="footer">
    <div class="footer-container">
        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col">
                <div class="footer-brand">
                    <span>🐾</span>
                    <span>Adote um Amigo</span>
                </div>
                <p class="footer-description">
                    Conectando animais a lares amorosos. Transformando vidas, uma patinha de cada vez.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Facebook" title="Facebook">
                        <span>f</span>
                    </a>
                    <a href="#" class="social-link" aria-label="Instagram" title="Instagram">
                        <span>📷</span>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter" title="Twitter">
                        <span>🐦</span>
                    </a>
                </div>
            </div>

            <!-- Navigation Column -->
            <div class="footer-col">
                <h3>Navegue</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Início</a></li>
                    <li><a href="adote.php">Adote</a></li>
                    <li><a href="blog.php">Blog</a></li>
                    <li><a href="sobre.php">Sobre Nós</a></li>
                    <li><a href="contacto.php">Contacto</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="footer-col">
                <h3>Contato</h3>
                <div class="footer-contact-info">
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📍</span>
                        <span>Rua Fictícia, 123<br>Cidade Exemplo, Brasil</span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📧</span>
                        <span>contato@adoteumamigo.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon">📞</span>
                        <span>(11) 98765-4321</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="footer-col">
                <h3>Links Úteis</h3>
                <ul class="footer-links">
                    <li><a href="cadastrar_animal.php">Cadastrar Pet</a></li>
                    <li><a href="perfil.php">Meu Perfil</a></li>
                    <li><a href="#">Política de Privacidade</a></li>
                    <li><a href="#">Termos de Uso</a></li>
                    <li><a href="#">FAQ</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <p class="footer-copyright">
                © <?php echo date('Y'); ?> Adote um Amigo. Todos os direitos reservados.
            </p>
            <p class="footer-credits">
                Feito com <span class="heart">❤️</span> para ajudar animais
            </p>
        </div>
    </div>
</footer>