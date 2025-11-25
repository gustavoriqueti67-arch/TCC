<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Verificar se um ID de post foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php');
    exit();
}

$post_id = $_GET['id'];

// Buscar os dados atuais do post
$stmt = $pdo->prepare("SELECT * FROM blog_posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: admin.php');
    exit();
}

$errors = [];
// Processar o formulário quando for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Falha de segurança: token CSRF inválido.';
    }
    if (empty($errors)) {
    $titulo = trim($_POST['titulo']);
    $conteudo = trim($_POST['conteudo']);
    
    if (empty($titulo) || empty($conteudo)) {
        $errors[] = "Título e conteúdo são obrigatórios.";
    }

    }
    if (empty($errors)) {
        $imagem_atual = $post['imagem_destaque'];
        $nome_imagem = $imagem_atual;

        // Verificar se uma nova imagem foi enviada
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $nova_imagem = $_FILES['imagem'];
            $extensao = strtolower(pathinfo($nova_imagem['name'], PATHINFO_EXTENSION));
            $permitidos = array('jpg', 'jpeg', 'png', 'gif');
            if (!in_array($extensao, $permitidos)) {
                $errors[] = 'Formato inválido. Use JPG, PNG ou GIF.';
            } elseif ($nova_imagem['size'] > 10 * 1024 * 1024) {
                $errors[] = 'A imagem precisa ter no máximo 10MB.';
            } else {
                $nome_imagem = 'post_' . uniqid() . '.' . $extensao;
                if(move_uploaded_file($nova_imagem['tmp_name'], __DIR__ . '/blog_imagens/' . $nome_imagem)) {
                // Apagar a imagem antiga, se existir
                if ($imagem_atual && file_exists(__DIR__ . '/blog_imagens/' . $imagem_atual)) {
                    unlink(__DIR__ . '/blog_imagens/' . $imagem_atual);
                }
            } else {
                $errors[] = "Erro ao carregar a nova imagem.";
                $nome_imagem = $imagem_atual;
            }
            }
        }

        if (empty($errors)) {
            try {
                $stmt_update = $pdo->prepare(
                    "UPDATE blog_posts SET titulo = ?, conteudo = ?, imagem_destaque = ? WHERE id = ?"
                );
                $stmt_update->execute([$titulo, $conteudo, $nome_imagem, $post_id]);

                $_SESSION['success_message_admin'] = "Publicação atualizada com sucesso!";
                header('Location: admin.php');
                exit();

            } catch (PDOException $e) {
                $errors[] = "Erro ao atualizar a publicação.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicação</title>
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
            <h1 class="form-title">Editar Publicação</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="editar_post.php?id=<?php echo $post_id; ?>" method="POST" enctype="multipart/form-data" class="form-layout">
                <?php csrf_input_field(); ?>
                <div class="form-group">
                    <label for="titulo" class="form-label">Título</label>
                    <input type="text" id="titulo" name="titulo" class="form-input" value="<?php echo htmlspecialchars($post['titulo']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="conteudo" class="form-label">Conteúdo</label>
                    <textarea id="conteudo" name="conteudo" class="form-textarea" rows="10" required><?php echo htmlspecialchars($post['conteudo']); ?></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Imagem Atual</label>
                    <?php if(!empty($post['imagem_destaque'])): ?>
                        <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>" alt="Imagem atual" style="max-width: 200px; border-radius: var(--border-radius-sm);">
                    <?php else: ?>
                        <p>Nenhuma imagem.</p>
                    <?php endif; ?>
                    <label for="imagem" class="form-label" style="margin-top: 1rem;">Trocar Imagem (Opcional)</label>
                    <input type="file" id="imagem" name="imagem" class="form-input" accept="image/jpeg,image/png,image/gif">
                </div>
                <button type="submit" class="btn btn-register btn-form">Salvar Alterações</button>
            </form>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
