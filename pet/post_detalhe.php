<?php
require_once 'config.php';

// Verificar se um ID de post foi fornecido
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: blog.php');
    exit();
}

$post_id = $_GET['id'];

// Buscar os detalhes do post, incluindo o nome do autor (APENAS PUBLICADOS)
// Adicionar coluna status se não existir
try {
    $pdo->exec("ALTER TABLE blog_posts ADD COLUMN IF NOT EXISTS status ENUM('pendente', 'publicado', 'rejeitado') DEFAULT 'publicado'");
} catch (PDOException $e) {}

$stmt = $pdo->prepare(
    "SELECT bp.*, u.nome as nome_autor 
     FROM blog_posts bp 
     JOIN usuarios u ON bp.id_autor = u.id 
     WHERE bp.id = ? AND (bp.status = 'publicado' OR bp.status IS NULL)"
);
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if (!$post) {
    header('Location: blog.php');
    exit();
}

// Utilitários de apresentação
// URL absoluta para compartilhamento
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$requestUri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$fullUrl = $scheme . '://' . $host . $requestUri;

// Data em pt-BR (sem depender de locale do servidor)
$monthsPt = [
    'January' => 'janeiro', 'February' => 'fevereiro', 'March' => 'março', 'April' => 'abril',
    'May' => 'maio', 'June' => 'junho', 'July' => 'julho', 'August' => 'agosto',
    'September' => 'setembro', 'October' => 'outubro', 'November' => 'novembro', 'December' => 'dezembro'
];
$day = date('d', strtotime($post['data_publicacao']));
$monthEn = date('F', strtotime($post['data_publicacao']));
$year = date('Y', strtotime($post['data_publicacao']));
$datePt = $day . ' de ' . (isset($monthsPt[$monthEn]) ? $monthsPt[$monthEn] : strtolower($monthEn)) . ' de ' . $year;

// Conteúdo com rich-text básico seguro
$allowedTags = '<p><br><strong><b><em><i><ul><ol><li><a><h2><h3><blockquote><img>'; 
$safeContent = nl2br(strip_tags($post['conteudo'], $allowedTags));

// Descrição curta para meta tags
$rawDescription = trim(strip_tags($post['conteudo']));
$metaDescription = mb_substr($rawDescription, 0, 160, 'UTF-8');

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
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?php echo htmlspecialchars($post['titulo']); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($fullUrl); ?>">
    <?php if (!empty($post['imagem_destaque'])): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars((($scheme==='https')?'https':'http') . '://' . $host . '/blog_imagens/' . $post['imagem_destaque']); ?>">
    <?php endif; ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($post['titulo']); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="animations.js" defer></script>
    <style>
        main { 
            background-color: var(--dark-bg); 
            min-height: 100vh; 
            padding: 2rem 0;
        }

        /* Container Principal - Limpo e Centrado */
        .post-container { 
            max-width: 900px; 
            margin: 0 auto; 
            padding: 0 1.5rem; 
        }

        /* Hero Section Simples */
        .post-hero { 
            background: var(--card-background); 
            border-radius: 16px; 
            overflow: hidden; 
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .post-hero-media { 
            width: 100%; 
            max-height: 500px; 
            overflow: hidden; 
            background: #000; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
        }
        
        .post-hero-media img { 
            width: 100%; 
            height: auto; 
            max-height: 500px; 
            object-fit: cover; 
            display: block; 
        }
        
        .post-hero-content { 
            padding: 2rem; 
        }
        
        .post-title { 
            font-size: clamp(2rem, 4vw, 3rem); 
            font-weight: 700; 
            color: var(--white); 
            margin-bottom: 1rem; 
            line-height: 1.2; 
        }
        
        .post-meta-row { 
            display: flex; 
            gap: 1.5rem; 
            flex-wrap: wrap; 
            color: var(--text-color-light); 
            font-size: 0.95rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .post-meta-chip { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
        }
        
        .post-meta-chip i { 
            color: var(--primary-accent); 
        }

        /* Article Content - Simplificado */
        .post-article { 
            background: var(--card-background); 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            padding: 3rem; 
            margin-bottom: 3rem;
        }
        
        .post-content { 
            font-size: 1.125rem; 
            line-height: 1.8; 
            color: var(--text-secondary); 
        }
        
        .post-content p { 
            margin-bottom: 1.5rem; 
        }
        
        .post-content h2 { 
            font-size: clamp(1.5rem, 3vw, 2rem); 
            margin: 2.5rem 0 1rem; 
            font-weight: 700; 
            color: var(--white);
        }
        
        .post-content h3 { 
            font-size: clamp(1.25rem, 2.5vw, 1.5rem); 
            margin: 2rem 0 1rem; 
            font-weight: 700; 
            color: var(--white);
        }
        
        .post-content ul, .post-content ol { 
            margin: 1.5rem 0 1.5rem 2rem; 
        }
        
        .post-content li { 
            margin-bottom: 0.75rem; 
        }
        
        .post-content img { 
            max-width: 100%; 
            height: auto; 
            border-radius: 12px; 
            margin: 2rem 0;
        }

        /* Sidebar Compacto */
        .post-sidebar { 
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        /* Autor card compacto */
        .author-card { 
            background: var(--card-background); 
            border: 1px solid var(--border-color); 
            border-radius: 16px; 
            padding: 1.5rem; 
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .author-avatar { 
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            background: linear-gradient(135deg, var(--primary), var(--secondary)); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: var(--white); 
            font-weight: 700; 
            font-size: 1.5rem;
            flex-shrink: 0;
        }
        
        .author-name { 
            font-weight: 700; 
            color: var(--white);
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        
        .author-role { 
            color: var(--text-color-light); 
            font-size: 0.95rem; 
        }

        /* Share compacto */
        .share-card h3 { 
            font-size: 1rem; 
            margin-bottom: 1rem;
            color: var(--white);
        }
        
        .share-buttons { 
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .share-btn { 
            padding: 0.875rem 1rem; 
            border-radius: 12px; 
            border: none; 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            gap: 0.75rem; 
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .share-btn.facebook { background: #1877f2; color: #fff; }
        .share-btn.twitter { background: #1da1f2; color: #fff; }
        .share-btn.whatsapp { background: #25d366; color: #fff; }
        .share-btn.copy { background: var(--primary); color: var(--white); }
        
        .share-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Related Posts - Simples */
        .related-posts { 
            background: var(--card-background);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 2rem;
        }
        
        .related-title { 
            font-size: 1.5rem; 
            margin-bottom: 1.5rem;
            color: var(--white);
            font-weight: 700;
        }
        
        .related-grid { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); 
            gap: 1.25rem; 
        }
        
        .related-card { 
            background: transparent;
            border: 1px solid var(--border-color); 
            border-radius: 12px; 
            overflow: hidden; 
            display: block; 
            transition: all 0.3s ease;
        }
        
        .related-card:hover { 
            transform: translateY(-4px); 
            border-color: var(--primary); 
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }
        
        .related-thumb { 
            height: 140px; 
            object-fit: cover; 
            width: 100%; 
        }
        
        .related-card-body { 
            padding: 1rem; 
        }
        
        .related-card h3 { 
            font-size: 1rem; 
            margin-bottom: 0.5rem;
            color: var(--white);
        }
        
        .related-card .meta { 
            font-size: 0.85rem; 
            color: var(--text-color-light); 
        }

        /* Back button */
        .back-button { 
            display: inline-flex; 
            align-items: center; 
            gap: 0.5rem; 
            padding: 0.75rem 1.25rem; 
            border-radius: 10px; 
            background: transparent; 
            color: var(--primary-accent); 
            border: 1px solid var(--border-color); 
            font-weight: 600;
            margin-bottom: 2rem;
            transition: all 0.3s ease;
        }
        
        .back-button:hover {
            border-color: var(--primary-accent);
            background: rgba(0, 170, 255, 0.1);
        }

        /* TOC oculto por padrão - só aparece se necessário */
        .toc-container { 
            display: none;
        }

        /* Responsive */
        @media (max-width: 1024px) { 
            .post-sidebar {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) { 
            .post-container { 
                padding: 0 1rem; 
            }
            
            .post-hero-content,
            .post-article {
                padding: 1.5rem;
            }
            
            .post-title {
                font-size: 1.75rem;
            }
            
            .post-meta-row {
                gap: 1rem;
                font-size: 0.85rem;
            }
            
            .related-grid {
                grid-template-columns: 1fr;
            }
            
            .share-buttons {
                display: grid;
                grid-template-columns: 1fr 1fr;
            }
            
            .post-sidebar {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <!-- Barra de Progresso de Leitura -->
        <div class="reading-progress" id="reading-progress"></div>

        <div class="post-container">
            <!-- Botão Voltar -->
            <a href="blog.php" class="back-button">
                <i class="fas fa-arrow-left"></i> 
                Voltar ao Blog
            </a>

            <!-- Hero Section -->
            <section class="post-hero">
                <?php if (!empty($post['imagem_destaque'])): ?>
                    <div class="post-hero-media">
                        <img src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>" 
                             alt="<?php echo htmlspecialchars($post['titulo']); ?>" 
                             class="lightbox-trigger"
                             data-src="blog_imagens/<?php echo htmlspecialchars($post['imagem_destaque']); ?>">
                    </div>
                <?php endif; ?>
                <div class="post-hero-content">
                    <h1 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h1>
                    <div class="post-meta-row">
                        <span class="post-meta-chip"><i class="far fa-calendar"></i> <?php echo htmlspecialchars($datePt); ?></span>
                        <span class="post-meta-chip"><i class="far fa-clock"></i> <?php echo ceil(str_word_count(strip_tags($post['conteudo'])) / 200); ?> min de leitura</span>
                        <span class="post-meta-chip"><i class="far fa-user"></i> <?php echo htmlspecialchars($post['nome_autor']); ?></span>
                    </div>
                </div>
            </section>

            <!-- Conteúdo do Post -->
            <article class="post-article" id="post-content">
                <div class="post-content">
                    <?php echo $safeContent; ?>
                </div>
            </article>

            <!-- Sidebar Compacto -->
            <div class="post-sidebar">
                <div class="author-card">
                    <div class="author-avatar"><?php echo strtoupper(substr($post['nome_autor'], 0, 1)); ?></div>
                    <div>
                        <div class="author-name"><?php echo htmlspecialchars($post['nome_autor']); ?></div>
                        <div class="author-role">Autor do Artigo</div>
                    </div>
                </div>

                <div class="share-card">
                    <h3>Compartilhe</h3>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($fullUrl); ?>" target="_blank" class="share-btn facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($fullUrl); ?>&text=<?php echo urlencode($post['titulo']); ?>" target="_blank" class="share-btn twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($post['titulo'] . ' ' . $fullUrl); ?>" target="_blank" class="share-btn whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                        <button onclick="copyLink()" class="share-btn copy">
                            <i class="fas fa-link"></i> Copiar Link
                        </button>
                    </div>
                </div>
            </div>

            <!-- Posts Relacionados -->
            <?php if (!empty($related_posts)): ?>
                <div class="related-posts">
                    <h2 class="related-title">Artigos Relacionados</h2>
                    <div class="related-grid">
                        <?php foreach ($related_posts as $related): ?>
                            <a href="post_detalhe.php?id=<?php echo $related['id']; ?>" class="related-card">
                                <?php if (!empty($related['imagem_destaque'])): ?>
                                    <img class="related-thumb" src="blog_imagens/<?php echo htmlspecialchars($related['imagem_destaque']); ?>" alt="<?php echo htmlspecialchars($related['titulo']); ?>">
                                <?php endif; ?>
                                <div class="related-card-body">
                                    <h3><?php echo htmlspecialchars($related['titulo']); ?></h3>
                                    <p class="meta"><?php echo date('d/m/Y', strtotime($related['data_publicacao'])); ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Lightbox -->
    <div class="lightbox" id="lightbox">
        <div class="lightbox-content">
            <img class="lightbox-image" id="lightbox-image" src="" alt="">
            <button class="lightbox-close" onclick="closeLightbox()">
                <i class="fas fa-times"></i>
            </button>
            <button class="lightbox-nav lightbox-prev" onclick="prevImage()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="lightbox-nav lightbox-next" onclick="nextImage()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
        // Barra de Progresso de Leitura
        function updateReadingProgress() {
            const progressBar = document.getElementById('reading-progress');
            const article = document.getElementById('post-content');
            if (!progressBar || !article) return;

            const articleTop = article.offsetTop;
            const articleHeight = article.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrollTop = window.pageYOffset;
            
            const progress = Math.min(100, Math.max(0, 
                ((scrollTop - articleTop + windowHeight) / articleHeight) * 100
            ));
            
            progressBar.style.width = progress + '%';
        }

        // TOC (Table of Contents)
        function generateTOC() {
            const tocContainer = document.getElementById('toc-container');
            const tocList = document.getElementById('toc-list');
            const article = document.getElementById('post-content');
            
            if (!tocContainer || !tocList || !article) return;

            const headings = article.querySelectorAll('h2, h3');
            if (headings.length === 0) return;

            tocContainer.style.display = 'block';
            tocList.innerHTML = '';

            headings.forEach((heading, index) => {
                const id = `heading-${index}`;
                heading.id = id;
                
                const li = document.createElement('li');
                li.className = 'toc-item';
                
                const a = document.createElement('a');
                a.href = `#${id}`;
                a.className = 'toc-link';
                a.textContent = heading.textContent;
                a.style.paddingLeft = heading.tagName === 'H3' ? '1rem' : '0';
                
                li.appendChild(a);
                tocList.appendChild(li);
            });

            // Highlight active section
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.id;
                        const activeLink = tocList.querySelector(`a[href="#${id}"]`);
                        if (activeLink) {
                            tocList.querySelectorAll('.toc-link').forEach(link => 
                                link.classList.remove('active')
                            );
                            activeLink.classList.add('active');
                        }
                    }
                });
            }, { rootMargin: '-20% 0px -70% 0px' });

            headings.forEach(heading => observer.observe(heading));

            // Smooth scroll para os links do TOC
            tocList.querySelectorAll('.toc-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const targetId = link.getAttribute('href');
                    const targetElement = document.querySelector(targetId);
                    if (targetElement) {
                        targetElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        }

        // Lightbox
        let currentImageIndex = 0;
        let images = [];

        function initLightbox() {
            // Adicionar lightbox-trigger a todas as imagens do post
            const postImages = document.querySelectorAll('#post-content img');
            postImages.forEach((img, index) => {
                img.classList.add('lightbox-trigger');
                img.dataset.src = img.src;
                img.dataset.index = index;
                
                // Adicionar cursor pointer
                img.style.cursor = 'pointer';
                
                // Adicionar overlay visual
                img.style.transition = 'transform 0.2s ease';
                img.addEventListener('mouseenter', () => {
                    img.style.transform = 'scale(1.02)';
                    img.style.filter = 'brightness(1.1)';
                });
                img.addEventListener('mouseleave', () => {
                    img.style.transform = 'scale(1)';
                    img.style.filter = 'brightness(1)';
                });
            });
            
            // Combine com as imagens de destaque já existentes
            const heroImages = Array.from(document.querySelectorAll('.lightbox-trigger:not(#post-content img)'));
            heroImages.forEach((img, idx) => {
                img.dataset.index = postImages.length + idx;
            });
            images = [...postImages, ...heroImages];
            
            // Adicionar event listeners
            images.forEach((img) => {
                const index = parseInt(img.dataset.index) || 0;
                img.addEventListener('click', (e) => {
                    e.preventDefault();
                    openLightbox(index);
                });
            });
        }

        function openLightbox(index) {
            const lightbox = document.getElementById('lightbox');
            const lightboxImage = document.getElementById('lightbox-image');
            
            currentImageIndex = index;
            lightboxImage.src = images[index].src || images[index].dataset.src;
            lightboxImage.alt = images[index].alt;
            lightbox.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox() {
            const lightbox = document.getElementById('lightbox');
            lightbox.classList.remove('active');
            document.body.style.overflow = '';
        }

        function nextImage() {
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex + 1) % images.length;
                const lightboxImage = document.getElementById('lightbox-image');
                lightboxImage.src = images[currentImageIndex].src || images[currentImageIndex].dataset.src;
                lightboxImage.alt = images[currentImageIndex].alt;
            }
        }

        function prevImage() {
            if (images.length > 1) {
                currentImageIndex = (currentImageIndex - 1 + images.length) % images.length;
                const lightboxImage = document.getElementById('lightbox-image');
                lightboxImage.src = images[currentImageIndex].src || images[currentImageIndex].dataset.src;
                lightboxImage.alt = images[currentImageIndex].alt;
            }
        }

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

        // Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            updateReadingProgress();
            generateTOC();
            initLightbox();
            
            // Newsletter subscription
            const newsletterForm = document.getElementById('newsletter-form');
            if (newsletterForm) {
                newsletterForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const email = newsletterForm.querySelector('.newsletter-input').value;
                    
                    // Simulação de inscrição (substituir por chamada real ao backend)
                    const btn = newsletterForm.querySelector('.newsletter-btn');
                    const originalText = btn.textContent;
                    btn.textContent = 'Inscrito! ✓';
                    btn.style.background = '#10b981';
                    btn.disabled = true;
                    
                    setTimeout(() => {
                        btn.textContent = originalText;
                        btn.style.background = '';
                        btn.disabled = false;
                        newsletterForm.querySelector('.newsletter-input').value = '';
                    }, 2000);
                });
            }
        });

        window.addEventListener('scroll', updateReadingProgress);
        window.addEventListener('resize', updateReadingProgress);

        // Lightbox keyboard navigation
        document.addEventListener('keydown', (e) => {
            const lightbox = document.getElementById('lightbox');
            if (!lightbox.classList.contains('active')) return;

            switch(e.key) {
                case 'Escape':
                    closeLightbox();
                    break;
                case 'ArrowLeft':
                    prevImage();
                    break;
                case 'ArrowRight':
                    nextImage();
                    break;
            }
        });

        // Close lightbox on backdrop click
        document.getElementById('lightbox').addEventListener('click', (e) => {
            if (e.target === e.currentTarget) {
                closeLightbox();
            }
        });
    </script>

</body>
</html>