# Gastos IQ · Bitácora de implementación y ruta reproducible

**Fecha de registro:** 2026-11-07 (America/Mexico_City).  
**Dominio:** https://gastosiq.gentemejor.com  
**Repositorio:** https://github.com/Iqneting/gastosiq  
**Referencia general:** https://github.com/Iqneting/GMIQ/blob/main/metodologias/RUTA_AUTOMATICA_PHP_MARIADB_HOSTINGER.md

## Qué se hizo y en qué orden

1. **Idea y alcance:** mini POS de gastos con login, categorías precargadas/personalizadas, descripción opcional, monto de captura rápida, gráfica, historial y operaciones crear/editar/eliminar.
2. **Maqueta aprobada:** interfaz visual POS HTML/CSS/JS adaptada a móvil y PC, guardado inicial de demostración en navegador.
3. **GitHub:** se creó/vinculó `Iqneting/gastosiq`; se subió como `index.html` a `main`, junto con README y guías.
4. **Hostinger:** propietario creó sitio PHP y asignó `gastosiq.gentemejor.com`, vinculó despliegue a GitHub y confirmó que se veía la interfaz.
5. **MariaDB:** propietario creó base/usuario. En su captura de hPanel aparecían ambos con nombre `u511387244_gastosiq`. La contraseña real **no se almacena aquí**.
6. **Configuración privada:** se preparó `gastosiq-config.php` como hermano de `public_html`, con datos DB y una clave temporal `SETUP_KEY`. El backend lo busca desde `api/bootstrap.php`. La confusión inicial fue que el archivo no aparecía en la raíz inspeccionada; se recreó en el lugar correcto.
7. **Instalación automática:** `instalar.php` protegido con clave privada + HTTPS + token CSRF lee `database/schema.sql` y crea tablas, categorías y usuario inicial. Tras usarlo, eliminar `SETUP_KEY` y **retirar/deshabilitar el instalador público**.
8. **API PHP:** `api/index.php` implementa login, logout, sesión y CRUD de categorías/gastos; `api/bootstrap.php` implementa PDO, CSRF, validación y sesiones.
9. **Interfaz conectada:** `index.html` utiliza API y deja de usar localStorage para guardar gastos.
10. **Sincronización automática:** se añadió consulta cada 4 s a la API mientras haya sesión visible, con detección de cambios y recuperación al enfocar pestaña. El usuario confirmó que **probó** y le funcionó la sincronización entre dispositivos.
11. **Auditoría de esquema en GitHub:** tablas `users`, `categories`, `expenses`, `login_attempts`. El diagrama documentado refleja el esquema definido, **sin certificar la base real desde phpMyAdmin**.

## Archivos relevantes

- `index.html`: POS y sincronización.
- `api/index.php`, `api/bootstrap.php`: API y conexión PDO.
- `database/schema.sql`: esquema de referencia versionado.
- `instalar.php`: instalación inicial protegida, **no debe quedar habilitado en producción**.
- `config/gastosiq-config.example.php`: ejemplo sin claves reales.
- `docs/ACTIVACION_MARIADB.md`: pasos de instalación.
- `docs/SINCRONIZACION_DISPOSITIVOS.md`: lógica de sincronización y prueba entre dispositivos.
- `docs/RUTA_COMPLETA_DEL_PROYECTO.md`: esta bitácora de transferencia.

## Modelo relacional

```text
users.id (PK)  1 ───────── N categories.user_id (FK)
    │                              │
    │ 1                            │ 1
    │                              │
    └───────── N expenses.user_id   └───────── N expenses.category_id
                       │
                  expenses.id (PK)

login_attempts.attempt_key (PK) — tabla independiente para control de intentos de acceso
```

La API solo devuelve gastos/categorías del usuario autenticado. Confirmar en phpMyAdmin que las claves foráneas realmente fueron aplicadas.

## Funciones probadas / por comprobar

- **Confirmado por usuario:** sitio visible, interfaz POS conservada, sincronización entre dispositivos funcionando en su prueba.
- **Implementado en código, pendiente de test técnico exhaustivo:** CRUD de gastos/categorías, permisos de usuario, creación automática inicial, CSRF, rate limiting, configuración MariaDB.
- **Pendiente crítico de seguridad:** verificar que `SETUP_KEY` ya fue retirado, que `instalar.php` no admite nuevas instalaciones y que `.git`, `database/`, `config/` y los archivos internos no son accesibles por HTTPS. La captura mostraba `.git` dentro de `public_html`.
- **Pendiente de escalabilidad:** la API devuelve máximo 2,000 gastos; el dashboard actualmente suma en frontend. Migrar totales a consultas agregadas SQL y paginar historial.
- **Pendiente de integridad:** reforzar pertenencia usuario/categoría dentro de la BD (no solo validación API), comprobación de montos positivos y procedimiento de migraciones versionadas.
- **Pendiente de calidad:** prueba de respaldo/restauración y logs sin exposición de credenciales.
- **Pendiente visual:** en captura del usuario se veía «Modo demo» aunque se había conectado backend; retirar leyenda engañosa sin alterar diseño aprobado.

## Procedimiento para repetir el proyecto

1. Crear maqueta UX/UI y aprobarla; no alterar su diseño sin solicitud.
2. Crear repositorio GitHub preferentemente privado y subir `index.html`, backend PHP, SQL, guías y `.gitignore`.
3. Crear sitio vacío PHP en Hostinger, asociar dominio/HTTPS, conectar repo y verificar despliegue `main`.
4. Crear MariaDB/usuario en Hostinger; archivo secreto privado fuera de `public_html` con host, puerto, nombre, usuario y contraseña; comprobar ruta de lectura en backend.
5. Agregar clave temporal de instalación aleatoria de 32+ caracteres; desplegar `instalar.php`; acceder por HTTPS para crear esquema y usuario.
6. Retirar clave y cerrar instalador; verificar conexiones y permisos.
7. Activar CRUD y login real; probar persistencia de sesión a sesión.
8. Si se necesita multi-dispositivo, implementar sincronización con polling ligero o mecanismo adecuado y verificar sin actualizar navegador.
9. Auditar servidor y datos, respaldos, y documentar versión final.

## Protección de información

Nunca almacenar contraseñas, claves secretas, `.env` de producción ni tokens GitHub en repositorios o conversaciones. Un repositorio público aumenta la importancia de bloquear archivos internos del servidor web. El backend debe ser la única vía de acceso a MariaDB.

**No confundir:** la documentación describe implementación y pruebas reportadas. No acredita por sí sola una auditoría completa de la instalación real.
