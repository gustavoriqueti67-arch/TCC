<?php
require_once 'config.php';

// Apenas administradores podem aceder a esta página
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Determina qual aba está ativa
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'animais'; // Mudar padrão para 'animais'

// Buscar posts do blog (apenas para contagem ou gestão, se mantida)
try {
    $stmt_posts = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor
         FROM blog_posts bp
         JOIN usuarios u ON bp.id_autor = u.id
         ORDER BY bp.data_publicacao DESC"
    );
    $posts = $stmt_posts->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar posts: " . $e->getMessage());
    $posts = [];
}


// Buscar todos os animais (agora inclui status)
try {
    $stmt_animais = $pdo->query(
        "SELECT a.*, u.nome as nome_usuario
         FROM animais a
         JOIN usuarios u ON a.id_usuario = u.id
         ORDER BY a.data_cadastro DESC"
    );
    $animais = $stmt_animais->fetchAll();
    // Contar pendentes para o badge
    $animais_pendentes_count = count(array_filter($animais, function($a){ return $a['status'] == 'pendente'; }));
} catch (PDOException $e) {
    error_log("Erro ao buscar animais: " . $e->getMessage());
    $animais = [];
    $animais_pendentes_count = 0;
}

// --- SISTEMA DE PESQUISA DE UTILIZADORES ---
$busca_usuario = isset($_GET['busca_usuario']) ? trim($_GET['busca_usuario']) : '';
$usuarios = [];

try {
    $sql_usuarios = "SELECT id, nome, email, nivel_acesso, data_registro, foto_perfil FROM usuarios";
    $params_usuarios = [];

    if (!empty($busca_usuario)) {
        $sql_usuarios .= " WHERE nome LIKE ? OR email LIKE ?";
        $params_usuarios[] = "%$busca_usuario%";
        $params_usuarios[] = "%$busca_usuario%";
    }

    $sql_usuarios .= " ORDER BY nome ASC";

    $stmt_usuarios = $pdo->prepare($sql_usuarios);
    $stmt_usuarios->execute($params_usuarios);
    $usuarios = $stmt_usuarios->fetchAll();
} catch (PDOException $e) {
    error_log("Erro ao buscar usuários: " . $e->getMessage());
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"> <!-- Font Awesome -->
    <style>
        .admin-page {
            min-height: calc(100vh - 76px); /* Altura mínima considerando navbar */
            padding: 2rem 0; /* Espaçamento vertical */
            background-color: var(--dark-bg); /* Fundo escuro */
        }

        .admin-container {
            max-width: 1400px; /* Largura máxima */
            margin: 0 auto; /* Centralizar */
            padding: 0 2rem; /* Espaçamento lateral */
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
            background: rgba(26, 26, 26, 0.6); /* Fundo semi-transparente */
            padding: 1rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow-x: auto; /* Permite scroll horizontal em telas pequenas */
        }

        .tab-button {
            flex: 1; /* Ocupa espaço igual */
            min-width: 150px; /* Largura mínima */
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
            text-decoration: none; /* Garante que 'a' não tenha sublinhado */
        }

        .tab-button:hover {
            background: rgba(255, 255, 255, 0.05);
            color: var(--white);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .tab-button.active {
            background: linear-gradient(135deg, var(--primary-accent), #00a67e); /* Gradiente verde */
            color: var(--dark-bg);
            border-color: var(--primary-accent);
            box-shadow: 0 4px 16px rgba(0, 196, 154, 0.3);
        }

        .tab-icon {
            font-size: 1.3rem; /* Tamanho do ícone */
        }

        .tab-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
        }

        .tab-button.active .tab-badge {
            background: rgba(0, 0, 0, 0.2); /* Badge mais escuro no botão ativo */
        }

         /* Estilo para badge de pendentes */
        .badge-pending {
            background-color: rgba(245, 158, 11, 0.8); /* Laranja */
            color: white;
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-left: 0.5rem; /* Espaço do texto */
        }

        /* Tab Content */
        .tab-content {
            display: none; /* Escondido por padrão */
            animation: fadeIn 0.3s ease; /* Animação de entrada */
        }

        .tab-content.active {
            display: block; /* Mostra o conteúdo ativo */
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
            background: var(--card-background); /* Fundo com efeito de vidro */
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 2.5rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); /* Sombra */
        }

        /* Section Header dentro do card */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap; /* Quebra linha se necessário */
            gap: 1rem;
        }

        .section-title {
            font-size: 1.8rem;
            color: var(--white);
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin: 0; /* Remove margem padrão */
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
            background-color: rgba(0, 196, 154, 0.15); /* Fundo verde claro */
            color: #d1e7dd; /* Texto verde claro */
            border-left: 4px solid var(--primary-accent); /* Borda esquerda verde */
        }

        .alert-error {
            background-color: rgba(231, 76, 60, 0.15); /* Fundo vermelho claro */
            color: #f8d7da; /* Texto vermelho claro */
            border-left: 4px solid #e74c3c; /* Borda esquerda vermelha */
        }

        /* Items List */
        .items-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .item {
            background: rgba(26, 26, 26, 0.6); /* Fundo do item */
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
            border-color: var(--primary-accent); /* Borda verde no hover */
            transform: translateY(-2px); /* Efeito de flutuar */
            box-shadow: 0 4px 16px rgba(0, 196, 154, 0.2); /* Sombra verde */
        }

        .item-info {
            flex: 1; /* Ocupa espaço disponível */
            min-width: 0; /* Evita que o conteúdo estique o item */
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .item-photo {
            width: 60px;
            height: 60px;
            border-radius: 50%; /* Foto redonda */
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
            display: block; /* Garante que o link ocupe a linha */
            overflow: hidden; /* Esconde texto que transborda */
            text-overflow: ellipsis; /* Adiciona "..." */
            white-space: nowrap; /* Impede quebra de linha */
        }
         /* Link no título do animal */
        .item-title a {
            color: inherit; /* Herda a cor */
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .item-title a:hover {
             color: var(--primary-accent);
             text-decoration: underline;
        }


        .item-meta {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.5); /* Cor cinza claro */
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
            background: linear-gradient(135deg, #00c49a, #00a67e); /* Gradiente verde */
            color: var(--dark-bg);
        }

        .badge-user {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

         /* Estilo específico para status no painel admin */
        .item-status-badge {
            display: inline-block;
            padding: 0.4em 0.8em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 0.375rem;
            margin-left: 1rem; /* Espaço da meta info */
        }
        .badge-warning { background-color: rgba(245, 158, 11, 0.2); color: #f59e0b; }
        .badge-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; }
        .badge-danger { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; }


        .item-actions {
            display: flex;
            gap: 0.75rem;
            flex-shrink: 0; /* Impede que os botões diminuam */
            flex-wrap: wrap; /* Quebra linha se necessário */
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
            white-space: nowrap; /* Impede quebra de linha no texto do botão */
            border: none;
            cursor: pointer;
            text-decoration: none; /* Garante que 'a' não tenha sublinhado */
        }

        .btn-edit {
            background: linear-gradient(135deg, #00c49a 0%, #00a67e 100%); /* Gradiente verde */
            color: var(--dark-bg);
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 196, 154, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%); /* Gradiente vermelho */
            color: var(--white);
        }

        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(231, 76, 60, 0.4);
        }

        /* Botões Aprovar/Rejeitar */
         .btn-approve {
            background: #10b981;
            color: white;
        }
        .btn-approve:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        .btn-reject {
            background: #ef4444;
            color: white;
        }
        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }


        .btn-promote {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); /* Gradiente azul */
            color: var(--white);
        }

        .btn-promote:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.4);
        }

        .btn-demote {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); /* Gradiente laranja */
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

        /* Search Form Styles */
        .search-form {
            display: flex;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            padding: 0.5rem;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .search-form input {
            background: transparent;
            border: none;
            color: var(--white);
            outline: none;
            font-size: 0.9rem;
            width: 250px;
        }
        
        .search-form button {
            background: transparent;
            border: none;
            color: var(--text-color-light);
            cursor: pointer;
            padding: 0 0.5rem;
            transition: color 0.3s;
        }
        
        .search-form button:hover {
            color: var(--primary-accent);
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
            
            .search-form {
                width: 100%;
                margin-top: 1rem;
            }
            
            .search-form input {
                width: 100%;
            }

            .tabs-navigation {
                 /* Mantém horizontal, mas permite scroll */
            }

            .item {
                flex-direction: column;
                align-items: flex-start;
            }

            .item-info {
                width: 100%;
                flex-direction: column; /* Coloca foto acima dos detalhes */
                align-items: flex-start;
                gap: 1rem; /* Reduz espaço */
            }
             .item-photo {
                width: 80px; /* Foto um pouco maior */
                height: 80px;
            }
             .item-title {
                 white-space: normal; /* Permite quebra de linha no título */
            }

            .item-actions {
                width: 100%;
                justify-content: flex-start; /* Alinha botões à esquerda */
            }

            .btn-action {
                 padding: 0.6rem 1rem; /* Botões menores */
                 font-size: 0.85rem;
            }
        }
         @media (max-width: 480px) {
             .tabs-navigation {
                 padding: 0.5rem; /* Menos padding */
            }
             .tab-button {
                 padding: 0.8rem 1rem; /* Botões de tab menores */
                 min-width: 120px;
                 font-size: 0.9rem;
            }
             .item {
                 padding: 1rem; /* Padding menor no item */
            }
             .item-actions {
                 /* Botões podem ficar um embaixo do outro se necessário */
                 flex-direction: column;
                 align-items: stretch; /* Estica botões na largura */
            }
             .btn-action {
                 justify-content: center; /* Centraliza texto nos botões */
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
                 <!-- Botão Moderação Animais -->
                <a href="moderar_animais.php" class="tab-button" style="text-decoration: none; <?php if ($animais_pendentes_count > 0) echo 'background: linear-gradient(135deg, #f59e0b, #d97706); color: white;'; ?>">
                    <span class="tab-icon">⚖️</span>
                    <span>Moderar Animais</span>
                    <?php if ($animais_pendentes_count > 0): ?>
                        <span class="badge-pending"><?php echo $animais_pendentes_count; ?></span>
                    <?php endif; ?>
                </a>
                 <!-- Tab Animais -->
                <button class="tab-button <?php echo $tab == 'animais' ? 'active' : ''; ?>" onclick="changeTab('animais')">
                    <span class="tab-icon">🐾</span>
                    <span>Todos Animais</span>
                    <span class="tab-badge"><?php echo count($animais); ?></span>
                </button>
                 <!-- Tab Usuários -->
                <button class="tab-button <?php echo $tab == 'usuarios' ? 'active' : ''; ?>" onclick="changeTab('usuarios')">
                    <span class="tab-icon">👥</span>
                    <span>Utilizadores</span>
                    <span class="tab-badge"><?php echo count($usuarios); ?></span>
                </button>
                 <!-- Tab Blog (opcional, manter se ainda quiser gerenciar posts) -->
                 <button class="tab-button <?php echo $tab == 'blog' ? 'active' : ''; ?>" onclick="changeTab('blog')">
                    <span class="tab-icon">📝</span>
                    <span>Blog</span>
                    <span class="tab-badge"><?php echo count($posts); ?></span>
                </button>
            </nav>

            <!-- Alerts -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <span class="alert-icon"><i class="fas fa-check-circle"></i></span> <!-- Ícone Font Awesome -->
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span> <!-- Ícone Font Awesome -->
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>


            <!-- Tab: Blog (Manter estrutura se necessário, remover se não for mais gerenciar posts aqui) -->
            <div id="tab-blog" class="tab-content <?php echo $tab == 'blog' ? 'active' : ''; ?>">
                 <div class="content-card">
                    <div class="section-header">
                        <h2 class="section-title">
                            📰 Publicações do Blog
                        </h2>
                        <a href="criar_post.php" class="btn-action btn-edit"> <!-- Usando classe de botão existente -->
                           <i class="fas fa-plus"></i> Novo Post
                        </a>
                    </div>

                    <div class="items-list">
                        <?php if (empty($posts)): ?>
                            <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-file-alt"></i></div>
                                <p class="empty-state-text">Nenhuma publicação encontrada</p>
                                <p class="empty-state-subtext">Crie seu primeiro post para começar!</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($posts as $post): ?>
                                <div class="item">
                                    <div class="item-info">
                                         <!-- Se houver imagem, mostrar -->
                                        <?php if (!empty($post['imagem_destaque'])): ?>
                                            <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>"
                                                 alt="Imagem do post"
                                                 class="item-photo" style="object-fit: contain;"> <!-- contain para ver melhor a img -->
                                        <?php else: ?>
                                             <!-- Placeholder se não houver imagem -->
                                            <div class="item-photo" style="background: var(--dark-bg); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--text-color-light);"><i class="far fa-image"></i></div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                             <a href="post_detalhe.php?id=<?php echo $post['id']; ?>" target="_blank" class="item-title" style="text-decoration: none; color: inherit;">
                                                <?php echo htmlspecialchars($post['titulo']); ?>
                                            </a>
                                            <div class="item-meta">
                                                <span class="item-meta-item">
                                                   <i class="fas fa-user"></i> <!-- Ícone Font Awesome -->
                                                    <span>por <?php echo htmlspecialchars($post['nome_autor']); ?></span>
                                                </span>
                                                <?php if (isset($post['data_publicacao'])): ?>
                                                    <span class="item-meta-item">
                                                        <i class="fas fa-calendar-alt"></i> <!-- Ícone Font Awesome -->
                                                        <span><?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Editar <!-- Ícone Font Awesome -->
                                        </a>
                                        <!-- Formulário de Exclusão POST -->
                                        <form action="excluir_post.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir esta publicação?\n\nEsta ação não pode ser desfeita.');" style="display: contents;">
                                            <?php csrf_input_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="fas fa-trash-alt"></i> Excluir <!-- Ícone Font Awesome -->
                                            </button>
                                        </form>
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
                           <i class="fas fa-paw"></i> Todos os Animais Cadastrados <!-- Ícone Font Awesome -->
                        </h2>
                        <a href="cadastrar_animal.php" class="btn-action btn-edit"> <!-- Link para cadastrar animal -->
                            <i class="fas fa-plus"></i> Novo Animal <!-- Ícone Font Awesome -->
                        </a>
                    </div>

                    <div class="items-list">
                        <?php if (empty($animais)): ?>
                             <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-cat"></i></div> <!-- Ícone Font Awesome -->
                                <p class="empty-state-text">Nenhum animal cadastrado</p>
                                <p class="empty-state-subtext">Aguardando cadastros de animais</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($animais as $animal):
                                // Determina o texto e a classe do status
                                $status_text = '';
                                $status_class = '';
                                switch ($animal['status']) {
                                    case 'pendente':
                                        $status_text = 'Pendente';
                                        $status_class = 'badge-warning';
                                        break;
                                    case 'aprovado':
                                        $status_text = 'Aprovado';
                                        $status_class = 'badge-success';
                                        break;
                                    case 'rejeitado':
                                        $status_text = 'Rejeitado';
                                        $status_class = 'badge-danger';
                                        break;
                                    default:
                                        $status_text = 'Indefinido'; // Caso a coluna ainda não exista ou esteja nula
                                        $status_class = 'badge-secondary'; // Uma cor neutra
                                        break;
                                }
                            ?>
                                <div class="item">
                                    <div class="item-info">
                                        <?php if (!empty($animal['foto_animal'])): ?>
                                            <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>"
                                                 alt="<?php echo htmlspecialchars($animal['nome']); ?>"
                                                 class="item-photo">
                                        <?php else: ?>
                                             <!-- Placeholder se não houver imagem -->
                                            <div class="item-photo" style="background: var(--dark-bg); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--text-color-light);"><i class="fas fa-paw"></i></div>
                                        <?php endif; ?>
                                        <div class="item-details">
                                            <h3 class="item-title">
                                                <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" target="_blank" style="color: inherit;">
                                                    <?php echo htmlspecialchars($animal['nome']); ?>
                                                </a>
                                            </h3>
                                            <div class="item-meta">
                                                <span class="item-meta-item">
                                                   <i class="fas fa-user"></i> <!-- Ícone -->
                                                    <span>por <?php echo htmlspecialchars($animal['nome_usuario']); ?></span>
                                                </span>
                                                <span class="item-meta-item">
                                                    <i class="fas fa-tag"></i> <!-- Ícone -->
                                                    <span><?php echo htmlspecialchars($animal['especie']); ?></span>
                                                </span>
                                                <?php if (isset($animal['data_cadastro'])): ?>
                                                    <span class="item-meta-item">
                                                        <i class="fas fa-calendar-alt"></i> <!-- Ícone -->
                                                        <span><?php echo date('d/m/Y', strtotime($animal['data_cadastro'])); ?></span>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($status_text): ?>
                                                    <span class="item-status-badge <?php echo $status_class; ?>">
                                                         <?php echo htmlspecialchars($status_text); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <!-- Botão Editar permanece -->
                                        <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i> Editar <!-- Ícone -->
                                        </a>
                                        <!-- Formulário de Exclusão -->
                                        <form action="excluir_animal.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este animal?\n\nEsta ação não pode ser desfeita.');" style="display: contents;">
                                            <?php csrf_input_field(); ?>
                                            <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">
                                            <input type="hidden" name="return_to" value="admin.php?tab=animais"> <!-- ADICIONADO CAMPO DE RETORNO -->
                                            <button type="submit" class="btn-action btn-delete">
                                               <i class="fas fa-trash-alt"></i> Excluir <!-- Ícone -->
                                            </button>
                                        </form>
                                         <!-- Botões Aprovar/Rejeitar -->
                                        <?php if ($animal['status'] != 'aprovado'): ?>
                                            <form action="moderar_animais.php" method="POST" style="display: contents;">
                                                 <?php csrf_input_field(); ?>
                                                <input type="hidden" name="animal_id" value="<?php echo $animal['id']; ?>">
                                                <input type="hidden" name="return_to" value="admin.php?tab=animais">
                                                <button type="submit" name="acao" value="aprovar" class="btn-action btn-approve">
                                                    <i class="fas fa-check"></i> Aprovar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($animal['status'] != 'rejeitado'): ?>
                                             <form action="moderar_animais.php" method="POST" style="display: contents;">
                                                 <?php csrf_input_field(); ?>
                                                <input type="hidden" name="animal_id" value="<?php echo $animal['id']; ?>">
                                                <input type="hidden" name="return_to" value="admin.php?tab=animais">
                                                <button type="submit" name="acao" value="rejeitar" class="btn-action btn-reject">
                                                    <i class="fas fa-times"></i> Rejeitar
                                                </button>
                                            </form>
                                        <?php endif; ?>
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
                           <i class="fas fa-users"></i> Todos os Utilizadores <!-- Ícone -->
                        </h2>
                        
                        <!-- Sistema de Pesquisa de Utilizadores -->
                        <form action="admin.php" method="GET" class="search-form">
                            <input type="hidden" name="tab" value="usuarios">
                            <input type="text" name="busca_usuario" placeholder="Buscar nome ou email..." value="<?php echo htmlspecialchars($busca_usuario); ?>">
                            <button type="submit"><i class="fas fa-search"></i></button>
                            <?php if(!empty($busca_usuario)): ?>
                                <a href="admin.php?tab=usuarios" style="color: var(--text-color-light); margin-left: 0.5rem; display:flex; align-items:center;"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="items-list">
                         <?php if (empty($usuarios)): ?>
                             <div class="empty-state">
                                <div class="empty-state-icon"><i class="fas fa-user-slash"></i></div> <!-- Ícone -->
                                <p class="empty-state-text">Nenhum utilizador encontrado</p>
                                <?php if(!empty($busca_usuario)): ?>
                                    <p class="empty-state-subtext">Tente uma pesquisa diferente.</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($usuarios as $usuario): ?>
                                <div class="item">
                                    <div class="item-info">
                                         <!-- Exibir foto de perfil -->
                                         <?php 
                                            $foto = $usuario['foto_perfil'];
                                            $foto_path = 'perfil_foto/' . $foto;
                                            
                                            // Verifica se existe foto e se o ficheiro existe
                                            if (!empty($foto) && file_exists(__DIR__ . '/' . $foto_path)) {
                                                echo '<img src="' . htmlspecialchars($foto_path) . '" alt="Foto" class="item-photo">';
                                            } else {
                                                // Placeholder se não houver imagem
                                                echo '<div class="item-photo" style="background: var(--dark-bg); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: var(--text-color-light); border-radius: 50%;"><i class="fas fa-user"></i></div>';
                                            }
                                         ?>
                                         
                                        <div class="item-details">
                                            <h3 class="item-title"><?php echo htmlspecialchars($usuario['nome']); ?></h3>
                                            <div class="item-meta">
                                                <span class="item-meta-item">
                                                    <i class="fas fa-envelope"></i> <!-- Ícone -->
                                                    <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                                                </span>
                                                <?php if (isset($usuario['data_registro'])): ?>
                                                    <span class="item-meta-item">
                                                       <i class="fas fa-calendar-alt"></i> <!-- Ícone -->
                                                        <span>Registrado em <?php echo date('d/m/Y', strtotime($usuario['data_registro'])); ?></span>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="user-badge <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'badge-admin' : 'badge-user'; ?>">
                                                <?php echo $usuario['nivel_acesso'] == 'admin' ? '<i class="fas fa-shield-alt"></i> Admin' : '<i class="fas fa-user"></i> Utilizador'; ?> <!-- Ícones -->
                                            </span>
                                        </div>
                                    </div>
                                    <div class="item-actions">
                                        <?php if ($_SESSION['user_id'] != $usuario['id']): // Não pode modificar a si mesmo ?>
                                            <?php if ($usuario['nivel_acesso'] == 'user'): ?>
                                                 <!-- Formulário para Promover -->
                                                <form action="gerir_usuario.php?action=promote&id=<?php echo $usuario['id']; ?>" method="POST" style="display: contents;">
                                                     <?php csrf_input_field(); ?>
                                                    <button type="submit" class="btn-action btn-promote">
                                                        <i class="fas fa-arrow-up"></i> Promover <!-- Ícone -->
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                 <!-- Formulário para Rebaixar -->
                                                <form action="gerir_usuario.php?action=demote&id=<?php echo $usuario['id']; ?>" method="POST" style="display: contents;">
                                                     <?php csrf_input_field(); ?>
                                                    <button type="submit" class="btn-action btn-demote">
                                                        <i class="fas fa-arrow-down"></i> Rebaixar <!-- Ícone -->
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                             <!-- Formulário para Excluir -->
                                            <form action="gerir_usuario.php?action=delete&id=<?php echo $usuario['id']; ?>" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir este utilizador?\n\nTodos os seus animais e publicações serão apagados.\nEsta ação não pode ser desfeita.');" style="display: contents;">
                                                <?php csrf_input_field(); ?>
                                                <button type="submit" class="btn-action btn-delete">
                                                   <i class="fas fa-trash-alt"></i> Excluir <!-- Ícone -->
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: rgba(255, 255, 255, 0.5); font-size: 0.9rem; align-self: center;">
                                                🔒 Você
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </main>
    <script>
        function changeTab(tabName) {
            // Remove active class from all tabs buttons and contents
            document.querySelectorAll('.tab-button').forEach(btn => {
                btn.classList.remove('active');
            });
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });

            // Find the correct button (handle case where click is on icon/text inside button)
            const clickedButton = event.target.closest('.tab-button');
            if (clickedButton) {
                 clickedButton.classList.add('active');
            } else {
                 // Fallback if the structure changes unexpectedly
                 const fallbackButton = document.querySelector(`.tab-button[onclick="changeTab('${tabName}')"]`);
                 if (fallbackButton) fallbackButton.classList.add('active');
            }


            // Add active class to the corresponding content
            const contentElement = document.getElementById('tab-' + tabName);
             if (contentElement) {
                contentElement.classList.add('active');
            }

            // Update URL without reload
            const url = new URL(window.location);
            url.searchParams.set('tab', tabName);
            window.history.pushState({}, '', url);
        }

        // Handle browser back/forward buttons and initial load
         function handleTabState() {
            const urlParams = new URLSearchParams(window.location.search);
            // Define o padrão como 'animais' se nenhum tab for especificado
            const tab = urlParams.get('tab') || 'animais';

             // Remove active class de todos os botões e conteúdos primeiro
             document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
             document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

             // Adiciona a classe active ao botão e conteúdo correto
             // Seleciona o botão correto, mesmo que seja um link ('a') ou 'button'
             const activeButton = document.querySelector(`.tab-button[onclick="changeTab('${tab}')"], .tab-button[href*='tab=${tab}']`);
             const activeContent = document.getElementById('tab-' + tab);

             if (activeButton) activeButton.classList.add('active');
             if (activeContent) activeContent.classList.add('active');
        }

        window.addEventListener('popstate', handleTabState);
         // Garante que a tab correta seja mostrada ao carregar a página
        document.addEventListener('DOMContentLoaded', handleTabState);

    </script>
     <?php include 'footer.php'; ?>
</body>
</html>