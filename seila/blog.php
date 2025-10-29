<?php
require_once 'config.php';

// Buscar todos os posts do blog, juntamente com o nome do autor (APENAS PUBLICADOS)
try {
    // Adicionar coluna status se não existir
    try {
        $pdo->exec("ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS status ENUM('pendente', 'publicado', 'rejeitado') DEFAULT 'publicado'");
    } catch (PDOException $e) {}
    
    $stmt = $pdo->query(
        "SELECT bp.*, u.nome as nome_autor 
         FROM blog_posts bp 
         JOIN usuarios u ON bp.id_autor = u.id 
         WHERE bp.status = 'publicado' OR bp.status IS NULL
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
    <style>
        /* Reset e ajustes gerais */
        main {
            background-color: var(--dark-bg);
            min-height: 100vh;
            padding: 0;
        }

        .section {
            padding: 4rem 0;
        }

        .blog-header {
            text-align: center;
            margin-bottom: 4rem;
            padding: 0 1rem;
        }

        .blog-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .blog-header p {
            font-size: 1.1rem;
            color: var(--text-color-light);
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Grid de posts */
        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Card do post */
        .post-card {
            background-color: var(--dark-bg-alt);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .post-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-color: var(--primary-accent);
        }

        .post-card-image {
            width: 100%;
            height: 240px;
            overflow: hidden;
            background-color: var(--dark-bg);
        }

        .post-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .post-card:hover .post-card-image img {
            transform: scale(1.05);
        }

        .post-card-body {
            padding: 1.5rem;
        }

        .post-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--text-color-light);
        }

        .post-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-author i {
            color: var(--primary-accent);
        }

        .post-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .post-date i {
            color: var(--primary-accent);
        }

        .post-card-body h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.75rem;
            line-height: 1.4;
        }

        .post-excerpt {
            font-size: 0.95rem;
            color: var(--text-color-light);
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .read-more {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary-accent);
            font-weight: 600;
            font-size: 0.95rem;
            text-decoration: none;
            transition: gap 0.3s ease;
        }

        .read-more:hover {
            gap: 0.75rem;
        }

        .read-more i {
            font-size: 0.8rem;
        }

        /* Mensagem de sem posts */
        .no-posts {
            text-align: center;
            padding: 4rem 2rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .no-posts i {
            font-size: 4rem;
            color: var(--primary-accent);
            margin-bottom: 1.5rem;
            opacity: 0.6;
        }

        .no-posts h3 {
            font-size: 1.5rem;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .no-posts p {
            color: var(--text-color-light);
            line-height: 1.6;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .blog-header h1 {
                font-size: 2rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .section {
                padding: 3rem 0;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="section">
            <div class="container">
                <div class="blog-header">
                    <h1>O Nosso Blog</h1>
                    <p>Histórias que inspiram, dicas para cuidar do seu melhor amigo e as últimas novidades do nosso projeto.</p>
                </div>
                
                <?php if (empty($posts)): ?>
                    <div class="no-posts">
                        <i class="fas fa-pen-fancy"></i>
                        <h3>Ainda não há publicações.</h3>
                        <p>O nosso blog está a ser preparado. Volte em breve para ler as nossas histórias e dicas!</p>
                    </div>
                <?php else: ?>
                    <div class="blog-grid">
                        <?php foreach ($posts as $post): ?>
                            <article class="post-card" onclick="window.location.href='post_detalhe.php?id=<?php echo $post['id']; ?>'">
                                <?php if (!empty($post['imagem_destaque'])): ?>
                                    <div class="post-card-image">
                                        <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>" alt="<?php echo htmlspecialchars($post['titulo']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="post-card-body">
                                    <div class="post-meta">
                                        <div class="post-author">
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($post['nome_autor']); ?></span>
                                        </div>
                                        <div class="post-date">
                                            <i class="far fa-calendar"></i>
                                            <span><?php echo date('d/m/Y', strtotime($post['data_publicacao'])); ?></span>
                                        </div>
                                    </div>
                                    <h3><?php echo htmlspecialchars($post['titulo']); ?></h3>
                                    <p class="post-excerpt">
                                        <?php 
                                            $resumo = substr(strip_tags($post['conteudo']), 0, 120);
                                            echo htmlspecialchars($resumo) . (strlen($post['conteudo']) > 120 ? '...' : ''); 
                                        ?>
                                    </p>
                                    <a href="post_detalhe.php?id=<?php echo $post['id']; ?>" class="read-more">
                                        Ler Mais <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>