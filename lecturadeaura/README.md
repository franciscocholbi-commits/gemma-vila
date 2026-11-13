# lecturadeaura.es — web nueva

Montada en `lecturadeaura.gemmavilaaccion.es` (Webempresa, mismo hosting que gemmavilaaccion.es) para sustituir la web antigua hackeada (Divi, hosting de Tus Expertos).

- `lib.php`: funciones que generan el JSON de Elementor (widgets clásicos) con clases `lda-*`.
- `paginas.php`: contenido de las 13 páginas, con los mismos slugs de la web antigua y su SEO (Yoast).
- `estilos.css`: sistema visual (copia de los tokens de gemmavilaaccion.es), guardado en el CSS personalizado del kit de Elementor.
- Redirecciones 301 de URLs antiguas: mu-plugin `wp-content/mu-plugins/lda-redirecciones.php`.

Despliegue: se copian `lib.php` y `paginas.php` a `/home/hosting177539eu/tmp-lda/` y se ejecutan con Novamira (`require lib.php; require paginas.php;`). Es idempotente: actualiza las páginas por slug.

Cuando se traslade el dominio: copiar a la carpeta `public_html/lecturadeaura.es`, reemplazar URLs con una herramienta que respete los datos serializados, quitar "Disuadir a los motores de búsqueda" y forzar la versión sin www.
