<?php
require_once 'config.php';

// Se já estiver logado, redireciona para a página principal
if (is_logged_in()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$nome = '';
$email = '';
$cidade = '';
$telefone = '';

// Verifica se o formulário foi submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar e limpar os dados
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $cidade = isset($_POST['cidade']) ? trim($_POST['cidade']) : '';
    $telefone = isset($_POST['telefone']) ? trim($_POST['telefone']) : '';
    $senha = isset($_POST['senha']) ? $_POST['senha'] : '';
    $confirmar_senha = isset($_POST['confirmar_senha']) ? $_POST['confirmar_senha'] : '';
    $foto_perfil = isset($_FILES['foto_perfil']) ? $_FILES['foto_perfil'] : null;

    // Validações
    if (empty($nome)) $errors[] = "O campo nome é obrigatório.";
    if (empty($cidade)) $errors[] = "O campo cidade é obrigatório.";
    if (empty($telefone)) $errors[] = "O campo telefone é obrigatório.";
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, insira um email válido.";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = "Este email já está registado.";
        }
    }

    if (strlen($senha) < 8) {
        $errors[] = "A senha deve ter pelo menos 8 caracteres.";
    } elseif ($senha !== $confirmar_senha) {
        $errors[] = "As senhas não coincidem.";
    }

    $nome_foto = 'default.png'; // Manter uma foto padrão
    if ($foto_perfil && $foto_perfil['error'] === UPLOAD_ERR_OK) {
        $tipo_permitido = ['jpg', 'jpeg', 'png', 'gif'];
        $extensao = strtolower(pathinfo($foto_perfil['name'], PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $tipo_permitido)) {
            $errors[] = "Formato de imagem inválido. Apenas JPG, PNG e GIF são permitidos.";
        } elseif ($foto_perfil['size'] > 5 * 1024 * 1024) { // 5MB
            $errors[] = "A imagem não pode ter mais de 5MB.";
        } else {
            $nome_foto = 'user_' . uniqid('', true) . '.' . $extensao;
        }
    }

    // 2. Se não houver erros, processar os dados
    if (empty($errors)) {
        if ($nome_foto !== 'default.png') {
            if (!is_dir(UPLOADS_PATH_PERFIL)) mkdir(UPLOADS_PATH_PERFIL, 0777, true);
            if (!move_uploaded_file($foto_perfil['tmp_name'], UPLOADS_PATH_PERFIL . $nome_foto)) {
                 $errors[] = "Ocorreu um erro ao carregar a sua foto.";
                 $nome_foto = 'default.png'; // Volta para o padrão em caso de erro
            }
        }

        if(empty($errors)) {
            $salt_chars = array_merge(range('A','Z'), range('a','z'), range(0,9));
            $salt = '$2y$10$';
            for($i = 0; $i < 22; $i++) {
              $salt .= $salt_chars[array_rand($salt_chars)];
            }
            $senha_hash = crypt($senha, $salt);

            try {
                $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, foto_perfil, cidade, telefone) VALUES (:nome, :email, :senha, :foto, :cidade, :telefone)");
                $stmt->execute([
                    'nome' => $nome,
                    'email' => $email,
                    'senha' => $senha_hash,
                    'foto' => $nome_foto,
                    'cidade' => $cidade,
                    'telefone' => $telefone
                ]);
                
                $_SESSION['success_message'] = "Registo concluído com sucesso! Faça o login.";
                header("Location: login.php");
                exit();

            } catch (PDOException $e) {
                $errors[] = "Erro ao registar utilizador. Por favor, tente novamente.";
                error_log($e->getMessage());
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registo - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .form-page { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 76px); padding: 2rem; }
        
        .form-container { 
            background: rgba(44, 62, 80, 0.7); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px); 
            border: 1px solid rgba(0, 170, 255, 0.3);
            border-radius: var(--border-radius); 
            padding: 2.5rem 3rem; 
            width: 100%; 
            max-width: 450px; 
            box-shadow: 0 0 25px rgba(0, 170, 255, 0.5), 0 0 10px rgba(0, 255, 155, 0.3);
            text-align: center; 
            color: var(--text-color); 
            transition: box-shadow 0.4s ease, border-color 0.4s ease;
        }

        .form-title { font-size: 2.2rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--white); }
        .form-subtitle { margin-bottom: 1.5rem; color: #aab; }
        .form-layout { display: flex; flex-direction: column; gap: 1.2rem; text-align: left; }
        .form-group { position: relative; }
        .form-input { width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 0.8rem 1rem 0.8rem 2.5rem; color: var(--white); font-size: 1rem; transition: all 0.3s ease; box-sizing: border-box; }
        .form-input:focus { outline: none; background: rgba(255, 255, 255, 0.1); border-color: var(--primary-accent); box-shadow: 0 0 0 3px rgba(0, 170, 255, 0.3); }
        .form-group i { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: #aab; transition: color 0.3s ease; }
        .form-input:focus ~ i { color: var(--primary-accent); }
        .form-group-photo { display: flex; flex-direction: column; align-items: center; margin-bottom: 1rem; }
        .form-group-photo label { cursor: pointer; text-align: center; position: relative; }
        .profile-pic-preview { width: 120px; height: 120px; border-radius: 50%; border: 4px solid rgba(255, 255, 255, 0.2); object-fit: cover; transition: all 0.3s ease; background-color: #34495e; }
        .form-group-photo label:hover .profile-pic-preview { border-color: var(--primary-accent); filter: brightness(0.8); }
        .form-group-photo .upload-icon { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: rgba(0,0,0,0.5); color: white; padding: 0.8rem; border-radius: 50%; font-size: 1.5rem; opacity: 0; transition: opacity 0.3s ease; }
        .form-group-photo label:hover .upload-icon { opacity: 1; }
        .photo-label-text { color: #aab; font-size: 0.9rem; margin-top: 0.5rem; }
        .btn-form { margin-top: 1rem; padding: 0.8rem; font-size: 1.1rem; }
        .form-switch { margin-top: 1.5rem; font-size: 0.9rem; }
        .form-switch a { color: var(--secondary-accent); font-weight: 600; text-decoration: none; }
        .form-switch a:hover { text-decoration: underline; }
        .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: 8px; text-align: left; font-size: 0.9rem; }
        .alert-error { background-color: rgba(231, 76, 60, 0.2); border: 1px solid rgba(231, 76, 60, 0.4); color: #f8d7da; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container">
            <h1 class="form-title">Criar Conta</h1>
            <p class="form-subtitle">Junte-se à nossa comunidade e ajude a mudar vidas.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST" enctype="multipart/form-data" class="form-layout">
                
                <div class="form-group-photo">
                    <label for="foto_perfil">
                        <img src="" alt="" id="image-preview" class="profile-pic-preview">
                        <div class="upload-icon"><i class="fas fa-camera"></i></div>
                    </label>
                    <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" style="display: none;">
                    <p class="photo-label-text">Foto de Perfil (Opcional)</p>
                </div>

                <div class="form-group">
                    <input type="text" id="nome" name="nome" class="form-input" placeholder="Nome Completo" required value="<?php echo htmlspecialchars($nome); ?>">
                    <i class="fas fa-user"></i>
                </div>
                 <div class="form-group">
                    <input type="email" id="email" name="email" class="form-input" placeholder="Seu melhor email" required value="<?php echo htmlspecialchars($email); ?>">
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="form-group">
                    <input type="text" id="cidade" name="cidade" class="form-input" placeholder="Cidade" required value="<?php echo htmlspecialchars($cidade); ?>">
                    <i class="fas fa-city"></i>
                </div>
                <div class="form-group">
                    <input type="tel" id="telefone" name="telefone" class="form-input" placeholder="Telefone" required value="<?php echo htmlspecialchars($telefone); ?>">
                    <i class="fas fa-phone"></i>
                </div>
                <div class="form-group">
                    <input type="password" id="senha" name="senha" class="form-input" placeholder="Senha (mín. 8 caracteres)" required>
                    <i class="fas fa-lock"></i>
                </div>
                <div class="form-group">
                    <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-input" placeholder="Confirme a senha" required>
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="btn btn-register btn-form">Registar</button>
            </form>
            <p class="form-switch">Já tem uma conta? <a href="login.php">Faça Login</a></p>
        </div>
    </main>

    <script>
        document.getElementById('foto_perfil').addEventListener('change', function(event) {
            const preview = document.getElementById('image-preview');
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onloadend = function() {
                    preview.src = reader.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>

</body>
</html>

