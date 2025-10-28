<?php
// Inicia a sessão para poder aceder e destruí-la
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Apaga todas as variáveis da sessão
$_SESSION = [];

// 2. Se for desejado destruir a sessão, apaga também o cookie da sessão.
// Nota: Isto destruirá a sessão, e não apenas os dados da sessão!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Finalmente, destrói a sessão.
session_destroy();

// 4. Redireciona para a página inicial (ou para a página de login)
header("Location: index.php");
exit();
?>
