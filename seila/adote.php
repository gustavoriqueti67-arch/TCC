<?php
require_once 'config.php';

// --- Lógica dos Filtros ---
// 1. Pegar os valores dos filtros da URL (se existirem)
$cidade_filtro = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$especie_filtro = isset($_GET['especie']) ? trim($_GET['especie']) : '';
$sexo_filtro = isset($_GET['sexo']) ? trim($_GET['sexo']) : '';

// 2. Construir a consulta SQL dinamicamente
$sql = "SELECT * FROM animais WHERE 1=1";
$params = [];

if (!empty($cidade_filtro)) {
    $sql .= " AND cidade LIKE ?";
    $params[] = "%" . $cidade_filtro . "%";
}
if (!empty($especie_filtro)) {
    $sql .= " AND especie = ?";
    $params[] = $especie_filtro;
}
if (!empty($sexo_filtro)) {
    $sql .= " AND sexo = ?";
    $params[] = $sexo_filtro;
}

$sql .= " ORDER BY data_cadastro DESC";

// 3. Executar a consulta
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $animais = $stmt->fetchAll();
} catch (PDOException $e) {
    $animais = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adote um Amigo - Encontre seu Novo Companheiro</title>
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

        /* Header da página */
        .page-header {
            text-align: center;
            padding: 3rem 1.5rem 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .page-title i {
            color: var(--primary-accent);
            margin-right: 0.75rem;
        }

        .page-subtitle {
            font-size: 1.1rem;
            color: var(--text-color-light);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Seção de filtros */
        .filter-section {
            padding: 0 1.5rem 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        .filter-container {
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-color-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-label i {
            color: var(--primary-accent);
            font-size: 0.9rem;
        }

        .filter-input {
            padding: 0.875rem 1rem;
            background: var(--dark-bg);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: var(--white);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            font-family: inherit;
        }

        .filter-input:focus {
            outline: none;
            border-color: var(--primary-accent);
        }

        .filter-input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        select.filter-input {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%2300e4ff' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1.2em;
            padding-right: 2.5rem;
        }

        select.filter-input option {
            background: var(--dark-bg);
            color: var(--white);
            padding: 0.5rem;
        }

        .filter-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-filter {
            padding: 0.875rem 1.5rem;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0, 228, 255, 0.3);
        }

        .btn-clear {
            padding: 0.875rem 1rem;
            background: var(--dark-bg);
            color: var(--text-color-light);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-clear:hover {
            border-color: var(--primary-accent);
            color: var(--primary-accent);
        }

        /* Contador de resultados */
        .results-info {
            text-align: center;
            padding: 1rem;
            color: var(--text-color-light);
            font-size: 0.95rem;
        }

        .results-count {
            color: var(--primary-accent);
            font-weight: 600;
        }

        /* Grid de animais */
        .animais-section {
            padding: 2rem 1.5rem 4rem;
        }

        .animais-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Card do animal */
        .animal-card {
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .animal-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-accent);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        }

        .animal-card-image {
            width: 100%;
            height: 280px;
            overflow: hidden;
            position: relative;
            background: var(--dark-bg);
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

        .animal-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--primary-accent);
        }

        .animal-card-body {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .animal-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .animal-info {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.9rem;
            color: var(--text-color-light);
        }

        .info-icon {
            width: 32px;
            height: 32px;
            background: var(--dark-bg);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-accent);
            flex-shrink: 0;
        }

        .info-label {
            font-weight: 600;
            color: var(--white);
            min-width: 60px;
        }

        .btn-conhecer {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-conhecer:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 228, 255, 0.3);
        }

        /* Estado vazio */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-accent);
            opacity: 0.5;
            margin-bottom: 1.5rem;
        }

        .empty-state h2 {
            font-size: 1.5rem;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--text-color-light);
            line-height: 1.6;
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .filter-container {
                grid-template-columns: 1fr 1fr;
            }

            .filter-actions {
                grid-column: 1 / -1;
                justify-content: stretch;
            }

            .btn-filter {
                flex: 1;
            }
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2rem;
            }

            .filter-container {
                grid-template-columns: 1fr;
            }

            .animais-grid {
                grid-template-columns: 1fr;
            }

            .animal-card-image {
                height: 240px;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="container">
            <!-- Header -->
            <header class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-heart"></i>
                    Encontre seu Novo Amigo
                </h1>
                <p class="page-subtitle">Utilize os filtros abaixo para encontrar o companheiro ideal para si!</p>
            </header>

            <!-- Seção de Filtros -->
            <section class="filter-section">
                <form action="adote.php" method="GET">
                    <div class="filter-container">
                        <div class="filter-group">
                            <label for="cidade" class="filter-label">
                                <i class="fas fa-map-marker-alt"></i>
                                Cidade
                            </label>
                            <input type="text" 
                                   id="cidade" 
                                   name="cidade" 
                                   class="filter-input" 
                                   placeholder="Digite uma cidade" 
                                   value="<?php echo htmlspecialchars($cidade_filtro); ?>">
                        </div>

                        <div class="filter-group">
                            <label for="especie" class="filter-label">
                                <i class="fas fa-paw"></i>
                                Espécie
                            </label>
                            <select id="especie" name="especie" class="filter-input">
                                <option value="">Todas</option>
                                <option value="Cachorro" <?php if ($especie_filtro == 'Cachorro') echo 'selected'; ?>>🐕 Cachorro</option>
                                <option value="Gato" <?php if ($especie_filtro == 'Gato') echo 'selected'; ?>>🐈 Gato</option>
                                <option value="Outro" <?php if ($especie_filtro == 'Outro') echo 'selected'; ?>>🦜 Outro</option>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="sexo" class="filter-label">
                                <i class="fas fa-venus-mars"></i>
                                Sexo
                            </label>
                            <select id="sexo" name="sexo" class="filter-input">
                                <option value="">Todos</option>
                                <option value="Macho" <?php if ($sexo_filtro == 'Macho') echo 'selected'; ?>>♂️ Macho</option>
                                <option value="Fêmea" <?php if ($sexo_filtro == 'Fêmea') echo 'selected'; ?>>♀️ Fêmea</option>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <button type="submit" class="btn-filter">
                                <i class="fas fa-search"></i>
                                Buscar
                            </button>
                            <a href="adote.php" class="btn-clear" title="Limpar Filtros">
                                <i class="fas fa-redo"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </section>

            <!-- Contador de resultados -->
            <?php if (!empty($animais)): ?>
                <div class="results-info">
                    <span class="results-count"><?php echo count($animais); ?></span> 
                    <?php echo count($animais) == 1 ? 'animal encontrado' : 'animais encontrados'; ?>
                </div>
            <?php endif; ?>

            <!-- Grid de Animais -->
            <section class="animais-section">
                <?php if (empty($animais)): ?>
                    <div class="empty-state">
                        <i class="fas fa-search"></i>
                        <h2>Nenhum animal encontrado</h2>
                        <p>Não encontramos animais com estes critérios. Tente ajustar a sua busca ou limpar os filtros para ver todos os animais disponíveis.</p>
                    </div>
                <?php else: ?>
                    <div class="animais-grid">
                        <?php foreach ($animais as $animal): ?>
                            <article class="animal-card">
                                <div class="animal-card-image">
                                    <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" 
                                         alt="<?php echo htmlspecialchars($animal['nome']); ?>">
                                    <span class="animal-badge">
                                        <i class="fas fa-heart"></i> Disponível
                                    </span>
                                </div>
                                <div class="animal-card-body">
                                    <h3 class="animal-name"><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                    <div class="animal-info">
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-tag"></i>
                                            </div>
                                            <span class="info-label">Raça:</span>
                                            <span><?php echo htmlspecialchars(!empty($animal['raca']) ? $animal['raca'] : 'Vira-lata'); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-birthday-cake"></i>
                                            </div>
                                            <span class="info-label">Idade:</span>
                                            <span><?php echo htmlspecialchars($animal['idade']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-map-marker-alt"></i>
                                            </div>
                                            <span class="info-label">Cidade:</span>
                                            <span><?php echo htmlspecialchars($animal['cidade']); ?></span>
                                        </div>
                                        <div class="info-item">
                                            <div class="info-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <span class="info-label">Bairro:</span>
                                            <span><?php echo htmlspecialchars(!empty($animal['endereco']) ? $animal['endereco'] : 'Não informado'); ?></span>
                                        </div>
                                    </div>
                                    <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" class="btn-conhecer">
                                        Conhecer
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>