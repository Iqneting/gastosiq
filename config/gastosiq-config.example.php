<?php
// Copiar manualmente como gastosiq-config.php UN NIVEL ARRIBA de public_html.
// Ese archivo REAL jamás debe colocarse en GitHub ni en la carpeta pública.
return [
  'DB_HOST' => 'HOST_DESDE_HPanel',
  'DB_PORT' => '3306',
  'DB_NAME' => 'BASE_DE_DATOS',
  'DB_USER' => 'USUARIO_DB',
  'DB_PASSWORD' => 'CONFIGURAR_SOLO_EN_SERVIDOR',
  // Genera una clave aleatoria única de 32+ caracteres, úsala solo durante instalación.
  // Borrar esta línea del archivo privado tras la primera instalación.
  'SETUP_KEY' => 'CLAVE_ALEATORIA_UNICA_DE_32_O_MAS_CARACTERES',
];
