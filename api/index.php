<?php
declare(strict_types=1);
require __DIR__ . '/bootstrap.php';
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($action, ['login','logout','session','data','expense_create','expense_update','expense_delete','category_create','category_update','category_delete'], true)) fail('Ruta inexistente',404);
if (!in_array($method, ['GET','POST'], true)) fail('Método no permitido',405);
if ($action === 'session' && $method === 'GET') {
    if (empty($_SESSION['uid'])) reply(['ok'=>true,'loggedIn'=>false,'csrf'=>csrf()]);
    reply(['ok'=>true,'loggedIn'=>true,'csrf'=>csrf()]);
}
if ($action === 'login' && $method === 'POST') {
    requireCsrf();
    $payload=input();
    $email=strtolower(trim((string)($payload['email']??'')));
    $password=(string)($payload['password']??'');
    if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($password)>1024) fail('Credenciales incorrectas',401);
    $pdo=db();
    $stmt=$pdo->prepare('SELECT id,password_hash FROM users WHERE email=? AND active=1 LIMIT 1');
    $stmt->execute([$email]); $row=$stmt->fetch();
    // Limitación por correo e IP (la IP no se confía desde cabeceras del cliente)
    $key=hash('sha256',strtolower($email).'|'.($_SERVER['REMOTE_ADDR']??''));
    $pdo->prepare('DELETE FROM login_attempts WHERE updated_at < NOW() - INTERVAL 2 DAY')->execute();
    $q=$pdo->prepare('SELECT attempts,updated_at FROM login_attempts WHERE attempt_key=?');$q->execute([$key]);$attempt=$q->fetch();
    if ($attempt && (int)$attempt['attempts']>=5 && strtotime($attempt['updated_at'])>time()-900) fail('Demasiados intentos. Intenta en 15 minutos',429);
    if (!$row || !password_verify($password,$row['password_hash'])) {
        $pdo->prepare('INSERT INTO login_attempts(attempt_key,attempts,updated_at) VALUES(?,1,NOW()) ON DUPLICATE KEY UPDATE attempts=attempts+1,updated_at=NOW()')->execute([$key]);
        fail('Credenciales incorrectas',401);
    }
    $pdo->prepare('DELETE FROM login_attempts WHERE attempt_key=?')->execute([$key]);
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$row['id'];
    $_SESSION['csrf']=bin2hex(random_bytes(32));
    $_SESSION['authenticated_at']=time();
    reply(['ok'=>true,'csrf'=>csrf()]);
}
if ($action === 'logout' && $method === 'POST') {
    requireCsrf();
    $_SESSION=[];
    session_regenerate_id(true);
    reply(['ok'=>true]);
}
if (empty($_SESSION['uid'])) fail('Inicia sesión',401);
$uid=userId();
if ($method==='POST') requireCsrf();
$pdo=db();
if ($action==='data' && $method==='GET') {
    $q=$pdo->prepare('SELECT id,name,icon FROM categories WHERE user_id=? ORDER BY sort_order ASC,name ASC');
    $q->execute([$uid]); $categories=$q->fetchAll();
    $q=$pdo->prepare('SELECT id,category_id AS categoryId,amount,note,expense_date AS date, UNIX_TIMESTAMP(created_at)*1000 AS createdAt FROM expenses WHERE user_id=? ORDER BY created_at DESC,id DESC LIMIT 2000');
    $q->execute([$uid]);$expenses=$q->fetchAll();
    foreach ($expenses as &$e) {$e['amount']=(float)$e['amount'];$e['createdAt']=(int)$e['createdAt'];}
    reply(['ok'=>true,'categories'=>$categories,'expenses'=>$expenses]);
}
if ($method!=='POST') fail('Método no permitido',405);
$p=input();
$id=(string)($p['id']??'');
if ($action==='expense_create' || $action==='expense_update') {
    $cid=(string)($p['categoryId']??'');
    $amount=validAmount($p['amount']??null);
    $note=cleanText($p['note']??'',160);
    $date=(string)($p['date']??'');
    if (!validDate($date)||!isUuid($cid)||!categoryOwned($pdo,$cid,$uid)) fail('Categoría o fecha inválida');
    if ($action==='expense_create') {
        $id=uniqueId();
        $q=$pdo->prepare('INSERT INTO expenses(id,user_id,category_id,amount,note,expense_date) VALUES(?,?,?,?,?,?)');
        $q->execute([$id,$uid,$cid,$amount,$note,$date]);
    } else {
        if (!isUuid($id)) fail('Identificador inválido');
        $q=$pdo->prepare('UPDATE expenses SET category_id=?,amount=?,note=?,expense_date=? WHERE id=? AND user_id=?');
        $q->execute([$cid,$amount,$note,$date,$id,$uid]);
        if (!$q->rowCount()) {
            $c=$pdo->prepare('SELECT 1 FROM expenses WHERE id=? AND user_id=?');$c->execute([$id,$uid]);
            if (!$c->fetchColumn()) fail('Gasto no encontrado',404);
        }
    }
    reply(['ok'=>true,'id'=>$id]);
}
if ($action==='expense_delete') {
    if (!isUuid($id)) fail('Identificador inválido');
    $q=$pdo->prepare('DELETE FROM expenses WHERE id=? AND user_id=?');
    $q->execute([$id,$uid]);if(!$q->rowCount()) fail('Gasto no encontrado',404);
    reply(['ok'=>true]);
}
if ($action==='category_create' || $action==='category_update') {
    $name=cleanText($p['name']??'',20);
    $icon=cleanText($p['icon']??'',12);
    if ($name==='' || $icon==='') fail('Nombre e ícono obligatorios');
    if ($action==='category_create') {
        $id=uniqueId();
        $q=$pdo->prepare('INSERT INTO categories(id,user_id,name,icon,sort_order) VALUES(?,?,?,?,100)');
        try {$q->execute([$id,$uid,$name,$icon]);}
        catch(PDOException $e){if($e->getCode()==='23000')fail('Ya existe esa categoría');throw $e;}
    } else {
        if(!isUuid($id))fail('Identificador inválido');
        $q=$pdo->prepare('UPDATE categories SET name=?,icon=? WHERE id=? AND user_id=?');
        try {$q->execute([$name,$icon,$id,$uid]);}
        catch(PDOException $e){if($e->getCode()==='23000')fail('Ya existe esa categoría');throw $e;}
    }
    reply(['ok'=>true,'id'=>$id]);
}
if ($action==='category_delete') {
    if(!isUuid($id))fail('Identificador inválido');
    $q=$pdo->prepare('SELECT COUNT(*) FROM expenses WHERE category_id=? AND user_id=?');$q->execute([$id,$uid]);
    if((int)$q->fetchColumn()>0)fail('La categoría tiene gastos asociados',409);
    $q=$pdo->prepare('DELETE FROM categories WHERE id=? AND user_id=?');
    $q->execute([$id,$uid]);if(!$q->rowCount())fail('Categoría no encontrada',404);
    reply(['ok'=>true]);
}
fail('Ruta inexistente',404);
