/**
 * Ikigai Admin Scripts
 * JavaScript consolidado para el backoffice de WordPress
 * 
 * Incluye:
 * - Cache Button Handler
 * - Log Viewer
 * - Form Detail Toggle
 */

(function($) {
    'use strict';
    
    // =============================================================================
    // CACHE BUTTON HANDLER
    // =============================================================================
    
    function initCacheButton() {
        $(document).on('click', '[data-name="vaciar_cache_css__js"] button, [data-key="vaciar_cache_css__js"] button', function(e) {
            e.preventDefault();
            
            var $btn = $(this);
            var originalText = $btn.text();
            
            $btn.prop('disabled', true).text('Vaciando cache...');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ikg_clear_cache',
                    nonce: ikgAdmin.cacheNonce
                },
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data);
                    } else {
                        alert('❌ Error: ' + response.data);
                    }
                    $btn.prop('disabled', false).text(originalText);
                },
                error: function() {
                    alert('❌ Se ha producido un error de conexión');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });
    }
    
    // =============================================================================
    // LOG VIEWER
    // =============================================================================
    
    function initLogViewer() {
        var $container = $('.acf-field[data-name="log_viewer_container"]');
        if (!$container.length) return;
        
        var html = `
            <div class="ikg-log-viewer">
                <div class="ikg-log-tabs">
                    <div class="ikg-log-tab active" data-type="error">🔴 Error Logs</div>
                    <div class="ikg-log-tab" data-type="debug">🔵 Debug Logs</div>
                    <div class="ikg-log-tab" data-type="accessibility">♿ Accesibilidad</div>
                </div>
                <div class="ikg-log-panel active" data-type="error">
                    <div class="ikg-log-controls">
                        <select class="ikg-log-select" id="error-log-select">
                            <option value="">-- Selecciona un archivo --</option>
                        </select>
                        <button type="button" class="ikg-btn ikg-btn-primary ikg-load-log" data-type="error">Ver Log</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-log" data-type="error">Borrar seleccionado</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-all" data-type="error">Borrar todos</button>
                    </div>
                    <div class="ikg-log-content" id="error-log-content">Selecciona un archivo para ver su contenido...</div>
                    <div class="ikg-log-info" id="error-log-info"></div>
                </div>
                <div class="ikg-log-panel" data-type="debug">
                    <div class="ikg-log-controls">
                        <select class="ikg-log-select" id="debug-log-select">
                            <option value="">-- Selecciona un archivo --</option>
                        </select>
                        <button type="button" class="ikg-btn ikg-btn-primary ikg-load-log" data-type="debug">Ver Log</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-log" data-type="debug">Borrar seleccionado</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-all" data-type="debug">Borrar todos</button>
                    </div>
                    <div class="ikg-log-content" id="debug-log-content">Selecciona un archivo para ver su contenido...</div>
                    <div class="ikg-log-info" id="debug-log-info"></div>
                </div>
                <div class="ikg-log-panel" data-type="accessibility">
                    <div class="ikg-log-controls">
                        <select class="ikg-log-select" id="accessibility-log-select">
                            <option value="">-- Selecciona un archivo --</option>
                        </select>
                        <button type="button" class="ikg-btn ikg-btn-primary ikg-load-log" data-type="accessibility">Ver Log</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-log" data-type="accessibility">Borrar seleccionado</button>
                        <button type="button" class="ikg-btn ikg-btn-danger ikg-delete-all" data-type="accessibility">Borrar todos</button>
                    </div>
                    <div class="ikg-log-content" id="accessibility-log-content">Selecciona un archivo para ver su contenido...</div>
                    <div class="ikg-log-info" id="accessibility-log-info"></div>
                </div>
            </div>
        `;
        
        $container.find('.acf-input').html(html);
        
        // Cargar archivos iniciales
        loadLogFiles('error');
        loadLogFiles('debug');
        loadLogFiles('accessibility');
        
        // Eventos de tabs
        $(document).on('click', '.ikg-log-tab', function() {
            var type = $(this).data('type');
            $('.ikg-log-tab').removeClass('active');
            $(this).addClass('active');
            $('.ikg-log-panel').removeClass('active');
            $('.ikg-log-panel[data-type="' + type + '"]').addClass('active');
        });
        
        // Evento ver log
        $(document).on('click', '.ikg-load-log', function() {
            var type = $(this).data('type');
            var filename = $('#' + type + '-log-select').val();
            if (filename) {
                loadLogContent(type, filename);
            }
        });
        
        // Evento borrar log individual
        $(document).on('click', '.ikg-delete-log', function() {
            var type = $(this).data('type');
            var filename = $('#' + type + '-log-select').val();
            if (filename && confirm('¿Seguro que quieres borrar este archivo?')) {
                deleteLog(type, filename);
            }
        });
        
        // Evento borrar todos
        $(document).on('click', '.ikg-delete-all', function() {
            var type = $(this).data('type');
            if (confirm('¿Seguro que quieres borrar TODOS los logs de ' + type + '?')) {
                deleteLog(type, '');
            }
        });
        
        // Doble click en select para cargar
        $(document).on('change', '.ikg-log-select', function() {
            var type = $(this).attr('id').replace('-log-select', '');
            var filename = $(this).val();
            if (filename) {
                loadLogContent(type, filename);
            }
        });
    }
    
    function loadLogFiles(type) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ikg_get_log_files',
                nonce: ikgAdmin.logViewerNonce,
                log_type: type
            },
            success: function(response) {
                var $select = $('#' + type + '-log-select');
                $select.html('<option value="">-- Selecciona un archivo --</option>');
                
                if (response.success && response.data.length > 0) {
                    response.data.forEach(function(file) {
                        $select.append('<option value="' + file.name + '">' + file.name + ' (' + file.size + ')</option>');
                    });
                } else {
                    $select.html('<option value="">No hay archivos de log</option>');
                }
            }
        });
    }
    
    function loadLogContent(type, filename) {
        var $content = $('#' + type + '-log-content');
        var $info = $('#' + type + '-log-info');
        
        $content.text('Cargando...');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ikg_get_log_content',
                nonce: ikgAdmin.logViewerNonce,
                log_type: type,
                filename: filename
            },
            success: function(response) {
                if (response.success) {
                    $content.text(response.data.content || '(Archivo vacío)');
                    $info.html('📄 ' + filename + ' | 📏 ' + response.data.size + ' | 📝 ' + response.data.lines + ' líneas');
                    // Scroll al final
                    $content.scrollTop($content[0].scrollHeight);
                } else {
                    $content.text('Error: ' + response.data);
                }
            },
            error: function() {
                $content.text('Error de conexión');
            }
        });
    }
    
    function deleteLog(type, filename) {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ikg_delete_logs',
                nonce: ikgAdmin.logViewerNonce,
                log_type: type,
                filename: filename
            },
            success: function(response) {
                if (response.success) {
                    alert('✅ ' + response.data);
                    loadLogFiles(type);
                    $('#' + type + '-log-content').text('Selecciona un archivo para ver su contenido...');
                    $('#' + type + '-log-info').html('');
                } else {
                    alert('❌ Error: ' + response.data);
                }
            },
            error: function() {
                alert('❌ Error de conexión');
            }
        });
    }
    
    // =============================================================================
    // FORM DETAIL TOGGLE
    // =============================================================================
    
    window.ikg_toggle_detail = function(id) {
        var row = document.getElementById("ikg-detail-" + id);
        if (row) {
            row.classList.toggle("active");
        }
    };
    
    // =============================================================================
    // INITIALIZATION
    // =============================================================================
    
    $(document).ready(function() {
        // Inicializar cache button
        initCacheButton();
        
        // Esperar a que ACF cargue para el log viewer
        setTimeout(initLogViewer, 500);
    });
    
    // Reinicializar cuando ACF cargue contenido dinámico
    if (typeof acf !== 'undefined') {
        acf.addAction('ready', initLogViewer);
    }
    
})(jQuery);
