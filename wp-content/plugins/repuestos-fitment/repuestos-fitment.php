<?php
/**
 * Plugin Name: Repuestos Fitment
 * Description: Buscador de repuestos por vehículo (año, marca, modelo) con datos de la API gratuita de NHTSA vPIC. Incluye metabox para asignar compatibilidad a productos WooCommerce.
 * Version: 1.0.0
 * Author: Repuestos
 * Requires Plugins: woocommerce
 * Text Domain: repuestos-fitment
 */

if (!defined('ABSPATH')) {
    exit;
}

define('REPUESTOS_FITMENT_VERSION', '1.0.0');
define('REPUESTOS_FITMENT_DIR', plugin_dir_path(__FILE__));
define('REPUESTOS_FITMENT_URL', plugin_dir_url(__FILE__));

require_once REPUESTOS_FITMENT_DIR . 'includes/class-vpic-client.php';
require_once REPUESTOS_FITMENT_DIR . 'includes/class-fitment-admin.php';
require_once REPUESTOS_FITMENT_DIR . 'includes/class-fitment-search.php';

add_action('plugins_loaded', function () {
    Repuestos_VPIC_Client::init();
    Repuestos_Fitment_Admin::init();
    Repuestos_Fitment_Search::init();
});
