/**
 * Admin: cascada Marca → Modelo en el metabox de compatibilidad.
 */
(function ($) {
    'use strict';

    var cfg = window.RepuestosFitmentAdmin || {};
    if (!cfg.ajaxUrl) return;

    var counter = 1000;

    function loadModels($row, preselect) {
        var make = $row.find('.rf-make').val();
        var year = parseInt($row.find('.rf-year').first().val(), 10);
        var $model = $row.find('.rf-model');
        if (!make || !year) {
            $model.html('<option value="">— Modelo —</option>');
            return;
        }
        $model.prop('disabled', true).html('<option value="">Cargando…</option>');
        $.getJSON(cfg.ajaxUrl, {
            action: 'repuestos_ymm_models',
            nonce: cfg.nonce,
            make: make,
            year: year
        }).done(function (res) {
            var html = '<option value="">— Modelo —</option>';
            if (res.success) {
                res.data.forEach(function (m) {
                    var sel = (preselect && preselect === m.value) ? ' selected' : '';
                    html += '<option value="' + m.value + '"' + sel + '>' + m.label + '</option>';
                });
            }
            $model.html(html).prop('disabled', false);
        }).fail(function () {
            $model.html('<option value="">Error de red</option>').prop('disabled', false);
        });
    }

    function bindRow($row) {
        var preselect = $row.find('.rf-model').data('selected');
        $row.find('.rf-make, .rf-year').on('change', function () {
            loadModels($row, null);
        });
        $row.find('.repuestos-fitment-remove').on('click', function () {
            $row.remove();
        });
        if (preselect) {
            loadModels($row, preselect);
        }
    }

    $('#repuestos-fitment-rows .repuestos-fitment-row').each(function () {
        bindRow($(this));
    });

    $('#repuestos-fitment-add').on('click', function () {
        var tpl = $('#repuestos-fitment-template').html().replace(/__i__/g, 'n' + (counter++));
        var $row = $(tpl);
        $('#repuestos-fitment-rows').append($row);
        bindRow($row);
    });
})(jQuery);
