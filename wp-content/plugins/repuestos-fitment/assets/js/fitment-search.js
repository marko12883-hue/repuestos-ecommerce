/**
 * Buscador Año → Marca → Modelo + VIN + chip "mi vehículo".
 */
(function () {
    'use strict';

    var KEY = 'repuestos_ymm';
    var cfg = window.RepuestosYMM || {};
    if (!cfg.ajaxUrl) return;

    function $(id) { return document.getElementById(id); }

    function getJSON(url) {
        return fetch(url, { credentials: 'same-origin' }).then(function (r) { return r.json(); });
    }

    function showError(msg) {
        var el = $('ymm-error');
        if (!el) return;
        el.textContent = msg;
        el.hidden = !msg;
    }

    function loadModels(make, year, selectModel, preselect) {
        selectModel.disabled = true;
        selectModel.innerHTML = '<option value="">Cargando…</option>';
        var url = cfg.ajaxUrl + '?action=repuestos_ymm_models&nonce=' + encodeURIComponent(cfg.nonce) +
            '&make=' + encodeURIComponent(make) + '&year=' + encodeURIComponent(year);
        getJSON(url).then(function (res) {
            selectModel.innerHTML = '<option value="">Modelo</option>';
            if (res.success) {
                res.data.forEach(function (m) {
                    var o = document.createElement('option');
                    o.value = m.value;
                    o.textContent = m.label;
                    selectModel.appendChild(o);
                });
                selectModel.disabled = false;
                if (preselect) {
                    selectModel.value = preselect;
                    if (!selectModel.value) {
                        // El modelo del VIN no está en la lista: usarlo igual.
                        var o = document.createElement('option');
                        o.value = preselect;
                        o.textContent = preselect;
                        o.selected = true;
                        selectModel.appendChild(o);
                    }
                }
            } else {
                selectModel.innerHTML = '<option value="">Sin modelos</option>';
            }
        }).catch(function () {
            selectModel.innerHTML = '<option value="">Error de red</option>';
        });
    }

    function goToShop(make, model, year, labels) {
        try {
            localStorage.setItem(KEY, JSON.stringify({ make: make, model: model, year: year, labels: labels || null }));
        } catch (e) { /* sin localStorage, seguir igual */ }
        var token = (make + '|' + model + '|' + year).toLowerCase();
        window.location.href = cfg.shopUrl + '?vehiculo=' + encodeURIComponent(token);
    }

    /* ---- Formulario principal ---- */
    var form = $('repuestos-ymm-form');
    if (form) {
        var selYear = $('ymm-year'), selMake = $('ymm-make'), selModel = $('ymm-model');

        function maybeLoad() {
            if (selMake.value && selYear.value) {
                loadModels(selMake.value, selYear.value, selModel);
            }
        }
        selMake.addEventListener('change', maybeLoad);
        selYear.addEventListener('change', maybeLoad);

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            showError('');
            if (!selYear.value || !selMake.value || !selModel.value) {
                showError('Elige año, marca y modelo.');
                return;
            }
            var labels = {
                make: selMake.options[selMake.selectedIndex].text,
                model: selModel.options[selModel.selectedIndex].text
            };
            goToShop(selMake.value, selModel.value, selYear.value, labels);
        });

        /* ---- VIN ---- */
        var vinBtn = $('ymm-vin-btn'), vinInput = $('ymm-vin');
        function decodeVin() {
            showError('');
            var vin = (vinInput.value || '').trim();
            if (vin.length !== 17) {
                showError('El VIN debe tener 17 caracteres.');
                return;
            }
            vinBtn.disabled = true;
            vinBtn.textContent = 'Decodificando…';
            var url = cfg.ajaxUrl + '?action=repuestos_ymm_decode_vin&nonce=' + encodeURIComponent(cfg.nonce) +
                '&vin=' + encodeURIComponent(vin);
            getJSON(url).then(function (res) {
                if (!res.success) {
                    showError(res.data && res.data.message ? res.data.message : 'No se pudo decodificar el VIN.');
                    return;
                }
                var d = res.data;
                selYear.value = d.year;
                selMake.value = d.make;
                if (!selMake.value) {
                    showError('La marca del VIN no está en el catálogo.');
                    return;
                }
                loadModels(d.make, d.year, selModel, d.model);
                showError('');
            }).catch(function () {
                showError('Error de red al decodificar el VIN.');
            }).finally(function () {
                vinBtn.disabled = false;
                vinBtn.textContent = 'Decodificar';
            });
        }
        vinBtn.addEventListener('click', decodeVin);
        vinInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); decodeVin(); }
        });
    }

    /* ---- Chip "mi vehículo" en tienda/categorías ---- */
    function isCatalogPage() {
        return document.body.classList.contains('post-type-archive-product') ||
            document.body.classList.contains('tax-product_cat') ||
            !!document.querySelector('.woocommerce .products, ul.products');
    }
    var params = new URLSearchParams(window.location.search);
    var hasFilter = params.has('vehiculo');
    var saved = null;
    try { saved = JSON.parse(localStorage.getItem(KEY)); } catch (e) { /* nada */ }

    if (isCatalogPage() && (hasFilter || saved)) {
        var v = saved;
        if (hasFilter && v) { /* el filtro manda; el chip usa lo guardado */ }
        if (v && v.make && v.model && v.year) {
            var label = (v.labels && v.labels.make ? v.labels.make : v.make) + ' ' +
                (v.labels && v.labels.model ? v.labels.model : v.model) + ' ' + v.year;
            var chip = document.createElement('div');
            chip.className = 'repuestos-ymm-chip';
            chip.innerHTML = '<span>🚗 ' + label.replace(/</g, '&lt;') + '</span> ' +
                '<button type="button" aria-label="Quitar filtro">×</button>';
            chip.querySelector('button').addEventListener('click', function () {
                try { localStorage.removeItem(KEY); } catch (e) { /* nada */ }
                window.location.href = cfg.shopUrl;
            });
            var anchor = document.querySelector('.woocommerce-notices-wrapper, main, #main');
            if (anchor) {
                anchor.insertBefore(chip, anchor.firstChild);
            } else {
                document.body.insertBefore(chip, document.body.firstChild);
            }
            if (!hasFilter) {
                // Entró a la tienda con vehículo guardado: aplicar filtro.
                goToShop(v.make, v.model, v.year, v.labels);
            }
        }
    }
})();
