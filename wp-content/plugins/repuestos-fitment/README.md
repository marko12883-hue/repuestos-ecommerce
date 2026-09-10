# Repuestos Fitment

Plugin de WordPress/WooCommerce: buscador de repuestos por vehículo (**año → marca → modelo**)
con datos reales de la API gratuita de NHTSA vPIC, más metabox para asignar compatibilidad
a cada producto.

## Qué incluye

- **Shortcode `[buscador_vehiculo]`** — formulario Año → Marca → Modelo con datos en vivo
  de la API. Al buscar, redirige a la tienda filtrando solo productos compatibles.
- **Búsqueda por VIN** — decodifica un VIN de 17 caracteres y preselecciona el vehículo.
- **"Mi vehículo"** — el vehículo elegido se guarda en el navegador y se muestra como
  chip en el catálogo; el filtro se mantiene al navegar.
- **Metabox en productos** — "Compatibilidad de vehículos": filas de
  marca + modelo (cascada desde la API) + rango de años.
- **Sin API key** — vPIC no requiere registro ni autenticación. Respuestas cacheadas
  1 semana con transients.

## Cómo funciona el filtrado

Al guardar un producto, cada fila de compatibilidad se expande a un índice de
metas `_repuestos_fit` con un valor por año: `marca|modelo|año` en minúsculas
(ej. `honda|civic|2020`).

La búsqueda (`?vehiculo=honda|civic|2020`) aplica una meta query:

- productos con ese token exacto, **o**
- productos sin compatibilidad asignada (se tratan como **universales**).

## Instalación

1. Copia la carpeta `repuestos-fitment` a `wp-content/plugins/`.
2. Activa **Repuestos Fitment** en Plugins (requiere WooCommerce).
3. Usa el shortcode `[buscador_vehiculo]` en la portada o donde quieras.
4. En cada producto, asigna compatibilidad en el metabox "Compatibilidad de vehículos".

## Personalización

```php
// Cambiar la lista de marcas (por defecto: 40 populares)
add_filter('repuestos_fitment_makes', fn() => ['TOYOTA', 'HONDA', 'NISSAN']);

// Cambiar el año mínimo del desplegable (por defecto: 1995)
add_filter('repuestos_fitment_year_from', fn() => 2000);
```

Más detalles de la API y alternativas en [`docs/API-VEHICULOS.md`](../../docs/API-VEHICULOS.md).
