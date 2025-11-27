<?php
// Iniciar a sessão com parâmetros mais seguros
if (session_status() == PHP_SESSION_NONE) {
    // Definir parâmetros de cookie de sessão antes de iniciar
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
    if (PHP_VERSION_ID >= 70300) {
        // Sintaxe por array disponível no PHP 7.3+
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'domain' => '',
            'secure' => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    } else {
        // Fallback para versões antigas: não suporta SameSite na API nativa
        session_set_cookie_params(0, '/; samesite=Lax', '', $isHttps, true);
    }
    session_start();
}

// --- Configurações do Banco de Dados ---
define('DB_HOST', 'sql202.infinityfree.com');
define('DB_USER', 'if0_40168077');
define('DB_PASS', '12365451');
define('DB_NAME', 'if0_40168077_pet');

// --- Conexão com o Banco de Dados ---
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Desabilitar emulação para usar prepared statements nativos
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
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

// --- CSRF Helpers ---
// Geração de bytes aleatórios compatível com versões antigas do PHP
function secure_random_bytes($length) {
    if (function_exists('random_bytes')) {
        return random_bytes($length);
    }
    if (function_exists('openssl_random_pseudo_bytes')) {
        $strong = false;
        $bytes = openssl_random_pseudo_bytes($length, $strong);
        if ($bytes !== false) {
            return $bytes;
        }
    }
    $bytes = '';
    for ($i = 0; $i < $length; $i++) {
        $bytes .= chr(mt_rand(0, 255));
    }
    return $bytes;
}

// Polyfill para hash_equals em PHP < 5.6
function hash_equals_polyfill($known_string, $user_string) {
    if (function_exists('hash_equals')) {
        return hash_equals($known_string, $user_string);
    }
    if (!is_string($known_string) || !is_string($user_string)) {
        return false;
    }
    $len = strlen($known_string);
    if ($len !== strlen($user_string)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < $len; $i++) {
        $result |= ord($known_string[$i]) ^ ord($user_string[$i]);
    }
    return $result === 0;
}

function csrf_get_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(secure_random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input_field() {
    $t = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    echo '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_is_valid($token) {
    $sessionToken = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
    if (!$sessionToken || !$token) return false;
    return hash_equals_polyfill($sessionToken, (string)$token);
}

?>
