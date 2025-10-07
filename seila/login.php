<?php
require_once 'config.php';

$errors = [];
$email = '';

// Se já estiver logado, redireciona para a página principal
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : null;
unset($_SESSION['success_message']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';

    if (empty($email) || empty($senha)) {
        $errors[] = "Todos os campos são obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // Lógica de verificação de senha para versões mais antigas do PHP
            if ($user && crypt($senha, $user['senha']) === $user['senha']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_photo'] = $user['foto_perfil'];
                $_SESSION['user_level'] = $user['nivel_acesso']; // <<< CORREÇÃO AQUI

                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Email ou senha inválidos.";
            }

        } catch (PDOException $e) {
            $errors[] = "Erro no servidor. Tente novamente mais tarde.";
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
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-input" required value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label for="senha" class="form-label">Senha</label>
                    <input type="password" id="senha" name="senha" class="form-input" required>
                </div>

                <button type="submit" class="btn btn-register btn-form">Login</button>
            </form>
            <p style="text-align: center; margin-top: 1.5rem;">Não tem uma conta? <a href="register.php" style="color: var(--primary-accent); font-weight: bold;">Registe-se</a></p>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>

</body>
</html>

