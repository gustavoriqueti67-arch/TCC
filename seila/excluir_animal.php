<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// 1. Verificar se um ID de animal foi fornecido na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: perfil.php');
    exit();
}

$animal_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // 2. Verificar se o animal pertence ao utilizador logado (SEGURANÇA)
    $stmt = $pdo->prepare("SELECT foto_animal FROM animais WHERE id = ? AND id_usuario = ?");
    $stmt->execute([$animal_id, $user_id]);
    $animal = $stmt->fetch();

    if ($animal) {
        // 3. Excluir a foto do animal da pasta
        $caminho_foto = __DIR__ . '/animais_fotos/' . $animal['foto_animal'];
        if (file_exists($caminho_foto)) {
            unlink($caminho_foto); // A função unlink() apaga o ficheiro
        }

        // 4. Excluir o registo do animal do banco de dados
        $stmt_delete = $pdo->prepare("DELETE FROM animais WHERE id = ?");
        $stmt_delete->execute([$animal_id]);

        // 5. Definir mensagem de sucesso e redirecionar
        $_SESSION['success_message_perfil'] = "Animal excluído com sucesso!";
    } else {
        // Se o animal não existe ou não pertence ao utilizador
        $_SESSION['error_message_perfil'] = "Operação não permitida.";
    }

} catch (PDOException $e) {
    $_SESSION['error_message_perfil'] = "Ocorreu um erro ao excluir o animal.";
    // Para depuração: error_log($e->getMessage());
}

// Redirecionar de volta para o perfil
header('Location: perfil.php');
exit();
?>
