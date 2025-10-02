<?php
require_once 'config.php';

// Buscar todos os posts do blog, juntamente com o nome do autor
try {
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         ORDER BY bp.data_publicacao DESC"
    );
    $posts = $stmt->fetchAll();
} catch (PDOException $e) {
    $posts = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="section">
            <div class="container">
                <h1 class="section-title">O Nosso Blog</h1>
                <p class="section-subtitle">Histórias que inspiram, dicas para cuidar do seu melhor amigo e as últimas novidades do nosso projeto.</p>
                
                <div class="blog-grid">
                    <?php if (empty($posts)): ?>
                        <div class="testimonial-card text-center">
                            <h3>Ainda não há publicações.</h3>
                            <p>O nosso blog está a ser preparado. Volte em breve para ler as nossas histórias e dicas!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <div class="animal-card">
                                <?php if (!empty($post['imagem_destaque'])): ?>
                                    <div class="animal-card-img-container">
                                        <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>" alt="Imagem de <?php echo htmlspecialchars($post['titulo']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="animal-card-content">
                                    <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                    <p class="info">
                                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($post['nome_autor']); ?></span>
                                        <span><i class="fas fa-calendar-alt"></i> <?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></span>
                                    </p>
                                    <p class="description">
                                        <?php 
                                            $resumo = substr(strip_tags($post['conteudo']), 0, 120);
                                            echo htmlspecialchars($resumo) . '...'; 
                                        ?>
                                    </p>
                                    <div class="card-button-wrapper">
                                        <a href="#" class="btn btn-register btn-card">Ler Mais</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
