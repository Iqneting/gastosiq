# IQneting Gastos POS · PHP + MariaDB

**Estado:** código de backend y frontend integrado en rama `main`. **Pendiente:** activar conexión privada en Hostinger, importar SQL, crear usuario y verificar funcionamiento real.

## Componentes

- `index.html` — interfaz POS con categorías visuales, teclado numérico, gráfica, historial y login.
- `api/index.php` — API PHP para sesiones, gastos y categorías.
- `api/bootstrap.php` — sesiones, conexión PDO, validaciones y protección CSRF.
- `database/schema.sql` — tablas `users`, `categories`, `expenses` y `login_attempts`.
- `database/create_admin.php` — inicialización CLI segura del primer usuario y categorías.
- `config/gastosiq-config.example.php` — ejemplo de configuración; no contiene credenciales reales.
- `docs/ACTIVACION_MARIADB.md` — **guía siguiente paso: activación del backend en Hostinger**.
- `docs/DESPLIEGUE_HOSTINGER.md` — despliegue base de la maqueta.

## Cómo continuar

Leer **[Activación de MariaDB](docs/ACTIVACION_MARIADB.md)**.

1. Desplegar última revisión GitHub `main` a Hostinger.
2. Importar `database/schema.sql` en MariaDB usando phpMyAdmin.
3. Configurar credenciales **exclusivamente en servidor**, fuera de `public_html` y de GitHub.
4. Crear el primer usuario con script CLI por SSH o procedimiento seguro equivalente.
5. Iniciar sesión y comprobar CRUD y persistencia.

**El antiguo acceso demo ya no está activo.** Hasta terminar la configuración privada y las pruebas del servidor, no considerar el proyecto en producción ni usar información sensible.

**Seguridad:** contraseñas con hash, sesiones HTTP seguras, CSRF, consultas preparadas, autorización por usuario y límite básico de intentos de login. Estas protecciones están implementadas en código pero aún no han sido verificadas en el alojamiento real. El repositorio actualmente es público: nunca subir contraseñas ni datos reales.
