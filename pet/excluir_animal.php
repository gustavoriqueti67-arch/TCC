<?php
require_once 'config.php';

// 1. Definir URL de retorno e chaves de sessão
$return_url = 'perfil.php'; // Padrão
$success_key = 'success_message_perfil';
$error_key = 'error_message_perfil';

// Verifica de onde o formulário foi enviado
if (isset($_POST['return_to'])) {
    if ($_POST['return_to'] === 'perfil.php') {
        $return_url = 'perfil.php';
    } elseif (strpos($_POST['return_to'], 'admin.php') === 0) {
        // Se veio do admin, usa as chaves de sessão do admin e a URL de retorno do admin
        $return_url = $_POST['return_to'];
        $success_key = 'success_message_admin';
        $error_key = 'error_message_admin';
    }
}

// 2. Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// 3. Excluir deve ser via POST com CSRF válido
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
    $_SESSION[$error_key] = 'Requisição inválida.';
    header('Location: ' . $return_url);
    exit();
}

// 4. Validar ID
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    header('Location: ' . $return_url);
    exit();
}

$animal_id = (int)$_POST['id'];
$user_id = $_SESSION['user_id'];

try {
    // 5. Buscar o animal pelo ID (para verificar permissão e pegar foto)
    $stmt = $pdo->prepare("SELECT foto_animal, id_usuario FROM animais WHERE id = ?");
    $stmt->execute([$animal_id]);
    $animal = $stmt->fetch();

    if ($animal) {
        // 6. Verificar permissão (Admin OU Dono)
        if (is_admin() || $animal['id_usuario'] == $user_id) {
            
            // 7. Excluir a foto
            $caminho_foto = __DIR__ . '/animais_fotos/' . $animal['foto_animal'];
            if ($animal['foto_animal'] && file_exists($caminho_foto)) {
                unlink($caminho_foto);
            }

            // 8. Excluir do banco de dados
            $stmt_delete = $pdo->prepare("DELETE FROM animais WHERE id = ?");
            $stmt_delete->execute([$animal_id]);

            $_SESSION[$success_key] = 'Animal excluído com sucesso!';
        } else {
            // Não é admin E não é o dono
            $_SESSION[$error_key] = 'Operação não permitida.';
        }
    } else {
        // Animal não existe
         $_SESSION[$error_key] = 'Animal não encontrado.';
    }

} catch (PDOException $e) {
    $_SESSION[$error_key] = 'Ocorreu um erro ao excluir o animal.';
    // Para depuração: error_log($e->getMessage());
}

// 9. Redirecionar de volta para a página de origem
header('Location: ' . $return_url);
exit();
?>