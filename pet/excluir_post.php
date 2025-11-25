<?php
require_once 'config.php';

// Apenas administradores podem executar esta ação
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Excluir deve ser via POST com CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    header('Location: admin.php');
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: admin.php');
    exit();
}

$post_id = (int)$_POST['id'];

try {
    // 2. Buscar o nome da imagem do post antes de apagar o registo
    $stmt = $pdo->prepare("SELECT imagem_destaque FROM blog_posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();

    if ($post && !empty($post['imagem_destaque'])) {
        // 3. Excluir a imagem da pasta
        $caminho_imagem = __DIR__ . '/blog_imagens/' . $post['imagem_destaque'];
        if (file_exists($caminho_imagem)) {
            unlink($caminho_imagem);
        }
    }

    // 4. Excluir o registo do post do banco de dados
    $stmt_delete = $pdo->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt_delete->execute([$post_id]);

    // 5. Definir mensagem de sucesso e redirecionar
    $_SESSION['success_message_admin'] = "Publicação excluída com sucesso!";

} catch (PDOException $e) {
    $_SESSION['error_message_admin'] = "Ocorreu um erro ao excluir a publicação.";
    // Para depuração: error_log($e->getMessage());
}

// Redirecionar de volta para o painel de administração
header('Location: admin.php');
exit();
?>
