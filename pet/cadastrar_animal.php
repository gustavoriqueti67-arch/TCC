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
    if (!csrf_is_valid(isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '')) {
        $errors[] = 'Falha de segurança: token CSRF inválido. Recarregue a página e tente novamente.';
    }
    if (empty($errors)) {
    // 1. Validar e limpar os dados com validações mais robustas
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
    if (empty($nome)) {
        $errors[] = "O nome do animal é obrigatório.";
    } elseif (strlen($nome) > 100) {
        $errors[] = "O nome do animal não pode ter mais de 100 caracteres.";
    }

    if (empty($especie)) {
        $errors[] = "A espécie é obrigatória.";
    } elseif (!in_array($especie, ['Cão', 'Gato', 'Outro'])) {
        $errors[] = "Espécie inválida.";
    }

    if (strlen($raca) > 100) {
        $errors[] = "A raça não pode ter mais de 100 caracteres.";
    }

    if (empty($idade)) {
        $errors[] = "A idade é obrigatória.";
    } elseif (strlen($idade) > 50) {
        $errors[] = "A idade não pode ter mais de 50 caracteres.";
    }

    if (empty($sexo)) {
        $errors[] = "O sexo é obrigatório.";
    } elseif (!in_array($sexo, ['Macho', 'Fêmea'])) {
        $errors[] = "Sexo inválido.";
    }

    if (empty($descricao)) {
        $errors[] = "A descrição é obrigatória.";
    } elseif (strlen($descricao) < 20) {
        $errors[] = "A descrição deve ter pelo menos 20 caracteres.";
    } elseif (strlen($descricao) > 1000) {
        $errors[] = "A descrição não pode ter mais de 1000 caracteres.";
    }

    if (empty($telefone_contato)) {
        $errors[] = "O telefone para contato é obrigatório.";
    } elseif (!preg_match('/^\(\d{2}\)\s?\d{4,5}-?\d{4}$/', $telefone_contato)) {
        $errors[] = "Formato de telefone inválido. Use: (XX) XXXXX-XXXX";
    }

    if (empty($endereco)) {
        $errors[] = "O endereço (bairro/rua) é obrigatório.";
    } elseif (strlen($endereco) > 200) {
        $errors[] = "O endereço não pode ter mais de 200 caracteres.";
    }

    if (empty($cidade)) {
        $errors[] = "A cidade é obrigatória.";
    } elseif (strlen($cidade) > 100) {
        $errors[] = "A cidade não pode ter mais de 100 caracteres.";
    }

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

    }
    // 2. Se não houver erros, processar os dados
    if (empty($errors)) {
        // Guardar a foto na pasta de uploads
        $upload_dir = __DIR__ . '/animais_fotos/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        if (file_put_contents($upload_dir . $nome_foto, $decoded_image) === false) {
            $errors[] = "Erro ao salvar a imagem. Tente novamente.";
        } else {
            // Inserir no banco de dados
            try {
                // Modificado para incluir o status 'pendente'
                $stmt = $pdo->prepare("INSERT INTO animais (nome, especie, raca, idade, sexo, descricao, foto_animal, id_usuario, telefone_contato, endereco, cidade, data_cadastro, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'pendente')");
                $stmt->execute([
                    $nome, 
                    $especie, 
                    $raca, 
                    $idade, 
                    $sexo, 
                    $descricao, 
                    $nome_foto, 
                    $_SESSION['user_id'], 
                    $telefone_contato, 
                    $endereco, 
                    $cidade
                ]);
                
                $success_message = "Animal cadastrado com sucesso! Ele será revisado por um administrador antes de ser publicado. Redirecionando..."; // Mensagem atualizada
                header("Refresh:3; url=perfil.php");
                // Removido exit() para permitir a exibição da mensagem antes do redirecionamento

            } catch (PDOException $e) {
                // Remove a foto se houver erro no banco
                if (file_exists($upload_dir . $nome_foto)) {
                    unlink($upload_dir . $nome_foto);
                }
                error_log("Erro ao cadastrar animal: " . $e->getMessage());
                $errors[] = "Erro ao cadastrar o animal. Por favor, tente novamente.";
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
    <title>Cadastrar Animal - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css">
    <style>
        .form-page {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 76px);
            padding: 2rem 1rem;
        }
        
        .form-container {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 2.5rem;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            color: var(--text-color);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .form-title {
            font-family: var(--font-headings);
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
            color: var(--primary-accent);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .form-subtitle {
            color: rgba(255, 255, 255, 0.6);
            font-size: 1rem;
        }
        
        .form-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }
        
        .required-mark {
            color: #ff4757;
        }
        
        .form-input, .form-select, .form-textarea {
            background: rgba(26, 26, 26, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 0.8rem 1rem;
            color: var(--white);
            font-size: 1rem;
            width: 100%;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }
        
        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            border-color: var(--primary-accent);
            box-shadow: 0 0 0 3px rgba(0, 196, 154, 0.2);
        }
        
        .form-input::placeholder, .form-textarea::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .form-textarea {
            min-height: 120px;
            resize: vertical;
            font-family: inherit;
        }
        
        .char-counter {
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.5);
            text-align: right;
            margin-top: 0.25rem;
        }
        
        /* Estilos para o upload de imagem */
        .photo-upload-area {
            grid-column: 1 / -1;
        }
        
        .photo-preview-wrapper {
            width: 100%;
            max-width: 350px;
            height: 350px;
            margin: 0 auto 1rem;
            background-color: rgba(26, 26, 26, 0.8);
            border: 2px dashed rgba(0, 196, 154, 0.3);
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
            transition: border-color 0.3s ease;
        }
        
        .photo-preview-wrapper:hover {
            border-color: var(--primary-accent);
        }
        
        #image-to-crop {
            display: none;
            max-width: 100%;
            max-height: 100%;
        }
        
        .photo-upload-placeholder {
            color: rgba(255, 255, 255, 0.5);
            text-align: center;
            padding: 2rem;
        }
        
        .photo-upload-placeholder i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: var(--primary-accent);
        }
        
        .photo-upload-placeholder p {
            font-size: 1.1rem;
        }
        
        .photo-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        .btn-form {
            padding: 1rem 2rem;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        
        #confirm-crop-btn {
            display: none;
        }
        
        /* Alertas melhorados */
        .alert {
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }
        
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .alert i {
            font-size: 1.2rem;
            margin-top: 0.1rem;
        }
        
        .alert-content {
            flex: 1;
        }
        
        .alert-error {
            background-color: rgba(231, 76, 60, 0.15);
            color: #f8d7da;
            border-left: 4px solid #e74c3c;
        }
        
        .alert-success {
            background-color: rgba(0, 196, 154, 0.15);
            color: #d1e7dd;
            border-left: 4px solid var(--primary-accent);
        }
        
        .alert-error p, .alert-success p {
            margin: 0.25rem 0;
        }
        
        /* Loading state */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }
        
        .btn-loading::after {
            content: "";
            position: absolute;
            width: 16px;
            height: 16px;
            top: 50%;
            left: 50%;
            margin-left: -8px;
            margin-top: -8px;
            border: 2px solid #ffffff;
            border-radius: 50%;
            border-top-color: transparent;
            animation: spinner 0.6s linear infinite;
        }
        
        @keyframes spinner {
            to { transform: rotate(360deg); }
        }
        
        /* Responsive */
        @media (min-width: 768px) {
            .form-layout {
                grid-template-columns: 350px 1fr;
                gap: 2rem;
            }
            
            .photo-upload-area {
                grid-column: 1 / 2;
                grid-row: 1 / 6;
            }
            
            .form-group-span-2 {
                grid-column: 1 / -1;
            }
        }
        
        @media (max-width: 767px) {
            .form-container {
                padding: 1.5rem;
            }
            
            .form-title {
                font-size: 2rem;
            }
            
            .photo-preview-wrapper {
                max-width: 100%;
                height: 300px;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main class="form-page">
        <div class="form-container">
            <div class="form-header">
                <h1 class="form-title">
                    💙 Cadastre para Adoção
                </h1>
                <p class="form-subtitle">Preencha os dados do animal para disponibilizá-lo para adoção</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <div style="font-size: 1.2rem;">⚠️</div>
                    <div class="alert-content">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <div style="font-size: 1.2rem;">✓</div>
                    <div class="alert-content">
                        <p><?php echo htmlspecialchars($success_message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="cadastrar_animal.php" method="POST" class="form-layout" id="animal-form">
                <?php csrf_input_field(); ?>
                
                <!-- Upload de Foto -->
                <div class="photo-upload-area">
                    <label class="form-label">
                        📷 Foto do Animal <span class="required-mark">*</span>
                    </label>
                    <div class="photo-preview-wrapper" id="preview-wrapper">
                        <img id="image-to-crop" alt="Imagem para recorte">
                        <div id="upload-placeholder" class="photo-upload-placeholder">
                            <div style="font-size: 4rem; margin-bottom: 1rem;">📸</div>
                            <p>A imagem aparecerá aqui</p>
                            <small>Recomendado: 500x500px</small>
                        </div>
                    </div>
                    <div class="photo-buttons">
                        <input type="file" id="input-image-file" accept="image/jpeg,image/jpg,image/png,image/gif" style="display: none;">
                        <button type="button" class="btn btn-login btn-form" id="btn-choose-img">
                            Escolher Imagem
                        </button>
                        <button type="button" class="btn btn-register btn-form" id="confirm-crop-btn">
                            ✓ Confirmar Recorte
                        </button>
                    </div>
                    <input type="hidden" name="foto_animal" id="cropped-image-data">
                </div>

                <!-- Dados do Animal -->
                <div class="form-group">
                    <label for="nome" class="form-label">
                        Nome do Animal <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="nome" name="nome" class="form-input" required maxlength="100" placeholder="Ex: Rex, Mia, Bob...">
                </div>

                <div class="form-group">
                    <label for="especie" class="form-label">
                        Espécie <span class="required-mark">*</span>
                    </label>
                    <select id="especie" name="especie" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="Cão">Cão</option>
                        <option value="Gato">Gato</option>
                        <option value="Outro">Outro</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="raca" class="form-label">
                        Raça (Opcional)
                    </label>
                    <input type="text" id="raca" name="raca" class="form-input" maxlength="100" placeholder="Ex: Vira-lata, SRD, Labrador...">
                </div>

                <div class="form-group">
                    <label for="idade" class="form-label">
                        Idade <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="idade" name="idade" class="form-input" required maxlength="50" placeholder="Ex: 2 anos, 6 meses, Filhote...">
                </div>

                <div class="form-group">
                    <label for="sexo" class="form-label">
                        Sexo <span class="required-mark">*</span>
                    </label>
                    <select id="sexo" name="sexo" class="form-select" required>
                        <option value="">Selecione...</option>
                        <option value="Macho">Macho</option>
                        <option value="Fêmea">Fêmea</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="telefone_contato" class="form-label">
                        Telefone para Contato <span class="required-mark">*</span>
                    </label>
                    <input type="tel" id="telefone_contato" name="telefone_contato" class="form-input" required placeholder="(XX) XXXXX-XXXX">
                </div>

                <div class="form-group form-group-span-2">
                    <label for="cidade" class="form-label">
                        Cidade <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="cidade" name="cidade" class="form-input" required maxlength="100" placeholder="Ex: São Paulo, Rio de Janeiro...">
                </div>

                <div class="form-group form-group-span-2">
                    <label for="endereco" class="form-label">
                        Bairro / Rua <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="endereco" name="endereco" class="form-input" required maxlength="200" placeholder="Ex: Centro, Jardim das Flores...">
                </div>

                <div class="form-group form-group-span-2">
                    <label for="descricao" class="form-label">
                        Descrição <span class="required-mark">*</span>
                    </label>
                    <textarea id="descricao" name="descricao" class="form-textarea" required minlength="20" maxlength="1000" placeholder="Fale sobre o temperamento, história e necessidades do animal. Quanto mais detalhes, melhor!"></textarea>
                    <div class="char-counter">
                        <span id="char-count">0</span>/1000 caracteres
                    </div>
                </div>

                <div class="form-group form-group-span-2">
                    <button type="submit" class="btn btn-register btn-form" id="submit-btn">
                        ❤️ Cadastrar Animal
                    </button>
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
            const submitBtn = document.getElementById('submit-btn');
            const descricao = document.getElementById('descricao');
            const charCount = document.getElementById('char-count');
            const telefoneInput = document.getElementById('telefone_contato');
            let cropper;

            // Contador de caracteres para descrição
            descricao.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });

            // Máscara de telefone
            telefoneInput.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
                    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
                    e.target.value = value;
                }
            });

            // Upload de imagem com cropper
            chooseBtn.addEventListener('click', function() {
                imageInput.click();
            });

            imageInput.addEventListener('change', (e) => {
                const files = e.target.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    
                    // Validar tamanho do arquivo
                    if (file.size > 5 * 1024 * 1024) {
                        alert('A imagem não pode ter mais de 5MB.');
                        return;
                    }
                    
                    // Validar tipo de arquivo
                    if (!file.type.match('image/(jpeg|jpg|png|gif)')) {
                        alert('Apenas arquivos JPG, PNG e GIF são permitidos.');
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = () => {
                        previewWrapper.innerHTML = '<img id="image-to-crop" style="display: block; max-width: 100%;">';
                        const newImage = document.getElementById('image-to-crop');
                        newImage.src = reader.result;
                        
                        if (cropper) cropper.destroy();

                        cropper = new Cropper(newImage, {
                            aspectRatio: 1,
                            viewMode: 1,
                            background: false,
                            autoCropArea: 1,
                            responsive: true,
                            restore: false,
                            guides: true,
                            center: true,
                            highlight: false,
                            cropBoxMovable: true,
                            cropBoxResizable: true,
                            toggleDragModeOnDblclick: false,
                        });

                        confirmBtn.style.display = 'flex';
                        chooseBtn.innerHTML = '🔄 Trocar Imagem';
                    };
                    reader.readAsDataURL(file);
                }
            });

            confirmBtn.addEventListener('click', () => {
                if (cropper) {
                    const canvas = cropper.getCroppedCanvas({
                        width: 500,
                        height: 500,
                        imageSmoothingEnabled: true,
                        imageSmoothingQuality: 'high'
                    });
                    
                    const croppedImageData = canvas.toDataURL('image/jpeg', 0.9);
                    hiddenInput.value = croppedImageData;
                    
                    previewWrapper.innerHTML = `<img src="${croppedImageData}" style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--border-radius);">`;
                    cropper.destroy();
                    cropper = null;
                    confirmBtn.style.display = 'none';
                    
                    // Feedback visual
                    previewWrapper.style.borderColor = 'var(--primary-accent)';
                    setTimeout(() => {
                        previewWrapper.style.borderColor = '';
                    }, 1000);
                }
            });

            // Validação do formulário antes de enviar
            form.addEventListener('submit', (e) => {
                if (!hiddenInput.value) {
                    e.preventDefault();
                    alert('Por favor, escolha uma imagem e confirme o recorte antes de continuar.');
                    return;
                }
                
                // Adicionar estado de loading no botão
                submitBtn.classList.add('btn-loading');
                submitBtn.innerHTML = 'Cadastrando...';
                submitBtn.disabled = true;
            });
        });
    </script>
    
    <?php include 'footer.php'; ?>
</body>
</html>

