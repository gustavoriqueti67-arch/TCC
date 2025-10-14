<?php
require_once 'config.php';

// Apenas administradores podem aceder a esta página
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Determina qual aba está ativa
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'blog';

// Buscar posts do blog
try {
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         ORDER BY bp.data_publicacao DESC"
    );
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar posts: " . $e->getMessage());
    $posts = [];
}

// Buscar todos os animais
try {
    $stmt = $pdo->query(
        "SELECT a.*, u.nome as nome_usuario 
         FROM animais a
         JOIN usuarios u ON a.id_usuario = u.id 
         ORDER BY a.data_cadastro DESC"
    );
    $animais = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar animais: " . $e->getMessage());
    $animais = [];
}

// Buscar todos os utilizadores
try {
    $stmt = $pdo->query("SELECT id, nome, email, nivel_acesso, data_registro FROM usuarios ORDER BY nome ASC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
    $usuarios = [];
}

// Pega as mensagens da sessão
$success_message = isset($_SESSION['success_message_admin']) ? $_SESSION['success_message_admin'] : null;
unset($_SESSION['success_message_admin']);

$error_message = isset($_SESSION['error_message_admin']) ? $_SESSION['error_message_admin'] : null;
unset($_SESSION['error_message_admin']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Administração - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        .admin-page {
            min-height: calc(100vh - 76px);
            padding: 2rem 0;
        }

        .admin-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .admin-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .admin-main-title {
            font-size: 2.5rem;
            color: var(--white);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .admin-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.1rem;
        }

        /* Tabs Navigation */
        .tabs-navigation {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            background: rgba(26, 26, 26, 0.6);
            padding: 1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow-x: auto;
        }

        .tab-button {
            flex: 1;
            min-width: 150px;
            padding: 1rem 1.5rem;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            font-size: 1rem;
        }

        .tab-button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--primary-accent), #00a67e);
            color: var(--dark-bg);
            border-color: var(--primary-accent);
            box-shadow: 0 4px 16px rgba(0, 196, 154, 0.3);
        }

        .tab-icon {
            font-size: 1.3rem;
        }

        .tab-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .tab-button.active .tab-badge {
            background: rgba(0, 0, 0, 0.2);
        }

        /* Tab Content */
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .tab-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-card {
            background: var(--card-background);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
        }

        /* Section Header */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--white);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Alerts */
        .alert {
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideDown 0.3s ease;
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

        .alert-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .alert-success {
            background-color: rgba(0, 196, 154, 0.15);
            color: #d1e7dd;
            border-left: 4px solid var(--primary-accent);
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.15);
            color: #f8d7da;
            border-left: 4px solid #e74c3c;
        }

        /* Items List */
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .item {
            background: rgba(26, 26, 26, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1.5rem;
            transition: all 0.3s ease;
        }

        .item:hover {
            border-color: var(--primary-accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 16px rgba(0, 196, 154, 0.2);
        }

        .item-info {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .item-photo {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .item-details {
            flex: 1;
            min-width: 0;
        }

        .item-title {
            font-size: 1.2rem;
            color: var(--white);
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
        }

        .item-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .item-meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .badge-admin {
            background: linear-gradient(135deg, #00c49a, #00a67e);
            color: var(--dark-bg);
        }

        .badge-user {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        .item-actions {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 0.75rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            white-space: nowrap;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, #00c49a 0%, #00a67e 100%);
            color: var(--dark-bg);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 196, 154, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: var(--white);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        .btn-promote {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: var(--white);
        }

        .btn-promote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .btn-demote {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: var(--white);
        }

        .btn-demote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }

        .empty-state-text {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }

        .empty-state-subtext {
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                padding: 0 1rem;
            }

            .content-card {
                padding: 1.5rem;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .tabs-navigation {
                flex-direction: column;
            }

            .tab-button {
                width: 100%;
            }

            .item {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-info {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
            }

            .item-actions {
                width: 100%;
            }

            .btn-action {
                flex: 1;
                justify-content: center;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="admin-page">
        <div class="admin-container">
            
            <div class="admin-header">
                <h1 class="admin-main-title">⚙️ Painel de Administração</h1>
                <p class="admin-subtitle">Gerencie todo o conteúdo da plataforma</p>
            </div>

            <!-- Tabs Navigation -->
            <nav class="tabs-navigation">
                <button class="tab-button <?php echo $tab == 'blog' ? 'active' : ''; ?>" onclick="changeTab('blog')">
                    <span class="tab-icon">📝</span>
                    <span>Blog</span>
                    <span class="tab-badge"><?php echo count($posts); ?></span>
                </button>
                <button class="tab-button <?php echo $tab == 'animais' ? 'active' : ''; ?>" onclick="changeTab('animais')">
                    <span class="tab-icon">🐾</span>
                    <span>Animais</span>
                    <span class="tab-badge"><?php echo count($animais); ?></span>
                </button>
                <button class="tab-button <?php echo $tab == 'usuarios' ? 'active' : ''; ?>" onclick="changeTab('usuarios')">
                    <span class="tab-icon">👥</span>
                    <span>Utilizadores</span>
                    <span class="tab-badge"><?php echo count($usuarios); ?></span>
                </button>
            </nav>

            <!-- Alerts -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <span class="alert-icon">✓</span>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Tab: Blog -->
            <div id="tab-blog" class="tab-content <?php echo $tab == 'blog' ? 'active' : ''; ?>">
                <div class="content-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            📰 Publicações do Blog
                        </h2>
                        <a href="criar_post.php" class="btn btn-register">
                            ➕ Novo Post
                        </a>
                    </div>
                    
                    <div class="items-list">
                        <?php if (empty($posts)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">📝</div>
                                <p class="empty-state-text">Nenhuma publicação encontrada</p>
                                <p class="empty-state-subtext">Crie seu primeiro post para começar!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="item">
                                    <div class="item-info">
                                        <div class="item-details">
                                            <h3 class="item-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                            <div class="item-meta">
                                                <span class="item-meta-item">
                                                    <span>✍️</span>
                                                    <span>por <?php echo htmlspecialchars($post['nome_autor']); ?></span>
                                                </span>
                                                <?php if (isset($post['data_publicacao'])): ?>
                                                    <span class="item-meta-item">
                                                        <span>📅</span>
                                                        <span><?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn-action btn-edit">
                                            ✏️ Editar
                                        </a>
                                        <a href="excluir_post.php?id=<?php echo $post['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Tem certeza que deseja excluir esta publicação?\n\nEsta ação não pode ser desfeita.');">
                                            🗑️ Excluir
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Animais -->
            <div id="tab-animais" class="tab-content <?php echo $tab == 'animais' ? 'active' : ''; ?>">
                <div class="content-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            🐾 Todos os Animais
                        </h2>
                    </div>
                    
                    <div class="items-list">
                        <?php if (empty($animais)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon">🐾</div>
                                <p class="empty-state-text">Nenhum animal cadastrado</p>
                                <p class="empty-state-subtext">Aguardando cadastros de animais</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($animais as $animal): ?>
                                <div class="item">
                                    <div class="item-info">
                                        <?php if (!empty($animal['foto_animal'])): ?>
                                            <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" 
                                                 alt="<?php echo htmlspecialchars($animal['nome']); ?>" 
                                                 class="item-photo">
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <h3 class="item-title"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                            <div class="item-meta">
                                                <span class="item-meta-item">
                                                    <span>👤</span>
                                                    <span>por <?php echo htmlspecialchars($animal['nome_usuario']); ?></span>
                                                </span>
                                                <span class="item-meta-item">
                                                    <span>🏷️</span>
                                                    <span><?php echo htmlspecialchars($animal['especie']); ?></span>
                                                </span>
                                                <?php if (isset($animal['data_cadastro'])): ?>
                                                    <span class="item-meta-item">
                                                        <span>📅</span>
                                                        <span><?php echo date('d/m/Y', strtotime($animal['data_cadastro'])); ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn-action btn-edit">
                                            ✏️ Editar
                                        </a>
                                        <a href="excluir_animal.php?id=<?php echo $animal['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Tem certeza que deseja excluir este animal?\n\nEsta ação não pode ser desfeita.');">
                                            🗑️ Excluir
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Tab: Utilizadores -->
            <div id="tab-usuarios" class="tab-content <?php echo $tab == 'usuarios' ? 'active' : ''; ?>">
                <div class="content-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            👥 Todos os Utilizadores
                        </h2>
                    </div>
                    
                    <div class="items-list">
                        <?php foreach ($usuarios as $usuario): ?>
                            <div class="item">
                                <div class="item-info">
                                    <div class="item-details">
                                        <h3 class="item-title"><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                                        <div class="item-meta">
                                            <span class="item-meta-item">
                                                <span>📧</span>
                                                <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                                            </span>
                                            <?php if (isset($usuario['data_registro'])): ?>
                                                <span class="item-meta-item">
                                                    <span>📅</span>
                                                    <span>Registrado em <?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?></span>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <span class="user-badge <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'badge-admin' : 'badge-user'; ?>">
                                            <?php echo $usuario['nivel_acesso'] == 'admin' ? '⭐ Admin' : '👤 Utilizador'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="item-actions">
                                    <?php if ($_SESSION['user_id'] != $usuario['id']): ?>
                                        <?php if ($usuario['nivel_acesso'] == 'user'): ?>
                                            <a href="gerir_usuario.php?action=promote&id=<?php echo $usuario['id']; ?>" class="btn-action btn-promote">
                                                ⬆️ Promover
                                            </a>
                                        <?php else: ?>
                                            <a href="gerir_usuario.php?action=demote&id=<?php echo $usuario['id']; ?>" class="btn-action btn-demote">
                                                ⬇️ Rebaixar
                                            </a>
                                        <?php endif; ?>
                                        <a href="gerir_usuario.php?action=delete&id=<?php echo $usuario['id']; ?>" 
                                           class="btn-action btn-delete" 
                                           onclick="return confirm('Tem certeza que deseja excluir este utilizador?\n\nTodos os seus animais e publicações serão apagados.\nEsta ação não pode ser desfeita.');">
                                            🗑️ Excluir
                                        </a>
                                    <?php else: ?>
                                        <span style="color: rgba(255, 255, 255, 0.5); font-size: 0.9rem;">
                                            🔒 Você não pode editar seu próprio perfil aqui
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        function changeTab(tabName) {
            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Add active class to selected tab
            event.target.closest('.tab-button').classList.add('active');
            document.getElementById('tab-' + tabName).classList.add('active');

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Handle browser back/forward buttons
        window.addEventListener('popstate', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tab = urlParams.get('tab') || 'blog';
            changeTab(tab);
        });
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>