<?php
require_once 'config.php';

// --- Lógica dos Filtros ---
// 1. Pegar os valores dos filtros da URL (se existirem)
// Compatível com versões antigas do PHP
$cidade_filtro = isset($_GET['cidade']) ? trim($_GET['cidade']) : '';
$especie_filtro = isset($_GET['especie']) ? trim($_GET['especie']) : '';
$sexo_filtro = isset($_GET['sexo']) ? trim($_GET['sexo']) : '';

// 2. Construir a consulta SQL dinamicamente
$sql = "SELECT * FROM animais WHERE 1=1"; // O "1=1" facilita adicionar cláusulas AND
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
    // error_log("Erro ao buscar animais: " . $e->getMessage());
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
        .page-header { text-align: center; padding: 4rem 1.5rem 2rem; }
        .page-title { font-size: 2.5rem; color: var(--primary-accent); margin-bottom: 0.5rem; }
        .page-subtitle { font-size: 1.1rem; color: var(--text-color-light); max-width: 600px; margin: 0 auto; }
        .animais-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 2rem; padding: 2rem 0; }
        .animal-card { display: flex; flex-direction: column; }
        .animal-card-content { padding-bottom: 1rem; }
        .animal-details { margin-bottom: 1rem; }
        .detail-item { font-size: 0.9rem; color: var(--text-color-light); margin-bottom: 0.4rem; display: flex; align-items: center; }
        .detail-item strong { color: var(--text-color); margin-right: 0.4rem; }
        .detail-item i { margin-right: 0.7rem; color: var(--secondary-accent); width: 14px; text-align: center; }
        .card-button-wrapper { padding: 0 1.5rem 1.5rem; margin-top: auto; display: flex; justify-content: center; }
        .btn-card-conhecer { padding: 0.8rem 1.5rem; font-size: 1rem; width: 100%; }

        /* --- Estilos para a Barra de Filtros (Novo Design Profissional) --- */
        .filter-section {
            padding: 0;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            background-color: transparent;
            border: none;
        }
        .filter-form {
            display: flex;
            background-color: var(--dark-bg-alt);
            border-radius: var(--border-radius);
            border: 1px solid var(--border-color);
            overflow: hidden;
            align-items: stretch;
            padding: 0.5rem;
            gap: 0.5rem;
        }
        .filter-group {
            padding: 0.5rem 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--border-color);
        }
        .filter-group:last-of-type {
             border-right: none;
        }
        .filter-group:first-child {
             flex-grow: 2; /* Mais espaço para cidade */
        }
        .filter-group label {
            font-size: 0.75rem;
            font-weight: 500;
            color: var(--text-color-light);
            margin-bottom: 0.25rem;
            text-transform: uppercase;
        }
        .filter-input {
            width: 100%;
            background-color: transparent;
            border: none;
            padding: 0;
            color: var(--white);
            font-family: var(--font-main);
            font-size: 1rem;
            outline: none;
        }
        select.filter-input {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23a0a0a0' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.5rem center;
            background-size: 1em;
            padding-right: 2rem;
        }
        /* CORREÇÃO AQUI */
        select.filter-input option {
            background: var(--dark-bg-alt);
            color: var(--text-color);
        }
        .filter-buttons-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex-shrink: 0;
        }
        .btn-filter, .btn-clear {
            padding: 0.75rem;
            aspect-ratio: 1 / 1;
            border-radius: 8px;
            border: none;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-filter {
            background: linear-gradient(45deg, var(--primary-accent), var(--secondary-accent));
        }
        .btn-filter:hover {
            opacity: 0.9;
        }
        .btn-clear {
            background-color: var(--dark-bg);
            color: var(--text-color-light);
        }
        .btn-clear:hover {
            background-color: #3e4c5a;
        }

        @media (max-width: 768px) {
            .filter-form {
                flex-direction: column;
                padding: 1rem;
            }
            .filter-group {
                border-right: none;
                border-bottom: 1px solid var(--border-color);
                padding-bottom: 1rem;
                margin-bottom: 1rem;
            }
            .filter-group:last-of-type {
                 border-bottom: none;
                 margin-bottom: 0;
            }
            .filter-buttons-group {
                flex-direction: row;
            }
            .btn-filter, .btn-clear {
                flex-grow: 1;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <div class="container">
            <header class="page-header">
                <h1 class="page-title">Encontre seu Novo Amigo</h1>
                <p class="page-subtitle">Utilize os filtros abaixo para encontrar o companheiro ideal para si!</p>
            </header>

            <!-- Seção de Filtros -->
            <section class="filter-section">
                <form action="adote.php" method="GET" class="filter-form">
                    <div class="filter-group">
                        <label for="cidade">Cidade</label>
                        <input type="text" id="cidade" name="cidade" class="filter-input" placeholder="Digite uma cidade" value="<?php echo htmlspecialchars($cidade_filtro); ?>">
                    </div>
                    <div class="filter-group">
                        <label for="especie">Espécie</label>
                        <select id="especie" name="especie" class="filter-input">
                            <option value="">Todas</option>
                            <option value="Cachorro" <?php if ($especie_filtro == 'Cachorro') echo 'selected'; ?>>Cachorro</option>
                            <option value="Gato" <?php if ($especie_filtro == 'Gato') echo 'selected'; ?>>Gato</option>
                            <option value="Outro" <?php if ($especie_filtro == 'Outro') echo 'selected'; ?>>Outro</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="sexo">Sexo</label>
                        <select id="sexo" name="sexo" class="filter-input">
                            <option value="">Todos</option>
                            <option value="Macho" <?php if ($sexo_filtro == 'Macho') echo 'selected'; ?>>Macho</option>
                            <option value="Fêmea" <?php if ($sexo_filtro == 'Fêmea') echo 'selected'; ?>>Fêmea</option>
                        </select>
                    </div>
                    <div class="filter-buttons-group">
                        <button type="submit" class="btn btn-filter" title="Buscar"><i class="fas fa-search"></i></button>
                        <a href="adote.php" class="btn btn-clear" title="Limpar Filtros"><i class="fas fa-times"></i></a>
                    </div>
                </form>
            </section>

            <?php if (empty($animais)): ?>
                <div style="text-align: center; padding: 4rem; color: var(--text-color-light);">
                    <h2>Nenhum animal encontrado com estes critérios.</h2>
                    <p>Tente ajustar a sua busca ou limpar os filtros.</p>
                </div>
            <?php else: ?>
                <div class="animais-grid">
                    <?php foreach ($animais as $animal): ?>
                        <div class="animal-card">
                            <div class="animal-card-img-container">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>">
                            </div>
                            <div class="animal-card-content">
                                <h3><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                <div class="animal-details">
                                    <p class="detail-item"><strong>Raça:</strong> <span><?php echo htmlspecialchars(!empty($animal['raca']) ? $animal['raca'] : 'Não informada'); ?></span></p>
                                    <p class="detail-item"><strong>Idade:</strong> <span><?php echo htmlspecialchars($animal['idade']); ?></span></p>
                                    <p class="detail-item"><i class="fas fa-map-marker-alt"></i><strong>Cidade:</strong> <span><?php echo htmlspecialchars($animal['cidade']); ?></span></p>
                                    <p class="detail-item"><i class="fas fa-home"></i><strong>Bairro:</strong> <span><?php echo htmlspecialchars(!empty($animal['endereco']) ? $animal['endereco'] : 'Não informado'); ?></span></p>
                                </div>
                            </div>
                            <div class="card-button-wrapper">
                                 <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" class="btn btn-register btn-card">Conhecer</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
<?php include 'footer.php'; ?>
</body>
</html>

