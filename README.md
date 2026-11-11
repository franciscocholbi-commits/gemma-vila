# Gemma Vila · código y documentación técnica

Repositorio privado del proyecto técnico de Gemma Vila (Gemma Vila Acción). Aquí vive todo el código propio que corre en sus webs y la documentación para que cualquiera pueda entenderlo y mantenerlo.

Responsable técnico: Francisco Cholbi.

## Webs

| Web | Papel |
|---|---|
| gemmavilaaccion.es | Marca de Gemma, captación |
| tecnicaestructural.es | Captación Técnica Estructural |
| lecturadeaura.es | Captación Lectura del Aura (en construcción) |
| academia.gemmavilaaccion.es | Única tienda + academia (WooCommerce) |

Todas en Webempresa (misma cuenta de hosting), WordPress + Elementor Pro.

## Qué hay aquí

```
academia/plugins/gemmavila-academia/   Plugin propio de la academia (includes/ = una función por archivo)
academia/entorno-pruebas/              mu-plugin que aísla la copia de pruebas
docs/mapa-tecnico.md                   Mapa técnico, decisiones y pendientes
CHANGELOG.md                           Qué ha cambiado en cada versión
```

## Reglas de trabajo

1. **Lo que hay en la web es lo que hay aquí.** No se toca código en el servidor sin que el cambio esté antes en este repositorio.
2. **Cada cambio, con su explicación** en el mensaje del commit y en `CHANGELOG.md`.
3. **Nunca se suben contraseñas ni claves** (la API Key de Técnica Estructural va en `wp-config.php` del servidor, no aquí).
4. Antes de desplegar se guarda una copia del archivo en el servidor (`.bak-<versión>`).

## Plugin `gemmavila-academia`

Se instala en `wp-content/plugins/gemmavila-academia/` de la academia. Hace, en lenguaje normal:

- **Menús por tipo de usuario.** La cabecera siempre usa el menú `academia-publico`; si entra un alumno se cambia por `academia-alumno`, y si entra alguien del equipo (permiso de gestionar la tienda) por `academia-gestion`. El elemento de menú con la clase `gv-logout` cierra sesión de verdad.
- **Mi cuenta** sin la sección de Descargas.
- **Compra directa.** No hay carrito a la vista: al pulsar "Inscribirme" se vacía el carrito, se añade ese curso y se va directo al pago. La página del carrito redirige al pago (o a la home si está vacío).
- **Enlaces antiguos.** Los enlaces `?add-to-cart=<ID>` de la web principal se traducen al producto equivalente de la academia (mapa en la opción `gv_mapa_productos_web_principal`).
- **SEO.** Solo la home de la academia se indexa en Google; el mapa del sitio solo incluye la home.
- **Formaciones, no productos.** En el escritorio el menú se llama "Formaciones" y las fichas están en `/formacion/<curso>/`; las direcciones antiguas `/producto/...` redirigen solas. (`includes/formaciones.php`)
- **Plazas sincronizadas con Técnica Estructural.** Cada hora se consultan las plazas libres de cada formación en su API y se ponen como existencias; justo antes de cobrar se vuelve a comprobar. Mientras no se aclare qué dato manda, se usa la cifra más baja entre las plazas de la entrada y la capacidad del evento. Si se agota: "Completo"; si quedan 5 o menos: "¡Últimas N plazas!". Si la API no responde, no se bloquea la venta. Necesita la constante `GV_HERMES_API_KEY` en `wp-config.php`. (`includes/plazas.php`)
- **Plazas a la vista.** El público solo ve el número cuando quedan 10 o menos (genera urgencia sin delatar cursos vacíos). El equipo ve en el listado de Formaciones la columna "Plazas" con la hora de la última sincronización, y en cada formación un botón "Sincronizar ahora". (`includes/plazas-visibles.php`)
- **NIF / DNI / NIE en el pago.** Obligatorio para poder emitir factura completa. Se valida la letra si el país es España y se guarda en el pedido (`_billing_nif`) y en la cuenta del alumno. (`includes/nif.php`)
- **Cursos con fecha.** Cada curso guarda su fecha de inicio en el campo `_gv_fecha_inicio` (AAAA-MM-DD). Desde ese día no se puede comprar, y cada noche a las 00:05 (hora de Madrid) los cursos empezados se ocultan del catálogo. No se borra nada.

Lo que **no** está en el plugin (se gestiona desde WordPress/Elementor):

- Fichas de curso: plantilla Elementor "Ficha de producto academia" + contenido Elementor de cada producto (datos de la edición y pestañas). Para un curso nuevo se duplica uno existente y se cambian fechas y lugar.
- Ajustes de WooCommerce, correos, Stripe y textos legales.

## Entorno de pruebas

`https://pruebas-academia.gemmavilaaccion.es` es una copia de la academia para probar antes de tocar producción.

- **Misma base de datos, tablas propias** (prefijo `pa_` en lugar de `wp_`): no comparte pedidos, usuarios ni ajustes con producción.
- `wp-config.php` de la copia define `GV_ENTORNO_PRUEBAS`, `WP_ENVIRONMENT_TYPE=staging`, `DISABLE_WP_CRON` y **no** tiene la clave de Hermes (nunca matricula a nadie en Técnica Estructural).
- El mu-plugin `academia/entorno-pruebas/gv-entorno-pruebas.php` (solo actúa si existe `GV_ENTORNO_PRUEBAS`): acceso solo con sesión iniciada o enlace con clave (`GV_PRUEBAS_TOKEN`, solo en el wp-config de la copia), todos los correos desviados a Francisco con "[PRUEBAS]" en el asunto, noindex, Stripe forzado a modo pruebas y franja rosa "ENTORNO DE PRUEBAS".
- Sin Magic Caché.

**Flujo:** cambio en GitHub → se despliega en pruebas → se prueba → se despliega en producción (con copia `.bak` y comprobación md5).
