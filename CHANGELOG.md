# Changelog · plugin gemmavila-academia

## 0.8.0 · 11/11/2026
- Plazas a la vista: en catálogo y ficha solo se muestra el número cuando quedan 10 o menos ("Quedan N plazas", "¡Últimas N plazas!", "Completo").
- Escritorio: columna "Plazas" en el listado de Formaciones (con hora de sincronización) y recuadro en cada formación con botón "Sincronizar ahora".

## 0.7.0 · 11/11/2026
- Plazas sincronizadas con tecnica-estructural.com (API Hermes) cada hora y comprobadas justo antes de cobrar. Se usa la cifra más baja entre las plazas de la entrada y la capacidad del evento, menos nuestras ventas aún no enviadas. "Completo" / "¡Últimas N plazas!".
- Los productos se llaman "Formaciones" en el escritorio y viven en /formacion/ (301 desde /producto/).
- La API Key pasa a wp-config.php (constante GV_HERMES_API_KEY).

## 0.6.0 · 11/11/2026
- Se retiran las pestañas de ficha generadas por código (0.5.0): las fichas pasan a hacerse con Elementor para que el equipo las edite sin depender de nadie.

## 0.5.0 · 11/11/2026 (descartada)
- Pestañas de ficha y panel de edición generados por código. Sustituido por Elementor en 0.6.0.

## 0.4.0 · 11/11/2026
- Cursos con fecha: no comprables desde su fecha de inicio y ocultos del catálogo por una tarea diaria (00:05, Madrid).

## 0.3.0 · 11/11/2026
- SEO: solo la home indexable; sitemap reducido a la home.

## 0.2.0 · 11/11/2026
- Compra directa sin carrito visible, botones "Inscribirme", traducción de enlaces antiguos `?add-to-cart`.

## 0.1.0 · 11/11/2026
- Menús por tipo de usuario, cierre de sesión con nonce, Mi cuenta sin Descargas, mensaje de catálogo vacío.
