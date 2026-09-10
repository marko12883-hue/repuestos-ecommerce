<?php
/**
 * Cliente de la API vPIC de NHTSA (gratis, sin registro ni API key).
 *
 * Endpoints usados:
 * - GetMakesForVehicleType/car        → lista de marcas
 * - GetModelsForMakeYear/make/{m}/modelyear/{y} → modelos de una marca en un año
 * - DecodeVinValues/{vin}             → decodifica un VIN de 17 caracteres
 *
 * Todo se cachea con transients de WordPress (1 semana) para no saturar la API.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Repuestos_VPIC_Client {

    const BASE = 'https://vpic.nhtsa.dot.gov/api/vehicles/';

    /** Marcas populares para no mostrar las 195 que devuelve la API. Filtrable. */
    public static function popular_makes() {
        $makes = array(
            'ACURA', 'AUDI', 'BMW', 'BUICK', 'CADILLAC', 'CHEVROLET', 'CHRYSLER',
            'DODGE', 'FIAT', 'FORD', 'GENESIS', 'GMC', 'HONDA', 'HYUNDAI',
            'INFINITI', 'JAGUAR', 'JEEP', 'KIA', 'LAND ROVER', 'LEXUS', 'LINCOLN',
            'MAZDA', 'MERCEDES-BENZ', 'MERCURY', 'MINI', 'MITSUBISHI', 'NISSAN',
            'PONTIAC', 'PORSCHE', 'RAM', 'SAAB', 'SATURN', 'SCION', 'SMART',
            'SUBARU', 'SUZUKI', 'TESLA', 'TOYOTA', 'VOLKSWAGEN', 'VOLVO',
        );
        return apply_filters('repuestos_fitment_makes', $makes);
    }

    public static function init() {
        // Reservado para hooks futuros.
    }

    /**
     * Nombre legible para mostrar ("MERCEDES-BENZ" → "Mercedes-Benz").
     */
    public static function display_name($raw) {
        $acronyms = array('BMW', 'GMC', 'BYD');
        $upper = strtoupper($raw);
        if (in_array($upper, $acronyms, true)) {
            return $upper;
        }
        return ucwords(strtolower($raw));
    }

    protected static function request($path) {
        $key = 'repuestos_vpic_' . md5($path);
        $cached = get_transient($key);
        if ($cached !== false) {
            return $cached;
        }

        $res = wp_remote_get(self::BASE . $path, array('timeout' => 15));
        if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
            // No cachear errores más de 1 hora para reintentar pronto.
            set_transient($key, array(), HOUR_IN_SECONDS);
            return array();
        }

        $data = json_decode(wp_remote_retrieve_body($res), true);
        $results = isset($data['Results']) && is_array($data['Results']) ? $data['Results'] : array();
        set_transient($key, $results, WEEK_IN_SECONDS);
        return $results;
    }

    /** Años disponibles, del próximo año hacia atrás. */
    public static function get_years($from = 1995) {
        $from = apply_filters('repuestos_fitment_year_from', absint($from));
        return range((int) date('Y') + 1, $from);
    }

    /** Marcas (lista curada por defecto). */
    public static function get_makes() {
        return self::popular_makes();
    }

    /** Modelos de una marca en un año dado. */
    public static function get_models($make, $year) {
        $make = strtoupper(sanitize_text_field($make));
        $year = absint($year);
        if ($make === '' || $year < 1950) {
            return array();
        }
        $rows = self::request(
            'GetModelsForMakeYear/make/' . rawurlencode($make) . '/modelyear/' . $year . '?format=json'
        );
        $models = array();
        foreach ($rows as $r) {
            if (!empty($r['Model_Name'])) {
                $models[] = strtoupper($r['Model_Name']);
            }
        }
        $models = array_values(array_unique($models));
        sort($models);
        return $models;
    }

    /**
     * Decodifica un VIN y devuelve ['make','model','year'] o WP_Error.
     */
    public static function decode_vin($vin) {
        $vin = strtoupper(sanitize_text_field($vin));
        if (strlen($vin) !== 17) {
            return new WP_Error('vin_length', 'El VIN debe tener 17 caracteres.');
        }
        $rows = self::request('DecodeVinValues/' . rawurlencode($vin) . '?format=json');
        if (empty($rows[0])) {
            return new WP_Error('vin_decode', 'No se pudo decodificar el VIN.');
        }
        $d = $rows[0];
        $make = isset($d['Make']) ? strtoupper(trim($d['Make'])) : '';
        $model = isset($d['Model']) ? strtoupper(trim($d['Model'])) : '';
        $year = isset($d['ModelYear']) ? trim($d['ModelYear']) : '';
        if ($make === '' || $model === '' || $year === '') {
            return new WP_Error('vin_incomplete', 'El VIN no devolvió datos completos del vehículo.');
        }
        return array('make' => $make, 'model' => $model, 'year' => $year);
    }
}
