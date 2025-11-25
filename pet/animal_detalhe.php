<?php
require_once 'config.php';

// Verificar se um ID de animal foi fornecido na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: adote.php');
    exit();
}

$animal_id = $_GET['id'];

// Buscar os detalhes do animal específico no banco de dados
// Modificado para buscar apenas aprovados OU se o usuário logado for o dono
$sql = "SELECT a.*, u.nome as nome_usuario FROM animais a JOIN usuarios u ON a.id_usuario = u.id WHERE a.id = ? AND (a.status = 'aprovado'";
$params = [$animal_id];

// Adiciona condição para permitir que o dono veja mesmo se pendente/rejeitado
if (is_logged_in()) {
    $sql .= " OR a.id_usuario = ?";
    $params[] = $_SESSION['user_id'];
}
$sql .= ")";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animal = $stmt->fetch();

// Se o animal não for encontrado OU não for aprovado e o usuário não for o dono, redireciona
if (!$animal) {
    header('Location: adote.php');
    exit();
}

// Determina o status para exibição (apenas se for o dono)
$show_status_badge = (is_logged_in() && $animal['id_usuario'] == $_SESSION['user_id']);
$status_text = '';
$status_class = '';
if ($show_status_badge) {
    switch ($animal['status']) {
        case 'pendente':
            $status_text = 'Pendente';
            $status_class = 'badge-warning'; // Usaremos a classe de warning do moderar_animais.php
            break;
        case 'aprovado':
            $status_text = 'Aprovado';
            $status_class = 'badge-success'; // Usaremos a classe de success do moderar_animais.php
            break;
        case 'rejeitado':
            $status_text = 'Rejeitado';
            $status_class = 'badge-danger'; // Usaremos a classe de danger do moderar_animais.php
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes de <?php echo htmlspecialchars($animal['nome']); ?></title>
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

        /* Breadcrumb */
        .breadcrumb {
            padding: 2rem 0 1rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-color-light);
            flex-wrap: wrap;
            padding: 0 1.5rem;
        }

        .breadcrumb-list a {
            color: var(--primary-accent);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .breadcrumb-list a:hover {
            opacity: 0.8;
        }

        /* Container principal */
        .detail-section {
            padding: 2rem 1.5rem 4rem;
        }

        .detail-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--dark-bg-alt);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Grid layout */
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 0;
        }

        /* Coluna da imagem */
        .image-column {
            position: relative;
            background: var(--dark-bg);
        }

        .animal-image-wrapper {
            position: sticky;
            top: 20px;
            padding: 2rem;
        }

        .animal-image-container {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            position: relative;
        }

        .animal-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.25rem;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--primary-accent);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .image-badge i {
            font-size: 1rem;
        }

        /* Coluna de informações */
        .info-column {
            padding: 3rem 3rem 3rem 2rem;
            display: flex;
            flex-direction: column;
        }

        /* Nome e autor */
        .animal-header {
            display: flex;
            align-items: baseline; /* Alinha pela base do texto */
            flex-wrap: wrap; /* Permite quebrar linha se necessário */
            gap: 0 1rem; /* Espaço entre nome e badge */
            margin-bottom: 2rem;
        }

        .animal-name {
            font-size: 3rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0; /* Remove margem inferior padrão */
            line-height: 1.1;
        }

        .posted-by {
             width: 100%; /* Ocupa toda a largura abaixo do nome/badge */
             margin-top: 0.5rem; /* Espaço acima */
            color: var(--text-color-light);
            font-size: 0.95rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .posted-by i {
            color: var(--primary-accent);
        }

        /* Seções de informação */
        .info-section {
            background: var(--dark-bg);
            border-radius: 12px;
            padding: 1.75rem;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-section-title {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-section-title i {
            font-size: 1.1rem;
        }

        .info-description {
            color: var(--text-color-light);
            line-height: 1.8;
            font-size: 1rem;
        }

        /* Tags de informação */
        .info-tags {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 0.75rem;
        }

        .info-tag {
            background: var(--dark-bg-alt);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.875rem 1rem;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
        }

        .info-tag:hover {
            border-color: var(--primary-accent);
            transform: translateY(-2px);
        }

        .tag-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-bg);
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .tag-content {
            flex: 1;
            min-width: 0;
        }

        .tag-label {
            font-size: 0.75rem;
            color: var(--text-color-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.2rem;
        }

        .tag-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--white);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Botão de contato */
        .contact-section {
            margin-top: auto;
            padding-top: 1.5rem;
        }

        .btn-contact {
            width: 100%;
            padding: 1.25rem;
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: var(--white);
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-contact:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }

        .btn-contact i {
            font-size: 1.3rem;
        }

        .contact-hint {
            text-align: center;
            margin-top: 1rem;
            font-size: 0.85rem;
            color: var(--text-color-light);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Botão voltar */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--dark-bg-alt);
            color: var(--primary-accent);
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .back-button:hover {
            background: var(--primary-accent);
            color: var(--dark-bg);
            transform: translateX(-5px);
        }

        /* Adiciona estilos para os badges de status */
        .status-badge {
            display: inline-block;
            padding: 0.4em 0.8em;
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 0.375rem; /* rounded-md */
            margin-left: 1rem; /* Espaço do nome */
        }
        .badge-warning { background-color: rgba(245, 158, 11, 0.2); color: #f59e0b; } /* Laranja */
        .badge-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; } /* Verde */
        .badge-danger { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; } /* Vermelho */

        /* Alerta para status não aprovado (usado no contact-section) */
        .alert {
             padding: 1rem 1.5rem;
             border-radius: 12px;
             display: flex;
             align-items: center;
             gap: 1rem;
             text-align: center;
             justify-content: center;
             background-color: transparent;
             border-style: dashed;
        }
        .alert-warning { border-color: #f59e0b; color: #f59e0b; }
        .alert-error { border-color: #ef4444; color: #ef4444; }


        /* Responsividade */
        @media (max-width: 1024px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }

            .animal-image-wrapper {
                position: static;
            }

            .info-column {
                padding: 2rem 1.5rem;
            }

            .animal-name {
                font-size: 2.5rem;
            }
        }

        @media (max-width: 768px) {
            .animal-image-wrapper {
                padding: 1.5rem;
            }

            .animal-name {
                font-size: 2rem;
            }

            .info-tags {
                grid-template-columns: 1fr;
            }

            .breadcrumb-list {
                font-size: 0.85rem;
            }

            .animal-header {
                 align-items: flex-start; /* Alinha no topo em telas menores */
            }
            .status-badge {
                margin-left: 0; /* Remove margem esquerda em telas menores */
                margin-top: 0.5rem; /* Adiciona espaço acima */
                width: 100%; /* Ocupa a largura */
                text-align: left; /* Alinha texto à esquerda */
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <nav class="breadcrumb-list">
                <a href="index.php">Início</a>
                <span>/</span>
                <a href="adote.php">Adoção</a>
                <span>/</span>
                <span><?php echo htmlspecialchars($animal['nome']); ?></span>
            </nav>
        </div>

        <section class="detail-section">
            <!-- Botão Voltar -->
            <div style="max-width: 1200px; margin: 0 auto; padding: 0 1.5rem;">
                <a href="adote.php" class="back-button">
                    <i class="fas fa-arrow-left"></i>
                    Voltar para Adoção
                </a>
            </div>

            <div class="detail-container">
                <div class="detail-grid">
                    <!-- Coluna da Imagem -->
                    <div class="image-column">
                        <div class="animal-image-wrapper">
                            <div class="animal-image-container">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>"
                                     alt="<?php echo htmlspecialchars($animal['nome']); ?>">
                                <!-- Badge de status -->
                                <?php if ($animal['status'] == 'aprovado'): ?>
                                    <div class="image-badge">
                                        <i class="fas fa-heart"></i>
                                        Disponível
                                    </div>
                                <?php elseif($show_status_badge && $status_text): ?>
                                     <!-- Mostra status pendente/rejeitado aqui se for o dono -->
                                     <div class="image-badge <?php echo $status_class; ?>" style="color: inherit; background: inherit; border: 1px solid currentColor;">
                                        <?php if($animal['status'] == 'pendente') echo '<i class="fas fa-clock"></i>'; ?>
                                        <?php if($animal['status'] == 'rejeitado') echo '<i class="fas fa-times-circle"></i>'; ?>
                                        <?php echo htmlspecialchars($status_text); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Coluna de Informações -->
                    <div class="info-column">
                        <!-- Header com badge de status (apenas para o dono) -->
                        <div class="animal-header">
                            <h1 class="animal-name"><?php echo htmlspecialchars($animal['nome']); ?></h1>
                            <?php if ($show_status_badge && $status_text): ?>
                                <span class="status-badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($status_text); ?></span>
                            <?php endif; ?>
                            <p class="posted-by">
                                <i class="fas fa-user"></i>
                                Cadastrado por: <strong><?php echo htmlspecialchars($animal['nome_usuario']); ?></strong>
                            </p>
                        </div>

                        <!-- Sobre o Animal -->
                        <div class="info-section">
                            <h2 class="info-section-title">
                                <i class="fas fa-file-alt"></i>
                                Sobre Mim
                            </h2>
                            <p class="info-description">
                                <?php echo nl2br(htmlspecialchars($animal['descricao'])); ?>
                            </p>
                        </div>

                        <!-- Ficha do Pet -->
                        <div class="info-section">
                            <h2 class="info-section-title">
                                <i class="fas fa-clipboard-list"></i>
                                Ficha do Pet
                            </h2>
                            <div class="info-tags">
                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-paw"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Espécie</div>
                                        <div class="tag-value"><?php echo htmlspecialchars($animal['especie']); ?></div>
                                    </div>
                                </div>

                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-venus-mars"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Sexo</div>
                                        <div class="tag-value"><?php echo htmlspecialchars($animal['sexo']); ?></div>
                                    </div>
                                </div>

                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-tag"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Raça</div>
                                        <div class="tag-value"><?php echo !empty($animal['raca']) ? htmlspecialchars($animal['raca']) : 'Vira-lata'; ?></div>
                                    </div>
                                </div>

                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-birthday-cake"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Idade</div>
                                        <div class="tag-value"><?php echo htmlspecialchars($animal['idade']); ?></div>
                                    </div>
                                </div>

                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Cidade</div>
                                        <div class="tag-value"><?php echo htmlspecialchars($animal['cidade']); ?></div>
                                    </div>
                                </div>

                                <div class="info-tag">
                                    <div class="tag-icon">
                                        <i class="fas fa-home"></i>
                                    </div>
                                    <div class="tag-content">
                                        <div class="tag-label">Bairro</div>
                                        <div class="tag-value"><?php echo htmlspecialchars($animal['endereco']); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                         <!-- Botão de Contato visível apenas se aprovado -->
                         <?php if ($animal['status'] == 'aprovado'): ?>
                            <div class="contact-section">
                                <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', $animal['telefone_contato'])); ?>"
                                   target="_blank"
                                   class="btn-contact">
                                    <i class="fab fa-whatsapp"></i>
                                    Entrar em Contato via WhatsApp
                                </a>
                                <p class="contact-hint">
                                    <i class="fas fa-info-circle"></i>
                                    Você será redirecionado para o WhatsApp
                                </p>
                            </div>
                        <?php elseif ($show_status_badge): ?>
                            <!-- Mensagem para o dono se não estiver aprovado -->
                             <div class="contact-section">
                                <div class="alert alert-<?php echo ($animal['status'] == 'pendente' ? 'warning' : 'error'); ?>">
                                    <i class="fas fa-<?php echo ($animal['status'] == 'pendente' ? 'clock' : 'times-circle'); ?>"></i>
                                    <span>Este animal está <?php echo htmlspecialchars($status_text); ?> e não está visível publicamente. O botão de contato aparecerá após a aprovação.</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>

