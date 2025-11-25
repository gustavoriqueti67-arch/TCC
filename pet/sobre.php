<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sobre Nós - Adote um Amigo</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .about-section {
            padding: 6rem 1.5rem;
        }
        .about-container {
            max-width: 900px;
            margin: 0 auto;
            text-align: center;
        }
        .about-content {
            background: var(--card-background);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            padding: 3rem;
            border-radius: var(--border-radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-xl);
        }
        .about-content h1 {
            margin-bottom: 2rem;
        }
        .about-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: var(--text-color-light);
            margin-bottom: 1.5rem;
            text-align: left;
        }
        .team-section {
            margin-top: 4rem;
        }
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .team-member {
            text-align: center;
        }
        .team-member img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 4px solid var(--primary-accent);
        }
        .team-member h4 {
            font-size: 1.25rem;
            color: var(--white);
            margin-bottom: 0.25rem;
        }
        .team-member span {
            color: var(--primary-accent);
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <main>
        <section class="about-section">
            <div class="container about-container">
                <div class="about-content reveal">
                    <h1 class="section-title">A Nossa Missão</h1>
                    <p>
                        Bem-vindo ao "Adote um Amigo", um projeto nascido da paixão e do respeito pelos animais. A nossa missão é simples, mas poderosa: criar uma ponte de esperança entre animais que precisam de um lar e pessoas de bom coração dispostas a oferecer amor e cuidado.
                    </p>
                    <p>
                        Acreditamos que cada animal merece uma segunda oportunidade. Lutamos contra o abandono, promovemos a posse responsável e trabalhamos incansavelmente para garantir que cada cão, gato e outros companheiros encontrem a segurança e o carinho de uma família. Este site é a nossa ferramenta para tornar isso realidade, um animal de cada vez.
                    </p>
                </div>

                <div class="team-section reveal">
                    <h2 class="section-title">A Nossa Equipa</h2>
                    <div class="team-grid">
                        <div class="team-member reveal">
                            <img src="https://placehold.co/150x150/333333/FFFFFF?text=Equipa" alt="Foto de um membro da equipa" loading="lazy" width="150" height="150">
                            <h4>João Silva</h4>
                            <span>Fundador & Coordenador</span>
                        </div>
                        <div class="team-member reveal">
                            <img src="CEO.jpg" alt="Foto de um membro da equipa" loading="lazy" width="150" height="150">
                            <h4>Gustavo Riqueti</h4>
                            <span>CEO e Programador</span>
                        </div>
                        <div class="team-member reveal">
                            <img src="https://placehold.co/150x150/333333/FFFFFF?text=Equipa" alt="Foto de um membro da equipa" loading="lazy" width="150" height="150">
                            <h4>Carlos Pereira</h4>
                            <span>Gestor de Voluntários</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <script>
        (function(){
            var observer = new IntersectionObserver(function(entries){
                entries.forEach(function(entry){
                    if(entry.isIntersecting){ entry.target.classList.add('is-visible'); }
                });
            }, { threshold: 0.1 });
            document.querySelectorAll('.reveal').forEach(function(el){ observer.observe(el); });
        })();
    </script>

    <?php include 'footer.php';  ?>

</body>
</html>
