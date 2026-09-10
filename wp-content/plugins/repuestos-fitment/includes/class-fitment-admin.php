<?php
/**
 * Metabox "Compatibilidad de vehículos" en el editor de productos.
 *
 * Cada fila: marca + modelo (cascada desde la API vPIC) + rango de años.
 * Al guardar se genera un índice expandido en el meta '_repuestos_fit'
 * con un valor por cada año: "marca|modelo|año" en minúsculas.
 * Ese índice es lo que usa la búsqueda para filtrar (meta query exacta).
 */

if (!defined('ABSPATH')) {
    exit;
}

class Repuestos_Fitment_Admin {

    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add_metabox'));
        add_action('save_post_product', array(__CLASS__, 'save'), 10, 2);
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue'));
    }

    public static function add_metabox() {
        add_meta_box(
            'repuestos_fitment',
            'Compatibilidad de vehículos',
            array(__CLASS__, 'render'),
            'product',
            'normal',
            'default'
        );
    }

    public static function enqueue($hook) {
        if (!in_array($hook, array('post.php', 'post-new.php'), true)) {
            return;
        }
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'product') {
            return;
        }
        wp_enqueue_script(
            'repuestos-fitment-admin',
            REPUESTOS_FITMENT_URL . 'assets/js/fitment-admin.js',
            array('jquery'),
            REPUESTOS_FITMENT_VERSION,
            true
        );
        wp_localize_script('repuestos-fitment-admin', 'RepuestosFitmentAdmin', array(
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('repuestos_fitment'),
            'makes'   => Repuestos_VPIC_Client::get_makes(),
        ));
        wp_enqueue_style(
            'repuestos-fitment-admin',
            REPUESTOS_FITMENT_URL . 'assets/css/fitment.css',
            array(),
            REPUESTOS_FITMENT_VERSION
        );
    }

    public static function render($post) {
        wp_nonce_field('repuestos_fitment_save', 'repuestos_fitment_nonce');
        $fitment = get_post_meta($post->ID, '_repuestos_fitment', true);
        if (!is_array($fitment)) {
            $fitment = array();
        }
        $makes = Repuestos_VPIC_Client::get_makes();
        $current_year = (int) date('Y') + 1;
        ?>
        <p class="description">
            Indica en qué vehículos funciona este repuesto. Los productos sin compatibilidad
            se muestran para cualquier búsqueda (se tratan como universales).
        </p>
        <table class="widefat repuestos-fitment-table">
            <thead>
                <tr>
                    <th>Marca</th>
                    <th>Modelo</th>
                    <th>Año desde</th>
                    <th>Año hasta</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="repuestos-fitment-rows">
                <?php foreach ($fitment as $i => $row) : ?>
                    <?php self::row($i, $row, $makes, $current_year); ?>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button" id="repuestos-fitment-add">+ Añadir vehículo</button>
        </p>
        <script type="text/html" id="repuestos-fitment-template">
            <?php self::row('__i__', array(), $makes, $current_year); ?>
        </script>
        <?php
    }

    protected static function row($i, $row, $makes, $current_year) {
        $make = isset($row['make']) ? $row['make'] : '';
        $model = isset($row['model']) ? $row['model'] : '';
        $from = isset($row['year_from']) ? absint($row['year_from']) : $current_year;
        $to = isset($row['year_to']) ? absint($row['year_to']) : $current_year;
        ?>
        <tr class="repuestos-fitment-row">
            <td>
                <select name="repuestos_fitment[<?php echo esc_attr($i); ?>][make]" class="rf-make">
                    <option value="">— Marca —</option>
                    <?php foreach ($makes as $m) : ?>
                        <option value="<?php echo esc_attr($m); ?>" <?php selected($make, $m); ?>>
                            <?php echo esc_html(Repuestos_VPIC_Client::display_name($m)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td>
                <select name="repuestos_fitment[<?php echo esc_attr($i); ?>][model]" class="rf-model" data-selected="<?php echo esc_attr($model); ?>">
                    <option value=""><?php echo $model ? esc_html($model) : '— Modelo —'; ?></option>
                    <?php if ($model) : ?>
                        <option value="<?php echo esc_attr($model); ?>" selected><?php echo esc_html($model); ?></option>
                    <?php endif; ?>
                </select>
            </td>
            <td><input type="number" name="repuestos_fitment[<?php echo esc_attr($i); ?>][year_from]" value="<?php echo esc_attr($from); ?>" min="1950" max="<?php echo esc_attr($current_year); ?>" class="rf-year small-text"></td>
            <td><input type="number" name="repuestos_fitment[<?php echo esc_attr($i); ?>][year_to]" value="<?php echo esc_attr($to); ?>" min="1950" max="<?php echo esc_attr($current_year); ?>" class="rf-year small-text"></td>
            <td><button type="button" class="button repuestos-fitment-remove" title="Quitar">×</button></td>
        </tr>
        <?php
    }

    public static function save($post_id, $post) {
        if (!isset($_POST['repuestos_fitment_nonce'])
            || !wp_verify_nonce($_POST['repuestos_fitment_nonce'], 'repuestos_fitment_save')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fitment = array();
        if (!empty($_POST['repuestos_fitment']) && is_array($_POST['repuestos_fitment'])) {
            foreach ($_POST['repuestos_fitment'] as $row) {
                $make = isset($row['make']) ? strtoupper(sanitize_text_field($row['make'])) : '';
                $model = isset($row['model']) ? strtoupper(sanitize_text_field($row['model'])) : '';
                $from = isset($row['year_from']) ? absint($row['year_from']) : 0;
                $to = isset($row['year_to']) ? absint($row['year_to']) : 0;
                if ($make === '' || $model === '' || $from < 1950 || $to < $from) {
                    continue;
                }
                // Tope de seguridad: rangos de máximo 40 años.
                $to = min($to, $from + 40);
                $fitment[] = array(
                    'make'      => $make,
                    'model'     => $model,
                    'year_from' => $from,
                    'year_to'   => $to,
                );
            }
        }

        update_post_meta($post_id, '_repuestos_fitment', $fitment);

        // Índice expandido para búsquedas exactas: "marca|modelo|año".
        delete_post_meta($post_id, '_repuestos_fit');
        foreach ($fitment as $row) {
            for ($y = $row['year_from']; $y <= $row['year_to']; $y++) {
                add_post_meta(
                    $post_id,
                    '_repuestos_fit',
                    strtolower($row['make'] . '|' . $row['model'] . '|' . $y),
                    false
                );
            }
        }
    }
}
