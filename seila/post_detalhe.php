<?php
require_once 'config.php';

// Verificar se um ID de post foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit();
}

$post_id = $_GET['id'];

// Buscar os detalhes do post, incluindo o nome do autor
$stmt = $pdo->prepare(
    "SELECT bp.*, u.nome as nome_autor 
     FROM blog_posts bp 
     JOIN usuarios u ON bp.id_autor = u.id 
     WHERE bp.id = ?"
);
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit();
}

// Buscar posts relacionados (excluindo o atual)
$stmt_related = $pdo->prepare(
    "SELECT bp.*, u.nome as nome_autor 
     FROM blog_posts bp 
     JOIN usuarios u ON bp.id_autor = u.id 
     WHERE bp.id != ? 
     ORDER BY bp.data_publicacao DESC 
     LIMIT 3"
);
$stmt_related->execute([$post_id]);
$related_posts = $stmt_related->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['titulo']); ?> - Blog Adote um Amigo</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            color: var(--text-color-light);
            flex-wrap: wrap;
        }

        .breadcrumb-list a {
            color: var(--primary-accent);
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .breadcrumb-list a:hover {
            opacity: 0.8;
        }

        .breadcrumb-separator {
            color: var(--text-color-light);
        }

        /* Container principal */
        .post-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1.5rem 4rem;
        }

        /* Cabeçalho do post */
        .post-header {
            margin-bottom: 3rem;
            text-align: center;
        }

        .post-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--white);
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .post-meta-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            margin-bottom: 2rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-color-light);
            font-size: 0.95rem;
        }

        .meta-item i {
            color: var(--primary-accent);
        }

        .author-info {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--dark-bg-alt);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-accent), var(--secondary-accent));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--white);
            font-weight: 600;
        }

        .author-details {
            text-align: left;
        }

        .author-name {
            font-weight: 600;
            color: var(--white);
            margin-bottom: 0.25rem;
        }

        .author-role {
            font-size: 0.85rem;
            color: var(--text-color-light);
        }

        /* Imagem destacada */
        .featured-image {
            width: 100%;
            max-height: 500px;
            object-fit: cover;
            border-radius: 16px;
            margin: 2rem 0 3rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        }

        /* Conteúdo do post */
        .post-content {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color);
        }

        .post-content p {
            margin-bottom: 1.5rem;
        }

        .post-content h2 {
            font-size: 1.8rem;
            color: var(--white);
            margin: 2.5rem 0 1rem;
            font-weight: 600;
        }

        .post-content h3 {
            font-size: 1.4rem;
            color: var(--white);
            margin: 2rem 0 1rem;
            font-weight: 600;
        }

        .post-content ul, .post-content ol {
            margin: 1.5rem 0;
            padding-left: 2rem;
        }

        .post-content li {
            margin-bottom: 0.75rem;
        }

        /* Seção de compartilhamento */
        .share-section {
            margin: 3rem 0;
            padding: 2rem;
            background: var(--dark-bg-alt);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .share-section h3 {
            font-size: 1.2rem;
            color: var(--white);
            margin-bottom: 1rem;
        }

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .share-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, opacity 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .share-btn:hover {
            transform: translateY(-2px);
            opacity: 0.9;
        }

        .share-btn.facebook {
            background: #1877f2;
            color: white;
        }

        .share-btn.twitter {
            background: #1da1f2;
            color: white;
        }

        .share-btn.whatsapp {
            background: #25d366;
            color: white;
        }

        .share-btn.copy {
            background: var(--primary-accent);
            color: var(--dark-bg);
        }

        /* Botão voltar */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--dark-bg-alt);
            color: var(--primary-accent);
            border-radius: 8px;
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

        /* Posts relacionados */
        .related-posts {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .related-posts h2 {
            font-size: 2rem;
            color: var(--white);
            margin-bottom: 2rem;
            text-align: center;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .related-card {
            background: var(--dark-bg-alt);
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
            text-decoration: none;
            display: block;
        }

        .related-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-accent);
        }

        .related-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .related-card-body {
            padding: 1.25rem;
        }

        .related-card h3 {
            font-size: 1.1rem;
            color: var(--white);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .related-card .meta {
            font-size: 0.85rem;
            color: var(--text-color-light);
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .post-header h1 {
                font-size: 1.8rem;
            }

            .post-meta-info {
                gap: 1rem;
            }

            .post-content {
                font-size: 1rem;
            }

            .share-buttons {
                flex-direction: column;
            }

            .share-btn {
                width: 100%;
                justify-content: center;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <div class="container">
                <nav class="breadcrumb-list">
                    <a href="index.php">Início</a>
                    <span class="breadcrumb-separator">/</span>
                    <a href="blog.php">Blog</a>
                    <span class="breadcrumb-separator">/</span>
                    <span><?php echo htmlspecialchars($post['titulo']); ?></span>
                </nav>
            </div>
        </div>

        <div class="post-container">
            <!-- Botão Voltar -->
            <a href="blog.php" class="back-button">
                <i class="fas fa-arrow-left"></i>
                Voltar ao Blog
            </a>

            <!-- Cabeçalho do Post -->
            <header class="post-header">
                <h1><?php echo htmlspecialchars($post['titulo']); ?></h1>
                
                <div class="post-meta-info">
                    <div class="meta-item">
                        <i class="far fa-calendar"></i>
                        <span><?php echo date('d \d\e F \d\e Y', strtotime($post['data_publicacao'])); ?></span>
                    </div>
                    <div class="meta-item">
                        <i class="far fa-clock"></i>
                        <span><?php echo ceil(str_word_count(strip_tags($post['conteudo'])) / 200); ?> min de leitura</span>
                    </div>
                </div>

                <div class="author-info">
                    <div class="author-avatar">
                        <?php echo strtoupper(substr($post['nome_autor'], 0, 1)); ?>
                    </div>
                    <div class="author-details">
                        <div class="author-name"><?php echo htmlspecialchars($post['nome_autor']); ?></div>
                        <div class="author-role">Autor</div>
                    </div>
                </div>
            </header>

            <!-- Imagem Destacada -->
            <?php if (!empty($post['imagem_destaque'])): ?>
                <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>" 
                     alt="<?php echo htmlspecialchars($post['titulo']); ?>" 
                     class="featured-image">
            <?php endif; ?>

            <!-- Conteúdo do Post -->
            <article class="post-content">
                <?php echo nl2br(htmlspecialchars($post['conteudo'])); ?>
            </article>

            <!-- Seção de Compartilhamento -->
            <div class="share-section">
                <h3>Gostou deste artigo? Compartilhe!</h3>
                <div class="share-buttons">
                    <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" 
                       class="share-btn facebook">
                        <i class="fab fa-facebook-f"></i>
                        Facebook
                    </a>
                    <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>&text=<?php echo urlencode($post['titulo']); ?>" 
                       target="_blank" 
                       class="share-btn twitter">
                        <i class="fab fa-twitter"></i>
                        Twitter
                    </a>
                    <a href="https://wa.me/?text=<?php echo urlencode($post['titulo'] . ' ' . $_SERVER['REQUEST_URI']); ?>" 
                       target="_blank" 
                       class="share-btn whatsapp">
                        <i class="fab fa-whatsapp"></i>
                        WhatsApp
                    </a>
                    <button onclick="copyLink()" class="share-btn copy">
                        <i class="fas fa-link"></i>
                        Copiar Link
                    </button>
                </div>
            </div>

            <!-- Posts Relacionados -->
            <?php if (!empty($related_posts)): ?>
                <div class="related-posts">
                    <h2>Artigos Relacionados</h2>
                    <div class="related-grid">
                        <?php foreach ($related_posts as $related): ?>
                            <a href="post_detalhe.php?id=<?php echo $related['id']; ?>" class="related-card">
                                <?php if (!empty($related['imagem_destaque'])): ?>
                                    <img src="blog_imagens/<?php echo htmlspecialchars($related['imagem_destaque']); ?>" 
                                         alt="<?php echo htmlspecialchars($related['titulo']); ?>">
                                <?php endif; ?>
                                <div class="related-card-body">
                                    <h3><?php echo htmlspecialchars($related['titulo']); ?></h3>
                                    <p class="meta">
                                        <i class="far fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($related['data_publicacao'])); ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'footer.php'; ?>

    <script>
        function copyLink() {
            const url = window.location.href;
            navigator.clipboard.writeText(url).then(() => {
                const btn = event.target.closest('.share-btn');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Link Copiado!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            });
        }
    </script>

</body>
</html>