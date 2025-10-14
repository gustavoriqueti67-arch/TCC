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
            
            if ($user && crypt($senha, $user['senha']) === $user['senha']) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['nome'];
                $_SESSION['user_photo'] = $user['foto_perfil'];
                $_SESSION['user_level'] = $user['nivel_acesso'];

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
    <style>
        main {
            background: #0f0f1e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            background: #1a1a2e;
            border-radius: 20px;
            border: 1px solid rgba(0, 228, 255, 0.2);
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.5s ease;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            background: linear-gradient(135deg, #00e4ff, #0ba8e6);
            padding: 3rem 2rem;
            text-align: center;
            position: relative;
        }

        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: #1a1a2e;
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .login-icon {
            width: 80px;
            height: 80px;
            background: rgba(26, 26, 46, 0.3);
            backdrop-filter: blur(10px);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: #1a1a2e;
            border: 3px solid rgba(26, 26, 46, 0.2);
        }

        .login-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a2e;
            margin: 0 0 0.5rem 0;
        }

        .login-subtitle {
            color: rgba(26, 26, 46, 0.8);
            font-size: 0.95rem;
            margin: 0;
        }

        .login-body {
            padding: 2.5rem 2rem;
        }

        /* Alertas */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .alert i {
            font-size: 1.25rem;
        }

        .alert-success {
            background: rgba(46, 204, 113, 0.15);
            border: 1px solid rgba(46, 204, 113, 0.5);
            color: #2ecc71;
        }

        .alert-error {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
        }

        /* Formulário */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #00e4ff;
            font-size: 1rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 3rem;
            background: #0f0f1e;
            border: 2px solid rgba(0, 228, 255, 0.2);
            border-radius: 10px;
            color: #ffffff;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: #00e4ff;
            background: #1a1a2e;
        }

        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* Botão de Login */
        .btn-login {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #00e4ff, #2ecc71);
            color: #0f0f1e;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 228, 255, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        /* Links */
        .login-footer {
            text-align: center;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .login-footer p {
            color: #a0a0a0;
            font-size: 0.95rem;
            margin: 0;
        }

        .login-footer a {
            color: #00e4ff;
            font-weight: 600;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .login-footer a:hover {
            opacity: 0.8;
        }

        /* Opções extras */
        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        .remember-me label {
            font-size: 0.9rem;
            color: #a0a0a0;
            cursor: pointer;
        }

        .forgot-password {
            font-size: 0.9rem;
            color: #00e4ff;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .forgot-password:hover {
            opacity: 0.8;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .login-container {
                max-width: 100%;
            }

            .login-header {
                padding: 2.5rem 1.5rem;
            }

            .login-title {
                font-size: 1.75rem;
            }

            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="login-container">
            <!-- Header -->
            <div class="login-header">
                <div class="login-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1 class="login-title">Bem-vindo de Volta!</h1>
                <p class="login-subtitle">Faça login para continuar a sua jornada</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <!-- Mensagem de Sucesso -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <span><?php echo htmlspecialchars($success_message); ?></span>
                    </div>
                <?php endif; ?>

                <!-- Mensagens de Erro -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div>
                            <?php foreach ($errors as $error): ?>
                                <p style="margin: 0;"><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Formulário -->
                <form action="login.php" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   class="form-input" 
                                   placeholder="seu@email.com"
                                   required 
                                   value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha" class="form-label">Senha</label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   id="senha" 
                                   name="senha" 
                                   class="form-input" 
                                   placeholder="••••••••"
                                   required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">
                        <i class="fas fa-sign-in-alt"></i>
                        Entrar
                    </button>
                </form>

                <!-- Footer -->
                <div class="login-footer">
                    <p>
                        Não tem uma conta? 
                        <a href="register.php">Registe-se agora</a>
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>

</body>
</html>