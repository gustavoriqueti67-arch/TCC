<?php
// Inclui o ficheiro de configuração para acesso à base de dados e funções
require_once 'config.php';

// Tenta buscar os 4 animais mais recentes do banco de dados
try {
    $stmt = $pdo->query("SELECT * FROM animais ORDER BY data_cadastro DESC LIMIT 4");
    $animais_destaque = $stmt->fetchAll();
} catch (PDOException $e) {
    $animais_destaque = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adote um Amigo - Encontre seu novo companheiro</title>
    
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <style>
        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.5)), 
                        url('https://images.unsplash.com/photo-1450778869180-41d0601e046e?w=1920') center/cover;
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4rem 1.5rem;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, var(--primary-accent) 0%, transparent 50%);
            opacity: 0.1;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 800px;
            animation: fadeInUp 1s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1.5rem;
            line-height: 1.2;
            text-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2.5rem;
            line-height: 1.6;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .btn-hero {
            padding: 1.25rem 3rem;
            font-size: 1.2rem;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            border: none;
            border-radius: 50px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 10px 30px rgba(0, 228, 255, 0.3);
        }

        .btn-hero:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 228, 255, 0.5);
        }

        /* Seções */
        .section {
            padding: 5rem 1.5rem;
        }

        .section-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--text-color-light);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Grid de Animais em Destaque */
        .destaques-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }

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
            transform: translateY(-10px);
            border-color: var(--primary-accent);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        .animal-card-img {
            width: 100%;
            height: 280px;
            overflow: hidden;
            position: relative;
        }

        .animal-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .animal-card:hover .animal-card-img img {
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

        .animal-card-body h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 0.5rem;
        }

        .animal-info {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: var(--text-color-light);
        }

        .animal-info span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .animal-info i {
            color: var(--primary-accent);
        }

        .animal-description {
            color: var(--text-color-light);
            line-height: 1.6;
            margin-bottom: 1.5rem;
            flex: 1;
        }

        .btn-conhecer {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            color: var(--dark-bg);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-conhecer:hover {
            transform: translateY(-2px);
        }

        /* Como Funciona */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2.5rem;
            max-width: 1400px;
            margin: 0 auto;
        }

        .step {
            text-align: center;
            padding: 2rem;
            background: var(--dark-bg-alt);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .step:hover {
            transform: translateY(-5px);
            border-color: var(--primary-accent);
        }

        .step-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            color: var(--dark-bg);
        }

        .step h3 {
            font-size: 1.3rem;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .step p {
            color: var(--text-color-light);
            line-height: 1.6;
        }

        /* Testemunhos */
        .testimonials-section {
            background: var(--dark-bg-alt);
            padding: 5rem 1.5rem;
        }

        .testimonial-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            align-items: center;
        }

        .testimonial-card {
            background: var(--dark-bg);
            padding: 2.5rem;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .quote-icon {
            font-size: 2.5rem;
            color: var(--primary-accent);
            opacity: 0.3;
            margin-bottom: 1.5rem;
        }

        .testimonial-text {
            font-size: 1.1rem;
            color: var(--text-color-light);
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .testimonial-author {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .author-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: 3px solid var(--primary-accent);
        }

        .author-name {
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.25rem;
        }

        .author-role {
            font-size: 0.9rem;
            color: var(--text-color-light);
        }

        .testimonial-image {
            border-radius: 16px;
            overflow: hidden;
            height: 100%;
            min-height: 400px;
        }

        .testimonial-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Empty State */
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
        }

        /* Responsividade */
        @media (max-width: 1024px) {
            .testimonial-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .hero-content h1 {
                font-size: 2.5rem;
            }

            .hero-content p {
                font-size: 1.1rem;
            }

            .section-title {
                font-size: 2rem;
            }

            .destaques-grid {
                grid-template-columns: 1fr;
            }

            .steps-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <!-- Seção Hero -->
        <section class="hero-section">
            <div class="hero-content">
                <h1>Encontre seu melhor amigo</h1>
                <p>Milhares de cães e gatos esperam por um lar. A sua jornada para encontrar um companheiro fiel começa aqui.</p>
                <a href="adote.php" class="btn-hero">
                    <i class="fas fa-heart"></i>
                    Quero Adotar!
                </a>
            </div>
        </section>

        <!-- Seção Animais em Destaque -->
        <section class="section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Recém-chegados para Adoção</h2>
                    <p class="section-subtitle">Conheça alguns dos nossos amigos que mal podem esperar para te conhecer.</p>
                </div>
                
                <?php if (empty($animais_destaque)): ?>
                    <div class="empty-state">
                        <i class="fas fa-paw"></i>
                        <h3>Ainda não há animais cadastrados</h3>
                        <p>Que tal ser o primeiro a cadastrar um amiguinho?</p>
                    </div>
                <?php else: ?>
                    <div class="destaques-grid">
                        <?php foreach ($animais_destaque as $animal): ?>
                        <article class="animal-card">
                            <div class="animal-card-img">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" 
                                     alt="<?php echo htmlspecialchars($animal['nome']); ?>">
                                <span class="animal-badge">
                                    <i class="fas fa-heart"></i> Novo
                                </span>
                            </div>
                            <div class="animal-card-body">
                                <h3><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                <div class="animal-info">
                                    <span>
                                        <i class="fas fa-venus-mars"></i>
                                        <?php echo htmlspecialchars($animal['sexo']); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-birthday-cake"></i>
                                        <?php echo htmlspecialchars($animal['idade']); ?>
                                    </span>
                                </div>
                                <p class="animal-description">
                                    <?php echo htmlspecialchars(substr($animal['descricao'], 0, 100)) . '...'; ?>
                                </p>
                                <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" class="btn-conhecer">
                                    Conhecer
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção Como Funciona -->
        <section class="section" style="background: var(--dark-bg-alt);">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Adotar é um ato de amor e é fácil!</h2>
                    <p class="section-subtitle">Siga estes simples passos e transforme uma vida</p>
                </div>
                <div class="steps-grid">
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>1. Encontre seu Pet</h3>
                        <p>Use nossos filtros para encontrar o companheiro ideal para seu estilo de vida.</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>2. Candidate-se</h3>
                        <p>Preencha o formulário de adoção online com suas informações.</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>3. Converse Conosco</h3>
                        <p>Nossa equipe entrará em contato para uma entrevista e para agendar uma visita.</p>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h3>4. Lar Feliz</h3>
                        <p>Após a aprovação, prepare-se para receber seu novo amigo em casa!</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Seção de Depoimentos -->
        <section class="testimonials-section">
            <div class="container">
                <div class="section-header">
                    <h2 class="section-title">Histórias que aquecem o coração</h2>
                </div>
                <div class="testimonial-grid">
                    <div class="testimonial-card">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p class="testimonial-text">
                            "Nunca imaginei que encontraria um amigo tão fiel. O processo de adoção foi super tranquilo e a equipe nos deu todo o suporte. Hoje, o Paçoca é a alegria da nossa casa!"
                        </p>
                        <div class="testimonial-author">
                            <img src="https://ui-avatars.com/api/?name=Familia+Silva&background=00e4ff&color=1a1a2e&size=60" 
                                 alt="Família Silva" 
                                 class="author-avatar">
                            <div>
                                <p class="author-name">Família Silva</p>
                                <p class="author-role">Adotaram o Paçoca</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-image">
                        <img src="https://images.unsplash.com/photo-1601758228041-f3b2795255f1?w=800" 
                             alt="Família feliz com pet">
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?php include 'footer.php'; ?>

</body>
</html>