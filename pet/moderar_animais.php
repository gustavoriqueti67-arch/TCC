<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Processar aprovação/rejeição
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message_admin'] = 'Token CSRF inválido.';
        header('Location: moderar_animais.php');
        exit();
    }

    $animal_id = isset($_POST['animal_id']) ? (int)$_POST['animal_id'] : 0;
    $acao = isset($_POST['acao']) ? $_POST['acao'] : '';

    if ($animal_id > 0 && in_array($acao, ['aprovar', 'rejeitar'])) {
        $status = ($acao === 'aprovar') ? 'aprovado' : 'rejeitado';

        try {
            $stmt = $pdo->prepare("UPDATE animais SET status = ? WHERE id = ?");
            $stmt->execute([$status, $animal_id]);

            $_SESSION['success_message_admin'] = "Animal " . ($acao === 'aprovar' ? 'aprovado' : 'rejeitado') . " com sucesso!";
        } catch (PDOException $e) {
            $_SESSION['error_message_admin'] = "Erro ao processar a ação.";
        }
    }

    header('Location: moderar_animais.php');
    exit();
}

// Buscar animais pendentes
try {
    $stmt = $pdo->query(
        "SELECT a.*, u.nome as nome_usuario
         FROM animais a
         JOIN usuarios u ON a.id_usuario = u.id
         WHERE a.status = 'pendente'
         ORDER BY a.data_cadastro DESC"
    );
    $animais_pendentes = $stmt->fetchAll();
} catch (PDOException $e) {
    $animais_pendentes = [];
}

// Buscar animais aprovados/rejeitados recentes (opcional, para histórico)
$animais_recentes_moderados = [];
try {
    $stmt = $pdo->query(
        "SELECT a.*, u.nome as nome_usuario
         FROM animais a
         JOIN usuarios u ON a.id_usuario = u.id
         WHERE a.status IN ('aprovado', 'rejeitado')
         ORDER BY a.data_cadastro DESC -- Ou ORDER BY data_modificacao se tiver
         LIMIT 10"
    );
    $animais_recentes_moderados = $stmt->fetchAll();
} catch (PDOException $e) {
     $animais_recentes_moderados = [];
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
    <title>Moderar Animais - Admin</title>
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

        .posts-section { /* Renomeado para items-section ou similar seria melhor */
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

        .items-grid {
            display: grid;
            gap: 1.5rem;
        }

        .item-card {
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 1.5rem;
            transition: all 0.3s ease;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .item-card:hover {
            border-color: var(--primary-accent);
            transform: translateY(-2px);
        }

        .item-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            flex-shrink: 0;
        }

        .item-content {
            flex-grow: 1;
        }

        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }

        .item-title {
            font-size: 1.25rem;
            color: var(--white);
            margin-bottom: 0.25rem; /* Reduzido */
        }

        .item-meta {
            color: var(--text-color-light);
            font-size: 0.9rem;
            margin-bottom: 1rem; /* Adicionado espaço abaixo */
        }
         .item-meta span { margin-right: 1rem;} /* Espaço entre meta itens */

        .item-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 1rem; /* Adicionado espaço acima */
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

        /* Badges de Status para Histórico */
        .status-badge {
            padding: 0.4em 0.8em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 0.375rem;
        }
        .badge-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; }
        .badge-danger { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; }


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

        /* Alertas */
        .alert {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }

         @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert i { font-size: 1.2rem; }

        .alert-success {
            background: rgba(46, 204, 113, 0.15); border-left: 4px solid #2ecc71; color: #d1e7dd;
        }
        .alert-error {
            background: rgba(231, 76, 60, 0.15); border-left: 4px solid #e74c3c; color: #f8d7da;
        }

        @media (max-width: 640px) {
             .item-card {
                 flex-direction: column;
                 align-items: center;
                 text-align: center;
             }
             .item-image {
                 width: 150px; /* Tamanho maior no mobile */
                 height: 150px;
             }
             .item-header {
                 flex-direction: column;
                 align-items: center;
             }
             .item-meta {
                 justify-content: center;
             }
             .item-actions {
                 justify-content: center;
             }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="admin-container">
            <div class="page-header">
                <h1><i class="fas fa-paw"></i> Moderar Animais</h1>
                <p>Gerencie e aprove animais pendentes de publicação</p>
            </div>

            <!-- Alertas -->
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                     <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Animais Pendentes -->
            <div class="posts-section">
                <h2 class="section-title">
                    <i class="fas fa-clock"></i>
                    Animais Pendentes (<?php echo count($animais_pendentes); ?>)
                </h2>

                <?php if (empty($animais_pendentes)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
                        <h3>Nenhum animal pendente!</h3>
                        <p>Todos os cadastros foram revisados.</p>
                    </div>
                <?php else: ?>
                    <div class="items-grid">
                        <?php foreach ($animais_pendentes as $animal): ?>
                            <div class="item-card">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="<?php echo htmlspecialchars($animal['nome']); ?>" class="item-image">
                                <div class="item-content">
                                    <div class="item-header">
                                        <div>
                                            <h3 class="item-title"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                            <div class="item-meta">
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($animal['nome_usuario']); ?></span>
                                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($animal['data_cadastro'])); ?></span>
                                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($animal['especie']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <p style="color: var(--text-color-light); line-height: 1.6;">
                                        <?php echo htmlspecialchars(substr(strip_tags($animal['descricao']), 0, 150)) . '...'; ?>
                                        <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" target="_blank" style="color: var(--primary-accent); font-weight: 500;">Ver detalhes</a>
                                    </p>
                                    <form method="POST" style="display: contents;"> <!-- Usar display: contents para não quebrar layout -->
                                        <?php csrf_input_field(); ?>
                                        <input type="hidden" name="animal_id" value="<?php echo $animal['id']; ?>">
                                        <div class="item-actions">
                                            <button type="submit" name="acao" value="aprovar" class="btn-approve">
                                                <i class="fas fa-check"></i> Aprovar
                                            </button>
                                            <button type="submit" name="acao" value="rejeitar" class="btn-reject">
                                                <i class="fas fa-times"></i> Rejeitar
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

             <!-- Histórico Recente -->
            <?php if (!empty($animais_recentes_moderados)): ?>
                <div class="posts-section">
                    <h2 class="section-title">
                        <i class="fas fa-history"></i>
                        Histórico Recente de Moderação
                    </h2>
                     <div class="items-grid">
                        <?php foreach ($animais_recentes_moderados as $animal): ?>
                             <div class="item-card">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="<?php echo htmlspecialchars($animal['nome']); ?>" class="item-image">
                                <div class="item-content">
                                    <div class="item-header">
                                        <div>
                                            <h3 class="item-title"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                            <div class="item-meta">
                                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($animal['nome_usuario']); ?></span>
                                                <span><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($animal['data_cadastro'])); ?></span>
                                            </div>
                                        </div>
                                         <span class="status-badge <?php echo ($animal['status'] == 'aprovado' ? 'badge-success' : 'badge-danger'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($animal['status'])); ?>
                                        </span>
                                    </div>
                                     <p style="color: var(--text-color-light); line-height: 1.6; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars(substr(strip_tags($animal['descricao']), 0, 100)) . '...'; ?>
                                         <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" target="_blank" style="color: var(--primary-accent); font-weight: 500;">Ver</a>
                                    </p>
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
