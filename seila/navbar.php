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

    /* Responsividade aprimorada */
    @media (max-width: 768px) {
        .nav-buttons .btn {
            padding: 0.625rem 1.25rem;
            font-size: 0.9rem;
        }

        .btn-cadastrar-pet {
            padding: 0.625rem 1.25rem !important;
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
    }
</style>

<header class="header">
    <nav class="container navbar">
        <a href="index.php" class="logo">
            <i class="fas fa-paw"></i> Adote um Amigo
        </a>
        
        <!-- Links Principais -->
        <ul class="nav-links">
            <li><a href="index.php">Início</a></li>
            <li><a href="adote.php">Adote</a></li>
            <li><a href="blog.php">Blog</a></li>
            <li><a href="sobre.php">Sobre Nós</a></li>
            <li><a href="contato.php">Contato</a></li>
        </ul>

        <!-- Botões de Ação -->
        <div class="nav-buttons">
            <?php if (is_logged_in()): ?>
                <!-- Utilizador Autenticado -->
                <a href="cadastrar_animal.php" class="btn btn-cadastrar-pet">
                    <i class="fas fa-plus-circle"></i>
                    <span>Cadastrar Pet</span>
                </a>
                <div class="user-buttons-container">
                    <a href="perfil.php" class="btn btn-login">
                        <img src="perfil_foto/<?php echo htmlspecialchars($_SESSION['user_photo']); ?>" alt="Foto de Perfil">
                        <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
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
        <button class="mobile-menu-button">
            <i class="fas fa-bars"></i>
        </button>
    </nav>
</header>