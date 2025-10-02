<!-- Cabeçalho e Navegação -->
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
                <a href="cadastrar_animal.php" class="btn btn-login">
                    <i class="fas fa-plus" style="margin-right: 8px;"></i> Cadastrar Pet
                </a>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <a href="perfil.php" class="btn btn-login">
                         <img src="perfil_foto/<?php echo htmlspecialchars($_SESSION['user_photo']); ?>" alt="Foto de Perfil" style="width: 24px; height: 24px; border-radius: 50%; margin-right: 8px;">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </a>
                    <a href="logout.php" class="btn btn-register">Sair</a>
                </div>
            <?php else: ?>
                <!-- Visitante -->
                <a href="login.php" class="btn btn-login">Login</a>
                <a href="register.php" class="btn btn-register">Cadastro</a>
            <?php endif; ?>
        </div>
        
        <!-- Botão Mobile -->
        <button class="mobile-menu-button">
            <i class="fas fa-bars"></i>
        </button>
    </nav>
</header>

