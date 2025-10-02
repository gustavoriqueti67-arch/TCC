<?php
// Inclui o ficheiro de configuração para acesso à base de dados e funções
require_once 'config.php';

// Tenta buscar os 4 animais mais recentes do banco de dados
try {
    // A cláusula LIMIT 4 garante que apenas 4 animais são selecionados
    $stmt = $pdo->query("SELECT * FROM animais ORDER BY data_cadastro DESC LIMIT 4");
    $animais_destaque = $stmt->fetchAll();
} catch (PDOException $e) {
    // Em caso de erro, define a lista como vazia para não quebrar a página
    $animais_destaque = [];
    // Opcional: registar o erro num ficheiro de log para depuração
    // error_log("Erro ao buscar animais em destaque: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adote um Amigo - Encontre seu novo companheiro</title>
    
    <!-- CSS Personalizado -->
    <link rel="stylesheet" href="style.css">

    <!-- Google Fonts: Poppins e Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Ícones (Font Awesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <!-- Seção Principal (Hero) -->
        <section class="hero-section">
            <div class="container hero-content">
                <h1>Encontre seu melhor amigo</h1>
                <p>Milhares de cães e gatos esperam por um lar. A sua jornada para encontrar um companheiro fiel começa aqui.</p>
                <a href="adote.php" class="btn btn-cta-hero">Quero Adotar!</a>
            </div>
        </section>

        <!-- Seção Animais em Destaque -->
        <section class="section section-bg-white">
            <div class="container">
                <h2 class="section-title">Recém-chegados para Adoção</h2>
                <p class="section-subtitle">Conheça alguns dos nossos amigos que mal podem esperar para te conhecer.</p>
                
                <?php if (empty($animais_destaque)): ?>
                    <div style="text-align: center; padding: 2rem; color: var(--text-color-light);">
                        <p>Ainda não há animais cadastrados. Que tal ser o primeiro?</p>
                    </div>
                <?php else: ?>
                    <div class="destaques-grid">
                        <?php foreach ($animais_destaque as $animal): ?>
                        <!-- Card de Animal Dinâmico -->
                        <div class="animal-card">
                            <div class="animal-card-img-container">
                                <img src="animais_fotos/<?php echo htmlspecialchars($animal['foto_animal']); ?>" alt="Foto de <?php echo htmlspecialchars($animal['nome']); ?>">
                            </div>
                            <div class="animal-card-content">
                                <h3><?php echo htmlspecialchars($animal['nome']); ?></h3>
                                <p class="info"><?php echo htmlspecialchars($animal['sexo']); ?>, <?php echo htmlspecialchars($animal['idade']); ?></p>
                                <p class="description"><?php echo htmlspecialchars(substr($animal['descricao'], 0, 80)) . '...'; ?></p>
                                <div class="card-button-wrapper">
                                    <a href="animal_detalhe.php?id=<?php echo $animal['id']; ?>" class="btn btn-register btn-card">Conhecer</a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Seção Como Funciona -->
        <section class="section section-bg-pattern">
            <div class="container">
                <h2 class="section-title">Adotar é um ato de amor e é fácil!</h2>
                <div class="como-funciona-steps">
                    <!-- Passo 1 -->
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <h3>1. Encontre seu Pet</h3>
                        <p>Use nossos filtros para encontrar o companheiro ideal para seu estilo de vida.</p>
                    </div>
                    <!-- Passo 2 -->
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h3>2. Candidate-se</h3>
                        <p>Preencha o formulário de adoção online com suas informações.</p>
                    </div>
                    <!-- Passo 3 -->
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3>3. Converse Conosco</h3>
                        <p>Nossa equipe entrará em contato para uma entrevista e para agendar uma visita.</p>
                    </div>
                    <!-- Passo 4 -->
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

        <!-- Seção de Depoimentos (Conteúdo Estático) -->
        <section class="section">
            <div class="container">
                <h2 class="section-title">Histórias que aquecem o coração</h2>
                <div class="depoimentos-grid">
                    <div class="testimonial-card">
                        <i class="fas fa-quote-left quote-icon"></i>
                        <p class="text">"Nunca imaginei que encontraria um amigo tão fiel. O processo de adoção foi super tranquilo e a equipe nos deu todo o suporte. Hoje, o Paçoca é a alegria da nossa casa!"</p>
                        <div class="testimonial-author">
                            <img src="https://placehold.co/60x60/2ECC71/FFFFFF?text=F" alt="Foto da Família">
                            <div class="author-info">
                                <p class="name">Família Silva</p>
                                <p class="role">Adotaram o Paçoca</p>
                            </div>
                        </div>
                    </div>
                    <div class="depoimentos-img">
                        <img src="https://placehold.co/500x350/3498DB/FFFFFF?text=Adotante+Feliz" alt="Família feliz com seu novo pet">
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Rodapé -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3><i class="fas fa-paw"></i> Adote um Amigo</h3>
                    <p>Conectando animais a lares amorosos. Transformando vidas, uma patinha de cada vez.</p>
                </div>
                <div class="footer-col">
                    <h3>Navegue</h3>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="adote.php">Adote</a></li>
                        <li><a href="#">Blog</a></li>
                        <li><a href="#">Sobre Nós</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contato</h3>
                    <p>Rua Fictícia, 123</p>
                    <p>Cidade Exemplo, Brasil</p>
                    <p>contato@adoteumamigo.com</p>
                </div>
                <div class="footer-col">
                    <h3>Siga-nos</h3>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Adote um Amigo. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>

