<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id_to_manage = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Segurança: um admin não pode modificar a sua própria conta
if ($user_id_to_manage === (int)$_SESSION['user_id']) {
    header('Location: admin_usuarios.php');
    exit();
}

try {
    if ($action === 'promote' && $user_id_to_manage > 0) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'admin' WHERE id = ?");
        $stmt->execute([$user_id_to_manage]);
    } elseif ($action === 'demote' && $user_id_to_manage > 0) {
        $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'user' WHERE id = ?");
        $stmt->execute([$user_id_to_manage]);
    } elseif ($action === 'delete' && $user_id_to_manage > 0) {
        // Excluir utilizador (isto também irá excluir animais e posts em cascata, se configurado no DB)
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$user_id_to_manage]);
    }
} catch (PDOException $e) {
    // Lidar com erros, se necessário
    // error_log($e->getMessage());
}

// Redirecionar de volta para a página de gestão de utilizadores
header('Location: admin_usuarios.php');
exit();
?>
