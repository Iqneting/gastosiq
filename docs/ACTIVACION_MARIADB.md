# Activación de MariaDB real · Gastos IQ POS

**Estado al 2026-11-07:** código PHP, SQL y frontend integrado **subidos a GitHub**. Aún se debe configurar la base y crear el primer usuario **en Hostinger**. No se ha comprobado el servidor real.

## Requisitos

- Hosting con **PHP 8.1 o superior**, extensiones `pdo_mysql` y `mbstring`, HTTPS habilitado.
- Hostinger configurado para desplegar el repositorio `Iqneting/gastosiq` desde `main`.
- MariaDB y usuario de base de datos ya creados.

## Paso 1 · Desplegar cambios

Desde hPanel desplegar la última revisión de `main`. Confirmar que están publicados `index.html`, `api/index.php` y `api/bootstrap.php`. No ingresar datos reales hasta completar los pasos siguientes.

## Paso 2 · Crear tablas

Abrir **phpMyAdmin** en Hostinger, seleccionar la base de datos correcta y ejecutar/importar el contenido de `database/schema.sql`.

Tablas: `users`, `categories`, `expenses`, `login_attempts`. Las claves externas protegen las relaciones.

## Paso 3 · Configurar secretos fuera del sitio público

**No subir contraseñas a GitHub ni colocarlas en el chat.**

La API intenta leer primero variables de entorno `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.

Si las variables de entorno no están disponibles, crear manualmente en el servidor un archivo llamado **`gastosiq-config.php`** en el **directorio padre de `public_html`**, utilizando como referencia `config/gastosiq-config.example.php`. El archivo real **no debe estar dentro de `public_html`** ni sincronizarse con GitHub. Comprobar la estructura de carpetas real de hPanel antes de colocarlo.

Si el despliegue usa otra carpeta pública, revisar la búsqueda de configuración en `api/bootstrap.php` antes de publicar.

## Paso 4 · Crear el primer usuario

Por seguridad, el sistema no expone un instalador público.

Abrir SSH del hosting (si el plan lo permite), ubicarse en el directorio publicado y ejecutar:

```bash
php database/create_admin.php correo@dominio.com
```

El programa solicitará la contraseña de manera interactiva (**mínimo 12 caracteres**) y creará también 7 categorías iniciales. Impide crear un nuevo administrador con este script si ya existe un usuario.

Si el hosting no ofrece SSH, **no publicar un instalador abierto**: solicitar otra forma segura de inicializar la cuenta. Puede generarse el hash con PHP ejecutado fuera del sitio público y crear el registro mediante phpMyAdmin.

## Paso 5 · Probar

1. Visitar `https://TU-DOMINIO/api/index.php?action=session`. Se espera un JSON con `ok: true`, `loggedIn: false` y token CSRF, sin contraseñas.
2. Abrir el sitio bajo HTTPS. Entrar con el correo y contraseña del paso 4. El acceso demo anterior ya no funciona.
3. Crear una categoría; crear un gasto; cerrar sesión; volver a iniciar sesión; verificar gasto y gráfica.
4. Editar y borrar el gasto; intentar borrar una categoría con gastos (debe impedirse).
5. Verificar que abrir la API de gastos sin sesión devuelve HTTP 401, que no pueda leerse el archivo de configuración y que PHP no muestre trazas de errores.
6. Revisar permisos del usuario MariaDB, respaldos y restauración.

## Seguridad incorporada

- Hash de contraseña con `password_hash`/`password_verify`.
- Cookie de sesión HttpOnly, Secure, SameSite=Lax; regeneración de ID al login.
- Token CSRF obligatorio en las peticiones POST.
- Consultas preparadas con PDO, sin interpolar datos del usuario.
- Filtrado por usuario en consultas y cambios de gastos/categorías.
- Bloqueo temporal básico tras 5 fallos de inicio de sesión por correo+IP.
- Errores sensibles ocultos al visitante (registrados en logs).
- Llaves de BD fuera de GitHub y fuera de la raíz web.

### Pendiente de verificar en servidor

No se ha realizado todavía prueba funcional en Hostinger ni auditoría de seguridad externa. La seguridad debe verificarse tras el despliegue; no debe darse por garantizada.

**Observación:** el repositorio es público y contiene solo código y ejemplos sin claves. Antes de utilizarlo con información real, confirmar que ninguna credencial se añadió al historial.
