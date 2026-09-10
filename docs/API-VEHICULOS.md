# API de datos de vehículos

Para el buscador Año → Marca → Modelo necesitamos una fuente de datos con todas las
marcas y modelos de autos. Estas son las opciones evaluadas (septiembre 2026).

## Recomendada: NHTSA vPIC (la que usa el plugin)

**API oficial del gobierno de EE.UU.** (National Highway Traffic Safety Administration).

- **Gratis, sin registro y sin API key** — no requiere autenticación.
- Base: `https://vpic.nhtsa.dot.gov/api/vehicles/`
- Datos del mercado estadounidense desde 1995 aprox.

### Endpoints que usamos

| Dato | Endpoint |
|---|---|
| Marcas de autos | `GET GetMakesForVehicleType/car?format=json` → 195 marcas (`Make_Name`) |
| Modelos de una marca en un año | `GET GetModelsForMakeYear/make/{marca}/modelyear/{año}?format=json` → `Model_Name` |
| Decodificar VIN | `GET DecodeVinValues/{vin}?format=json` → `Make`, `Model`, `ModelYear` |

Ejemplo real: `GetModelsForMakeYear/make/honda/modelyear/2020?format=json`
devuelve los modelos Honda del 2020.

### Por qué el orden es Año → Marca → Modelo

vPIC no tiene un endpoint de "años disponibles": los años se generan como rango
(1995 → año actual + 1). Y los modelos dependen de **marca + año juntos**, así que
el flujo natural es: eliges año y marca → la API devuelve los modelos exactos
de esa combinación.

### Caché

Todas las respuestas se guardan en transients de WordPress por **1 semana**
(clave `repuestos_vpic_*`). Si la API falla, no se cachea el error más de 1 hora.

## Alternativas evaluadas

| API | Costo | Notas |
|---|---|---|
| **NHTSA vPIC** | Gratis, sin key | La elegida. Solo mercado EE.UU. |
| **411 AutoAPI** | Gratis 3.000 req/mes (vía RapidAPI); planes $19–$149 | Reemplazo de CarQuery con trims y specs extra. Requiere API key. |
| **VinAudit Specs API** | Pago | Datos YMMT completos (año/marca/modelo/trim). |
| **CarQuery** | — | **Muerta desde 2019, no usar.** |
| **TecDoc** | Licencia comercial | El estándar profesional de la industria de repuestos. A futuro, si el negocio escala. |

## Cómo se conecta la compatibilidad con los productos

La API solo da los **vehículos**; la **compatibilidad repuesto ↔ vehículo** la defines tú
en el metabox del producto (nadie vende esa relación gratis y precisa a nivel
repuesto genérico; TecDoc la vende como licencia).

Flujo de datos:

```
vPIC API → desplegables del buscador →?vehiculo=marca|modelo|año
↓
Producto (metabox) → índice _repuestos_fit → meta query exacta
```

## Notas

- Los nombres de la API vienen en mayúsculas (`HONDA`); el plugin los normaliza
a minúsculas para el índice y los muestra con formato legible.
- La lista de marcas está curada a ~40 populares (filtro `repuestos_fitment_makes`)
porque la API devuelve 195 incluyendo fabricantes raros.
- Si el negocio se enfoca en Latinoamérica, los modelos más vendidos coinciden en
su mayoría con el catálogo EE.UU.; a futuro se puede complementar con una lista
propia vía el filtro de marcas.
