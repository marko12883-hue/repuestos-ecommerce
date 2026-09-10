<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="barra-top"><?php esc_html_e('Envío gratis en pedidos mayores a $99 · Garantía en todos los repuestos', 'repuestos'); ?></div>

<header class="cabecera">
    <div class="contenedor">
        <div class="logo">
            <a href="<?php echo esc_url(home_url('/')); ?>">
                <?php
                if (has_custom_logo()) {
                    the_custom_logo();
                } else {
                    echo esc_html(get_bloginfo('name')) . ' <span>·</span>';
                }
                ?>
            </a>
        </div>
        <nav class="menu-principal">
            <?php
            wp_nav_menu(array(
                'theme_location' => 'principal',
                'fallback_cb'    => 'repuestos_menu_respaldo',
            ));
            ?>
        </nav>
    </div>
</header>

<?php
// Menú de respaldo si el usuario aún no creó uno
function repuestos_menu_respaldo() {
    $tienda = function_exists('repuestos_url_tienda') ? repuestos_url_tienda() : home_url('/');
    echo '<ul>';
    echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('Inicio', 'repuestos') . '</a></li>';
    echo '<li><a href="' . esc_url($tienda) . '">' . esc_html__('Tienda', 'repuestos') . '</a></li>';
    echo '<li><a href="' . esc_url(wc_get_cart_url()) . '">' . esc_html__('Carrito', 'repuestos') . '</a></li>';
    echo '</ul>';
}
?>
