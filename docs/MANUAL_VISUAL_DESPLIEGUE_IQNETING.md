# Manual visual aprobado — Gastos IQ · PDF de 8 páginas

**Edición:** V1.1, 2026-11-07 (hora CDMX).  
**Documento canónico de conocimiento IQneting:** [Manual visual y procedimiento detallado en GMIQ](https://github.com/Iqneting/GMIQ/blob/main/manuales/MANUAL_VISUAL_GASTOS_IQ_DESPLIEGUE_V1.md) (**repositorio privado**: requiere permisos para visualizar).  
**PDF entregado en el hilo:** `Manual_Visual_IQneting_Gastos_IQ_8_Paginas.pdf` — presentación horizontal, 8 páginas; el **binario PDF no está subido a GitHub** mediante esta operación.

## Objetivo

Repetir el proceso de Gastos IQ para nuevos proyectos PHP + MariaDB, respetando el diseño ya aprobado de las maquetas, automatizando el instalador inicial y documentando claramente qué hace la persona y qué hace el código.

## Índice del PDF

1. Portada: ruta GitHub + Hostinger + MariaDB.
2. Paso 1: definir funciones, maquetar UX/UI y obtener aprobación.
3. Paso 2: crear repositorio GitHub y subir código.
4. Paso 3: crear sitio Hostinger, asignar dominio, conectar GitHub `main`, comprobar publicación.
5. **Paso 4 corregido:** crear MariaDB y usuario y **guardar los datos de conexión, incluida expresamente la contraseña de MariaDB que el usuario eligió en Hostinger**. Esto **NO** significa crear ni guardar tablas manualmente.
6. Paso 5: crear archivo de configuración secreto PHP fuera de `public_html`, junto con clave temporal `SETUP_KEY`.
7. Paso 6: abrir el instalador protegido por HTTPS para crear tablas, relaciones, primer usuario y categorías; quitar clave y desactivar instalador al terminar.
8. Paso 7: pruebas, seguridad y sincronización entre dispositivos.

## Corrección editorial del Paso 4

### Texto autorizado

> Paso 4: Crea tu base de datos y conserva los datos de conexión.
>
> En Hostinger crea la base de datos y su usuario. Guarda el nombre de la base (`DB_NAME`), usuario (`DB_USER`), host (`DB_HOST`), puerto (`DB_PORT`) y **la contraseña del usuario de MariaDB (`DB_PASSWORD`) que tú creaste al configurar la base**.
>
> **No crees las tablas manualmente:** en este flujo se crean automáticamente al ejecutar `instalar.php` durante el paso 6.

### Lo que no debe volver a aparecer

- Instrucciones ambiguas como «guarda las tablas» o «guarda los datos» sin aclarar **credenciales de conexión**.
- Confusión entre `DB_PASSWORD`, `SETUP_KEY` y contraseña de inicio de sesión.
- Rediseños del resto del manual para corregir una sola frase o una sola lámina.

## Contraseñas: tres credenciales diferentes

| Nombre | Quién la crea | Para qué sirve | Al finalizar |
|---|---|---|---|
| `DB_PASSWORD` | Usuario en hPanel al crear el usuario MariaDB | Autenticación de la aplicación PHP ante MariaDB | Permanece en configuración privada, fuera de Git y web |
| `SETUP_KEY` | Usuario, clave aleatoria de 32+ caracteres | Autorizar **una sola vez** la ejecución del instalador | **Borrar de configuración privada** y desactivar instalador |
| Contraseña de acceso a Gastos IQ | Usuario en formulario `instalar.php` | Iniciar sesión en la aplicación; se guarda **hash**, no texto plano, en `users.password_hash` | Mantener privada; no reutilizar las anteriores |

## Archivo de configuración en Hostinger

La API PHP actual de Gastos IQ busca `gastosiq-config.php` desde `api/bootstrap.php` un nivel por encima del directorio público que contiene `index.html`, cuando el hosting usa la estructura de carpetas esperada.

Ejemplo **SIN credenciales**:

```php
<?php
return [
    'DB_HOST' => 'HOST_REAL',
    'DB_PORT' => '3306',
    'DB_NAME' => 'BASE_REAL',
    'DB_USER' => 'USUARIO_REAL',
    'DB_PASSWORD' => 'SECRETO_SOLO_EN_HOSTINGER',
    'SETUP_KEY' => 'CLAVE_TEMPORAL_ALEATORIA_32_O_MAS_CARACTERES',
];
```

Nunca publicar ese archivo en GitHub ni dentro de `public_html`. Una vez completada la instalación, **eliminar la entrada `SETUP_KEY`** del archivo privado. Los valores de ejemplo no son para producción. Confirmar host y puerto con hPanel.

## Advertencias de seguridad pendientes del piloto

- En una captura se observó `.git` dentro de `public_html`; comprobar denegación de acceso externo y no publicar directorios sensibles.
- `instalar.php` no debe seguir accesible tras completar la instalación; no basta con suponer que está bloqueado.
- Verificar en phpMyAdmin el esquema real, índices y claves foráneas; el SQL de Git no demuestra por sí solo el estado de MariaDB.
- La sincronización actual es polling cada 4 segundos: casi tiempo real, no WebSocket; el usuario confirmó prueba satisfactoria.
- La API limita las filas de historial a 2,000; los totales gráficos deben migrarse a SQL antes de escalar.
- No utilizar datos reales sensibles antes de revisar permisos, backups, sesiones, errores y rutas internas.

## Cómo replicar

1. Aprobar UX/UI y funciones.
2. Crear repo preferentemente privado, subir código y documentación.
3. Configurar sitio PHP, HTTPS, dominio y despliegue de `main` hacia `public_html`.
4. Crear BD/usuario MariaDB y **anotar la contraseña creada**.
5. Poner credenciales privadas PHP y `SETUP_KEY` temporal fuera de web.
6. Ejecutar `instalar.php` por HTTPS con la clave temporal; crear tablas/usuario/categorías.
7. Retirar clave, desactivar instalador y reducir permisos del usuario de BD según proceda.
8. Comprobar CRUD, permisos, persistencia y sincronización en dos dispositivos.
9. Documentar el resultado verificado y conservar el diseño aprobado.

## Reglas visuales acordadas

- Manual específico y profesional, legible y fácil, **no infantil**.
- Mantener paleta, composición, retícula, tipografía, iconos y orden aprobados.
- Si se pide una corrección concreta, modificar solamente esa sección/página.
- Presentación final: PDF horizontal, **una lámina por página**.

**Importante:** Este Markdown deja el contenido auditado y reutilizable; para preservar los 8 gráficos idénticos y el PDF final en Git, hace falta subir sus archivos binarios por un canal que admita PDF/PNG. No se afirma una subida binaria no realizada.
