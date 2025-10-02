<?php
// Iniciar a sessão em todas as páginas que incluírem este ficheiro
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Configurações do Banco de Dados ---
define('DB_HOST', '127.0.0.1'); // Normalmente localhost ou 127.0.0.1
define('DB_USER', 'root');      // Utilizador padrão do EasyPHP é 'root'
define('DB_PASS', '');          // Senha padrão do EasyPHP é vazia
define('DB_NAME', 'adote_um_amigo'); // O nome que daremos ao nosso banco de dados
define('DB_CHARSET', 'utf8mb4');

// --- Conexão com o Banco de Dados (DSN) ---
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Criar a conexão usando PDO (PHP Data Objects)
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Se a conexão falhar, termina o script e mostra uma mensagem de erro genérica
    // Em produção, é melhor registar o erro num ficheiro de log em vez de o exibir
    error_log($e->getMessage());
    die("Erro: Não foi possível conectar ao banco de dados. Por favor, tente novamente mais tarde.");
}

// --- Constantes e Funções Globais ---
define('UPLOADS_PATH', __DIR__ . '/perfil_foto/'); // Define o caminho absoluto para a pasta de fotos de perfil

/**
 * Verifica se o utilizador está autenticado.
 * @return bool True se estiver autenticado, False caso contrário.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}
?>
