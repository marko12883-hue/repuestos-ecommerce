# Repuestos – E-commerce de repuestos de autos

Tienda en línea construida con **WordPress + WooCommerce** y un tema personalizado.
El repo guarda el código del tema y la configuración; WordPress corre en tu hosting.

## Estructura

```
├── wp-content/themes/repuestos/   # Tema personalizado (lo que ve el cliente)
│   ├── style.css                  # Estilos + cabecera del tema
│   ├── functions.php              # Soporte WooCommerce, menús, widgets
│   ├── front-page.php             # Portada: hero + buscador por vehículo
│   ├── header.php / footer.php
│   ├── index.php
│   └── inc/fitment-search.php     # Shortcode [buscador_vehiculo]
├── datos/productos-ejemplo.csv    # 8 productos de ejemplo para importar
├── docker-compose.yml             # WordPress local para probar
└── .github/workflows/deploy.yml   # Despliegue automático al hosting
```

## Puesta en marcha (la ruta rápida)

### 1. Hosting + WordPress
Contrata un hosting con WordPress (ej. SiteGround, Bluehost, Hostinger o cualquiera
con instalador de WordPress en 1 clic) e instala WordPress.

### 2. Instala WooCommerce
En el escritorio de WordPress: **Plugins → Añadir nuevo → busca "WooCommerce" → Instalar y activar**.
Sigue su asistente: moneda (USD), ubicación, impuestos y envíos.

### 3. Sube el tema
Comprime la carpeta `wp-content/themes/repuestos/` en un `.zip` y súbelo en
**Apariencia → Temas → Añadir nuevo → Subir tema**. Actívalo.

### 4. Carga productos de ejemplo
**Productos → Importar** y sube `datos/productos-ejemplo.csv`. Así ves la tienda
funcionando en minutos y luego reemplazas con tu catálogo real.

### 5. Configura pagos
**WooCommerce → Ajustes → Pagos**: activa Stripe y/o PayPal (ambos tienen plugins oficiales).

### 6. Crea la portada
Crea una página, asígnale la plantilla "Portada" (o ponla como página de inicio en
**Ajustes → Lectura**). El shortcode `[buscador_vehiculo]` muestra el buscador por
marca, modelo y año.

## Desarrollo local (opcional)

```bash
docker compose up -d
# Abre http://localhost:8080 y completa la instalación de WordPress
```

El tema ya está montado como volumen, así que los cambios se ven al instante.

## Despliegue automático

`.github/workflows/deploy.yml` sube el tema al hosting con cada `push` a `main`.
Configura estos secretos en el repo (**Settings → Secrets**): `SSH_HOST`, `SSH_USER`,
`SSH_KEY` y `DEPLOY_PATH`.
