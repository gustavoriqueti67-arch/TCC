<!-- Cabeçalho e Navegação Melhorados -->
<style>
    /* Estilos específicos para botões da navbar */
    .nav-buttons .btn {
        padding: 0.75rem 1.75rem;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .nav-buttons .btn-login {
        background: rgba(255, 255, 255, 0.08);
        color: #e8e8f0;
        border: 1.5px solid rgba(167, 139, 250, 0.3);
        backdrop-filter: blur(10px);
    }

    .nav-buttons .btn-login:hover {
        background: rgba(167, 139, 250, 0.15);
        border-color: #a78bfa;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(167, 139, 250, 0.3);
    }

    .nav-buttons .btn-login img {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        margin-right: 0.5rem;
        border: 2px solid #a78bfa;
        object-fit: cover;
    }

    .nav-buttons .btn-register {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: white;
        border: none;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        font-weight: 700;
    }

    .nav-buttons .btn-register:hover {
        background: linear-gradient(135deg, #8b5cf6, #a78bfa);
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
    }

    /* Botão de cadastrar pet */
    .btn-cadastrar-pet {
        background: linear-gradient(135deg, #10b981, #059669) !important;
        border: none !important;
        padding: 0.75rem 1.5rem !important;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-cadastrar-pet:hover {
        background: linear-gradient(135deg, #059669, #047857) !important;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
    }

    .btn-cadastrar-pet i {
        font-size: 1rem;
    }

    /* Container dos botões do usuário */
    .user-buttons-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    /* Mobile menu button melhorado */
    .mobile-menu-button {
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        border: none;
        color: white;
        font-size: 1.35rem;
        cursor: pointer;
        padding: 0.75rem;
        border-radius: 10px;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
    }

    .mobile-menu-button:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
    }

    /* Menu Mobile Container */
    #mobile-menu-container {
        position: fixed;
        top: 70px;
        right: 16px;
        background: rgba(26, 26, 26, 0.98);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
        border: 1px solid var(--border-color);
        z-index: 99;
        min-width: 280px;
        max-width: 90vw;
        display: none;
        flex-direction: column;
        gap: 1rem;
        animation: slideDown 0.3s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    #mobile-menu-container.active {
        display: flex;
    }

    #mobile-menu-container .nav-links {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        list-style: none;
        margin: 0;
        padding: 0;
        width: 100%;
    }

    #mobile-menu-container .nav-links li {
        width: 100%;
    }

    #mobile-menu-container .nav-links a {
        display: block;
        padding: 0.75rem 1rem;
        color: var(--text-color);
        border-radius: 8px;
        transition: all 0.3s ease;
        width: 100%;
    }

    #mobile-menu-container .nav-links a:hover,
    #mobile-menu-container .nav-links a.active {
        background: rgba(0, 170, 255, 0.1);
        color: var(--primary-accent);
    }

    #mobile-menu-container .nav-buttons {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
        margin-top: 0.5rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border-color);
    }

    #mobile-menu-container .nav-buttons .btn {
        width: 100%;
        justify-content: center;
        padding: 0.75rem 1rem;
    }

    #mobile-menu-container .user-buttons-container {
        flex-direction: column;
        gap: 0.75rem;
        width: 100%;
    }

    /* Responsividade aprimorada */
    @media (max-width: 768px) {
        .nav-buttons .btn {
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
        }

        .btn-cadastrar-pet {
            padding: 0.625rem 1.25rem !important;
            font-size: 0.9rem;
        }

        .user-buttons-container {
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
        }

        .user-buttons-container .btn {
            width: 100%;
            justify-content: center;
        }

        .mobile-menu-button {
            padding: 0.625rem;
            font-size: 1.25rem;
        }
    }

    @media (max-width: 480px) {
        #mobile-menu-container {
            top: 65px;
            right: 8px;
            left: 8px;
            min-width: auto;
            max-width: calc(100vw - 16px);
        }
    }
</style>

<header class="header">
    <nav class="container navbar">
        <a href="index.php" class="logo reveal animate-fade-in-left">
            <i class="fas fa-paw animate-float"></i> Adote um Amigo
        </a>

        <!-- Links Principais -->
        <ul class="nav-links reveal">
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Início</a></li>
            <li><a href="adote.php" <?php echo basename($_SERVER['PHP_SELF']) == 'adote.php' ? 'class="active"' : ''; ?>>Adote</a></li>
            <li><a href="blog.php" <?php echo basename($_SERVER['PHP_SELF']) == 'blog.php' ? 'class="active"' : ''; ?>>Blog</a></li>
            <li><a href="sobre.php" <?php echo basename($_SERVER['PHP_SELF']) == 'sobre.php' ? 'class="active"' : ''; ?>>Sobre Nós</a></li>
            <li><a href="contato.php" <?php echo basename($_SERVER['PHP_SELF']) == 'contato.php' ? 'class="active"' : ''; ?>>Contato</a></li>
        </ul>

        <!-- Botões de Ação -->
        <div class="nav-buttons reveal">
            <?php if (is_logged_in()): ?>
                <!-- Utilizador Autenticado -->
                <a href="cadastrar_animal.php" class="btn btn-cadastrar-pet">
                    <i class="fas fa-plus-circle"></i>
                    <span>Cadastrar Pet</span>
                </a>
                <div class="user-buttons-container">
                    <?php
                        $photo = isset($_SESSION['user_photo']) ? (string)$_SESSION['user_photo'] : '';
                        $isValidPhoto = preg_match('/^[A-Za-z0-9_.-]+$/', $photo) === 1 && $photo !== '' && file_exists(__DIR__ . '/perfil_foto/' . $photo);
                        $photoSrc = $isValidPhoto ? ('perfil_foto/' . htmlspecialchars($photo)) : 'perfil_foto/default-avatar.png'; // Caminho para foto padrão

                        // Pega apenas o primeiro nome
                        $userNameCompleto = isset($_SESSION['user_name']) ? (string)$_SESSION['user_name'] : 'Usuário';
                        $partesNome = explode(' ', trim($userNameCompleto));
                        $primeiroNome = htmlspecialchars($partesNome[0]);
                    ?>
                    <a href="perfil.php" class="btn btn-login">
                        <img src="<?php echo $photoSrc; ?>" alt="Foto de Perfil" loading="lazy" width="28" height="28">
                        <span><?php echo $primeiroNome; // Exibe apenas o primeiro nome ?></span>
                    </a>
                    <a href="logout.php" class="btn btn-register">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span>
                    </a>
                </div>
            <?php else: ?>
                <!-- Visitante -->
                <a href="login.php" class="btn btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Login</span>
                </a>
                <a href="register.php" class="btn btn-register">
                    <i class="fas fa-user-plus"></i>
                    <span>Cadastro</span>
                </a>
            <?php endif; ?>
        </div>

        <!-- Botão Mobile -->
        <button class="mobile-menu-button" aria-label="Abrir menu" id="mobile-menu-toggle">
            <i class="fas fa-bars" id="menu-icon"></i>
        </button>
    </nav>
</header>

<!-- Menu Mobile Container -->
<div id="mobile-menu-container"></div>

<script>
(function() {
    'use strict';
    
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const mobileMenuContainer = document.getElementById('mobile-menu-container');
    const navLinks = document.querySelector('.nav-links');
    const navButtons = document.querySelector('.nav-buttons');
    const menuIcon = document.getElementById('menu-icon');
    
    if (!mobileMenuToggle || !mobileMenuContainer) return;
    
    // Função para criar o menu mobile
    function createMobileMenu() {
        if (!navLinks || !navButtons) return;
        
        // Limpa o container
        mobileMenuContainer.innerHTML = '';
        
        // Clona os links
        const linksClone = navLinks.cloneNode(true);
        linksClone.classList.add('nav-links-mobile');
        
        // Clona os botões
        const buttonsClone = navButtons.cloneNode(true);
        buttonsClone.classList.add('nav-buttons-mobile');
        
        // Adiciona ao container
        mobileMenuContainer.appendChild(linksClone);
        mobileMenuContainer.appendChild(buttonsClone);
        
        // Adiciona event listeners aos links para fechar o menu
        linksClone.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
        
        // Adiciona event listeners aos botões
        buttonsClone.querySelectorAll('a, button').forEach(btn => {
            btn.addEventListener('click', function() {
                closeMobileMenu();
            });
        });
    }
    
    // Função para abrir o menu
    function openMobileMenu() {
        if (window.innerWidth > 768) return; // Não abre em desktop
        
        createMobileMenu();
        mobileMenuContainer.classList.add('active');
        menuIcon.classList.remove('fa-bars');
        menuIcon.classList.add('fa-times');
        document.body.style.overflow = 'hidden'; // Previne scroll do body
    }
    
    // Função para fechar o menu
    function closeMobileMenu() {
        mobileMenuContainer.classList.remove('active');
        menuIcon.classList.remove('fa-times');
        menuIcon.classList.add('fa-bars');
        document.body.style.overflow = ''; // Restaura scroll do body
    }
    
    // Toggle do menu
    mobileMenuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        if (mobileMenuContainer.classList.contains('active')) {
            closeMobileMenu();
        } else {
            openMobileMenu();
        }
    });
    
    // Fechar ao clicar fora
    document.addEventListener('click', function(e) {
        if (mobileMenuContainer.classList.contains('active')) {
            if (!mobileMenuContainer.contains(e.target) && !mobileMenuToggle.contains(e.target)) {
                closeMobileMenu();
            }
        }
    });
    
    // Fechar ao pressionar ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && mobileMenuContainer.classList.contains('active')) {
            closeMobileMenu();
        }
    });
    
    // Ajustar ao redimensionar a janela
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
    
    // Animação reveal
    (function(){
        var observer = new IntersectionObserver(function(entries){
            entries.forEach(function(entry){
                if(entry.isIntersecting){ 
                    entry.target.classList.add('is-visible'); 
                }
            });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(function(el){ 
            observer.observe(el); 
        });
    })();
})();
</script>

