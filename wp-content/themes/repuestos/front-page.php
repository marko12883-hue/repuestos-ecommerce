<?php
/**
 * Portada de la tienda.
 * Asigna esta plantilla a tu página de inicio en Ajustes → Lectura.
 *
 * Template Name: Portada
 */

get_header();
$tienda = repuestos_url_tienda();
?>

<section class="hero">
    <div class="contenedor">
        <h1><?php esc_html_e('Repuestos para tu auto,', 'repuestos'); ?> <span><?php esc_html_e('sin vueltas.', 'repuestos'); ?></span></h1>
        <p><?php esc_html_e('Frenos, suspensión, filtros y más. Originales y alternativos con garantía, directo a tu puerta.', 'repuestos'); ?></p>
        <?php echo do_shortcode('[buscador_vehiculo]'); ?>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <h2><?php esc_html_e('Compra por categoría', 'repuestos'); ?></h2>
        <p class="sub"><?php esc_html_e('Lo que más se busca, organizado para que lo encuentres rápido.', 'repuestos'); ?></p>
        <div class="tarjetas">
            <?php
            $categorias = array(
                array('Frenos', 'frenos', '🛑', __('Pastillas, discos, tambores y kits.', 'repuestos')),
                array('Suspensión', 'suspension', '🔧', __('Amortiguadores, resortes y bujes.', 'repuestos')),
                array('Filtros', 'filtros', '🧰', __('Aceite, aire, gasolina y cabina.', 'repuestos')),
                array('Motor', 'motor', '⚙️', __('Bandas, bombas, empaques y más.', 'repuestos')),
            );
            foreach ($categorias as $cat) :
            ?>
            <a class="tarjeta" href="<?php echo esc_url($tienda . '?product_cat=' . $cat[1]); ?>">
                <div class="icono"><?php echo $cat[2]; ?></div>
                <h3><?php echo esc_html($cat[0]); ?></h3>
                <p><?php echo esc_html($cat[3]); ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="seccion" style="background: var(--color-gris);">
    <div class="contenedor">
        <h2><?php esc_html_e('Destacados', 'repuestos'); ?></h2>
        <p class="sub"><?php esc_html_e('Los repuestos más vendidos esta semana.', 'repuestos'); ?></p>
        <?php echo do_shortcode('[products limit="8" columns="4" orderby="popularity"]'); ?>
    </div>
</section>

<section class="seccion">
    <div class="contenedor">
        <div class="beneficios">
            <div class="beneficio">
                <div class="icono">🚚</div>
                <h4><?php esc_html_e('Envío rápido', 'repuestos'); ?></h4>
                <p><?php esc_html_e('Gratis en pedidos mayores a $99.', 'repuestos'); ?></p>
            </div>
            <div class="beneficio">
                <div class="icono">✅</div>
                <h4><?php esc_html_e('Garantía real', 'repuestos'); ?></h4>
                <p><?php esc_html_e('Todos los repuestos están garantizados.', 'repuestos'); ?></p>
            </div>
            <div class="beneficio">
                <div class="icono">💬</div>
                <h4><?php esc_html_e('Asesoría experta', 'repuestos'); ?></h4>
                <p><?php esc_html_e('Te ayudamos a elegir el repuesto correcto.', 'repuestos'); ?></p>
            </div>
            <div class="beneficio">
                <div class="icono">🔒</div>
                <h4><?php esc_html_e('Pago seguro', 'repuestos'); ?></h4>
                <p><?php esc_html_e('Tarjeta y PayPal con protección.', 'repuestos'); ?></p>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
