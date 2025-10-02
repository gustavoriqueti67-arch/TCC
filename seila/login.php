<?php
require_once 'config.php';

$errors = [];
$email = '';

// Se já estiver logado, redireciona para a página principal
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

// Pega a mensagem de sucesso do registo, se existir
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
if (isset($_SESSION['success_message'])) {
    unset($_SESSION['success_message']);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($email) || empty($senha)) {
        $errors[] = "Todos os campos são obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            // --- CORREÇÃO PARA PHP ANTIGO ---
            // Substituído password_verify() por uma verificação com crypt()
            // para ser compatível com a forma como a senha foi guardada no registo.
            if ($user && $user['senha'] === crypt($senha, $user['senha'])) {
                // Login bem-sucedido, guardar dados na sessão
                session_regenerate_id(true); // Prevenção de session fixation
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_photo'] = $user['foto_perfil'];

                header("Location: index.php"); // Redirecionar para a página principal
                exit();
            } else {
                $errors[] = "Email ou senha inválidos.";
            }

        } catch (PDOException $e) {
            $errors[] = "Erro no servidor. Tente novamente mais tarde.";
            error_log($e->getMessage()); // Para depuração
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .form-page { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 76px); padding: 2rem; }
        .form-container { background: rgba(44, 62, 80, 0.7); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(0, 170, 255, 0.3); border-radius: var(--border-radius); padding: 2.5rem 3rem; width: 100%; max-width: 450px; box-shadow: 0 0 25px rgba(0, 170, 255, 0.5), 0 0 10px rgba(0, 255, 155, 0.3); text-align: center; color: var(--text-color); }
        .form-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--white); }
        .form-subtitle { margin-bottom: 2rem; color: #aab; }
        .form-layout { display: flex; flex-direction: column; gap: 1.5rem; text-align: left; }
        .form-group { position: relative; }
        .form-input { width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 0.8rem 1rem 0.8rem 2.5rem; color: var(--white); font-size: 1rem; transition: all 0.3s ease; box-sizing: border-box; }
        .form-input:focus { outline: none; background: rgba(255, 255, 255, 0.1); border-color: var(--primary-blue); box-shadow: 0 0 0 3px rgba(0, 170, 255, 0.3); }
        .form-group i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #aab; transition: color 0.3s ease; }
        .form-input:focus ~ i { color: var(--primary-blue); }
        .btn-form { margin-top: 1rem; padding: 0.8rem; font-size: 1.1rem; }
        .form-switch { margin-top: 2rem; font-size: 0.9rem; }
        .form-switch a { color: var(--accent-green); font-weight: 600; text-decoration: none; }
        .form-switch a:hover { text-decoration: underline; }
        .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; text-align: left; font-size: 0.9rem; }
        .alert-error { background-color: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.4); color: #f8d7da; }
        .alert-success { background-color: rgba(46, 204, 113, 0.2); border-color: rgba(46, 204, 113, 0.4); color: #d4edda; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container">
            <h1 class="form-title">Bem-vindo de Volta!</h1>
            <p class="form-subtitle">Faça login para continuar a sua jornada.</p>

            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="form-layout">
                <div class="form-group">
                    <input type="email" id="email" name="email" class="form-input" placeholder="Email" required value="<?php echo htmlspecialchars($email); ?>">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="form-group">
                    <input type="password" id="senha" name="senha" class="form-input" placeholder="Senha" required>
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="btn btn-register btn-form">Login</button>
            </form>
            <p class="form-switch">Não tem uma conta? <a href="register.php">Registe-se</a></p>
        </div>
    </main>

</body>
</html>

