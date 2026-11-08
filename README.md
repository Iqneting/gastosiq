# IQneting Gastos POS · PHP + MariaDB

**Estado:** código de backend y frontend integrado en rama `main`. **Pendiente:** desplegar instalador protegido, completar instalación en Hostinger y verificar funcionamiento real.

## Componentes

- `index.html` — interfaz POS con categorías visuales, teclado numérico, gráfica, historial y login.
- `api/index.php` — API PHP para sesiones, gastos y categorías.
- `api/bootstrap.php` — sesiones, conexión PDO, validaciones y protección CSRF.
- `instalar.php` — instalador de un solo uso para crear tablas y primer usuario mediante clave secreta privada.
- `database/schema.sql` — tablas `users`, `categories`, `expenses` y `login_attempts`.
- `database/create_admin.php` — inicialización CLI segura del primer usuario y categorías.
- `config/gastosiq-config.example.php` — ejemplo de configuración; no contiene credenciales reales.
- `docs/ACTIVACION_MARIADB.md` — **guía siguiente paso: activación del backend en Hostinger**.
- `docs/DESPLIEGUE_HOSTINGER.md` — despliegue base de la maqueta.

## Cómo continuar

Leer **[Instalación automática protegida de MariaDB](docs/ACTIVACION_MARIADB.md)**.

1. Desplegar última revisión GitHub `main` a Hostinger.
2. Mantener las credenciales de MariaDB **exclusivamente en el archivo privado del servidor** y agregar temporalmente `SETUP_KEY` (mínimo 32 caracteres aleatorios).
3. Abrir por HTTPS `/instalar.php`, introducir la clave, correo y contraseña del primer usuario. Las tablas y las categorías se crearán automáticamente.
4. Retirar `SETUP_KEY` del archivo privado para cerrar la instalación.
5. Iniciar sesión y comprobar CRUD y persistencia.

**El antiguo acceso demo ya no está activo.** Hasta terminar la configuración privada y las pruebas del servidor, no considerar el proyecto en producción ni usar información sensible.

**Seguridad:** contraseñas con hash, sesiones HTTP seguras, CSRF, consultas preparadas, autorización por usuario y límite básico de intentos de login. Estas protecciones están implementadas en código pero aún no han sido verificadas en el alojamiento real. El repositorio actualmente es público: nunca subir contraseñas ni datos reales.
