<footer class="pie">
    <div class="contenedor">
        <div>
            <h4><?php echo esc_html(get_bloginfo('name')); ?></h4>
            <p><?php esc_html_e('Repuestos de autos originales y alternativos con garantía. Compra en línea fácil y segura.', 'repuestos'); ?></p>
        </div>
        <div>
            <h4><?php esc_html_e('Tienda', 'repuestos'); ?></h4>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'pie',
                'fallback_cb'    => false,
            ));
            ?>
        </div>
        <div>
            <h4><?php esc_html_e('Contacto', 'repuestos'); ?></h4>
            <p><?php esc_html_e('Escríbenos por WhatsApp o correo para cotizaciones.', 'repuestos'); ?></p>
        </div>
    </div>
    <div class="copy">
        © <?php echo esc_html(date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. <?php esc_html_e('Todos los derechos reservados.', 'repuestos'); ?>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
