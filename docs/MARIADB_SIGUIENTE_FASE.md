# Próxima fase: PHP y MariaDB (aún no implementada)

1. Crear MariaDB en hPanel, usuario exclusivo y contraseña robusta.
2. Obtener host, puerto, nombre de BD y usuario desde hPanel. No asumir host ni puerto.
3. Configurar variables privadas del servidor: `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`. Nunca guardar contraseñas en GitHub o en HTML.
4. Implementar API PHP con PDO_MySQL, consultas preparadas, validaciones y manejo de errores.
5. Crear migraciones para tablas `users`, `categories`, `expenses` (gastos asociados a usuario, monto DECIMAL, fecha, categoría, nota).
6. Implementar inicio de sesión real con `password_hash`/`password_verify`, sesiones seguras, control de permisos y CSRF.
7. Conectar frontend a API autenticada; dejar de usar `localStorage` para los gastos.
8. Probar CRUD, persistencia tras cerrar sesión, aislamiento entre usuarios, respaldos y restauración.

**IMPORTANTE:** el login actual es solo una demo y no protege los datos. La estructura propuesta es un plan; no significa que ya exista conexión MariaDB.

El archivo `.env.example` sirve como plantilla de nombres y no debe contener secretos.
