<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Falha de segurança: token CSRF inválido.';
    }
    if (empty($errors)) {
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    $imagem = $_FILES['imagem'];
    
    if (empty($titulo) || empty($conteudo)) {
        $errors[] = "Título e conteúdo são obrigatórios.";
    }

    $nome_imagem = null;
    if (isset($imagem) && $imagem['error'] === UPLOAD_ERR_OK) {
        // Validar extensão e tamanho; permitir GIF, JPG, JPEG, PNG
        $extensao = strtolower(pathinfo($imagem['name'], PATHINFO_EXTENSION));
        $permitidos = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($extensao, $permitidos)) {
            if ($imagem['size'] <= 10 * 1024 * 1024) { // 10MB
                $nome_imagem = 'post_' . uniqid() . '.' . $extensao;
                if (!is_dir(__DIR__ . '/blog_imagens/')) {
                    mkdir(__DIR__ . '/blog_imagens/', 0777, true);
                }
                if (!move_uploaded_file($imagem['tmp_name'], __DIR__ . '/blog_imagens/' . $nome_imagem)) {
                    $errors[] = 'Falha ao salvar a imagem.';
                }
            } else {
                $errors[] = 'A imagem precisa ter no máximo 10MB.';
            }
        } else {
            $errors[] = 'Formato inválido. Use JPG, PNG ou GIF.';
        }
    }

    }
    if (empty($errors)) {
        try {
            // Verificar se a coluna status existe, se não, adicionar
            try {
                $pdo->exec("ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS status ENUM('pendente', 'publicado', 'rejeitado') DEFAULT 'pendente'");
            } catch (PDOException $e) {}
            
            $stmt = $pdo->prepare(
                "INSERT INTO blog_posts (titulo, conteudo, imagem_destaque, id_autor, status) VALUES (?, ?, ?, ?, 'pendente')"
            );
            $stmt->execute([$titulo, $conteudo, $nome_imagem, $_SESSION['user_id']]);
            
            header("Location: moderar_posts.php");
            exit();

        } catch (PDOException $e) {
            $errors[] = "Erro ao criar a publicação.";
            // error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Nova Publicação</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container" style="max-width: 800px;">
            <h1 class="form-title">Nova Publicação do Blog</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="criar_post.php" method="POST" enctype="multipart/form-data" class="form-layout">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="conteudo" class="form-label">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" class="form-textarea" rows="10" required></textarea>
                </div>
                <div class="form-group">
                    <label for="imagem" class="form-label">Imagem de Destaque (Opcional)</label>
                    <input type="file" id="imagem" name="imagem" class="form-input" accept="image/jpeg,image/png,image/gif">
                </div>
                <button type="submit" class="btn btn-register btn-form">Enviar para Moderação</button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
