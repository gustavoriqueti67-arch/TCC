<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Buscar todos os utilizadores
try {
    $stmt = $pdo->query("SELECT id, nome, email, nivel_acesso, data_registro FROM usuarios ORDER BY nome ASC");
    $usuarios = $stmt->fetchAll();
} catch (PDOException $e) {
    $usuarios = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerir Utilizadores</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .admin-wrapper { display: flex; gap: 2rem; align-items: flex-start; }
        .admin-sidebar { background-color: var(--dark-bg-alt); padding: 1.5rem; border-radius: var(--border-radius); border: 1px solid var(--border-color); min-width: 220px; }
        .admin-sidebar h3 { font-size: 1.2rem; color: var(--white); margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 2px solid var(--primary-accent); }
        .admin-sidebar ul { list-style: none; }
        .admin-sidebar li a { display: block; padding: 0.8rem 1rem; color: var(--text-color-light); border-radius: var(--border-radius-sm); transition: all var(--transition-normal); }
        .admin-sidebar li a:hover { background-color: var(--dark-bg-elevated); color: var(--white); }
        .admin-sidebar li a.active { background-color: var(--primary-accent); color: var(--dark-bg); font-weight: 600; }
        .admin-content { flex-grow: 1; }
        .btn-promote { background-color: var(--secondary-accent); color: var(--dark-bg); }
        .btn-demote { background-color: #f39c12; color: var(--dark-bg); }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>
    <main class="section">
        <div class="container admin-wrapper">
            <aside class="admin-sidebar">
                <h3>Menu Admin</h3>
                <ul>
                    <li><a href="admin.php">Gerir Blog</a></li>
                    <li><a href="admin_animais.php">Gerir Animais</a></li>
                    <li><a href="admin_usuarios.php" class="active">Gerir Utilizadores</a></li>
                </ul>
            </aside>
            <div class="admin-content">
                <div class="animais-header" style="margin-bottom: 2rem;">
                    <h2>Gerir Todos os Utilizadores</h2>
                </div>
                <div class="animais-card">
                     <?php foreach ($usuarios as $usuario): ?>
                        <div class="animal-item">
                            <div class="animal-item-info">
                                <span class="animal-item-nome"><?php echo htmlspecialchars($usuario['nome']); ?></span>
                                <span style="color: var(--text-color-light);"><?php echo htmlspecialchars($usuario['email']); ?></span>
                                <span class="btn" style="padding: 0.2rem 0.5rem; font-size: 0.8rem; background-color: <?php echo ($usuario['nivel_acesso'] == 'admin') ? 'var(--secondary-accent)' : 'var(--border-color)'; ?>; color: var(--dark-bg);">
                                    <?php echo htmlspecialchars($usuario['nivel_acesso']); ?>
                                </span>
                            </div>
                            <div class="animal-item-actions">
                                <?php if ($_SESSION['user_id'] != $usuario['id']): // Não mostrar ações para o próprio admin ?>
                                    <?php if ($usuario['nivel_acesso'] == 'user'): ?>
                                        <a href="gerir_usuario.php?action=promote&id=<?php echo $usuario['id']; ?>" class="btn btn-action btn-promote">Promover a Admin</a>
                                    <?php else: ?>
                                        <a href="gerir_usuario.php?action=demote&id=<?php echo $usuario['id']; ?>" class="btn btn-action btn-demote">Rebaixar a User</a>
                                    <?php endif; ?>
                                    <a href="gerir_usuario.php?action=delete&id=<?php echo $usuario['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Tem a certeza que deseja excluir este utilizador? Todos os seus animais e publicações serão apagados.');">Excluir</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
    <?php include 'footer.php'; ?>
</body>
</html>

