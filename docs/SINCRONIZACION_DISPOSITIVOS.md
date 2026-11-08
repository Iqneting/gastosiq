# Sincronización automática · Gastos IQ POS

**Versión:** 2026-11-07. **Estado:** implementado en código; pendiente de comprobar el despliegue en Hostinger.

## Comportamiento

- La aplicación consulta `api/index.php?action=data` automáticamente cada **4 segundos** mientras está abierta, visible y con sesión iniciada.
- Las consultas incluyen la cookie de sesión; el servidor solo devuelve categorías y gastos del usuario autenticado.
- Si cambian los datos, se actualizan las categorías, resumen, movimientos y gráfica, sin recargar la página.
- Si no existen cambios, la interfaz no se vuelve a dibujar.
- Se evita superponer dos comprobaciones automáticas.
- Cuando la pestaña está oculta, se suspenden las comprobaciones; al volver a enfocarla se consulta inmediatamente.
- Los cambios que se guardan en el dispositivo actual siguen actualizando inmediatamente al recibir confirmación de la API.

## Alcance técnico

Es **sincronización casi en tiempo real mediante sondeo HTTP (polling)**. No usa sockets, notificaciones push ni conexión persistente. El retraso habitual es hasta 4 segundos más la latencia de red y servidor. El sondeo solo compara el conjunto de datos devuelto por la API, cuyo historial está limitado actualmente a 2,000 gastos por usuario.

Para proyectos con gran escala, sustituir el sondeo por una señal de versión ligera, eventos SSE o WebSockets según el alojamiento y las necesidades.

## Prueba manual de aceptación

1. Desplegar la última versión de la rama `main` en Hostinger.
2. Abrir `gastosiq.gentemejor.com` en móvil y PC, iniciar sesión con el mismo usuario en ambos.
3. Sin actualizar la PC, registrar en móvil la categoría temporal «Prueba sincronización». Verificar su aparición en PC en unos segundos.
4. Crear un gasto en esa categoría y comprobar totales, historial y gráfica en PC.
5. Editar y eliminar ese gasto. Confirmar las actualizaciones automáticas en ambos sentidos.
6. Ocultar la pestaña de la PC, modificar desde el móvil, volver a mostrar la pestaña y verificar la actualización.
7. Eliminar la categoría temporal al terminar.

**Nota:** estas pruebas deben hacerse en el dominio desplegado. Validar la sintaxis de JS o ver un commit en GitHub no prueba por sí solo la actualización en tiempo real del sitio.
