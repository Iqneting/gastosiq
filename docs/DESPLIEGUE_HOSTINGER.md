# Despliegue Gastos IQ POS V1 en Hostinger

**Estado:** pendiente de configurar y comprobar en un dominio real.

## Pasos

1. Crear un sitio web vacío en Hostinger y asignarle dominio o subdominio.
2. Activar HTTPS/SSL.
3. Buscar herramienta Git en hPanel (disponibilidad según plan). Vincular `https://github.com/Iqneting/gastosiq.git`.
4. Seleccionar rama `main` y carpeta pública del sitio (habitualmente `public_html`; confirmar en hPanel).
5. Ejecutar despliegue inicial. Verificar que `index.html` quede en la raíz publicada y no en un subdirectorio adicional.
6. Visitar dominio por HTTPS, iniciar sesión demo, registrar un gasto, editarlo, eliminarlo, probar categorías y gráfica.
7. Verificar si el despliegue automático está habilitado: no basta con vincular Git; podría requerir desplegar manualmente cada cambio.
8. Probar en PC y celular y registrar resultado.

**Atención:** el repositorio es público; la contraseña `123456` es únicamente una simulación visible en el frontend. No hay seguridad real, API ni MariaDB. Los datos se guardan en el navegador mediante `localStorage`. No usar datos sensibles.

**Criterio de aprobación:** mostrar la maqueta funcionando en la URL final. Que esté en GitHub no demuestra que Hostinger haya desplegado.
