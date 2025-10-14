<?php
// Iniciar a sessão em todas as páginas que incluírem este ficheiro
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Configurações do Banco de Dados ---
define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'adote_um_amigo');

// --- Conexão com o Banco de Dados ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro: Não foi possível conectar ao banco de dados.");
}

// --- Constantes e Funções Globais ---
define('UPLOADS_PATH_PERFIL', __DIR__ . '/perfil_foto/');
define('UPLOADS_PATH_ANIMAIS', __DIR__ . '/animais_fotos/');

// Função para verificar se o utilizador está logado
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Função para verificar se o utilizador é administrador
function is_admin() {
    // Retorna true se o utilizador estiver logado E o seu nível de acesso na sessão for 'admin'
    return is_logged_in() && isset($_SESSION['user_level']) && $_SESSION['user_level'] === 'admin';
}

?>

