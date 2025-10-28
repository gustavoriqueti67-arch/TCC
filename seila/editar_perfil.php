<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Buscar os dados atuais do utilizador para preencher o formulário
$stmt = $pdo->prepare("SELECT nome, email, cidade, telefone, foto_perfil FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // Caso raro, mas seguro
    header('Location: logout.php');
    exit();
}

$errors = [];
// Processar o formulário quando for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar e limpar os dados
    $nome = trim($_POST['nome']);
    $cidade = trim($_POST['cidade']);
    $telefone = trim($_POST['telefone']);
    
    // Validações básicas
    if (empty($nome)) $errors[] = "O nome é obrigatório.";
    if (empty($cidade)) $errors[] = "A cidade é obrigatória.";
    if (empty($telefone)) $errors[] = "O telefone é obrigatório.";

    // Processamento da nova foto de perfil (se enviada)
    $foto_atual = $usuario['foto_perfil'];
    $nome_foto = $foto_atual;

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $nova_foto = $_FILES['foto_perfil'];
        $tipo_permitido = ['jpg', 'jpeg', 'png', 'gif'];
        $extensao = strtolower(pathinfo($nova_foto['name'], PATHINFO_EXTENSION));

        if (in_array($extensao, $tipo_permitido) && $nova_foto['size'] <= 5 * 1024 * 1024) { // 5MB
            $nome_foto = 'user_' . uniqid('', true) . '.' . $extensao;
            
            // Mover a nova foto e apagar a antiga
            if(move_uploaded_file($nova_foto['tmp_name'], UPLOADS_PATH_PERFIL . $nome_foto)) {
                if ($foto_atual && file_exists(UPLOADS_PATH_PERFIL . $foto_atual)) {
                    unlink(UPLOADS_PATH_PERFIL . $foto_atual);
                }
            } else {
                $errors[] = "Erro ao carregar a nova foto.";
                $nome_foto = $foto_atual; // Reverter para a foto antiga em caso de erro
            }
        } else {
             $errors[] = "Ficheiro de imagem inválido ou demasiado grande (máx 5MB).";
        }
    }

    if (empty($errors)) {
        try {
            $stmt_update = $pdo->prepare(
                "UPDATE usuarios SET nome = ?, cidade = ?, telefone = ?, foto_perfil = ? WHERE id = ?"
            );
            $stmt_update->execute([$nome, $cidade, $telefone, $nome_foto, $user_id]);

            // Atualizar os dados da sessão para refletir as mudanças imediatamente
            $_SESSION['user_name'] = $nome;
            $_SESSION['user_photo'] = $nome_foto;

            $_SESSION['success_message_perfil'] = "Perfil atualizado com sucesso!";
            header('Location: perfil.php');
            exit();

        } catch (PDOException $e) {
            $errors[] = "Erro ao atualizar o perfil. Por favor, tente novamente.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Meu Perfil</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .form-page { padding-top: 3rem; padding-bottom: 3rem; }
        .form-container { max-width: 600px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container">
            <h1 class="form-title">Editar Perfil</h1>
            <p class="form-subtitle">Mantenha as suas informações sempre atualizadas.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="editar_perfil.php" method="POST" enctype="multipart/form-data" class="form-layout">
                
                <div class="form-group-photo">
                    <label for="foto_perfil">
                        <img src="perfil_foto/<?php echo htmlspecialchars($usuario['foto_perfil'] ?: 'default-avatar.png'); ?>" 
                             alt="Foto de Perfil Atual" 
                             id="image-preview" 
                             class="profile-pic-preview">
                        <div class="upload-icon"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                    <p class="photo-label-text">Clique na imagem para alterar</p>
                </div>

                <div class="form-group">
                    <label for="nome" class="form-label">Nome Completo</label>
                    <input type="text" id="nome" name="nome" class="form-input" required value="<?php echo htmlspecialchars($usuario['nome']); ?>">
                </div>
                <div class="form-group">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" disabled value="<?php echo htmlspecialchars($usuario['email']); ?>" style="background-color: var(--dark-bg); color: var(--text-color-light);">
                    <small style="color: var(--text-color-lighter); margin-top: 0.5rem; display: block;">O email não pode ser alterado.</small>
                </div>
                <div class="form-group">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-input" required value="<?php echo htmlspecialchars($usuario['cidade']); ?>">
                </div>
                 <div class="form-group">
                    <label for="telefone" class="form-label">Telefone</label>
                    <input type="tel" id="telefone" name="telefone" class="form-input" required value="<?php echo htmlspecialchars($usuario['telefone']); ?>">
                </div>

                <button type="submit" class="btn btn-register btn-form">Salvar Alterações</button>
            </form>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>

    <script>
        document.getElementById('foto_perfil').addEventListener('change', function(event) {
            const preview = document.getElementById('image-preview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onloadend = function() {
                    preview.src = reader.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
