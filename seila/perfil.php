<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Buscar os dados do utilizador logado
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

// Buscar os animais cadastrados por este utilizador
$stmt_animais = $pdo->prepare("SELECT * FROM animais WHERE id_usuario = ? ORDER BY data_cadastro DESC");
$stmt_animais->execute([$user_id]);
$animais_do_usuario = $stmt_animais->fetchAll();

// Pega as mensagens de sucesso/erro da sessão, se existirem
$success_message = isset($_SESSION['success_message_perfil']) ? $_SESSION['success_message_perfil'] : null;
unset($_SESSION['success_message_perfil']);

$error_message = isset($_SESSION['error_message_perfil']) ? $_SESSION['error_message_perfil'] : null;
unset($_SESSION['error_message_perfil']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - <?php echo htmlspecialchars($usuario['nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .perfil-section {
            padding: 4rem 1.5rem;
        }
        .perfil-container {
            max-width: 900px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        .perfil-card, .animais-card {
            background-color: var(--dark-bg-alt);
            padding: 2rem;
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
        }
        .perfil-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .perfil-foto {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--primary-accent);
        }
        .perfil-nome h1 {
            font-size: 2.5rem;
            margin: 0;
            color: var(--white);
        }
        .perfil-nome p {
            font-size: 1rem;
            color: var(--text-color-light);
            margin: 0;
        }
        .perfil-info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }
        .perfil-info-item i {
            color: var(--primary-accent);
            width: 20px;
        }
        .animais-header h2 {
            font-size: 1.8rem;
            color: var(--white);
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--secondary-accent);
            padding-bottom: 0.5rem;
        }
        .animal-item {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
            border-radius: 8px;
            background-color: var(--dark-bg);
            margin-bottom: 1rem;
            gap: 1rem;
        }
        .animal-item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .animal-item-foto {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .animal-item-nome {
            font-size: 1.2rem;
            color: var(--white);
            font-weight: 500;
        }
        .animal-item-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-action {
            padding: 0.5rem 0.8rem;
            font-size: 0.9rem;
            border: none;
            color: var(--white);
        }
        .btn-edit { 
            background: linear-gradient(45deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            font-weight: 600;
        }
        .btn-delete { 
            background-color: #e74c3c; 
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 8px;
            border: 1px solid transparent;
            width: 100%;
            box-sizing: border-box;
        }
        .alert-error {
            background-color: rgba(231, 76, 60, 0.15);
            border-color: rgba(231, 76, 60, 0.5);
            color: #f8d7da;
        }
        .alert-success {
            background-color: rgba(46, 204, 113, 0.15);
            border-color: rgba(46, 204, 113, 0.5);
            color: #d4edda;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="perfil-section">
        <div class="perfil-container">
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <p><?php echo htmlspecialchars($error_message); ?></p>
                </div>
            <?php endif; ?>

            <!-- Card de Informações do Utilizador -->
            <div class="perfil-card">
                <div class="perfil-header">
                    <img src="perfil_foto/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de Perfil" class="perfil-foto">
                    <div class="perfil-nome">
                        <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                        <p>Membro desde <?php echo isset($usuario['data_registro']) ? date('d/m/Y', strtotime($usuario['data_registro'])) : 'Data não disponível'; ?></p>
                    </div>
                </div>
                <div class="perfil-info">
                    <div class="perfil-info-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                    </div>
                    <div class="perfil-info-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($usuario['telefone']); ?></span>
                    </div>
                    <div class="perfil-info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo htmlspecialchars($usuario['cidade']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Card de Animais Cadastrados -->
            <div class="animais-card">
                <div class="animais-header">
                    <h2>Meus Animais Cadastrados</h2>
                </div>
                <div class="lista-animais">
                    <?php if (empty($animais_do_usuario)): ?>
                        <p style="color: var(--text-color-light);">Você ainda não cadastrou nenhum animal.</p>
                    <?php else: ?>
                        <?php foreach ($animais_do_usuario as $animal): ?>
                            <div class="animal-item">
                                <div class="animal-item-info">
                                    <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>" class="animal-item-foto">
                                    <span class="animal-item-nome"><?php echo htmlspecialchars($animal['nome']); ?></span>
                                </div>
                                <div class="animal-item-actions">
                                    <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-action btn-edit"><i class="fas fa-pencil-alt"></i> Editar</a>
                                    <a href="excluir_animal.php?id=<?php echo $animal['id']; ?>" class="btn btn-action btn-delete" onclick="return confirm('Tem a certeza que deseja excluir este animal? Esta ação não pode ser desfeita.');"><i class="fas fa-trash"></i> Excluir</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>

