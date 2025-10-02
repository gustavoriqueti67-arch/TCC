<?php
require_once 'config.php';

// Verificar se um ID de animal foi fornecido na URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Se não houver ID, redireciona para a página de adoção
    header('Location: adote.php');
    exit();
}

$animal_id = $_GET['id'];

// Buscar os detalhes do animal específico no banco de dados, incluindo o nome de quem o cadastrou
$stmt = $pdo->prepare("SELECT a.*, u.nome as nome_usuario FROM animais a JOIN usuarios u ON a.id_usuario = u.id WHERE a.id = ?");
$stmt->execute([$animal_id]);
$animal = $stmt->fetch();

// Se o animal não for encontrado, redireciona
if (!$animal) {
    header('Location: adote.php');
    exit();
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
        .detalhe-animal-section {
            padding: 4rem 1.5rem;
        }
        /* Container principal com nova aparência */
        .detalhe-container-wrapper {
            max-width: 1000px;
            margin: 0 auto;
            background-color: var(--dark-bg); /* Fundo um pouco mais escuro */
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            padding: 2rem;
            box-shadow: var(--shadow-lg);
        }
        .detalhe-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }
        @media (min-width: 768px) {
            .detalhe-grid {
                grid-template-columns: 1fr 1.5fr;
                gap: 3rem;
            }
        }
        .animal-imagem-grande {
            width: 100%;
            aspect-ratio: 1 / 1;
            border-radius: var(--border-radius);
            overflow: hidden;
        }
        .animal-imagem-grande img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .animal-info h1 {
            font-size: 3.5rem; /* Maior para mais impacto */
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.25rem;
            line-height: 1.1;
        }
        .cadastrado-por {
            color: var(--text-color-light);
            margin-bottom: 2rem;
            font-style: italic;
        }
        /* Blocos de informações agora são "cards" internos */
        .info-bloco {
            background-color: var(--dark-bg-alt);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        .info-bloco h3 {
            font-size: 1.1rem;
            color: var(--primary-accent);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 1rem;
            padding-bottom: 0;
            border-bottom: none; /* Remove a linha antiga */
        }
        .info-bloco p {
            color: var(--text-color-light);
            line-height: 1.7;
        }
        .tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .detail-tag {
            background-color: var(--dark-bg);
            padding: 0.6rem 1.2rem;
            border-radius: 8px; /* Tags mais quadradas */
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-color);
            border: 1px solid transparent;
            transition: all 0.3s ease;
        }
        .detail-tag:hover {
            transform: translateY(-2px);
            border-color: var(--primary-accent);
        }
        .detail-tag i {
            color: var(--primary-accent);
        }
        .btn-contato {
            margin-top: 1rem;
            width: 100%;
            font-size: 1.2rem;
            padding: 1rem;
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="detalhe-animal-section">
            <div class="detalhe-container-wrapper">
                <div class="detalhe-grid">
                    <div class="animal-imagem-grande">
                        <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>">
                    </div>
                    <div class="animal-info">
                        <h1><?php echo htmlspecialchars($animal['nome']); ?></h1>
                        <p class="cadastrado-por">Cadastrado por: <?php echo htmlspecialchars($animal['nome_usuario']); ?></p>
                        
                        <div class="info-bloco">
                            <h3>Sobre mim</h3>
                            <p><?php echo nl2br(htmlspecialchars($animal['descricao'])); ?></p>
                        </div>

                        <div class="info-bloco">
                            <h3>Ficha do Pet</h3>
                            <div class="tags-container">
                                <span class="detail-tag"><i class="fas fa-paw"></i><?php echo htmlspecialchars($animal['especie']); ?></span>
                                <span class="detail-tag"><i class="fas fa-venus-mars"></i><?php echo htmlspecialchars($animal['sexo']); ?></span>
                                <span class="detail-tag"><i class="fas fa-tag"></i><?php echo !empty($animal['raca']) ? htmlspecialchars($animal['raca']) : 'Não informada'; ?></span>
                                <span class="detail-tag"><i class="fas fa-birthday-cake"></i><?php echo htmlspecialchars($animal['idade']); ?></span>
                                <span class="detail-tag"><i class="fas fa-map-marker-alt"></i><?php echo htmlspecialchars($animal['cidade']); ?></span>
                                <span class="detail-tag"><i class="fas fa-home"></i><?php echo htmlspecialchars($animal['endereco']); ?></span>
                            </div>
                        </div>

                        <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/[^0-9]/', '', $animal['telefone_contato'])); ?>" target="_blank" class="btn btn-register btn-contato">
                            <i class="fab fa-whatsapp"></i> Entrar em Contato
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
<?php include 'footer.php'; ?>
</body>
</html>

