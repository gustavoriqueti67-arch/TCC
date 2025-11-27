<?php
require_once 'config.php';

// Apenas administradores podem aceder
if (!is_admin()) {
    header('Location: index.php');
    exit();
}

// Verificar CSRF se for POST (Segurança adicional)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $_SESSION['error_message_admin'] = 'Erro de segurança (Token CSRF inválido). Tente novamente.';
        header('Location: admin.php?tab=usuarios');
        exit();
    }
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$user_id_to_manage = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Define a URL de retorno correta (para a aba de usuários no admin.php)
$redirect_url = 'admin.php?tab=usuarios';

// Segurança: um admin não pode modificar a sua própria conta
if ($user_id_to_manage === (int)$_SESSION['user_id']) {
    $_SESSION['error_message_admin'] = 'Você não pode alterar o seu próprio nível de acesso.';
    header('Location: ' . $redirect_url);
    exit();
}

try {
    if ($user_id_to_manage > 0) {
        if ($action === 'promote') {
            // Promover para admin
            $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'admin' WHERE id = ?");
            $stmt->execute([$user_id_to_manage]);
            $_SESSION['success_message_admin'] = 'Usuário promovido a Administrador com sucesso!';
            
        } elseif ($action === 'demote') {
            // Rebaixar para user
            $stmt = $pdo->prepare("UPDATE usuarios SET nivel_acesso = 'user' WHERE id = ?");
            $stmt->execute([$user_id_to_manage]);
            $_SESSION['success_message_admin'] = 'Usuário rebaixado para nível padrão com sucesso!';
            
        } elseif ($action === 'delete') {
            // Excluir utilizador (isto também irá excluir animais e posts em cascata, se configurado no DB)
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$user_id_to_manage]);
            $_SESSION['success_message_admin'] = 'Usuário excluído com sucesso!';
        }
    } else {
        $_SESSION['error_message_admin'] = 'ID de usuário inválido.';
    }
} catch (PDOException $e) {
    $_SESSION['error_message_admin'] = 'Erro ao processar a ação: ' . $e->getMessage();
}

// Redirecionar de volta para a página de administração correta
header('Location: ' . $redirect_url);
exit();
?>