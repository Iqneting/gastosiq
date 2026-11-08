# IQneting Gastos POS

**Estado:** maqueta V1 subida a GitHub; despliegue en Hostinger pendiente de verificar. Backend PHP y MariaDB aún no implementados.

## Contenido

- [index.html](index.html) — maqueta de punto de venta para registro de gastos. Login demo, categorías, teclado numérico, gráfica e historial editable.
- [Guía de despliegue en Hostinger](docs/DESPLIEGUE_HOSTINGER.md).
- [Plan de conexión PHP + MariaDB](docs/MARIADB_SIGUIENTE_FASE.md).
- [.env.example](.env.example) — nombres de variables futuras, sin contraseñas.
- [.gitignore](.gitignore) — excluye secretos y archivos locales.

## Uso de la maqueta

Abrir `index.html` en el navegador o publicar en un hosting estático/PHP.

**Acceso de demostración:** `demo@iqneting.com` / `123456`.

**Aviso:** el login NO es autenticación real. Los datos se almacenan únicamente en el navegador (`localStorage`) y la sesión demo en `sessionStorage`. Este repositorio actualmente es **público**: no subir información privada ni credenciales reales.

## Siguiente paso

Configurar dominio/sitio vacío en Hostinger, enlazar GitHub `main` y verificar que abre la interfaz. Después crear MariaDB y programar API PHP y login real, manteniendo la maqueta aprobada.
