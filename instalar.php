<?php
declare(strict_types=1);
/**
 * Instalador de un solo uso de Gastos IQ.
 * Requiere SETUP_KEY en el archivo privado gastosiq-config.php.
 * Solo crea esquema y primer usuario tras validar esa clave secreta.
 * Al existir un usuario, bloquea instalaciones posteriores.
 */
require __DIR__ . '/api/bootstrap.php';
header('Content-Type: text/html; charset=utf-8');
header("Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'");
header('Cache-Control: no-store, private');
header('X-Robots-Tag: noindex, nofollow, noarchive');
header('Referrer-Policy: no-referrer');

$notice = '';
$success = false;
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
$setupKey = config('SETUP_KEY');
if (!$https) {
    http_response_code(403);
    $notice = 'Abre el instalador exclusivamente por HTTPS.';
} elseif (strlen($setupKey) < 32 || str_starts_with($setupKey, 'CLAVE_ALEATORIA_')) {
    http_response_code(503);
    $notice = 'Falta SETUP_KEY: añade una clave aleatoria de 32 o más caracteres al archivo privado gastosiq-config.php.';
} elseif ($method === 'POST') {
    $posted = $_POST;
    $token = (string)($posted['csrf'] ?? '');
    $key = (string)($posted['setup_key'] ?? '');
    $email = strtolower(trim((string)($posted['email'] ?? '')));
    $password = (string)($posted['password'] ?? '');
    $confirm = (string)($posted['password_confirm'] ?? '');

    if (strlen($key) > 256 || !hash_equals(csrf(), $token)) {
        http_response_code(403);
        $notice = 'Formulario no válido. Actualiza la página.';
    } elseif (!hash_equals($setupKey, $key)) {
        http_response_code(403);
        $notice = 'Clave de instalación incorrecta.';
        usleep(300000);
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $notice = 'Introduce un correo electrónico válido.';
    } elseif (strlen($password) < 12 || strlen($password) > 128 || $password !== $confirm) {
        $notice = 'La contraseña debe tener de 12 a 128 caracteres y coincidir con su confirmación.';
    } else {
        $pdo = db();
        $lockName = 'gastosiq_first_install_' . substr(hash('sha256', config('DB_NAME')), 0, 12);
        $locked = false;
        try {
            $lock = $pdo->prepare('SELECT GET_LOCK(?,10)');
            $lock->execute([$lockName]);
            $locked = (int)$lock->fetchColumn() === 1;
            if (!$locked) {
                throw new RuntimeException('Instalación ocupada; vuelve a intentarlo.');
            }
            $schema = file_get_contents(__DIR__ . '/database/schema.sql');
            if ($schema === false) throw new RuntimeException('Archivo SQL no disponible.');
            $schema = preg_replace('/^\s*--[^\r\n]*$/m', '', $schema);
            foreach (explode(';', $schema) as $statement) {
                $statement = trim($statement);
                if ($statement !== '') $pdo->exec($statement);
            }
            $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count > 0) {
                http_response_code(409);
                $notice = 'El sistema ya tiene un usuario. La instalación está cerrada.';
            } else {
                $pdo->beginTransaction();
                try {
                    $stmt = $pdo->prepare('INSERT INTO users(email,password_hash,active) VALUES(?,?,1)');
                    $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
                    $uid = (int)$pdo->lastInsertId();
                    $defaults = [
                        ['Comida','🍔'],['Transporte','🚕'],['Compras','🛍️'],
                        ['Servicios','💡'],['Salud','💊'],['Hogar','🏠'],['Ocio','🎬']
                    ];
                    $stmt = $pdo->prepare('INSERT INTO categories(id,user_id,name,icon,sort_order) VALUES(?,?,?,?,?)');
                    foreach ($defaults as $i => $category) {
                        $stmt->execute([bin2hex(random_bytes(16)),$uid,$category[0],$category[1],$i]);
                    }
                    $pdo->commit();
                    $success = true;
                    $notice = 'Instalación terminada. Tablas, usuario y categorías creados. Ya puedes iniciar sesión.';
                } catch (Throwable $e) {
                    if ($pdo->inTransaction()) $pdo->rollBack();
                    throw $e;
                }
            }
        } catch (Throwable $e) {
            error_log('IQ POS setup: ' . $e->getMessage());
            http_response_code(503);
            $notice = 'No se completó la instalación. Comprueba el usuario y los permisos de MariaDB; revisa el registro de errores de PHP.';
        } finally {
            if ($locked) {
                try {
                    $unlock = $pdo->prepare('SELECT RELEASE_LOCK(?)');
                    $unlock->execute([$lockName]);
                } catch (Throwable $e) {
                    error_log('IQ POS unlock: '.$e->getMessage());
                }
            }
        }
    }
} elseif ($method !== 'GET') {
    http_response_code(405);
    $notice = 'Método no permitido.';
}
function h(string $v): string {return htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');}
$allowed = $https && strlen($setupKey) >= 32 && !str_starts_with($setupKey, 'CLAVE_ALEATORIA_') && !$success && $method === 'GET' || $https && strlen($setupKey) >= 32 && !$success && $method === 'POST' && $notice !== 'El sistema ya tiene un usuario. La instalación está cerrada.';
?><!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gastos IQ · Instalación protegida</title>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f3f6fc;color:#15243d;font-family:system-ui,-apple-system,Segoe UI,sans-serif;padding:22px}
main{width:min(460px,100%);background:white;padding:32px;border:1px solid #e0e7f1;border-radius:24px;box-shadow:0 16px 50px #2438570c}
.logo{display:inline-grid;place-items:center;background:#155ee8;color:white;border-radius:14px;width:48px;height:48px;font-weight:800}
h1{font-size:24px;margin:18px 0 8px}.sub{color:#64748b;line-height:1.6;font-size:14px;margin:0 0 18px}
label{font-size:13px;font-weight:700;display:block;margin:15px 0 7px}
input{width:100%;border:1px solid #ccd7e8;border-radius:13px;font:inherit;padding:13px 14px}
button,a.button{display:block;width:100%;margin-top:22px;background:#155ee8;color:#fff;text-decoration:none;text-align:center;font:inherit;font-weight:700;padding:15px;border:0;border-radius:13px;cursor:pointer}
.alert{background:#f0f5ff;color:#234c84;padding:14px 16px;border-radius:12px;line-height:1.5;font-size:14px;margin:12px 0}
small{display:block;color:#64748b;line-height:1.5;margin-top:20px}
</style></head>
<body><main><span class="logo">IQ</span><h1>Activar Gastos IQ</h1>
<p class="sub">Instalación protegida de PHP y MariaDB. Las tablas y categorías se crean automáticamente.</p>
<?php if ($notice !== ''): ?><div class="alert"><?=h($notice)?></div><?php endif; ?>
<?php if ($success): ?><a class="button" href="./">Entrar al sistema</a>
<?php elseif ($allowed): ?>
<form method="post" autocomplete="off">
<input type="hidden" name="csrf" value="<?=h(csrf())?>">
<label for="setup_key">Clave privada de instalación</label>
<input id="setup_key" name="setup_key" type="password" required minlength="32" maxlength="256" autocomplete="off">
<label for="email">Correo del administrador</label>
<input id="email" name="email" type="email" required maxlength="190" autocomplete="email">
<label for="password">Contraseña nueva (mínimo 12 caracteres)</label>
<input id="password" name="password" type="password" required minlength="12" maxlength="128" autocomplete="new-password">
<label for="password_confirm">Confirmar contraseña</label>
<input id="password_confirm" name="password_confirm" type="password" required minlength="12" maxlength="128" autocomplete="new-password">
<button type="submit">Crear base, tablas y usuario</button>
</form>
<?php endif; ?>
<small>La instalación exige una clave definida fuera de public_html. Tras crear el primer usuario, el instalador rechaza nuevas altas. Elimina SETUP_KEY del archivo privado al terminar.</small>
</main></body></html>
