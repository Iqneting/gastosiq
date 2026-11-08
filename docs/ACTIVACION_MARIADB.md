# Activación automática y protegida de Gastos IQ · MariaDB

**Fecha:** 2026-11-07. **Estado:** instalador programado y subido a GitHub, pendiente de probar en Hostinger.

## Instalación nueva sin phpMyAdmin

Si ya creaste la base de datos vacía y el archivo privado `gastosiq-config.php` al mismo nivel que `public_html`, **no tienes que importar el SQL manualmente**.

1. Edita el archivo privado `gastosiq-config.php` desde el Administrador de archivos de Hostinger, **fuera de `public_html`**. Mantén los cinco campos de MariaDB que ya escribiste.
2. Añade dentro del arreglo PHP una sexta entrada: `'SETUP_KEY' => 'TU_CLAVE_ALEATORIA_UNICA_DE_32_O_MAS_CARACTERES',`. Genera una clave nueva larga y aleatoria con tu gestor de contraseñas, guárdala temporalmente y **no la envíes por chat** ni la subas a GitHub.
3. Guarda los cambios en el archivo privado.
4. Desde hPanel actualiza el despliegue de la rama `main` hasta incluir el archivo `instalar.php`.
5. Abre **[https://gastosiq.gentemejor.com/instalar.php](https://gastosiq.gentemejor.com/instalar.php)** con HTTPS.
6. Introduce la clave privada de instalación, el correo que usarás para entrar y una contraseña nueva de **12 a 128 caracteres**; pulsa «Crear base, tablas y usuario».
7. PHP creará automáticamente las cuatro tablas `users`, `categories`, `expenses` y `login_attempts`, creará el primer usuario con contraseña hasheada y añadirá siete categorías visuales.
8. Una vez terminada la instalación, **elimina la línea `SETUP_KEY` de tu archivo privado**. El instalador ya no permitirá crear cuentas mediante ese mecanismo.
9. Abre [https://gastosiq.gentemejor.com/](https://gastosiq.gentemejor.com/) e inicia sesión. Registra un gasto, cierra sesión, vuelve a entrar, verifica los datos, edita y elimina el gasto.

## Seguridad y límites

- El instalador requiere clave secreta definida únicamente en el archivo privado, HTTPS y un token antifalsificación; **no crea usuarios automáticamente para cualquier visitante**.
- Después de crear el primer usuario, no permite nuevas instalaciones. La clave se debe retirar de la configuración privada.
- El usuario de MariaDB debe tener permisos de `CREATE TABLE`, `INSERT`, `SELECT`, `UPDATE` y `DELETE` para instalar. Recomendable después retirar el permiso de creación/alteración si no se requieren migraciones.
- Nunca colocar la clave de instalación, el usuario de MariaDB ni su contraseña en archivos versionados en GitHub.
- El instalador crea tablas solo cuando se autoriza con la clave secreta. **No asegura la conexión real hasta completar la instalación y las pruebas en Hostinger**.
- Si la base contiene ya usuarios, el instalador bloqueará el alta de un segundo administrador.
- Si falla, revisa el mensaje y el log privado de PHP. No muestres públicamente los errores de base de datos.
- La maqueta POS se conserva; la instalación no modifica los estilos ni el diseño.

## Ruta manual de respaldo

`database/schema.sql` sigue incluido como alternativa para phpMyAdmin, y `database/create_admin.php` como alternativa de inicialización por CLI/SSH. No son necesarios en la instalación automática normal.

## Verificación obligatoria

Antes de utilizar datos reales: revisar acceso autenticado, permisos por usuario, cierre de sesión, HTTPS, protección de archivos, y confirmar que cada gasto se conserva entre sesiones. Las pruebas de seguridad en el servidor siguen pendientes.
