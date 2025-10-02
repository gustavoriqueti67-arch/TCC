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
        .form-page {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 4rem 1.5rem;
        }
        .form-container {
            width: 100%;
            max-width: 700px;
        }
        .form-layout {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .form-input-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
        }
        .foto-atual-preview {
            max-width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
            display: block;
            border: 2px solid var(--border-color);
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container animal-card">
            <h1 class="form-title">Editar Dados do Pet</h1>
            <p class="form-subtitle">Atualize as informações de <?php echo htmlspecialchars($animal['nome']); ?>.</p>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form action="editar_animal.php?id=<?php echo $animal_id; ?>" method="POST" enctype="multipart/form-data" class="form-layout">
                
                <div class="form-group">
                    <label class="form-label">Foto Atual</label>
                    <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="Foto atual" class="foto-atual-preview">
                    <label for="foto_animal" class="form-label">Trocar Foto (Opcional)</label>
                    <input type="file" name="foto_animal" class="form-input">
                </div>

                <div class="form-group">
                    <label for="nome" class="form-label">Nome do Animal *</label>
                    <input type="text" name="nome" value="<?php echo htmlspecialchars($animal['nome']); ?>" class="form-input" required>
                </div>

                <div class="form-input-grid">
                    <div class="form-group">
                        <label for="especie" class="form-label">Espécie *</label>
                        <select name="especie" class="form-input" required>
                            <option value="Cachorro" <?php echo ($animal['especie'] == 'Cachorro') ? 'selected' : ''; ?>>Cachorro</option>
                            <option value="Gato" <?php echo ($animal['especie'] == 'Gato') ? 'selected' : ''; ?>>Gato</option>
                            <option value="Outro" <?php echo ($animal['especie'] == 'Outro') ? 'selected' : ''; ?>>Outro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sexo" class="form-label">Sexo *</label>
                        <select name="sexo" class="form-input" required>
                            <option value="Macho" <?php echo ($animal['sexo'] == 'Macho') ? 'selected' : ''; ?>>Macho</option>
                            <option value="Fêmea" <?php echo ($animal['sexo'] == 'Fêmea') ? 'selected' : ''; ?>>Fêmea</option>
                        </select>
                    </div>
                </div>

                <div class="form-input-grid">
                    <div class="form-group">
                        <label for="raca" class="form-label">Raça (Opcional)</label>
                        <input type="text" name="raca" value="<?php echo htmlspecialchars($animal['raca']); ?>" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="idade" class="form-label">Idade *</label>
                        <input type="text" name="idade" value="<?php echo htmlspecialchars($animal['idade']); ?>" class="form-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="telefone_contato" class="form-label">Telefone para Contato *</label>
                    <input type="text" name="telefone_contato" value="<?php echo htmlspecialchars($animal['telefone_contato']); ?>" class="form-input" required>
                </div>

                <div class="form-input-grid">
                    <div class="form-group">
                        <label for="cidade" class="form-label">Cidade *</label>
                        <input type="text" name="cidade" value="<?php echo htmlspecialchars($animal['cidade']); ?>" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="endereco" class="form-label">Bairro/Rua *</label>
                        <input type="text" name="endereco" value="<?php echo htmlspecialchars($animal['endereco']); ?>" class="form-input" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="descricao" class="form-label">Descrição *</label>
                    <textarea name="descricao" class="form-input" rows="4" required><?php echo htmlspecialchars($animal['descricao']); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-register">Salvar Alterações</button>
            </form>
        </div>
    </main>
</body>
</html>

