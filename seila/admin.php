<?php
require_once 'config.php';

// Apenas administradores podem aceder a esta página
if (!is_admin()) {
    header('Location: index.php'); // Redireciona para a página inicial se não for admin
    exit();
}

// Buscar todos os posts do blog para gestão
try {
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         ORDER BY bp.data_publicacao DESC"
    );
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}

// Pega as mensagens da sessão (versão compatível com PHP antigo)
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
    <title>Painel de Administração</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-wrapper {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
        }
        .admin-sidebar {
            background-color: var(--dark-bg-alt);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            min-width: 220px;
        }
        .admin-sidebar h3 {
            font-size: 1.2rem;
            color: var(--white);
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary-accent);
        }
        .admin-sidebar ul {
            list-style: none;
        }
        .admin-sidebar li a {
            display: block;
            padding: 0.8rem 1rem;
            color: var(--text-color-light);
            border-radius: var(--border-radius-sm);
            transition: all var(--transition-normal);
        }
        .admin-sidebar li a:hover {
            background-color: var(--dark-bg-elevated);
            color: var(--white);
        }
        .admin-sidebar li a.active {
            background-color: var(--primary-accent);
            color: var(--dark-bg);
            font-weight: 600;
        }
        .admin-content {
            flex-grow: 1;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="section">
        <div class="container admin-wrapper">
            
            <aside class="admin-sidebar">
                <h3>Menu Admin</h3>
                <ul>
                    <li><a href="admin.php" class="active">Gerir Blog</a></li>
                    <li><a href="admin_animais.php">Gerir Animais</a></li>
                    <li><a href="admin_usuarios.php">Gerir Utilizadores</a></li>
                </ul>
            </aside>

            <div class="admin-content">
                <?php if ($success_message): ?>
                    <div class="alert alert-success mb-4"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error mb-4"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>

                <div class="animais-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>Publicações do Blog</h2>
                    <a href="criar_post.php" class="btn btn-register">Novo Post</a>
                </div>
                
                <div class="animais-card">
                     <?php if (empty($posts)): ?>
                        <p class="text-center">Ainda não há nenhuma publicação no blog.</p>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="animal-item">
                                <div class="animal-item-info">
                                    <span class="animal-item-nome"><?php echo htmlspecialchars($post['titulo']); ?></span>
                                    <span style="color: var(--text-color-light);">por <?php echo htmlspecialchars($post['nome_autor']); ?></span>
                                </div>
                                <div class="animal-item-actions">
                                    <a href="editar_post.php?id=<?php echo $post['id']; ?>" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i> Editar</a>
                                    <a href="excluir_post.php?id=<?php echo $post['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Tem a certeza que deseja excluir esta publicação?');"><i class="fas fa-trash"></i> Excluir</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>

