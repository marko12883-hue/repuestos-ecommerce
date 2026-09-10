<?php
/**
 * Buscador por vehículo – shortcode [buscador_vehiculo]
 *
 * Muestra 3 desplegables (marca, modelo, año) y redirige a la tienda
 * buscando "Marca Modelo Año". Para una prueba rápida es suficiente;
 * más adelante se puede conectar con atributos de producto reales.
 *
 * Si el plugin "Repuestos Fitment" está activo, él registra la versión
 * completa (con datos de la API vPIC) y esta versión básica no se usa.
 */

if (!defined('ABSPATH')) exit;

function repuestos_datos_vehiculos() {
    return array(
        'Toyota'     => array('Corolla', 'Camry', 'RAV4', 'Tacoma', 'Yaris'),
        'Honda'      => array('Civic', 'Accord', 'CR-V', 'Pilot', 'Fit'),
        'Nissan'     => array('Sentra', 'Altima', 'Rogue', 'Frontier', 'Versa'),
        'Ford'       => array('F-150', 'Escape', 'Explorer', 'Mustang', 'Ranger'),
        'Chevrolet'  => array('Silverado', 'Equinox', 'Malibu', 'Tahoe', 'Spark'),
        'Volkswagen' => array('Jetta', 'Tiguan', 'Golf', 'Passat', 'Taos'),
        'Hyundai'    => array('Elantra', 'Sonata', 'Tucson', 'Santa Fe', 'Accent'),
        'Kia'        => array('Forte', 'Optima', 'Sportage', 'Sorento', 'Rio'),
        'Mazda'      => array('Mazda3', 'Mazda6', 'CX-5', 'CX-30'),
        'Jeep'       => array('Wrangler', 'Cherokee', 'Grand Cherokee', 'Compass'),
    );
}

function repuestos_buscador_vehiculo_shortcode() {
    $vehiculos = repuestos_datos_vehiculos();
    $anos = range((int) date('Y') + 1, 2000);
    $tienda = function_exists('repuestos_url_tienda') ? repuestos_url_tienda() : home_url('/');

    ob_start(); ?>
    <div class="buscador-vehiculo">
        <h3><?php esc_html_e('Encuentra repuestos para tu auto', 'repuestos'); ?></h3>
        <form id="form-vehiculo" action="<?php echo esc_url($tienda); ?>" method="get">
            <select id="fv-marca" required>
                <option value=""><?php esc_html_e('Marca', 'repuestos'); ?></option>
                <?php foreach (array_keys($vehiculos) as $marca) : ?>
                    <option value="<?php echo esc_attr($marca); ?>"><?php echo esc_html($marca); ?></option>
                <?php endforeach; ?>
            </select>
            <select id="fv-modelo" required disabled>
                <option value=""><?php esc_html_e('Modelo', 'repuestos'); ?></option>
            </select>
            <select id="fv-ano" required>
                <option value=""><?php esc_html_e('Año', 'repuestos'); ?></option>
                <?php foreach ($anos as $ano) : ?>
                    <option value="<?php echo esc_attr($ano); ?>"><?php echo esc_html($ano); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="s" id="fv-q">
            <input type="hidden" name="post_type" value="product">
            <button type="submit"><?php esc_html_e('Buscar', 'repuestos'); ?></button>
        </form>
    </div>
    <script>
    (function () {
        var datos = <?php echo json_encode($vehiculos); ?>;
        var marca = document.getElementById('fv-marca');
        var modelo = document.getElementById('fv-modelo');
        marca.addEventListener('change', function () {
            modelo.innerHTML = '<option value=""><?php esc_html_e('Modelo', 'repuestos'); ?></option>';
            (datos[this.value] || []).forEach(function (m) {
                var o = document.createElement('option');
                o.value = m; o.textContent = m;
                modelo.appendChild(o);
            });
            modelo.disabled = false;
        });
        document.getElementById('form-vehiculo').addEventListener('submit', function () {
            document.getElementById('fv-q').value =
                marca.value + ' ' + modelo.value + ' ' + document.getElementById('fv-ano').value;
        });
    })();
    </script>
    <?php
    return ob_get_clean();
}
if (!defined('REPUESTOS_FITMENT_VERSION')) {
    add_shortcode('buscador_vehiculo', 'repuestos_buscador_vehiculo_shortcode');
}
