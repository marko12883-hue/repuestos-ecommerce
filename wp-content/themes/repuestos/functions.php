<?php
/**
 * Repuestos – functions.php
 * Tema para tienda de repuestos de autos con WooCommerce.
 */

if (!defined('ABSPATH')) exit;

require_once get_template_directory() . '/inc/fitment-search.php';

/* ---------- Configuración del tema ---------- */
function repuestos_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');

    register_nav_menus(array(
        'principal' => __('Menú principal', 'repuestos'),
        'pie'       => __('Menú pie de página', 'repuestos'),
    ));
}
add_action('after_setup_theme', 'repuestos_setup');

/* ---------- Estilos ---------- */
function repuestos_assets() {
    wp_enqueue_style('repuestos-style', get_stylesheet_uri(), array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'repuestos_assets');

/* ---------- Widgets ---------- */
function repuestos_widgets() {
    register_sidebar(array(
        'name'          => __('Barra lateral de la tienda', 'repuestos'),
        'id'            => 'sidebar-tienda',
        'before_widget' => '<div class="widget">',
        'after_widget'  => '</div>',
    ));
}
add_action('widgets_init', 'repuestos_widgets');

/* ---------- Ajustes WooCommerce ---------- */

// Productos por página en el catálogo
add_filter('loop_shop_per_page', function () { return 12; });

// Texto del botón "Añadir al carrito" en español neutro
add_filter('woocommerce_product_add_to_cart_text', function () {
    return __('Agregar al carrito', 'repuestos');
});

// Quita el campo de "notas del pedido" para un checkout más rápido
add_filter('woocommerce_enable_order_notes_field', '__return_false');

/* ---------- Utilidades ---------- */

// URL de la tienda (página asignada a WooCommerce)
function repuestos_url_tienda() {
    $id = wc_get_page_id('shop');
    return $id > 0 ? get_permalink($id) : home_url('/tienda/');
}
