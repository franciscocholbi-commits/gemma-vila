# Changelog · plugin gemmavila-academia

## 0.14.0 · 13/11/2026 (solo en pruebas hasta validarlo con David)
- Matrículas en tecnica-estructural.com (`includes/matriculas.php`): al quedar pagado un pedido (Procesando o Completado) se envía `POST /bookings` por cada formación con `amelia_event_id` (nombre, apellidos, email, teléfono, DNI en `custom_fields`, entrada si la hay, `external_order_id` = WC-número, sin notificaciones de su lado). Guarda el `booking_id` en la línea del pedido y deja nota.
- 409 (ya matriculada) y otros rechazos 4xx: no se reintenta; nota y correo al equipo (destinatarios de «Nuevo pedido»). Errores temporales (sin conexión, 429, 5xx): 3 reintentos a 1, 5 y 30 minutos con Action Scheduler; después, correo al equipo.
- Acción manual en el pedido: «Enviar matrícula a Técnica Estructural».
- La API real pide `customer.first_name` / `last_name` (su OpenAPI 1.7.11 dice `firstName`/`lastName`) y devuelve los errores de validación con HTTP 500 y código `validation_error`: se tratan como rechazo, no como error temporal.
- Una línea ya enviada (estado `enviado`) no se reenvía aunque la API devuelva `booking_id` 0.
- Constante opcional `GV_HERMES_EVENTOS_PERMITIDOS` en wp-config para limitar los eventos (en pruebas: solo el curso de prueba 1052).

## 0.13.0 · 13/11/2026
- NIF / DNI / NIE obligatorio reactivado en el pago (`includes/nif.php`). El 403 del 11/11 era el cortafuegos de Webempresa, no este módulo. Paso previo a emitir las facturas desde la tienda. Se prueba primero en el entorno de pruebas.
- Si el NIF está vacío solo sale el aviso de campo obligatorio de WooCommerce (antes salían dos mensajes).

## 0.12.0 · 12/11/2026
- Pago por transferencia (`includes/transferencia.php`): los pedidos por transferencia bancaria quedan "En espera" ocupando plaza; a los 2 días sin pago se envía a la alumna un recordatorio con los datos bancarios y a los 3 días el pedido se cancela solo (se libera la plaza y se avisa a la alumna). Tarea programada cada hora (`gv_transferencia_revisar`). Plazos ajustables con los filtros `gv_transferencia_dias_aviso` y `gv_transferencia_dias_cancelar`.

## 0.11.0 · 12/11/2026
- Datos para el cuadro de mando (`includes/datos.php`): listados CSV `/wp-json/gv/v1/datos/ventas` (una fila por pedido) y `/wp-json/gv/v1/datos/ediciones` (una fila por formación), protegidos con la clave de la opción `gv_datos_clave`. Sin datos personales. Los pedidos de prueba se marcan con la meta `_gv_pedido_prueba`.

## 0.10.0 · 12/11/2026
- Origen de cada venta (`includes/origen.php`): los enlaces de compra pueden llevar `?origen=<marca>` y UTM; se guardan en la sesión de compra (sin cookies nuevas) y pasan al pedido (`_gv_origen`, `_gv_origen_web`, `_gv_utm_*`). Columna "Landing / campaña" en Pedidos (WooCommerce ya tiene su propia columna "Origen", basada en cookies), recuadro en cada pedido y línea en el correo "Nuevo pedido" del equipo. Sin marca, se guarda al menos la web de procedencia.

## 0.9.2 · 11/11/2026
- NIF desactivado temporalmente para diagnosticar el 403 en el pago.

## 0.9.1 · 11/11/2026
- Pago sin el recuadro "Notas del pedido".

## 0.9.0 · 11/11/2026
- Campo obligatorio "NIF / DNI / NIE" en el pago y en Mi cuenta (necesario para la factura completa: los cursos superan 400 €). En España se valida formato y letra (DNI, NIE y CIF); fuera de España se acepta el documento. Se muestra en la dirección de facturación del pedido, correos y escritorio.

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
