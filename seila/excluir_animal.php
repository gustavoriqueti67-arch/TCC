<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Excluir deve ser via POST com CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    $_SESSION['error_message_perfil'] = 'Requisição inválida.';
    header('Location: perfil.php');
    exit();
}

if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: perfil.php');
    exit();
}

$animal_id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

try {
    // Verificar se o animal pertence ao utilizador logado
    $stmt = $pdo->prepare("SELECT foto_animal FROM animais WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$animal_id, $user_id]);
    $animal = $stmt->fetch();

    if ($animal) {
        $caminho_foto = __DIR__ . '/animais_fotos/' . $animal['foto_animal'];
        if ($animal['foto_animal'] && file_exists($caminho_foto)) {
            unlink($caminho_foto);
        }

        $stmt_delete = $pdo->prepare("DELETE FROM animais WHERE id = ? AND id_usuario = ?");
        $stmt_delete->execute([$animal_id, $user_id]);

        $_SESSION['success_message_perfil'] = 'Animal excluído com sucesso!';
    } else {
        $_SESSION['error_message_perfil'] = 'Operação não permitida.';
    }

} catch (PDOException $e) {
    $_SESSION['error_message_perfil'] = 'Ocorreu um erro ao excluir o animal.';
}

header('Location: perfil.php');
exit();
?>
