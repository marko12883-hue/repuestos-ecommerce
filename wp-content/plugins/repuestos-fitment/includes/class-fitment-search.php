<?php
/**
 * Shortcode [buscador_vehiculo]: Año → Marca → Modelo con datos de vPIC.
 * Filtra el catálogo por compatibilidad (?vehiculo=marca|modelo|año).
 * Incluye búsqueda por VIN y "mi vehículo" persistente (localStorage).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Repuestos_Fitment_Search {

    public static function init() {
        add_shortcode('buscador_vehiculo', array(__CLASS__, 'shortcode'));
        add_action('wp_ajax_repuestos_ymm_models', array(__CLASS__, 'ajax_models'));
        add_action('wp_ajax_nopriv_repuestos_ymm_models', array(__CLASS__, 'ajax_models'));
        add_action('wp_ajax_repuestos_ymm_decode_vin', array(__CLASS__, 'ajax_decode_vin'));
        add_action('wp_ajax_nopriv_repuestos_ymm_decode_vin', array(__CLASS__, 'ajax_decode_vin'));
        add_action('pre_get_posts', array(__CLASS__, 'filter_query'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_shop'));
    }

    /** Token canónico: "marca|modelo|año" en minúsculas. */
    public static function token($make, $model, $year) {
        return strtolower(trim($make) . '|' . trim($model) . '|' . absint($year));
    }

    public static function shortcode() {
        self::enqueue();
        $years = Repuestos_VPIC_Client::get_years();
        $makes = Repuestos_VPIC_Client::get_makes();
        $options_makes = array();
        foreach ($makes as $m) {
            $options_makes[] = array(
                'value' => $m,
                'label' => Repuestos_VPIC_Client::display_name($m),
            );
        }

        ob_start(); ?>
        <div class="repuestos-ymm" id="repuestos-ymm">
            <h3>Encuentra repuestos para tu auto</h3>
            <form id="repuestos-ymm-form" autocomplete="off">
                <select id="ymm-year" required>
                    <option value="">Año</option>
                    <?php foreach ($years as $y) : ?>
                        <option value="<?php echo esc_attr($y); ?>"><?php echo esc_html($y); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="ymm-make" required>
                    <option value="">Marca</option>
                    <?php foreach ($options_makes as $o) : ?>
                        <option value="<?php echo esc_attr($o['value']); ?>"><?php echo esc_html($o['label']); ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="ymm-model" required disabled>
                    <option value="">Modelo</option>
                </select>
                <button type="submit">Buscar repuestos</button>
            </form>
            <div class="repuestos-ymm-vin">
                <span>o busca por VIN</span>
                <input type="text" id="ymm-vin" maxlength="17" placeholder="17 caracteres" autocomplete="off">
                <button type="button" id="ymm-vin-btn">Decodificar</button>
            </div>
            <p class="repuestos-ymm-error" id="ymm-error" hidden></p>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function enqueue() {
        wp_enqueue_script(
            'repuestos-ymm',
            REPUESTOS_FITMENT_URL . 'assets/js/fitment-search.js',
            array(),
            REPUESTOS_FITMENT_VERSION,
            true
        );
        wp_localize_script('repuestos-ymm', 'RepuestosYMM', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('repuestos_fitment'),
            'shopUrl' => function_exists('wc_get_page_permalink')
                ? wc_get_page_permalink('shop')
                : home_url('/tienda/'),
        ));
        wp_enqueue_style(
            'repuestos-ymm',
            REPUESTOS_FITMENT_URL . 'assets/css/fitment.css',
            array(),
            REPUESTOS_FITMENT_VERSION
        );
    }

    /** En la tienda y categorías también se necesita el JS (chip "mi vehículo"). */
    public static function enqueue_shop() {
        if (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) {
            self::enqueue();
        }
    }

    public static function ajax_models() {
        check_ajax_referer('repuestos_fitment', 'nonce');
        $make = isset($_GET['make']) ? sanitize_text_field($_GET['make']) : '';
        $year = isset($_GET['year']) ? absint($_GET['year']) : 0;
        $models = Repuestos_VPIC_Client::get_models($make, $year);
        $out = array();
        foreach ($models as $m) {
            $out[] = array('value' => $m, 'label' => Repuestos_VPIC_Client::display_name($m));
        }
        wp_send_json_success($out);
    }

    public static function ajax_decode_vin() {
        check_ajax_referer('repuestos_fitment', 'nonce');
        $vin = isset($_GET['vin']) ? sanitize_text_field($_GET['vin']) : '';
        $decoded = Repuestos_VPIC_Client::decode_vin($vin);
        if (is_wp_error($decoded)) {
            wp_send_json_error(array('message' => $decoded->get_error_message()));
        }
        wp_send_json_success($decoded);
    }

    /**
     * ?vehiculo=marca|modelo|año → filtra productos compatibles + universales.
     */
    public static function filter_query($query) {
        if (is_admin() || !$query->is_main_query()) {
            return;
        }
        if (empty($_GET['vehiculo'])) {
            return;
        }
        $parts = explode('|', sanitize_text_field($_GET['vehiculo']));
        if (count($parts) !== 3) {
            return;
        }
        list($make, $model, $year) = $parts;
        if ($make === '' || $model === '' || absint($year) < 1950) {
            return;
        }
        $token = self::token($make, $model, $year);

        $is_catalog = function_exists('is_shop') &&
            ($query->is_post_type_archive('product') || $query->is_tax('product_cat') || $query->is_search());
        if (!$is_catalog) {
            return;
        }

        $meta_query = (array) $query->get('meta_query');
        $meta_query[] = array(
            'relation' => 'OR',
            array('key' => '_repuestos_fit', 'value' => $token, 'compare' => '='),
            array('key' => '_repuestos_fit', 'compare' => 'NOT EXISTS'),
        );
        $query->set('meta_query', $meta_query);
    }
}
