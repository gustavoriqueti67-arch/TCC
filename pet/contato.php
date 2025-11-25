<?php
require_once 'config.php';

$errors = [];
$success_message = '';

// Simples processamento do formulário (não envia email real, apenas mostra mensagem)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim(isset($_POST['nome']) ? $_POST['nome'] : '');
    $email = trim(isset($_POST['email']) ? $_POST['email'] : '');
    $assunto = trim(isset($_POST['assunto']) ? $_POST['assunto'] : '');
    $mensagem = trim(isset($_POST['mensagem']) ? $_POST['mensagem'] : '');

    if (empty($nome) || empty($email) || empty($assunto) || empty($mensagem)) {
        $errors[] = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, insira um email válido.";
    } else {
        // Simulação de envio de email
        // Numa aplicação real, aqui iria o código para enviar o email usando uma biblioteca como PHPMailer
        $success_message = "A sua mensagem foi enviada com sucesso! Entraremos em contacto em breve.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto - Adote um Amigo</title>
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
                <h1 class="section-title">Entre em Contacto</h1>
                <p class="section-subtitle">Tem alguma dúvida, sugestão ou quer ser nosso parceiro? Adoraríamos ouvir de si. Preencha o formulário abaixo e a nossa equipa responderá o mais breve possível.</p>
                
                <div class="form-container" style="max-width: 800px; margin: 0 auto;">
                     <?php if (!empty($success_message)): ?>
                        <div class="alert alert-success">
                            <p><?php echo htmlspecialchars($success_message); ?></p>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-error">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form action="contato.php" method="POST" class="form-layout">
                        <div class="form-input-grid">
                            <div class="form-group">
                                <label for="nome" class="form-label">O seu Nome</label>
                                <input type="text" id="nome" name="nome" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">O seu Email</label>
                                <input type="email" id="email" name="email" class="form-input" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="assunto" class="form-label">Assunto</label>
                            <input type="text" id="assunto" name="assunto" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label for="mensagem" class="form-label">Mensagem</label>
                            <textarea id="mensagem" name="mensagem" class="form-textarea" rows="6" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-register btn-form">Enviar Mensagem</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?php include 'footer.php'; ?>

</body>
</html>
