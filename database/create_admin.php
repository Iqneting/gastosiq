<?php
// Ejecutar mediante CLI desde Hostinger SSH: php database/create_admin.php usuario@ejemplo.com
// Contraseña solicitada de forma interactiva: nunca escrita en el comando ni en Git.
declare(strict_types=1);
if (PHP_SAPI!=='cli') {http_response_code(404);exit;}
require dirname(__DIR__).'/api/bootstrap.php';
$email=strtolower(trim($argv[1]??''));
if(!filter_var($email,FILTER_VALIDATE_EMAIL)){fwrite(STDERR,"Uso: php database/create_admin.php correo@dominio.com\n");exit(1);}
fwrite(STDOUT,"Contraseña nueva (mínimo 12 caracteres; entrada oculta en sistemas Unix): ");
if (DIRECTORY_SEPARATOR==='/') system('stty -echo');
$password=trim((string)fgets(STDIN));
if (DIRECTORY_SEPARATOR==='/') system('stty echo');
fwrite(STDOUT,"\n");
if(strlen($password)<12){fwrite(STDERR,"Debe tener al menos 12 caracteres.\n");exit(1);}
$pdo=db();
$exists=$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
if((int)$exists>0){fwrite(STDERR,"Ya existe al menos un usuario; se bloquea la inicialización.\n");exit(1);}
$pdo->beginTransaction();
try{
$q=$pdo->prepare('INSERT INTO users(email,password_hash) VALUES(?,?)');
$q->execute([$email,password_hash($password,PASSWORD_DEFAULT)]);
$uid=(int)$pdo->lastInsertId();
$defaults=[['Comida','🍔'],['Transporte','🚕'],['Compras','🛍️'],['Servicios','💡'],['Salud','💊'],['Hogar','🏠'],['Ocio','🎬']];
$q=$pdo->prepare('INSERT INTO categories(id,user_id,name,icon,sort_order) VALUES(?,?,?,?,?)');
foreach($defaults as $i=>$c)$q->execute([bin2hex(random_bytes(16)),$uid,$c[0],$c[1],$i]);
$pdo->commit();fwrite(STDOUT,"Usuario y categorías creados.\n");
}catch(Throwable $e){$pdo->rollBack();error_log($e->getMessage());fwrite(STDERR,"Error al crear usuario.\n");exit(1);}
