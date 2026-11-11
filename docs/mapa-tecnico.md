# Gemma Vila — Mapa técnico, decisiones y pendientes

_Última actualización: 11/11/2026_

## ▶ PRÓXIMA SESIÓN: EMPEZAR AQUÍ
1. ✅ **Dominio de la academia corregido (11/11/2026)**: `www.academia` → `academia` en toda la base de datos (48 cambios con WP-CLI). WordPress redirige www a sin www y Novamira ya anuncia la dirección sin www. Copia previa de la base de datos en `/home/hosting177539eu/backup-academia-antes-dominio-20261111.sql` (fuera de public_html; borrarla cuando todo esté comprobado).
2. **Correo (WP Mail SMTP)**: SMTP cp7066.webempresa.eu, puerto 465, SSL, usuario y remitente info@gemmavilaaccion.es (remitente forzado).
   - ✅ Configurado en gemmavilaaccion.es y en la academia (11/11/2026). La autenticación SMTP funciona en las dos (comprobada contra el servidor). En la academia el usuario estaba como "cholbi" (autorrelleno del navegador) y el remitente con una errata (gemmavilaacion.es): corregido. Remitente de WooCommerce en la academia: info@gemmavilaaccion.es / "Academia Gemma Vila".
   - ✅ Correos de prueba recibidos desde las dos webs. **mail-tester.com: 10/10** (11/11/2026).
   - DNS (gestionados en Webempresa, ns7021/ns7022): SPF correcto (`+a +mx +ip4:213.158.86.37 include:_spf.webempresa.eu ~all`), DKIM activo (selector `default`), DMARC `v=DMARC1; p=none; rua=mailto:info@gemmavilaaccion.es; fo=1` (publicado el 11/11/2026).
   - [ ] **Hacia el 9/12/2026: revisar los informes DMARC que llegan a info@ y subir a `p=quarantine`** si todo el correo legítimo sale de Webempresa.
   - El email de administración de las dos webs sigue en gemmavilapages@gmail.com.
3. **Nota técnica:** las cuatro webs comparten la cuenta de hosting `hosting177539eu`. Desde el conector de gemmavilaaccion.es se puede administrar la academia con WP-CLI (`--path=/home/hosting177539eu/public_html/academia.gemmavilaaccion.es`) si su conector falla.
   - Activar DKIM en Webempresa y revisar SPF y DMARC del dominio.
   - Probar con mail-tester.com (objetivo ≥ 9/10).
   - Preguntar a Webempresa su límite de envíos por hora.
3. Radiografía completa de la academia por Novamira y limpieza de plugins. PHP 8.5.12.

## Negocio
Formación en Técnica Estructural, lecturas de aura y sesiones presenciales de Gemma Vila. Un solo negocio: todas las webs captan para él.
Equipo: Gemma (dueña), Dana y Johi (RRSS, landings, gestión de usuarios). Soporte técnico: Francisco.

## Arquitectura (DECIDIDA)

| Web | Papel | Contenido |
|---|---|---|
| gemmavilaaccion.es | Marca principal, SEO, captación | WordPress + Elementor Pro. Sin tienda. |
| tecnicaestructural.es | Satélite de captación | WordPress + Elementor Pro. Landings → academia. |
| lecturadeaura.es | Satélite de captación | WordPress + Elementor Pro. Landings → academia. |
| academia.gemmavilaaccion.es | **Única instalación transaccional** | WooCommerce + LearnDash + Stripe + API técnica-estructural.com |
| tecnica-estructural.com | Web oficial del método (NO es de Gemma) | Recibe las ventas por API (Hermes) |

- Cada web es una instalación de WordPress independiente. Nada de multisite.
- Tienda y academia juntas: la integración nativa LearnDash–WooCommerce matricula al alumno al comprar. Un solo login para el alumno.
- Las landings de todas las webs enlazan al carrito de la academia (`?add-to-cart=ID`).
- Venta cruzada mediante packs en la tienda única.
- Todo el hosting y los dominios se migran a **Webempresa** (cuenta propiedad de Gemma). Se rehace de cero, sin arrastrar nada del hosting anterior.
- Temporal: `te.gemmavilaaccion.es` (MCP Novamira) hasta que tecnicaestructural.es esté en Webempresa.
- Landings con Elementor Pro, construidas desde Claude con el conector Novamira.
- Servidores con LiteSpeed.

## Diseño (DECIDIDO)
- **El diseño de gemmavilaaccion.es es la referencia para todas las webs**, empezando por la academia: mismo tema (Hello Elementor), mismos ajustes globales del kit de Elementor (colores, tipografías, variables y clases V4) y misma maquetación de cabecera y pie.
- Plantillas origen en gemmavilaaccion.es: cabecera "Elementor Header #59" (ID 59) y pie "Pie de página principal" (ID 133), ambas aplicadas a todo el sitio.
- En la academia, cabecera y pie con el mismo diseño pero adaptados: enlaces que apuntan a gemmavilaaccion.es y elementos propios (Mi cuenta / Mis cursos / carrito).
- Las páginas internas de LearnDash (curso, lección, test) usan plantillas propias de LearnDash: se igualan colores y tipografías desde sus ajustes.
- Carrito y checkout con las plantillas nativas de WooCommerce, estilizadas con la marca; no maquetados con Elementor.

## Plugins (DECIDIDO)
Regla: **lo que no está en esta lista no se instala sin pasar por Francisco.**

**Base común en todas las webs:** Hello Elementor · Elementor · Elementor Pro · Yoast SEO · Complianz · Novamira · UpdraftPlus · WP Mail SMTP · LiteSpeed Cache · plugin de 2FA (ej. Two Factor).
- LiteSpeed Cache solapa con Image Optimization en la optimización de imágenes: quedarse con uno solo (previsiblemente LiteSpeed Cache).
- Comprobar que la licencia de Elementor Pro cubre todas las webs.

**Solo en la academia (lista cerrada):**
| Función | Plugin |
|---|---|
| Tienda | WooCommerce |
| Pagos | WooCommerce Stripe Gateway (oficial) |
| Cursos | LearnDash (licencia de pago) |
| Tienda ↔ cursos | Integración oficial LearnDash–WooCommerce |
| API tecnica-estructural.com + campo NIF/DNI en checkout | Plugin propio (código nuestro, versionado) |

SEO en la academia: indexar fichas de curso; no indexar lecciones, carrito, checkout ni Mi cuenta.

**Pendientes de decisión (no instalar hasta cerrarlas):** plugin del programa de facturación; plugin de suscripciones si los pagos fraccionados lo requieren.
**Sesiones presenciales:** sin plugin al principio (producto con variaciones por fecha y stock = plazas). Plugin de reservas/entradas solo si se necesita control de asistencia, QR o calendario.

**No instalar:** paquetes de complementos de Elementor (Essential Addons, Ultimate Addons…), más de un plugin de caché/optimización, Code Snippets en producción, Akismet (sin comentarios), Angie (salvo que se use), personalizadores de login/checkout/emails sin necesidad concreta.

### Estado actual de gemmavilaaccion.es (10/11/2026)
WordPress 7.1.4 · PHP 8.5.11 (LiteSpeed) · es_ES · tema Hello Elementor.
Activos: Angie 1.1.19, Complianz 7.5.7, Elementor 4.3.5, Elementor Pro 4.3.1, Image Optimization 1.7.7, Novamira 1.12.14, WooCommerce 11.2.2, WooCommerce Stripe Gateway 11.1.1, Yoast SEO 28.8.
Inactivos (a borrar): Akismet, Code Snippets, Hello Dolly.
Faltan: UpdraftPlus, WP Mail SMTP, LiteSpeed Cache, 2FA.
Sobra según la arquitectura: WooCommerce + Stripe → ✅ desactivados el 11/11/2026 (tienda ya en la academia).

### Estado de la academia (11/11/2026, comprobado desde fuera, sin entrar al panel)
WordPress 7.1.4 · Novamira 1.12.15 · servidor nginx · nombre del sitio "La academia de Gemma Vila".
- **Rutas OAuth de Novamira: OK.** `/.well-known/oauth-protected-resource` y `/.well-known/oauth-authorization-server` devuelven 200 con JSON de WordPress. `/wp-json/mcp/novamira` responde 401 (correcto sin autenticar).
- **Problema: el sitio está configurado como `https://www.academia.gemmavilaaccion.es`** (home/siteurl). El OAuth anuncia issuer y endpoints en `www.academia…`. Además responden las dos variantes (con y sin www) con 200 y sin redirección → contenido duplicado y riesgo de fallo al conectar el MCP por desajuste de dominio. Corregir: home y siteurl a `https://academia.gemmavilaaccion.es` y redirección 301 de www.academia → academia (Webempresa, o reglas del servidor). Revisar el certificado SSL de la variante www.
- Las respuestas OAuth llevan la cabecera Content-Type duplicada (nginx + WordPress). De momento no rompe nada; si el cliente MCP falla, pedir a Webempresa que no la añada en esas rutas.
- Plugins activos detectados por su API REST: WooCommerce, Elementor, Elementor Pro, Hello Elementor, WP Mail SMTP, Novamira, Contact Form 7, Wordfence (+ Login Security), Jetpack, All-in-One WP Migration, Code Snippets, Elementor MCP Composer, Elementor One.
- **Fuera de la lista (a revisar/quitar):** Contact Form 7, Wordfence, Jetpack, All-in-One WP Migration, Code Snippets, Elementor MCP Composer, Elementor One. Wordfence choca con LiteSpeed/2FA de la base: decidir uno.
- **No aparecen (no instalados o no activos):** LearnDash, Yoast SEO, Stripe Gateway (sin confirmar), Complianz, UpdraftPlus, LiteSpeed Cache. Confirmar con la radiografía por Novamira.

### Academia: trabajo hecho el 11/11/2026 (por Novamira)
- Instalación real: Hello Elementor 3.5.2 · Elementor 4.3.5 · Elementor Pro 4.3.1 · WooCommerce 11.2.2 · WP Mail SMTP · Novamira 1.12.15 · Wordfence 9.1.0 · ACF 6.8.11 · Code Snippets 4.1.0. Usuarios: cholbi y gemma (admins). Mismo hosting que gemmavilaaccion.es (cuenta hosting177539eu).
- **Limpieza:** 12 productos demo de WooCommerce, "Página de ejemplo" y borrador "Refund and Returns Policy" a la papelera; Contact Form 7 (y su formulario) y All-in-One WP Migration desinstalados; 8 temas sin uso borrados (quedan Hello Elementor y Twenty Twenty-Five).
- **Pruebas Hermes de Francisco (se mantienen, uso interno):** snippets 5-8 de Code Snippets (solo consultas GET, ninguno matricula), grupo ACF "Integración API Hermes" (campos amelia_event_id / amelia_ticket_id en productos), página 56 "cursos tecnica estructural.com" y producto 55 "Curso de Prueba Hermes" → página y producto pasados a **privado** (producto también oculto del catálogo). La API Key de producción sigue en texto plano dentro de los snippets: se resuelve con el plugin propio. Ojo: el evento 970 es real ("Módulo 1 en Mataró"), no de sandbox.
- **Diseño trasladado desde gemmavilaaccion.es:** kit de Elementor completo (colores, tipografías Instrument Serif + Manrope, botones, formularios y CSS de WooCommerce; copia del kit anterior en la meta `_novamira_backup_page_settings` del kit 47), logo (ID 90) y favicon (ID 91), menú "Menú Principal" (ID 20) con los mismos enlaces a gemmavilaaccion.es, plantilla "Cabecera academia" (ID 104) con carrito y botón "Mi cuenta" en lugar de "Reservar sesión", y "Pie academia" (ID 105) igual al de la web principal. Ambas en todo el sitio.
- WooCommerce: precios en formato español (0,00 €) y país de la tienda Barcelona, España (estaba en Reino Unido).
- ✅ **Wordfence desinstalado de la academia (11/11/2026)**, con sus tablas y la carpeta wflogs (su cortafuegos no estaba enganchado al servidor). DECIDIDO: Wordfence fuera de todas las webs. ✅ También quitado de te.gemmavilaaccion.es (11/11/2026), junto con Akismet y Hello Dolly; allí quedan Elementor, Elementor Pro, Novamira y Yoast SEO. Pendiente: elegir un servicio ligero de avisos de vulnerabilidades para las cuatro webs (Claude trae propuesta) + plugin ligero de 2FA para administradores.
- ✅ **HPOS activado en la academia (11/11/2026)**, con 0 pedidos. El menú de gestión "Pedidos" apunta a `admin.php?page=wc-orders`.
- `.htaccess` de la academia corregido: la regla de Webempresa de http→https mandaba a www. Ahora www→sin www y http→https van a `https://academia.gemmavilaaccion.es`. Copia: `.htaccess.bak-20261111`.

**Menús y home de la academia (11/11/2026)** — la academia NO repite el menú de la web: solo sirve para comprar y para que los alumnos accedan.
- Home (página "Inicio", ID 21, Elementor): cabecera de bienvenida + "Cursos disponibles" con el shortcode `[products]` (todos los productos publicados, orden de menú) + acceso para alumnos. Si no hay cursos publicados sale "Muy pronto publicaremos nuevas formaciones". Página Blog eliminada (la academia no tiene blog).
- Tres menús, un solo hueco en la cabecera (ID 104): el widget de menú siempre usa `academia-publico` y el plugin propio lo cambia según quién mira:
  - **Público** (`academia-publico`, ID 23): Cursos · Web de Gemma Vila · Acceder (botón).
  - **Alumno** (`academia-alumno`, ID 24): Mis cursos (de momento → Mi cuenta; con LearnDash irá a sus cursos) · Catálogo · Mis pedidos · Mis datos · Cerrar sesión.
  - **Gestión** (`academia-gestion`, ID 25, quien tenga `manage_woocommerce`: hoy los administradores) · Pedidos · Cursos y productos · Alumnos · Informes · Escritorio · Cerrar sesión.
- Logo y nombre de la cabecera enlazan a la home de la academia; subtítulo "Academia". Botón "Mi cuenta" sustituido por el botón "Acceder" del menú público.
- **Plugin propio creado: `gemmavila-academia` v0.1.0** (wp-content/plugins/gemmavila-academia/). Hace: cambio de menú según usuario, "Cerrar sesión" con nonce, quita "Descargas" de Mi cuenta, mensaje de catálogo vacío. Aquí irá la integración Hermes. Falta versionarlo (repositorio).

### Tienda trasladada a la academia (11/11/2026)
- Productos recreados en la academia (ID web principal → ID academia): 117→123 Módulo 1 presencial Mataró (440 €), 164→128 Módulo 1 online (440 €), 126→127 Módulo 2 (630 €), 118→125 Pack M1+M2 (1.070 € / oferta 999 €), 165→129 Pack M1 online+M2 (1.070 € / 999 €). Virtuales, venta individual, IVA 21% incluido. Mapa guardado en la opción `gv_mapa_productos_web_principal` de la academia.
- Categorías copiadas (Cursos y formaciones, Packs, Formación presencial, Sesiones online, Sesiones presenciales). Ajustes de IVA y checkout iguales a los de la web (teléfono obligatorio, empresa oculta, compra como invitado). Páginas Política de privacidad (130) y Términos y condiciones (131) copiadas y enlazadas en el pago.
- Stripe (WooCommerce Stripe Gateway 11.1.1) conectado por Francisco en la academia, **modo PRUEBAS** (para enseñarlo al equipo; pasar a producción después).
- Landings de gemmavilaaccion.es (93 Módulo 1 Mataró, 140 curso online): 14 enlaces cambiados a `https://academia.gemmavilaaccion.es/finalizar-compra/?add-to-cart=<ID academia>`. Copia previa en las metas `_gv_backup_elementor_data_20261111` y `_gv_backup_post_content_20261111` de cada página.
- gemmavilaaccion.es: WooCommerce y Stripe **desactivados** (no borrados; el pedido de prueba 194 de Dana sigue en la base de datos). `.htaccess` con 301 de /producto/*, /tienda, /carrito, /finalizar-compra y /mi-cuenta a la academia (copia `.htaccess.bak-20261111`).
- Plugin propio v0.1.2: traduce `?add-to-cart=<ID antiguo>` al producto nuevo y evita el error de "no puedes añadir otro" si el curso ya está en el carrito.
- Pendiente: los campos ACF `amelia_event_id` / `amelia_ticket_id` de los productos nuevos están vacíos (rellenar cuando se monte la integración Hermes). Pasar Stripe a producción tras la demo. Desinstalar WooCommerce y Stripe de la web principal cuando todo esté probado.

- **Compra directa (plugin propio v0.2.0):** al añadir un curso se vacía el carrito, se añade solo ese y se va al pago; sin aviso de "añadido al carrito"; botones "Inscribirme"; /carrito/ redirige al pago (o a la home si está vacío); icono de carrito quitado de la cabecera; AJAX de añadir al carrito desactivado.
- Landing 93 con nueva dirección `tecnica-estructural-modulo-1-en-mataro-14-y-15-de-noviembre-gemma-vila` (301 desde la de "24-y-25-de-octubre" en el .htaccess de la web principal).
- Pies de página con info@gemmavilaaccion.es: web principal (133), academia (105) y te. (41, 43 y 67). Quedan con el correo antiguo: landing 140 de la web principal (gemmavilapages@gmail.com) y la página 87 "Área de alumnos · Módulo 1" de te. (info@gemmavilapages.com).

### Páginas legales (✅ 11/11/2026)
- 5 páginas en las tres webs (gemmavilaaccion.es, academia y tecnicaestructural.es) con la estructura de las de tecnica-estructural.com y redacción propia: `/aviso-legal/`, `/politica-de-privacidad/`, `/politica-de-cookies/` (tabla de cookies real de cada web; la academia incluye WooCommerce y Stripe), `/terminos-y-condiciones/` (título "Condiciones generales") y `/politica-de-redes/`.
- Titular: Gemma Vila Pagès, autónoma (sin Registro Mercantil), nombre comercial Gemma Vila Acción, domicilio en Mataró, teléfono +34 622 082 930, info@gemmavilaaccion.es. Política de cancelación igual que Técnica Estructural (más de 7 días: cambio o devolución; menos de 7: saldo 12 meses; empezada: sin devolución). La privacidad recoge la cesión de datos a LLAOR I ANGRUNETA, SL para la matrícula.
- Copia del contenido anterior en la meta `_gv_backup_post_content_20261111` de cada página. Borrador "Condiciones de venta" (119) de la web principal a la papelera.
- Pies de página con los 5 enlaces (relativos, cada web a sus páginas): 133 web principal, 105 academia, 67 tecnicaestructural.es. Copia previa en `_gv_backup_elementor_data_legal_20261111`.
- Academia: página de términos = 131, privacidad = 130. Texto de la casilla del pago con la aceptación de que no hay desistimiento (art. 103 RDL 1/2007) y aviso de cesión a Técnica Estructural junto a la privacidad.
- Pendiente: banner de cookies (Complianz) cuando se añada analítica o píxeles; ahora solo hay cookies técnicas.

### Correos de la tienda (academia, 11/11/2026)
- Compra de prueba OK: pedido 137 (Francisco, Stripe pruebas).
- Plantilla de correos WooCommerce: logo (ID 90, 90 px, centrado), cabecera #34508A, fondo #FBF7EE, texto #221D3F, pie "Gemma Vila Acción · Academia / info@ · teléfono / web", Reply-To info@gemmavilaaccion.es. Textos finales cercanos en "Procesando", "Completado" y "En espera". Método de pago visible como "Tarjeta".
- Destinatarios de Nuevo pedido, Pedido cancelado y Pedido fallido: gemmavilapages@gmail.com, federicidana@gmail.com (Dana) y amor.johi@gmail.com (Johi). Dana y Johi son administradoras en la academia.

### tecnicaestructural.es (11/11/2026)
- A las 12:23 (hora de Madrid) alguien trasladó te.gemmavilaaccion.es a **tecnicaestructural.es** (cambio de dominio de Webempresa: misma base de datos, prefijo `w47fa_`). te.gemmavilaaccion.es ya no existe y su conector Novamira está muerto.
- Claude corrigió 47 referencias a te.gemmavilaaccion.es que quedaron en la base de datos (imágenes rotas en la home) con WP-CLI. Copia previa: `/home/hosting177539eu/backup-tecnicaestructural-antes-dominio-20261111.sql`.
- Pendiente: crear el conector Novamira de tecnicaestructural.es (`https://tecnicaestructural.es/wp-json/mcp/novamira-oauth`) y borrar el antiguo. Mientras, se puede administrar con WP-CLI `--path=/home/hosting177539eu/public_html/tecnicaestructural.es` desde el conector de gemmavilaaccion.es.

### Hallazgos en gemmavilaaccion.es (11/11/2026)
- **El menú principal enlaza a páginas en BORRADOR** (Quién soy 13, Formación 12, Contacto 14, Sesiones 15, Máster 16): los visitantes llegan a un 404. El botón "Reservar sesión" de la cabecera apunta a Contacto (borrador). Publicar esas páginas o quitar los enlaces.
- El pie muestra info@gemmavilapages.com, no info@gemmavilaaccion.es (el buzón que vamos a usar para todo). Decidir cuál es el correo público.
- La copia del menú en la academia apunta a las mismas URL `?page_id=`; cuando se publiquen las páginas, WordPress redirigirá solo a la URL bonita.

## Conectores Novamira (MCP)
| Conector | Web | Estado |
|---|---|---|
| novamira-gemmavilaaccion | gemmavilaaccion.es | Conectado |
| novamira-tecnicaestructur | tecnicaestructural.es | Conectado (11/11/2026). El antiguo de te.gemmavilaaccion.es está muerto: borrarlo de Claude |
| novamira-academia-gemmavi | academia.gemmavilaaccion.es | Conectado (11/11/2026, recreado con la URL sin www: `https://academia.gemmavilaaccion.es/wp-json/mcp/novamira-oauth`) |

**Paso estándar al dar de alta cada web en Webempresa:** pedir al soporte que las URLs `/.well-known/oauth-protected-resource` y `/.well-known/oauth-authorization-server` pasen a WordPress en lugar de devolver 404 desde nginx, por ejemplo añadiendo antes de la regla de `/.well-known/`:

```
location ^~ /.well-known/oauth- {
    try_files $uri /index.php?$args;
}
```
Además: comprobar que home/siteurl no llevan www en subdominios y que la variante no canónica redirige con 301.

## Integración API → tecnica-estructural.com (API Hermes)
- Documentación: https://tecnica-estructural.com/hermes-api-doc/manual/ (dice v1.5.1) · Swagger: https://tecnica-estructural.com/hermes-api-doc/ · OpenAPI: https://tecnica-estructural.com/hermes-api-doc/openapi.json · Wizard de pruebas: https://tecnica-estructural.com/hermes-testing/
- Material en Drive, carpeta "INFORMACION INTEGRACIÓN TECNICA ESTRUCTURAL": doc "INTEGRACION TECNICA ESTRUCTURAL" (chat con el informático + API Key) y script `test-hermes-enroll.php` (script de prueba por línea de comandos hecho por su informático; no es una integración con WooCommerce).
- **Contacto técnico:** David Herrero García (informático de tecnica-estructural.com). Entregó el endpoint el 22/09/2026.
- **API Key de Gemma: actualmente en PRODUCCIÓN** (ya no está en sandbox). Cualquier prueba con ella crea matrículas reales. ID de curso mencionado en producción: 1052. En el script de prueba: 970.

**Qué es en realidad:** una API de **matrículas** sobre Amelia (plugin de reservas). Cada curso de Técnica Estructural es un *evento* de Amelia con fechas, precios (tickets) y **plazas limitadas**. Al vender, hacemos `POST /bookings` y el alumno queda matriculado en su sistema; ellos facturan la comisión fuera de nuestro entorno.

- Base: `https://tecnica-estructural.com/wp-json/hermes/v1` · Autenticación por cabecera `X-API-Key` · JSON · solo HTTPS.
- Endpoints de lectura: `/events`, `/events/{id}`, `/events/{id}/students`, `/events/{id}/tickets` (plazas libres), `/customers`.
- `POST /bookings` obligatorios: `event_id`, `customer.first_name`, `customer.last_name`, `customer.email`, `payment.gateway` ("wc"), `payment.status` ("paid"). Opcionales: teléfono, país, `custom_fields` (ej. dni, idioma_temario), `tickets[]` (necesario si el curso tiene varios precios), `payment.external_order_id`, `payment.amount`, `payment.currency`.
- El script de David (que cita Hermes **v1.6.0**) envía además campos que el manual online no documenta: `locale`, `timezone` y `send_notifications: false`. → El manual online puede estar desactualizado.
- Errores clave: `event_full` / `ticket_sold_out` (sin plazas), `duplicate_booking` 409 (ya matriculado; no reintentar), 403 (la key no tiene permiso `bookings:write`), 429 (esperar 60 s), 500 (reintentar 3 veces cada 5 s).
- Sandbox: se activa por API Key desde su panel; matricula en un curso ficticio **pero crea los alumnos en su sistema real**.
- Reembolsos y cancelaciones: fuera de línea, no por API (la API tampoco los contempla).
- Hay pruebas conectadas en gemmavilaaccion.es (pendiente localizar cómo están hechas: no hay snippets de Angie y Code Snippets está inactivo). **Hay que trasladarlas a la academia.**

**Requisitos de nuestro lado para la integración:**
1. Cada producto de WooCommerce de Técnica Estructural mapeado a su `event_id` (y `ticket_id` si aplica).
2. Comprobar plazas en `/events/{id}/tickets` **antes de vender** y reflejarlas en el stock del producto, para no cobrar una plaza que ya no existe.
3. `payment.amount` por línea de producto, no el total del pedido (el ejemplo de WooCommerce de su documentación envía el total por cada curso y puede sobrecontar comisiones).
4. Envío en cola con reintentos según su guía, registro de cada envío en el pedido (`booking_id` devuelto) y aviso por email cuando falle.
5. Enviar siempre `external_order_id` (número de pedido) para poder cuadrar con ellos.
6. RGPD: estamos cediendo datos de alumnos a un tercero → debe figurar en la política de privacidad y en el checkout.
7. La API Key se guarda en `wp-config.php` como constante, nunca en la base de datos, en documentos ni en la hoja de contraseñas.

## Pagos
- Stripe.
- Pagos fraccionados: no se ofrecen en la página de venta, solo a petición por correo.

## Correo (DECIDIDO)
- **WP Mail SMTP** en todas las webs, enviando por SMTP autenticado desde el buzón **info@gemmavilaaccion.es alojado en Webempresa** (sin servicio externo por ahora).
- Condiciones: SPF, DKIM y DMARC correctos en el dominio; prueba con mail-tester.com antes de abrir la academia.
- Riesgos asumidos: límite de envíos por hora del hosting, IP compartida y sin registro de envíos en la versión gratuita. Si dan problemas, pasar a un servicio transaccional (p. ej. Brevo) cambiando solo el ajuste de WP Mail SMTP.

## Copias de seguridad
- UpdraftPlus en todas las webs, con destino remoto (fuera del hosting).

## Accesos
- Contraseñas: ahora en una hoja de cálculo compartida (Francisco usa su propio gestor).

---

## PENDIENTES

### Urgente
- [ ] **PHP vulnerable**: gemmavilaaccion.es y te.gemmavilaaccion.es pasaron de 8.4.26 a **8.5.11**, que sigue afectada por CVE-2026-109618 y CVE-2026-109619 (corregidas en 8.5.12 / 8.4.27, 21/10/2026). Pasar a 8.5.12+ o que el hosting confirme que su build 8.5.11 está parcheado. Comprobar lo mismo en el resto de webs.
- [ ] **API Key de producción expuesta**: está en texto plano en un Google Doc, en el script PHP de Drive y en conversaciones con IA. Cuando la integración nueva esté lista, pedir a David que la **rote** (key nueva) y guardarla solo en `wp-config.php` de la academia. Mientras tanto, quitarla del Doc y del script.
- [ ] **No hacer pruebas con la key de producción**: pedir a David una **segunda key en sandbox** para desarrollo.

### Por decidir
- [ ] **Angie**: ¿se usa? Si no, desinstalar (puede ejecutar PHP, igual que Novamira).
- [ ] **Image Optimization vs LiteSpeed Cache** para optimizar imágenes: quedarse con uno.
- [ ] **WooCommerce en gemmavilaaccion.es**: ¿hay ventas o pedidos reales ahí ahora? Desactivarlo cuando la tienda esté en la academia.
- [ ] **Facturación**: elegir programa (FacturaDirecta, Holded u otro económico) compatible con Verifactu, y decidir si factura WooCommerce o el programa externo.
- [ ] **Pagos fraccionados**: cómo se cobran (producto oculto, suscripción con cuotas limitadas o Stripe directo), si el acceso se da con el primer pago y se retira si falla una cuota, y cómo se informa a tecnica-estructural.com. La API solo admite `status: "paid"`: **preguntar a David**.
- [ ] **Entorno de pruebas** para la academia: recomendado una vez haya ventas reales (clon en subdominio; confirmar si Webempresa lo ofrece en el plan).
- [ ] **Roles**: propuesta de Claude → Gemma y Francisco administradores en todo; Dana y Johi administradoras en las webs de marketing, y en la academia como gestoras de tienda + rol de LearnDash. Francisco prefiere que todos sean administradores. Sin cerrar.
- [ ] **Gestor de contraseñas compartido** (Bitwarden o 1Password para equipos) en lugar de la hoja de cálculo + 2FA en Stripe, Webempresa, registrador de dominios y administradores de WordPress. Recomendado, sin cerrar.
- [ ] **Academia: Wordfence** (instalado, fuera de la lista) frente a LiteSpeed Cache + plugin de 2FA: quedarse con un enfoque.

### Preguntas para David (informático de tecnica-estructural.com)
- [ ] ¿Nos puede dar una API Key en sandbox para desarrollo, separada de la de producción? ¿Y rotar la de producción al terminar?
- [ ] ¿Cuál es la versión vigente de la documentación? (el manual online dice v1.5.1; su script, v1.6.0 y usa `locale`, `timezone` y `send_notifications`).
- [ ] `send_notifications`: ¿quién envía el email de confirmación al alumno, ellos o nosotros? Hay que decidirlo para no mandar dos o ninguno.
- [ ] Ventas a plazos: ¿cómo quieren recibirlas?
- [ ] La FAQ habla de `gateway: "onSite"` / `status: "pending"`, que contradice la tabla de campos. ¿Qué valores aceptan realmente?
- [ ] ¿Cómo evitamos duplicados si reintentamos? (no hay idempotencia, solo el 409 por alumno+curso).
- [ ] Un mismo alumno que compra dos ediciones del mismo curso: ¿da `duplicate_booking`?
- [ ] Límite real de peticiones por minuto.
- [ ] ¿Pueden borrar los alumnos de prueba creados en su sistema real?
- [ ] ¿Qué cursos (IDs) tiene Gemma activos ahora y con qué precios/tickets?

### Por hacer
- [ ] **Academia: corregir home/siteurl a `https://academia.gemmavilaaccion.es` + 301 desde www.academia** (antes de conectar el MCP y antes de meter contenido).
- [ ] **Añadir el conector Novamira de la academia en Claude** (URL `https://academia.gemmavilaaccion.es/wp-json/mcp/novamira-oauth`, autorizar con un usuario administrador). Después: radiografía de la instalación (WordPress/PHP, plugins, usuarios y roles, ajustes básicos) y orden de montaje.
- [ ] **Academia: limpiar plugins fuera de la lista** (Contact Form 7, Jetpack, All-in-One WP Migration, Code Snippets, Elementor MCP Composer, Elementor One, Wordfence según decisión) e instalar los que faltan (LearnDash, integración LearnDash–WooCommerce, Stripe, Yoast, Complianz, UpdraftPlus, LiteSpeed Cache, 2FA).
- [ ] **Crear el buzón info@gemmavilaaccion.es en Webempresa** (Francisco) y configurar WP Mail SMTP + SPF/DKIM/DMARC → ver "Próxima sesión".
- [x] **Trasladar el diseño a la academia** (11/11/2026). Falta añadir "Mis cursos" cuando esté LearnDash.
- [ ] **gemmavilaaccion.es: publicar o desenlazar las páginas del menú que están en borrador** (dan 404 a los visitantes).
- [ ] Desarrollar el plugin propio (integración Hermes + campo NIF/DNI).
- [ ] Limpiar gemmavilaaccion.es: borrar Akismet, Code Snippets y Hello Dolly; añadir UpdraftPlus, WP Mail SMTP, LiteSpeed Cache y 2FA.
- [ ] Localizar cómo están hechas las pruebas actuales de la API en gemmavilaaccion.es y revisar si envían el total del pedido como importe.
- [ ] Migrar los dominios y webs a Webempresa (tecnicaestructural.es incluido), aplicando en cada una el paso estándar de las rutas OAuth para Novamira y la base común de plugins.
- [ ] **Sesiones presenciales** en WooCommerce: plazas limitadas y fechas concretas. Los detalles se irán definiendo.
- [ ] **Textos legales**: revisar los existentes tomando como referencia los de tecnica-estructural.com. Incluir condiciones de venta, la casilla de renuncia al desistimiento para contenido digital en el checkout y la cesión de datos a tecnica-estructural.com.
- [ ] **Importación de alumnos** desde el sistema antiguo (más adelante).
- [ ] Crear Search Console y GA4 en las cuatro webs, con medición entre dominios y banner de consentimiento de cookies.
- [ ] Configurar UpdraftPlus con destino remoto en todas las webs y hacer una restauración de prueba.

## SEO conjunto (11/11/2026)
- Documento: "ESTRATEGIA SEO CONJUNTA · GEMMA VILA · NOVIEMBRE 2026" (Google Doc en la carpeta INFORMES MENSUALES de Francisco, id 1sdokwbJCTfARea2ptQCGMGkMZFIgCSJU5ZOqcMGJnks). Pendiente de que Francisco lo apruebe.
- Reparto: gemmavilaaccion.es = marca/sesiones/hub · tecnicaestructural.es = técnica estructural + landings de ediciones · lecturadeaura.es = aura/Máster de Sanadores · academia = noindex salvo la home (hecho, plugin v0.3.0).
- Dominios antiguos vivos en el hosting antiguo: gemmavilapages.com (sin plugin SEO, 14 páginas) y lecturadeaura.es (Yoast, 22 páginas + tienda). NO apagar: primero Search Console, luego 301 página a página.
- Ficha de Gemma en tecnica-estructural.com/facilitadores/gemma-vila/ enlaza a gemmavilapages.com → pedir cambio.
- Fase 1 pendiente: GSC+GA4 en las 6 webs, borrar páginas de prueba (ID 1, 2, Elementor #9), resolver la landing de Mataró duplicada (canónica en tecnicaestructural.es), noindex del área de alumnos 87 de TE, títulos/descripciones Yoast, nombre "Técnica Estructural con Gemma Vila", Google Business Profile en Mataró.

### tecnicaestructural.es revisada (11/11/2026, conector nuevo)
- Landing 137 (Mataró): los botones "Reservar mi plaza" e "Inscribirme" iban a la tienda antigua lecturadeaura.tusclases.online → ahora a la academia (`?add-to-cart=123`); "Quiero el pack completo" (antes WhatsApp) → academia 125. Copia en meta `_gv_backup_elementor_data_seo_20261111`.
- Página 87 (área de alumnos): correo cambiado a info@gemmavilaaccion.es; ya era noindex.
- Papelera: entrada 1 (Webempresa), Sample Page (2), Elementor #9.
- Nombre del sitio "Técnica Estructural", lema y Yoast (nombre web "Técnica Estructural con Gemma Vila", persona = Gemma, título/descr. de la home, keyword "técnica estructural"). Legales en noindex. Sitemap: solo home + landing.
- gemmavilaaccion.es landing 93: canónica → la de tecnicaestructural.es (sin redirigir, los enlaces de RRSS siguen funcionando).
- Pendiente de decidir: la home de tecnicaestructural.es es una home genérica de Gemma (Lectura de Aura, Numerología, Máster, Packs) y su menú también → pisa a gemmavilaaccion.es y a lecturadeaura.es. Además todos sus botones van a WhatsApp, ninguno a la academia. Faltan WP Mail SMTP, LiteSpeed Cache y UpdraftPlus. Plantillas Elementor sin uso (31, 33, 41, 43, 47).

### Limpieza y plugins iguales (11/11/2026, tarde)
- Papelera: web principal → entrada 1, Sample Page, borrador Privacy Policy, Refund draft, páginas WooCommerce 108-111 y la landing 93 de Mataró. Academia → entrada 1. tecnicaestructural.es → borrador 3 y plantillas Elementor 31, 33, 41, 43, 47.
- Landing de Mataró solo en tecnicaestructural.es: `.htaccess` de la web principal (copia `.htaccess.bak-20261111b`) redirige 301 los dos slugs (24-25 octubre y 14-15 noviembre) a https://tecnicaestructural.es/tecnica-estructural-modulo-1-en-mataro-14-y-15-de-noviembre-gemma-vila/.
- Plugins base ahora en las tres: Elementor, Elementor Pro, Yoast, Complianz, Novamira, UpdraftPlus, WP Mail SMTP, Two-Factor (cada usuario lo activa en su perfil).
  - Web principal: borrados Akismet, Hello Dolly y Code Snippets. Siguen: Angie e Image Optimization (por decidir), WooCommerce y Stripe desactivados.
  - Academia: además WooCommerce, Stripe, ACF, Code Snippets (pruebas Hermes) y plugin propio. Yoast con todo en noindex salo la home, sitemap de Yoast apagado (manda el sitemap de WordPress filtrado por el plugin propio). Verificado.
  - tecnicaestructural.es: WP Mail SMTP con la misma configuración que la web principal (remitente \"Técnica Estructural · Gemma Vila\"); correo de prueba enviado.
- **DESCARTADO LiteSpeed Cache:** el servidor de Webempresa es nginx + Apache, no LiteSpeed; el plugin no cachea nada. Desinstalado de las tres. Falta elegir caché (preguntar a Webempresa qué recomiendan en este plan).
- Pendiente: configurar UpdraftPlus con destino remoto (Drive) y Complianz (asistente) en academia y tecnicaestructural.es antes de meter analítica.
- ✅ **Caché: Magic Caché de Webempresa (Varnish)**, activado en WePanel por Francisco + plugin \"wePanel Varnish Cache\" (carpeta `wepanel-varnish-cache-wepanel`, se descarga de WePanel > Herramientas > Magic Caché) en gemmavilaaccion.es, academia y tecnicaestructural.es, con caché dinámica y autolimpieza. Verificado: páginas públicas `x-cache: cached`; pago, Mi cuenta y usuarios conectados `uncached`; compra directa en la academia funciona. lecturadeaura.es: activarlo cuando se monte. Angie desinstalado. Image Optimization: web principal y tecnicaestructural.es (conectar a la cuenta de Elementor).

### Cursos de tecnica-estructural.com en la academia (11/11/2026)
- Fuente: API Hermes `GET /events` (20 eventos de Gemma; 8 futuros y aprobados = los 8 de https://tecnica-estructural.com/formaciones/?srch=gemma). Se excluyen pasados, la feria MAGiC (1378) y el curso de prueba (1052).
- Producto → evento / ticket primera vez / ticket repetición (precio repetición):
  - 123 M1 Mataró 14-15 nov → 1349 / 2084 / 2085 (50 €)
  - 128 M1 online 15 nov-13 dic → 1339 / 2072 / 2073 (80 €)
  - 140 M1 Alicante 27-29 nov → 1300 / 2006 / 2007 (50 €)
  - 127 M2 Mataró 5-6 dic → 1395 / 2165 / 2166 (50 €) (antes \"Módulo 2\" genérico)
  - 141 M2 online 5-6 dic → 1396 / 2167 / 2168 (80 €)
  - 142 M1 Las Palmas 12-13 dic → 1299 / 2004 / 2005 (50 €)
  - 143 M2 online 27 dic + 3 ene → 1397 / 2169 / 2170 (80 €)
  - 144 M1 Móstoles 6-7 feb 2027 → 1422 / 2205 / 2206 (50 €)
  - Packs 125 y 129: sin evento (cada pack son 2 eventos y el M2 no está fijado). Decidir.
- Guardado en ACF `amelia_event_id` / `amelia_ticket_id` + metas `_gv_hermes_ticket_repeticion`, `_gv_precio_repeticion`, `_gv_fecha_inicio`. Precio: 440 € (M1) / 630 € (M2), primera vez. Orden de catálogo por fecha; packs al final.
- Alicante y Las Palmas tienen precio 0 en la API (el ticket dice 440 €): confirmar con Gemma.
- Cuenta obligatoria: compra como invitado desactivada, usuario y contraseña generados por WooCommerce (correo \"Nueva cuenta\" con enlace para poner contraseña).
- Pendiente: ocultar automáticamente los cursos pasados (por `_gv_fecha_inicio`), opción \"soy repetidor\", aviso en el pago de que se crea la cuenta, conexión Hermes al pagar. El conector Novamira de la academia pidió volver a iniciar sesión (se trabajó con WP-CLI desde el conector de la web principal).
- ✅ **Cursos con caducidad (plugin propio v0.4.0, copia `gemmavila-academia.php.bak-0.3.0`):** desde `_gv_fecha_inicio` el curso no es comprable (filtro `woocommerce_is_purchasable`) y la tarea diaria `gv_ocultar_cursos_pasados` (00:05 Madrid) lo pasa a visibilidad \"oculto\" y anota `_gv_ocultado_por_fecha`. No se borra nada. Todo curso nuevo debe llevar `_gv_fecha_inicio` (AAAA-MM-DD).
- Zona horaria de las 3 webs puesta en Europe/Madrid (estaban con desfase fijo +2 / +1).
- Ojo: WP-Cron depende de visitas y con Magic Caché hay menos peticiones a PHP → pedir a Webempresa un cron real del servidor (wp-cron.php cada 15 min) y `DISABLE_WP_CRON`.
- ✅ **Fichas de curso con Elementor (decisión de Francisco, 11/11/2026):** plantilla de Theme Builder "Ficha de producto academia" (ID 145, condición: todos los productos) con foto, título, precio, entradilla y botón Inscribirme + "Contenido del post". Cada curso se edita con Elementor (soporte de Elementor activado para productos): recuadro "Datos de esta edición" (lista de iconos) + pestañas anidadas (Descripción / Qué necesitas / Inscripción y condiciones / Formadora) con editor de texto. **Curso nuevo = duplicar uno existente y cambiar fechas, lugar y `_gv_fecha_inicio`.** Las pestañas por código (plugin 0.5.0) se retiraron (plugin 0.6.0). Las metas `_gv_tipo`, `_gv_sesiones`, `_gv_lugar`… y la opción `gv_fichas_textos` quedan sin uso.
- Pendiente: confirmar con Técnica Estructural que nos parece bien usar la información del método.
- **Repositorio de código:** `gemma-vila` (privado, GitHub de Francisco). Estructura: `academia/plugins/gemmavila-academia/`, `docs/`, `README.md`, `CHANGELOG.md`. Regla: nada se toca en el servidor sin estar antes en el repositorio. Preparado en local (commit inicial verificado byte a byte con el servidor); falta que Francisco cree el repositorio vacío para subirlo.

### Academia 0.7.0 (11/11/2026) · en GitHub `franciscocholbi-commits/gemma-vila` (commit a7cab0f)
- **Repositorio activo**: app de Claude instalada en el repo; push funcionando. Despliegue = archivo a archivo con copia `.bak-<versión>` y comprobación md5 contra el repo (0.7.0 verificado).
- **API Key Hermes en `wp-config.php` de la academia** (constante `GV_HERMES_API_KEY`, copia previa `wp-config.php.bak-20261111`). Sigue también en los snippets 5-8 de Code Snippets (pruebas de Francisco): pendiente quitarla de ahí.
- **Plazas sincronizadas** (`includes/plazas.php`): cada hora + comprobación al pagar. Cifra más baja entre plazas de la entrada y capacidad del evento (si > 1) menos ventas nuestras sin matrícula enviada. Primera sincronización: Mataró 9, online 56, Alicante 22, M2 Mataró 16, M2 online dic 30, Las Palmas 28, M2 online ene 30, **Móstoles 1** (su entrada está configurada con 1 plaza en Técnica Estructural: preguntar a David junto con lo de 12 vs 30 en Mataró). El pedido de prueba 137 descuenta 1 del online.
- **Formaciones** (`includes/formaciones.php`): menú \"Formaciones\" en el escritorio; URLs `/formacion/<slug>/`; `/producto/...` → 301.
- ✅ **Academia 0.8.0 (commit 6870eee):** plazas a la vista (`includes/plazas-visibles.php`). Público: solo con 10 o menos (\"Quedan N plazas\", \"¡Últimas N plazas!\" con 3 o menos, \"Completo\"), en catálogo y ficha. Escritorio: columna \"Plazas\" en Formaciones con hora de sincronización + recuadro \"Plazas en Técnica Estructural\" con botón \"Sincronizar ahora\". Umbral en la constante `GV_UMBRAL_PLAZAS`.
- ✅ **Academia 0.9.0 (commit 02dd6d2):** campo obligatorio \"NIF / DNI / NIE\" en el pago y en Mi cuenta (`includes/nif.php`), validación de DNI/NIE/CIF si el país es España, guardado en `_billing_nif` (pedido) y `billing_nif` (cuenta), visible en la dirección de facturación (escritorio, correos, Mi cuenta). Motivo: los cursos superan 400 € → factura completa con NIF del alumno.
- **Facturación (pendiente de decisión con Gemma):** recomendación = programa externo de facturación conectado a WooCommerce (todas las facturas de Gemma en un sitio y una numeración, preparado para Verifactu, acceso de la gestoría). Preguntas abiertas: gestoría y programa que usa, cómo factura hoy lo que no pasa por la web, confirmar IVA 21 % (no exención de enseñanza). Verifactu: legal 1/7/2027 para autónomos; Hacienda anunció (5/10/2026) aplazar a octubre de 2028, pendiente de aprobarse.
- **LearnDash 5.3.0** instalado por Francisco (licencia OK, sin cursos). Ajustes (11/11/2026, copia previa en opciones `gv_backup_ld30_20261111` y `gv_backup_ldcpt_20261111`): colores de marca (principal #34508A azul, secundario #D9A400 dorado, terciario #E58AC6 rosa), logo en el login (ID 90), modo concentración activo, vídeos adaptables, cursos fuera del buscador y sin listado público (/courses/ → 404). Se cambian con `LearnDash_Settings_Section::set_section_setting` (update_option directo lo rechaza su validación).
- **La integración WooCommerce ya viene dentro de LearnDash 5.3** (`sfwd-lms/includes/woocommerce`, opción `learndash_woocommerce_version` = 5.3.0) y matricula con pedidos en Procesando y Completado. El complemento aparte `learndash-woocommerce` 2.0.3 que se instaló **choca** con la versión incluida (función redeclarada): WordPress impidió activarlo, la web no se vio afectada. Borrado por Francisco (11/11/2026). Plan online: 1 curso LearnDash por edición (duplicar), conectado a su formación, lecciones abiertas por fecha, acceso 3 meses, \"Mis cursos\" en el menú del alumno. Decidir alojamiento de vídeos (Vimeo con restricción de dominio recomendado).
- **LearnDash LMS - Elementor 1.0.12** activado (sin duplicar nada de LearnDash 5.3; comprobado). Elementor habilitado para `sfwd-courses`, `sfwd-lessons`, `sfwd-topic` (además de post, page, product). Bloques disponibles: ld-course-content, ld-course-infobar, ld-course-certificate, ld-login, ld-profile, ld-course-list, ld-lesson-list, ld-topic-list, ld-quiz-list.
- **Curso de ejemplo LearnDash (11/11/2026):** curso 166 \"Módulo 1 online · del 15 de noviembre al 13 de diciembre de 2026\" (/courses/modulo-1-online-15-noviembre-2026/), acceso cerrado (solo comprando; botón a la formación 128), progresión libre, lecciones 167-171 (Semana 1-5) abiertas por fecha cada domingo a las 00:00 con texto de relleno (vídeo, material, enlace al directo). Formación 128 conectada (`_related_course` = 166). Página \"Mis cursos\" (172, `[ld_profile]`) enlazada en el menú del alumno (elemento 109). Sin caducidad de acceso todavía (decidir: 3 meses desde el inicio o desde el final). El pedido de prueba 137 fue como invitado (antes de la cuenta obligatoria): no se puede matricular → hacer otra compra de prueba.

### Entorno de pruebas (11/11/2026)
- **https://pruebas-academia.gemmavilaaccion.es** (carpeta `public_html/pruebas-academia.gemmavilaaccion.es`, subdominio creado por Francisco). Copia de la academia a las 18:31: archivos (sin cachés ni .bak) + 97 tablas copiadas en la MISMA base de datos con prefijo `pa_` (roles y metas de usuario renombrados). search-replace academia → pruebas-academia solo en tablas `pa_` (314 cambios).
- wp-config de la copia: `table_prefix pa_`, `WP_ENVIRONMENT_TYPE staging`, `GV_ENTORNO_PRUEBAS`, `GV_PRUEBAS_TOKEN`, `WP_HOME/WP_SITEURL`, `DISABLE_WP_CRON`; SIN `GV_HERMES_API_KEY`.
- mu-plugin `gv-entorno-pruebas.php` (en GitHub): acceso solo con sesión o enlace `?gv_pruebas=<token>`; correos → Francisco con [PRUEBAS]; noindex; Stripe forzado a pruebas; franja rosa. Magic Caché desactivado en la copia.
- Estado de la copia = producción a las 18:31: sin NIF (plugin 0.9.2), compra como invitado, LearnDash desactivado. Ahí se reactivan y prueban NIF, cuenta obligatoria y LearnDash.
- Pendiente: conector Novamira para la copia (el de la academia no sirve); de momento se administra con WP-CLI `--path=` desde el conector de la web principal.
- **Pago 403:** el cortafuegos de Webempresa bloqueó la IP de Francisco tras varios intentos (desde el móvil el pago funciona: pedido 173). Escribir a Webempresa para saber la regla y evitar que bloquee a clientes.
