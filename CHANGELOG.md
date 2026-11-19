# Changelog · plugin gemmavila-academia

## 0.15.0 · 19/11/2026 (pruebas y academia real; en la real desde el 19/11 a la 01:27)
- Pago en cuotas con WooCommerce Subscriptions (planes en cada formación): Módulo 1 en 2 cuotas de 225 €, Módulo 2 en 3 de 210 €, packs en 3 de 333 €, mensuales. La primera al inscribirse; el resto se cobra solo a la tarjeta guardada.
- `includes/cuotas.php`: enlace de compra en cuotas (`gv_url_cuotas()`, para landings) y shortcode `[gv_boton_cuotas]` para las fichas; la alumna no puede cancelar ni suspender las cuotas desde Mi cuenta (sí cambiar la tarjeta).
- Plazas y matrículas: las cuotas 2, 3… (pedidos de renovación) no cuentan como plaza nueva ni se envían otra vez a Técnica Estructural.
- Cuadro de mando: nueva columna `cuota` («única», «1/2», «2/2»…) al final del listado de ventas; las renovaciones salen con alumno = «cuota» y no cuentan como inscripción en el listado de ediciones (sí suman en ingresos).
- Paso a la academia real (19/11, 01:27): código copiado desde pruebas con comprobación md5 (copia de la 0.13.0 en `/home/hosting177539eu/gv-copias/academia-plugin-0.13.0/`); `gv_matriculas_activas` sin activar (verificado: el envío no está enganchado); planes en 123, 125, 127, 128, 129, 140, 141, 142, 143, 144 y 192 (Sevilla, como el resto del Módulo 1); traducción de WooCommerce Subscriptions; reintentos de cobro activados; renovación anticipada desactivada; correos «cuota completada», «aviso de fin del plan» y los de regalo, desactivados; factura PDF adjunta también a los justificantes de cuota; `learndash_woocommerce_disable_access_removal_on_expiration` = yes (LearnDash aún no está activo en la real). Copia de los ajustes anteriores en la opción `gv_backup_ajustes_cuotas_20261119`. La compra sin cuenta sigue permitida para el pago único; en cuotas, el plugin obliga a crear cuenta.
- Matrículas (0.14.0) con interruptor: solo se envían si la opción `gv_matriculas_activas` vale «yes». En pruebas, encendido; en la academia real, apagado hasta que David dé el visto bueno (allí sí está la clave de la API y no hay lista de eventos permitidos).
- Finalizar compra: recuadro «¿Cómo quieres pagar?» con «Pago único de 440 €» o «En 2 cuotas mensuales de 225 €» (según la formación). Hacía falta porque los botones «Inscribirme» llevan directos al pago con el pago único y no había dónde elegir las cuotas. Al cambiar la opción, el resumen del pedido se recalcula. No sale al pagar una cuota pendiente.
- En la home, el listado y las fichas solo se ve el precio total; el «o 225 €/mes» que añade el plugin se quita (las cuotas se eligen en Finalizar compra).
- Resumen del pedido en Finalizar compra en lenguaje de cuotas: «225 € hoy (cuota 1 de 2)» y «Cuotas siguientes: 1 cuota más de 225 € el 19/12/2026» (o «2 cuotas más de 210 €: 19/12/2026 y 19/01/2027»), en vez de «/ mes durante 2 meses».
- Ajuste (no es código), 19/11, en pruebas y en la academia real: sin el texto «IVA incluido» detrás de los precios (copia en la opción `gv_backup_price_suffix_20261119`). El desglose del IVA sigue en el total de Finalizar compra y en la factura.
- Traducción al castellano de WooCommerce Subscriptions (482 frases de lo que ve la alumna: correos, pago, Mi cuenta, precios y fechas). «Subscription» se dice «plan de pago» y «renewal», «cuota»; el estado Expired sale como «Pagado» (en las cuotas, terminar el plan significa que está todo pagado). Archivos `wp-content/languages/plugins/woocommerce-subscriptions-es_ES.po/.mo` en pruebas (md5 del .mo d9bee59014093e977153eb98b574eaa3); copia con los JSON de origen en `/home/hosting177539eu/gv-copias/traducciones/` (fuera de las webs). El plugin no tiene traducción oficial y sus actualizaciones no la pisan. Falta copiarla a la academia real con el resto de 0.15.0.

## mu-plugin gv-iva-facturas 1.0.0 · 18/11/2026 (producción)
- Facturas con el sistema interno (PDF Invoices & Packing Slips) en la academia real. Decisión de Francisco: España y UE con 21 % de IVA incluido; fuera de la UE, factura sin IVA por el **mismo importe final** (no el reducido).
- `academia/mu-plugins/gv-iva-facturas.php`: `woocommerce_adjust_non_base_location_prices` = false (mismo precio en todos los países), la factura PDF muestra base + IVA desglosados (la web sigue mostrando precios con IVA incluido) y una nota de «Operación no sujeta al IVA español» cuando el cliente es de fuera de la UE y el pedido va sin IVA.
- Ajustes de WooCommerce (no son código): impuestos según la dirección de facturación; tasa «IVA 21%» para España y los 26 países restantes de la UE; sin tasa para el resto del mundo.
- 18/11 (tarde), confirmado por Francisco: **las formaciones presenciales llevan IVA siempre**, también para clientes de fuera de la UE. Clase de impuesto «Presencial (IVA siempre)» (`presencial-iva-siempre`, 21 % en todos los países) en los productos 123, 125, 127, 129, 140, 142, 144 y 192. Las online usan la clase estándar (21 % en España y UE, 0 % fuera). **Toda formación presencial nueva debe llevar esta clase** (al duplicar una presencial ya la lleva).
- Pedidos 180 (AR), 182 (MX) y 197 (CL), online y cobrados con IVA antes del cambio: pasados a 440 € sin IVA (el importe cobrado no cambia), con nota interna y copia en la meta `_gv_backup_impuestos_20261118`.
- Facturas 2026-0001 a 2026-0010 emitidas el 18/11 para los 10 pedidos reales pagados hasta ese día (se excluyen los de prueba 137 y 173). No se han enviado por correo; se descargan desde el pedido o desde Mi cuenta.

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
