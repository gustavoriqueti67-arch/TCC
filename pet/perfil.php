<?php
require_once 'config.php';

// Apenas utilizadores autenticados podem aceder
if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

// Buscar os dados do utilizador logado
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch();

// Buscar os animais cadastrados por este utilizador
$stmt_animais = $pdo->prepare("SELECT * FROM animais WHERE id_usuario = ? ORDER BY data_cadastro DESC");
$stmt_animais->execute([$user_id]);
$animais_do_usuario = $stmt_animais->fetchAll();

// Pega as mensagens de sucesso/erro da sessão, se existirem
$success_message = isset($_SESSION['success_message_perfil']) ? $_SESSION['success_message_perfil'] : null;
unset($_SESSION['success_message_perfil']);

$error_message = isset($_SESSION['error_message_perfil']) ? $_SESSION['error_message_perfil'] : null;
unset($_SESSION['error_message_perfil']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - <?php echo htmlspecialchars($usuario['nome']); ?></title>
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

        .perfil-section {
            padding: 3rem 1.5rem;
        }

        /* Alertas */
        .alert {
            max-width: 1200px;
            margin: 0 auto 2rem;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert i {
            font-size: 1.5rem;
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

        /* Container Principal */
        .perfil-container {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
            align-items: start;
        }

        /* Card de Perfil */
        .perfil-card {
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }

        .perfil-header {
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            padding: 2.5rem 2rem 3rem;
            text-align: center;
            position: relative;
        }

        .perfil-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: var(--dark-bg-alt);
            border-radius: 50% 50% 0 0 / 100% 100% 0 0;
        }

        .perfil-foto-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }

        .perfil-foto {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--dark-bg-alt);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
        }

        .perfil-foto-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: var(--primary-accent);
            color: var(--dark-bg);
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid var(--dark-bg-alt);
            font-size: 1rem;
        }

        .perfil-nome {
            position: relative;
            z-index: 1;
        }

        .perfil-nome h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--white);
            margin: 0 0 0.5rem 0;
        }

        .perfil-nome p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.85rem;
            margin: 0;
            font-weight: 500;
        }

        .perfil-body {
            padding: 2rem;
        }

        .perfil-stats {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-item {
            background: var(--dark-bg);
            padding: 1rem;
            border-radius: 12px;
            text-align: center;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-accent);
            display: block;
        }

        .stat-label {
            font-size: 0.85rem;
            color: var(--text-color-light);
            margin-top: 0.25rem;
        }

        .perfil-info {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .perfil-info-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: var(--dark-bg);
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            transition: all 0.3s ease;
        }

        .perfil-info-item:hover {
            border-color: var(--primary-accent);
            transform: translateX(5px);
        }

        .perfil-info-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--dark-bg);
            font-size: 1rem;
        }

        .perfil-info-text {
            flex: 1;
        }

        .perfil-info-label {
            font-size: 0.75rem;
            color: var(--text-color-light);
            display: block;
            margin-bottom: 0.25rem;
        }

        .perfil-info-value {
            color: var(--white);
            font-weight: 500;
        }

        .perfil-actions {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .btn-edit-perfil {
            width: 100%;
            padding: 0.875rem;
            background: var(--dark-bg);
            color: var(--primary-accent);
            border: 2px solid var(--primary-accent);
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-edit-perfil:hover {
            background: var(--primary-accent);
            color: var(--dark-bg);
        }

        .btn-admin {
            width: 100%;
            padding: 0.875rem;
            background: var(--dark-bg);
            color: #f39c12; /* Cor dourada/laranja para destaque */
            border: 2px solid #f39c12;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            margin-top: 1rem; /* Espaçamento entre os botões */
        }

        .btn-admin:hover {
            background: #f39c12;
            color: var(--dark-bg);
        }

        /* Card de Animais */
        .animais-section {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .animais-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .animais-header h2 {
            font-size: 2rem;
            color: var(--white);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .animais-header h2 i {
            color: var(--primary-accent);
        }

        .btn-add-animal {
            padding: 0.75rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .btn-add-animal:hover {
            transform: translateY(-2px);
        }

        /* Grid de Animais */
        .animais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .animal-card {
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .animal-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-accent);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .animal-card-image {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
        }

        .animal-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .animal-card:hover .animal-card-image img {
            transform: scale(1.1);
        }

        .animal-card-body {
            padding: 1.5rem;
            position: relative; /* Para posicionar o status */
        }

        .animal-name-wrapper {
             margin-bottom: 0.5rem; /* Ajusta espaço se necessário */
        }

        .animal-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0; /* Remove margem para ficar junto do badge */
            display: inline-block; /* Para o badge ficar ao lado */
            vertical-align: middle; /* Alinha o texto do nome com o badge */
        }

        .animal-details {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .animal-detail-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: var(--dark-bg);
            border-radius: 6px;
            font-size: 0.85rem;
            color: var(--text-color-light);
        }

        .animal-detail-badge i {
            color: var(--primary-accent);
        }

        .animal-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.75rem;
        }

        .btn-action {
            padding: 0.75rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: var(--dark-bg);
            color: var(--primary-accent);
            border: 2px solid var(--primary-accent);
        }

        .btn-edit:hover {
            background: var(--primary-accent);
            color: var(--dark-bg);
        }

        .btn-delete {
            background: rgba(231, 76, 60, 0.15);
            color: #e74c3c;
            border: 2px solid rgba(231, 76, 60, 0.3);
        }

        .btn-delete:hover {
            background: #e74c3c;
            color: var(--white);
        }

        /* Mensagem vazia */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-accent);
            opacity: 0.5;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-color-light);
            margin-bottom: 2rem;
        }

        /* Adiciona estilos para os badges de status */
       .animal-status-badge {
            display: inline-block;
            padding: 0.4em 0.8em;
            font-size: 0.75rem; /* Menor que na página de detalhe */
            font-weight: 600;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle; /* Alinha com o texto do nome */
            border-radius: 0.375rem; /* rounded-md */
            margin-left: 0.5rem; /* Espaço do nome */
        }
        .badge-warning { background-color: rgba(245, 158, 11, 0.2); color: #f59e0b; } /* Laranja */
        .badge-success { background-color: rgba(16, 185, 129, 0.2); color: #10b981; } /* Verde */
        .badge-danger { background-color: rgba(239, 68, 68, 0.2); color: #ef4444; } /* Vermelho */

        /* Responsividade */
        @media (max-width: 1024px) {
            .perfil-container {
                grid-template-columns: 1fr;
            }

            .perfil-card {
                position: static;
            }
        }

        @media (max-width: 768px) {
            .perfil-stats {
                grid-template-columns: 1fr;
            }

            .animais-grid {
                grid-template-columns: 1fr;
            }

            .animais-header {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-add-animal {
                width: 100%;
                justify-content: center;
            }

            .animal-actions {
                grid-template-columns: 1fr;
            }
             .animal-name {
                 display: block; /* Nome em cima, badge embaixo */
                 margin-bottom: 0.3rem;
            }
            .animal-status-badge {
                margin-left: 0; /* Remove margem */
                margin-top: 0;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="perfil-section">
            <?php if ($success_message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success_message); ?></span>
                </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <div class="perfil-container">
                <!-- Card de Perfil do Utilizador -->
                <aside class="perfil-card">
                    <div class="perfil-header">
                        <div class="perfil-foto-container">
                            <img src="perfil_foto/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>"
                                 alt="Foto de Perfil"
                                 class="perfil-foto">
                            <div class="perfil-foto-badge">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="perfil-nome">
                            <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                            <p>
                                <i class="far fa-calendar"></i>
                                Membro desde <?php echo isset($usuario['data_registro']) ? date('d/m/Y', strtotime($usuario['data_registro'])) : 'Data não disponível'; ?>
                            </p>
                        </div>
                    </div>

                    <div class="perfil-body">
                        <div class="perfil-stats">
                            <div class="stat-item">
                                <span class="stat-number"><?php echo count($animais_do_usuario); ?></span>
                                <span class="stat-label">Animais</span>
                            </div>
                            <div class="stat-item">
                                <span class="stat-number">
                                    <?php echo isset($usuario['data_registro']) ? floor((time() - strtotime($usuario['data_registro'])) / (60 * 60 * 24)) : '0'; ?>
                                </span>
                                <span class="stat-label">Dias</span>
                            </div>
                        </div>

                        <div class="perfil-info">
                            <div class="perfil-info-item">
                                <div class="perfil-info-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="perfil-info-text">
                                    <span class="perfil-info-label">Email</span>
                                    <span class="perfil-info-value"><?php echo htmlspecialchars($usuario['email']); ?></span>
                                </div>
                            </div>

                            <div class="perfil-info-item">
                                <div class="perfil-info-icon">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="perfil-info-text">
                                    <span class="perfil-info-label">Telefone</span>
                                    <span class="perfil-info-value"><?php echo htmlspecialchars($usuario['telefone']); ?></span>
                                </div>
                            </div>

                            <div class="perfil-info-item">
                                <div class="perfil-info-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="perfil-info-text">
                                    <span class="perfil-info-label">Localização</span>
                                    <span class="perfil-info-value"><?php echo htmlspecialchars($usuario['cidade']); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="perfil-actions">
                            <a href="editar_perfil.php" class="btn-edit-perfil">
                                <i class="fas fa-user-edit"></i>
                                Editar Perfil
                            </a>

                            <!-- Botão de Admin visível apenas para administradores -->
                            <?php if (is_admin()): ?>
                                <a href="admin.php" class="btn-admin">
                                    <i class="fas fa-user-shield"></i>
                                    Painel Admin
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </aside>

                <!-- Seção de Animais -->
                <div class="animais-section">
                    <div class="animais-header">
                        <h2>
                            <i class="fas fa-paw"></i>
                            Meus Animais
                        </h2>
                        <a href="cadastrar_animal.php" class="btn-add-animal">
                            <i class="fas fa-plus"></i>
                            Adicionar Animal
                        </a>
                    </div>

                    <?php if (empty($animais_do_usuario)): ?>
                        <div class="empty-state">
                            <i class="fas fa-heart"></i>
                            <h3>Ainda não há animais cadastrados</h3>
                            <p>Comece a fazer a diferença! Cadastre um animal para adoção e ajude-o a encontrar um lar amoroso.</p>
                            <a href="cadastrar_animal.php" class="btn-add-animal" style="display: inline-flex;">
                                <i class="fas fa-plus"></i>
                                Cadastrar Primeiro Animal
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="animais-grid">
                            <?php foreach ($animais_do_usuario as $animal):
                                // Determina o texto e a classe do status
                                $status_text = '';
                                $status_class = '';
                                switch ($animal['status']) {
                                    case 'pendente':
                                        $status_text = 'Pendente';
                                        $status_class = 'badge-warning';
                                        break;
                                    case 'aprovado':
                                        $status_text = 'Aprovado';
                                        $status_class = 'badge-success';
                                        break;
                                    case 'rejeitado':
                                        $status_text = 'Rejeitado';
                                        $status_class = 'badge-danger';
                                        break;
                                }
                            ?>
                                <article class="animal-card">
                                    <div class="animal-card-image">
                                        <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>"> <!-- Link na imagem -->
                                            <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>"
                                                 alt="<?php echo htmlspecialchars($animal['nome']); ?>">
                                        </a>
                                        <!-- Badge de status público removido -->
                                    </div>
                                    <div class="animal-card-body">
                                        <div class="animal-name-wrapper">
                                            <h3 class="animal-name"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                            <?php if ($status_text): ?>
                                                <span class="animal-status-badge <?php echo $status_class; ?>">
                                                    <?php echo htmlspecialchars($status_text); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="animal-details">
                                            <span class="animal-detail-badge">
                                                <i class="fas fa-venus-mars"></i>
                                                <?php echo isset($animal['sexo']) ? htmlspecialchars($animal['sexo']) : 'N/A'; ?>
                                            </span>
                                            <span class="animal-detail-badge">
                                                <i class="fas fa-birthday-cake"></i>
                                                <?php echo isset($animal['idade']) ? htmlspecialchars($animal['idade']) : 'N/A'; ?>
                                            </span>
                                            <span class="animal-detail-badge">
                                                <i class="fas fa-tag"></i>
                                                <?php echo isset($animal['especie']) ? htmlspecialchars($animal['especie']) : 'N/A'; ?>
                                            </span>
                                        </div>
                                        <div class="animal-actions">
                                            <a href="editar_animal.php?id=<?php echo $animal['id']; ?>" class="btn-action btn-edit">
                                                <i class="fas fa-edit"></i>
                                                Editar
                                            </a>
                                            <!-- Formulário para exclusão segura via POST -->
                                            <form action="excluir_animal.php" method="POST" onsubmit="return confirm('Tem a certeza que deseja excluir <?php echo htmlspecialchars(addslashes($animal['nome'])); ?>? Esta ação não pode ser desfeita.');" style="display: contents;">
                                                <?php csrf_input_field(); ?>
                                                <input type="hidden" name="id" value="<?php echo $animal['id']; ?>">
                                                <input type="hidden" name="return_to" value="perfil.php"> <!-- ADICIONADO CAMPO DE RETORNO -->
                                                <button type="submit" class="btn-action btn-delete">
                                                    <i class="fas fa-trash"></i>
                                                    Excluir
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>