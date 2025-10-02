<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validar e limpar os dados
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $especie = isset($_POST['especie']) ? trim($_POST['especie']) : '';
    $raca = isset($_POST['raca']) ? trim($_POST['raca']) : '';
    $idade = isset($_POST['idade']) ? trim($_POST['idade']) : '';
    $sexo = isset($_POST['sexo']) ? trim($_POST['sexo']) : '';
    $descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $telefone_contato = isset($_POST['telefone_contato']) ? trim($_POST['telefone_contato']) : '';
    $endereco = isset($_POST['endereco']) ? trim($_POST['endereco']) : '';
    $cidade = isset($_POST['cidade']) ? trim($_POST['cidade']) : '';
    
    // Recebe a imagem cortada como uma string base64
    $foto_animal_base64 = isset($_POST['foto_animal']) ? $_POST['foto_animal'] : '';

    // Validações básicas
    if (empty($nome)) $errors[] = "O nome do animal é obrigatório.";
    if (empty($especie)) $errors[] = "A espécie é obrigatória.";
    if (empty($idade)) $errors[] = "A idade é obrigatória.";
    if (empty($sexo)) $errors[] = "O sexo é obrigatório.";
    if (empty($descricao)) $errors[] = "A descrição é obrigatória.";
    if (empty($telefone_contato)) $errors[] = "O telefone para contato é obrigatório.";
    if (empty($endereco)) $errors[] = "O endereço (bairro/rua) é obrigatório.";
    if (empty($cidade)) $errors[] = "A cidade é obrigatória.";

    // Validação da foto do animal (base64)
    $nome_foto = '';
    $decoded_image = null;
    if (!empty($foto_animal_base64)) {
        // Extrai a extensão e os dados da imagem
        preg_match('/^data:image\/(\w+);base64,/', $foto_animal_base64, $type);
        $extensao = isset($type[1]) ? strtolower($type[1]) : 'jpg';

        $img_data = substr($foto_animal_base64, strpos($foto_animal_base64, ',') + 1);
        $decoded_image = base64_decode($img_data);

        $tipo_permitido = ['jpeg', 'png', 'gif', 'jpg'];
        if (in_array($extensao, $tipo_permitido)) {
            if (strlen($decoded_image) <= 5 * 1024 * 1024) { // 5MB
                $nome_foto = 'animal_' . uniqid() . '.' . $extensao;
            } else {
                $errors[] = "A imagem não pode ter mais de 5MB.";
            }
        } else {
            $errors[] = "Formato de imagem inválido. Apenas JPG, PNG e GIF são permitidos.";
        }
    } else {
        $errors[] = "A foto do animal é obrigatória.";
    }

    // 2. Se não houver erros, processar os dados
    if (empty($errors)) {
        // Guardar a foto na pasta de uploads
        $upload_dir = __DIR__ . '/animais_fotos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        file_put_contents($upload_dir . $nome_foto, $decoded_image);

        // Inserir no banco de dados
        try {
            $stmt = $pdo->prepare("INSERT INTO animais (nome, especie, raca, idade, sexo, descricao, foto_animal, id_usuario, telefone_contato, endereco, cidade) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $especie, $raca, $idade, $sexo, $descricao, $nome_foto, $_SESSION['user_id'], $telefone_contato, $endereco, $cidade]);
            
            $success_message = "Animal registado com sucesso! Redirecionando...";
            header("Refresh:3; url=adote.php");

        } catch (PDOException $e) {
            $errors[] = "Erro ao registar o animal. Por favor, tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Animal - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .form-page { display: flex; justify-content: center; align-items: center; min-height: calc(100vh - 76px); padding: 2rem; }
        .form-container { background: var(--card-background); border-radius: var(--border-radius); padding: 2.5rem; width: 100%; max-width: 800px; box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37); text-align: center; color: var(--text-color); border: 1px solid rgba(255, 255, 255, 0.1); }
        .form-title { font-family: var(--font-headings); font-size: 2.5rem; margin-bottom: 2rem; color: var(--primary-accent); }
        .form-layout { display: grid; grid-template-columns: 1fr; gap: 1.5rem 2rem; text-align: left; }
        .form-group { display: flex; flex-direction: column; }
        .form-label { margin-bottom: 0.5rem; font-weight: 500; }
        .form-input, .form-select, .form-textarea { background: rgba(26, 26, 26, 0.8); border: 1px solid rgba(255, 255, 255, 0.2); border-radius: 8px; padding: 0.8rem 1rem; color: var(--white); font-size: 1rem; width: 100%; box-sizing: border-box; }
        .form-input:focus, .form-select:focus, .form-textarea:focus { outline: none; border-color: var(--primary-accent); box-shadow: 0 0 0 3px rgba(0, 168, 255, 0.3); }
        .form-textarea { min-height: 120px; resize: vertical; grid-column: 1 / -1; }
        .btn-form { width: 100%; padding: 1rem; font-size: 1.1rem; grid-column: 1 / -1; }
        .alert { padding: 1rem; margin-bottom: 1.5rem; border-radius: var(--border-radius); text-align: left; }
        .alert-error { background-color: rgba(231, 76, 60, 0.15); color: #f8d7da; }
        .alert-success { background-color: rgba(0, 196, 154, 0.15); color: #d1e7dd; }
        
        /* Estilos para o upload de imagem */
        .photo-upload-area { grid-column: 1 / -1; }
        .photo-preview-wrapper { width: 100%; max-width: 400px; height: 300px; margin: 0 auto 1rem; background-color: rgba(26, 26, 26, 0.8); border: 2px dashed rgba(255, 255, 255, 0.2); border-radius: var(--border-radius); display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative; }
        #image-to-crop { display: none; max-width: 100%; max-height: 100%; }
        .photo-upload-placeholder { color: rgba(255, 255, 255, 0.5); text-align: center; }
        .photo-upload-placeholder i { font-size: 3rem; margin-bottom: 1rem; }
        .photo-buttons { display: flex; justify-content: center; gap: 1rem; }
        #confirm-crop-btn { display: none; } /* Escondido por padrão */
        
        @media (min-width: 768px) {
            .form-layout { grid-template-columns: 1fr 1fr; }
            .form-group-span-2 { grid-column: 1 / -1; }
            .photo-upload-area { grid-column: 1 / 2; grid-row: 1 / 5; }
            .form-group-nome { grid-column: 2 / 3; grid-row: 1 / 2; }
            .form-group-especie { grid-column: 2 / 3; grid-row: 2 / 3; }
            .form-group-raca { grid-column: 2 / 3; grid-row: 3 / 4; }
            .form-group-idade { grid-column: 2 / 3; grid-row: 4 / 5; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container">
            <h1 class="form-title">Cadastre para Adoção</h1>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <p><?php echo htmlspecialchars($success_message); ?></p>
                </div>
            <?php endif; ?>

            <form action="cadastrar_animal.php" method="POST" class="form-layout" id="animal-form">
                
                <div class="photo-upload-area">
                    <label class="form-label">Foto do Animal</label>
                    <div class="photo-preview-wrapper" id="preview-wrapper">
                        <img id="image-to-crop">
                        <div id="upload-placeholder" class="photo-upload-placeholder">
                            <i class="fas fa-camera"></i>
                            <p>A imagem aparecerá aqui</p>
                        </div>
                    </div>
                    <div class="photo-buttons">
                        <input type="file" id="input-image-file" accept="image/*" style="display: none;">
                        <button type="button" class="btn btn-login" id="btn-choose-img" onclick="document.getElementById('input-image-file').click();">Escolher Imagem</button>
                        <button type="button" class="btn btn-register" id="confirm-crop-btn">Confirmar Recorte</button>
                    </div>
                    <input type="hidden" name="foto_animal" id="cropped-image-data">
                </div>

                <div class="form-group form-group-nome">
                    <label for="nome" class="form-label">Nome do Animal</label>
                    <input type="text" id="nome" name="nome" class="form-input" required>
                </div>
                <div class="form-group form-group-especie">
                    <label for="especie" class="form-label">Espécie</label>
                    <select id="especie" name="especie" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="Cão">Cão</option>
                        <option value="Gato">Gato</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>
                <div class="form-group form-group-raca">
                    <label for="raca" class="form-label">Raça (Opcional)</label>
                    <input type="text" id="raca" name="raca" class="form-input">
                </div>
                 <div class="form-group form-group-idade">
                    <label for="idade" class="form-label">Idade</label>
                    <input type="text" id="idade" name="idade" class="form-input" required placeholder="Ex: 2 anos">
                </div>

                <div class="form-group">
                    <label for="sexo" class="form-label">Sexo</label>
                    <select id="sexo" name="sexo" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="Macho">Macho</option>
                        <option value="Fêmea">Fêmea</option>
                    </select>
                </div>
                 <div class="form-group">
                    <label for="telefone_contato" class="form-label">Telefone para Contato</label>
                    <input type="text" id="telefone_contato" name="telefone_contato" class="form-input" required placeholder="(XX) XXXXX-XXXX">
                </div>

                <div class="form-group form-group-span-2">
                    <label for="cidade" class="form-label">Cidade</label>
                    <input type="text" id="cidade" name="cidade" class="form-input" required>
                </div>
                <div class="form-group form-group-span-2">
                    <label for="endereco" class="form-label">Bairro / Rua</label>
                    <input type="text" id="endereco" name="endereco" class="form-input" required>
                </div>

                <div class="form-group form-group-span-2">
                    <label for="descricao" class="form-label">Descrição</label>
                    <textarea id="descricao" name="descricao" class="form-textarea" required placeholder="Fale sobre o temperamento, história e necessidades do animal."></textarea>
                </div>
                <div class="form-group form-group-span-2">
                    <button type="submit" class="btn btn-register btn-form">Cadastrar Animal</button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('input-image-file');
            const image = document.getElementById('image-to-crop');
            const placeholder = document.getElementById('upload-placeholder');
            const hiddenInput = document.getElementById('cropped-image-data');
            const form = document.getElementById('animal-form');
            const confirmBtn = document.getElementById('confirm-crop-btn');
            const chooseBtn = document.getElementById('btn-choose-img');
            const previewWrapper = document.getElementById('preview-wrapper');
            let cropper;

            imageInput.addEventListener('change', (e) => {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const reader = new FileReader();
                    reader.onload = () => {
                        previewWrapper.innerHTML = '<img id="image-to-crop" style="display: none; max-width: 100%;">';
                        const newImage = document.getElementById('image-to-crop');
                        newImage.src = reader.result;
                        
                        if (cropper) cropper.destroy();

                        cropper = new Cropper(newImage, {
                            aspectRatio: 1, viewMode: 1, background: false,
                        });

                        confirmBtn.style.display = 'inline-block';
                    };
                    reader.readAsDataURL(files[0]);
                }
            });

            confirmBtn.addEventListener('click', () => {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({ width: 500, height: 500, imageSmoothingQuality: 'high' });
                    const croppedImageData = canvas.toDataURL('image/jpeg');
                    hiddenInput.value = croppedImageData;
                    
                    previewWrapper.innerHTML = `<img src="${croppedImageData}" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">`;
                    cropper.destroy();
                    confirmBtn.style.display = 'none';
                    chooseBtn.textContent = 'Trocar Imagem';
                }
            });

            form.addEventListener('submit', (e) => {
                if (!hiddenInput.value) {
                    e.preventDefault();
                    alert('Por favor, escolha uma imagem e confirme o recorte antes de continuar.');
                }
            });
        });
    </script>
    <?php include 'footer.php'; ?>
</body>
</html>

