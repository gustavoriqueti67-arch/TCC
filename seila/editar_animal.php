<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// 1. Verificar se um ID de animal foi fornecido e se o animal pertence ao utilizador
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: perfil.php');
    exit();
}

$animal_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Buscar os dados atuais do animal para preencher o formulário
$stmt = $pdo->prepare("SELECT * FROM animais WHERE id = ? AND id_usuario = ?");
$stmt->execute([$animal_id, $user_id]);
$animal = $stmt->fetch();

// Se o animal não for encontrado ou não pertencer ao utilizador, redireciona
if (!$animal) {
    $_SESSION['error_message_perfil'] = "Animal não encontrado ou operação não permitida.";
    header('Location: perfil.php');
    exit();
}

$errors = [];
// 2. Processar o formulário quando for submetido
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Falha de segurança: token CSRF inválido. Recarregue a página.';
    }
    if (empty($errors)) {
    // Validar e limpar os dados
    $nome = trim($_POST['nome']);
    $especie = trim($_POST['especie']);
    $raca = trim($_POST['raca']);
    $idade = trim($_POST['idade']);
    $sexo = trim($_POST['sexo']);
    $descricao = trim($_POST['descricao']);
    $telefone_contato = trim($_POST['telefone_contato']);
    $cidade = trim($_POST['cidade']);
    $endereco = trim($_POST['endereco']);

    if (empty($nome) || empty($especie) || empty($idade) || empty($sexo) || empty($descricao) || empty($telefone_contato) || empty($cidade) || empty($endereco)) {
        $errors[] = "Todos os campos marcados com * são obrigatórios.";
    }

    }
    if (empty($errors)) {
        $foto_atual = $animal['foto_animal'];
        $nome_foto = $foto_atual;

        if (isset($_FILES['foto_animal']) && $_FILES['foto_animal']['error'] === UPLOAD_ERR_OK) {
            $nova_foto = $_FILES['foto_animal'];
            $tipo_permitido = ['jpg', 'jpeg', 'png', 'gif'];
            $extensao = strtolower(pathinfo($nova_foto['name'], PATHINFO_EXTENSION));

            if (in_array($extensao, $tipo_permitido) && $nova_foto['size'] <= 5 * 1024 * 1024) {
                $nome_foto = 'animal_' . uniqid() . '.' . $extensao;
                if(move_uploaded_file($nova_foto['tmp_name'], __DIR__ . '/animais_fotos/' . $nome_foto)) {
                    if ($foto_atual && file_exists(__DIR__ . '/animais_fotos/' . $foto_atual)) {
                        unlink(__DIR__ . '/animais_fotos/' . $foto_atual);
                    }
                } else {
                    $errors[] = "Erro ao carregar a nova foto.";
                    $nome_foto = $foto_atual; 
                }
            } else {
                 $errors[] = "Ficheiro de imagem inválido ou demasiado grande (máx 5MB).";
            }
        }

        if (empty($errors)) {
            try {
                $stmt_update = $pdo->prepare(
                    "UPDATE animais SET nome = ?, especie = ?, raca = ?, idade = ?, sexo = ?, descricao = ?, foto_animal = ?, telefone_contato = ?, cidade = ?, endereco = ?
                     WHERE id = ? AND id_usuario = ?"
                );
                $stmt_update->execute([
                    $nome, $especie, $raca, $idade, $sexo, $descricao, $nome_foto, 
                    $telefone_contato, $cidade, $endereco, $animal_id, $user_id
                ]);

                $_SESSION['success_message_perfil'] = "Dados do animal atualizados com sucesso!";
                header('Location: perfil.php');
                exit();

            } catch (PDOException $e) {
                $errors[] = "Erro ao atualizar os dados do animal.";
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
    <title>Editar Dados de <?php echo htmlspecialchars($animal['nome']); ?></title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        main {
            background-color: var(--dark-bg);
            min-height: 100vh;
            padding: 0;
        }

        .edit-section {
            padding: 3rem 1.5rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            max-width: 800px;
            margin: 0 auto 2rem;
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-color-light);
            flex-wrap: wrap;
        }

        .breadcrumb-list a {
            color: var(--primary-accent);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .breadcrumb-list a:hover {
            opacity: 0.8;
        }

        /* Container do formulário */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        /* Header do formulário */
        .form-header {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .form-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--white);
            margin: 0 0 0.5rem 0;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.95rem;
            margin: 0;
        }

        /* Body do formulário */
        .form-body {
            padding: 2.5rem 2rem;
        }

        /* Alertas */
        .alert {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.5);
            color: #e74c3c;
        }

        .alert i {
            font-size: 1.25rem;
        }

        /* Preview da foto */
        .photo-preview-section {
            margin-bottom: 2rem;
            text-align: center;
        }

        .current-photo-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-color-light);
            margin-bottom: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .photo-preview-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }

        .photo-preview {
            width: 200px;
            height: 200px;
            object-fit: cover;
            border-radius: 16px;
            border: 3px solid var(--primary-accent);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .photo-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary-accent);
            color: var(--dark-bg);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        /* Upload de arquivo customizado */
        .file-upload-wrapper {
            position: relative;
            margin-top: 1rem;
        }

        .file-upload-label {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.875rem 1.5rem;
            background: var(--dark-bg);
            color: var(--primary-accent);
            border: 2px solid var(--primary-accent);
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: var(--primary-accent);
            color: var(--dark-bg);
        }

        .file-upload-input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        /* Grupos de formulário */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: var(--primary-accent);
            margin-left: 0.25rem;
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 0.875rem 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--white);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            background: var(--dark-bg-alt);
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }

        /* Grid de campos */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        /* Botões de ação */
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 228, 255, 0.3);
        }

        .btn-secondary {
            background: var(--dark-bg);
            color: var(--text-color-light);
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .btn-secondary:hover {
            border-color: var(--text-color-light);
            color: var(--white);
        }

        /* Dicas */
        .form-hint {
            font-size: 0.85rem;
            color: var(--text-color-light);
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-hint i {
            color: var(--primary-accent);
            font-size: 0.9rem;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .form-header h1 {
                font-size: 1.5rem;
            }

            .form-body {
                padding: 2rem 1.5rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .photo-preview {
                width: 150px;
                height: 150px;
            }
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <section class="edit-section">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <nav class="breadcrumb-list">
                    <a href="index.php">Início</a>
                    <span>/</span>
                    <a href="perfil.php">Meu Perfil</a>
                    <span>/</span>
                    <span>Editar Animal</span>
                </nav>
            </div>

            <div class="form-container">
                <!-- Header -->
                <div class="form-header">
                    <h1><i class="fas fa-edit"></i> Editar Dados do Pet</h1>
                    <p>Atualize as informações de <?php echo htmlspecialchars($animal['nome']); ?></p>
                </div>

                <!-- Body -->
                <div class="form-body">
                    <?php if (!empty($errors)): ?>
                        <div class="alert">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div>
                                <?php foreach ($errors as $error): ?>
                                    <p style="margin: 0;"><?php echo htmlspecialchars($error); ?></p>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form action="editar_animal.php?id=<?php echo $animal_id; ?>" method="POST" enctype="multipart/form-data">
                        <?php csrf_input_field(); ?>
                        
                        <!-- Preview da Foto -->
                        <div class="photo-preview-section">
                            <span class="current-photo-label">Foto Atual</span>
                            <div class="photo-preview-container">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" 
                                     alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>" 
                                     class="photo-preview"
                                     id="photoPreview">
                                <span class="photo-badge">
                                    <i class="fas fa-camera"></i> Atual
                                </span>
                            </div>
                            <div class="file-upload-wrapper">
                                <label for="foto_animal" class="file-upload-label">
                                    <i class="fas fa-upload"></i>
                                    Trocar Foto
                                </label>
                                <input type="file" 
                                       name="foto_animal" 
                                       id="foto_animal" 
                                       class="file-upload-input"
                                       accept="image/*"
                                       onchange="previewImage(event)">
                            </div>
                            <p class="form-hint">
                                <i class="fas fa-info-circle"></i>
                                Formatos aceitos: JPG, PNG, GIF (máx. 5MB)
                            </p>
                        </div>

                        <!-- Nome do Animal -->
                        <div class="form-group">
                            <label for="nome" class="form-label">
                                Nome do Animal<span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nome" 
                                   id="nome"
                                   value="<?php echo htmlspecialchars($animal['nome']); ?>" 
                                   class="form-input" 
                                   required
                                   placeholder="Ex: Rex, Mimi, Bob...">
                        </div>

                        <!-- Espécie e Sexo -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="especie" class="form-label">
                                    Espécie<span class="required">*</span>
                                </label>
                                <select name="especie" id="especie" class="form-select" required>
                                    <option value="Cachorro" <?php echo ($animal['especie'] == 'Cachorro') ? 'selected' : ''; ?>>🐕 Cachorro</option>
                                    <option value="Gato" <?php echo ($animal['especie'] == 'Gato') ? 'selected' : ''; ?>>🐈 Gato</option>
                                    <option value="Outro" <?php echo ($animal['especie'] == 'Outro') ? 'selected' : ''; ?>>🦜 Outro</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="sexo" class="form-label">
                                    Sexo<span class="required">*</span>
                                </label>
                                <select name="sexo" id="sexo" class="form-select" required>
                                    <option value="Macho" <?php echo ($animal['sexo'] == 'Macho') ? 'selected' : ''; ?>>♂️ Macho</option>
                                    <option value="Fêmea" <?php echo ($animal['sexo'] == 'Fêmea') ? 'selected' : ''; ?>>♀️ Fêmea</option>
                                </select>
                            </div>
                        </div>

                        <!-- Raça e Idade -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="raca" class="form-label">Raça</label>
                                <input type="text" 
                                       name="raca" 
                                       id="raca"
                                       value="<?php echo htmlspecialchars($animal['raca']); ?>" 
                                       class="form-input"
                                       placeholder="Ex: Vira-lata, Siamês...">
                                <p class="form-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Opcional
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="idade" class="form-label">
                                    Idade<span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="idade" 
                                       id="idade"
                                       value="<?php echo htmlspecialchars($animal['idade']); ?>" 
                                       class="form-input" 
                                       required
                                       placeholder="Ex: 2 anos, 6 meses...">
                            </div>
                        </div>

                        <!-- Telefone -->
                        <div class="form-group">
                            <label for="telefone_contato" class="form-label">
                                Telefone para Contato<span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="telefone_contato" 
                                   id="telefone_contato"
                                   value="<?php echo htmlspecialchars($animal['telefone_contato']); ?>" 
                                   class="form-input" 
                                   required
                                   placeholder="Ex: (11) 99999-9999">
                        </div>

                        <!-- Cidade e Endereço -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="cidade" class="form-label">
                                    Cidade<span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="cidade" 
                                       id="cidade"
                                       value="<?php echo htmlspecialchars($animal['cidade']); ?>" 
                                       class="form-input" 
                                       required
                                       placeholder="Ex: São Paulo">
                            </div>
                            <div class="form-group">
                                <label for="endereco" class="form-label">
                                    Bairro/Rua<span class="required">*</span>
                                </label>
                                <input type="text" 
                                       name="endereco" 
                                       id="endereco"
                                       value="<?php echo htmlspecialchars($animal['endereco']); ?>" 
                                       class="form-input" 
                                       required
                                       placeholder="Ex: Centro, Av. Paulista...">
                            </div>
                        </div>
                        
                        <!-- Descrição -->
                        <div class="form-group">
                            <label for="descricao" class="form-label">
                                Descrição<span class="required">*</span>
                            </label>
                            <textarea name="descricao" 
                                      id="descricao"
                                      class="form-textarea" 
                                      required
                                      placeholder="Conte um pouco sobre a personalidade, comportamento e histórico do pet..."><?php echo htmlspecialchars($animal['descricao']); ?></textarea>
                            <p class="form-hint">
                                <i class="fas fa-lightbulb"></i>
                                Quanto mais detalhes, maior a chance de encontrar um lar perfeito!
                            </p>
                        </div>
                        
                        <!-- Botões de Ação -->
                        <div class="form-actions">
                            <a href="perfil.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('photoPreview').src = e.target.result;
                    document.querySelector('.photo-badge').innerHTML = '<i class="fas fa-check"></i> Nova';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>