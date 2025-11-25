<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Adicionar coluna status se não existir
try {
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS status ENUM('pendente', 'publicado', 'rejeitado') DEFAULT 'pendente'");
} catch (PDOException $e) {
    // Coluna já existe ou erro
}

// Processar aprovação/rejeição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message_admin'] = 'Token CSRF inválido.';
        header('Location: moderar_posts.php');
        exit();
    }

    $post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($post_id > 0 && in_array($acao, ['aprovar', 'rejeitar'])) {
        $status = ($acao === 'aprovar') ? 'publicado' : 'rejeitado';
        
        try {
            $stmt = $pdo->prepare("UPDATE blog_posts SET status = ? WHERE id = ?");
            $stmt->execute([$status, $post_id]);
            
            $_SESSION['success_message_admin'] = "Post " . ($acao === 'aprovar' ? 'aprovado' : 'rejeitado') . " com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['error_message_admin'] = "Erro ao processar a ação.";
        }
    }
    
    header('Location: moderar_posts.php');
    exit();
}

// Buscar posts pendentes
try {
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         WHERE bp.status = 'pendente'
         ORDER BY bp.data_publicacao DESC"
    );
    $posts_pendentes = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts_pendentes = [];
}

// Buscar posts publicados recentes
try {
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         WHERE bp.status = 'publicado'
         ORDER BY bp.data_publicacao DESC
         LIMIT 10"
    );
    $posts_publicados = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts_publicados = [];
}

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
    <title>Moderar Posts - Admin</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        main { background-color: var(--dark-bg); min-height: 100vh; padding: 3rem 0; }
        
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1.5rem;
        }
        
        .page-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        
        .page-header p {
            color: var(--text-color-light);
        }
        
        .posts-section {
            margin-bottom: 3rem;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--white);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .posts-grid {
            display: grid;
            gap: 1.5rem;
        }
        
        .post-card {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
        }
        
        .post-card:hover {
            border-color: var(--primary-accent);
            transform: translateY(-2px);
        }
        
        .post-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .post-title {
            font-size: 1.25rem;
            color: var(--white);
            margin-bottom: 0.5rem;
        }
        
        .post-meta {
            color: var(--text-color-light);
            font-size: 0.9rem;
        }
        
        .post-actions {
            display: flex;
            gap: 0.75rem;
        }
        
        .btn-approve {
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-approve:hover {
            background: #059669;
            transform: translateY(-2px);
        }
        
        .btn-reject {
            background: #ef4444;
            color: white;
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-reject:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            color: var(--text-color-light);
        }
        
        .empty-state i {
            font-size: 3rem;
            color: var(--primary-accent);
            opacity: 0.5;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="admin-container">
            <div class="page-header">
                <h1><i class="fas fa-gavel"></i> Moderar Posts</h1>
                <p>Gerencie e aprove posts pendentes de publicação</p>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-error" style="margin-bottom: 2rem;">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Posts Pendentes -->
            <div class="posts-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Posts Pendentes (<?php echo count($posts_pendentes); ?>)
                </h2>

                <?php if (empty($posts_pendentes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Nenhum post pendente!</h3>
                        <p>Todos os posts foram revisados.</p>
                    </div>
                <?php else: ?>
                    <div class="posts-grid">
                        <?php foreach ($posts_pendentes as $post): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <div>
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                        <div class="post-meta">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['nome_autor']); ?> | 
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <p style="color: var(--text-color-light); margin-bottom: 1.5rem; line-height: 1.6;">
                                    <?php echo htmlspecialchars(substr(strip_tags($post['conteudo']), 0, 200)) . '...'; ?>
                                </p>
                                <form method="POST" style="display: inline;">
                                    <?php csrf_input_field(); ?>
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <div class="post-actions">
                                        <button type="submit" name="acao" value="aprovar" class="btn-approve">
                                            <i class="fas fa-check"></i> Aprovar
                                        </button>
                                        <button type="submit" name="acao" value="rejeitar" class="btn-reject">
                                            <i class="fas fa-times"></i> Rejeitar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Posts Publicados Recentes -->
            <?php if (!empty($posts_publicados)): ?>
                <div class="posts-section">
                    <h2 class="section-title">
                        <i class="fas fa-check-circle"></i>
                        Posts Publicados Recentemente
                    </h2>
                    <div class="posts-grid">
                        <?php foreach ($posts_publicados as $post): ?>
                            <div class="post-card">
                                <div class="post-header">
                                    <div>
                                        <h3 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                        <div class="post-meta">
                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['nome_autor']); ?> | 
                                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($post['data_publicacao'])); ?>
                                        </div>
                                    </div>
                                    <span style="background: #10b981; color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.85rem; font-weight: 600;">
                                        PUBLICADO
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>

