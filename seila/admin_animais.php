<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
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
    $animais = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Gerir Animais</title>
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
                    <li><a href="admin_animais.php" class="active">Gerir Animais</a></li>
                    <li><a href="admin_usuarios.php">Gerir Utilizadores</a></li>
                </ul>
            </aside>
            <div class="admin-content">
                <div class="animais-header" style="margin-bottom: 2rem;">
                    <h2>Gerir Todos os Animais</h2>
                </div>
                <div class="animais-card">
                     <?php if (empty($animais)): ?>
                        <p class="text-center">Não há animais cadastrados no site.</p>
                    <?php else: ?>
                        <?php foreach ($animais as $animal): ?>
                            <div class="animal-item">
                                <div class="animal-item-info">
                                    <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" class="animal-item-foto">
                                    <span class="animal-item-nome"><?php echo htmlspecialchars($animal['nome']); ?></span>
                                    <span style="color: var(--text-color-light);">por <?php echo htmlspecialchars($animal['nome_usuario']); ?></span>
                                </div>
                                <div class="animal-item-actions">
                                    <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i> Editar</a>
                                    <a href="excluir_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Tem a certeza que deseja excluir este animal?');"><i class="fas fa-trash"></i> Excluir</a>
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

