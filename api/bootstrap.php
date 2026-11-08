<?php
declare(strict_types=1);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Content-Security-Policy: default-src \'none\'; frame-ancestors \'none\'');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    header('Strict-Transport-Security: max-age=31536000');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('IQPOSSESSID');
    session_set_cookie_params([
        'lifetime' => 0, 'path' => '/', 'secure' => true,
        'httponly' => true, 'samesite' => 'Lax'
    ]);
    session_start();
}
function reply(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}
function fail(string $message, int $status = 400): never {
    reply(['ok' => false, 'error' => $message], $status);
}
function config(string $name, string $default = ''): string {
    $value = getenv($name);
    if ($value !== false && $value !== '') return $value;
    $local = dirname(__DIR__, 2) . '/gastosiq-config.php';
    if (is_file($local)) {
        $settings = require $local;
        if (is_array($settings) && isset($settings[$name])) return (string) $settings[$name];
    }
    return $default;
}
function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;
    $host = config('DB_HOST'); $name = config('DB_NAME');
    $user = config('DB_USER'); $password = config('DB_PASSWORD');
    if ($host === '' || $name === '' || $user === '' || $password === '') fail('Servicio sin configurar', 503);
    try {
        $pdo = new PDO(
            'mysql:host=' . $host . ';port=' . config('DB_PORT', '3306') . ';dbname=' . $name . ';charset=utf8mb4',
            $user, $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
             PDO::ATTR_EMULATE_PREPARES => false]
        );
    } catch (Throwable $e) {
        error_log('DB error: ' . $e->getMessage());
        fail('No se pudo conectar al servicio', 503);
    }
    return $pdo;
}
function input(): array {
    if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 16384) fail('Solicitud demasiado grande', 413);
    $obj = json_decode(file_get_contents('php://input'), true);
    if (!is_array($obj)) fail('JSON inválido');
    return $obj;
}
function csrf(): string {
    if (!isset($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function requireCsrf(): void {
    $submitted = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($submitted === '' || !hash_equals(csrf(), $submitted)) fail('Sesión no válida', 419);
}
function userId(): int {
    if (empty($_SESSION['uid'])) fail('Inicia sesión', 401);
    return (int) $_SESSION['uid'];
}
function validDate(string $date): bool {
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}
function validAmount(mixed $value): string {
    if (!is_numeric($value) || !preg_match('/^\d{1,9}(?:\.\d{1,2})?$/', (string)$value) || (float)$value <= 0) {
        fail('Monto inválido');
    }
    return number_format((float)$value, 2, '.', '');
}
function uniqueId(): string { return bin2hex(random_bytes(16)); }
function isUuid(string $id): bool { return (bool)preg_match('/^[0-9a-f]{32}$/', $id); }
function categoryOwned(PDO $db, string $id, int $uid): bool {
    $q=$db->prepare('SELECT 1 FROM categories WHERE id=? AND user_id=?');
    $q->execute([$id,$uid]);
    return (bool)$q->fetchColumn();
}
function cleanText(mixed $value, int $max): string {
    if (!is_string($value)) fail('Texto inválido');
    $value=trim($value);
    if (mb_strlen($value)>$max) fail('Texto demasiado largo');
    return $value;
}
