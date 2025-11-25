<footer class="footer">
    <div class="container">
        <!-- Newsletter -->
        <div class="newsletter">
            <div class="newsletter-content">
                <h3>Fique por dentro das novidades!</h3>
                <p>Receba notificações sobre novos animais disponíveis para adoção e dicas de cuidados.</p>
                <form class="newsletter-form" id="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Seu melhor e-mail" required>
                    <button type="submit" class="newsletter-btn">Inscrever-se</button>
                </form>
            </div>
        </div>

        <div class="footer-grid">
            <!-- Brand Column -->
            <div class="footer-col">
                <div class="footer-brand">
                    <span class="animate-float">🐾</span>
                    <span>Adote um Amigo</span>
                </div>
                <p class="footer-description">
                    Conectando animais a lares amorosos. Transformando vidas, uma patinha de cada vez.
                </p>
                <div class="social-links">
                    <a href="#" class="social-link hover-scale" aria-label="Facebook" title="Facebook">
                        <span>f</span>
                    </a>
                    <a href="#" class="social-link hover-scale" aria-label="Instagram" title="Instagram">
                        <span>📷</span>
                    </a>
                    <a href="#" class="social-link hover-scale" aria-label="Twitter" title="Twitter">
                        <span>🐦</span>
                    </a>
                </div>
            </div>

            <!-- Navigation Column -->
            <div class="footer-col">
                <h3>Navegue</h3>
                <ul class="footer-links">
                    <li><a href="index.php" class="hover-glow">Início</a></li>
                    <li><a href="adote.php" class="hover-glow">Adotar</a></li>
                    <li><a href="blog.php" class="hover-glow">Blog</a></li>
                    <li><a href="sobre.php" class="hover-glow">Sobre nós</a></li>
                    <li><a href="contato.php" class="hover-glow">Contato</a></li>
                </ul>
            </div>

            <!-- Contact Column -->
            <div class="footer-col">
                <h3>Contato</h3>
                <div class="footer-contact-info">
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon animate-float">📍</span>
                        <span>Rua Fictícia, 123<br>Cidade Exemplo, Brasil</span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon animate-float" style="animation-delay: 0.2s;">📧</span>
                        <span>contato@adoteumamigo.com</span>
                    </div>
                    <div class="footer-contact-item">
                        <span class="footer-contact-icon animate-float" style="animation-delay: 0.4s;">📞</span>
                        <span>(11) 98765-4321</span>
                    </div>
                </div>
            </div>

            <!-- Quick Links Column -->
            <div class="footer-col">
                <h3>Links Úteis</h3>
                <ul class="footer-links">
                    <li><a href="cadastrar_animal.php" class="hover-glow">Cadastrar Pet</a></li>
                    <li><a href="perfil.php" class="hover-glow">Meu Perfil</a></li>
                    <li><a href="#" class="hover-glow">Política de Privacidade</a></li>
                    <li><a href="#" class="hover-glow">Termos de Uso</a></li>
                    <li><a href="#" class="hover-glow">FAQ</a></li>
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
    <script src="newsletter.js"></script>
</footer>