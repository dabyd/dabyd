<?php

class IkigaiInstaller {

    private $config_path;
    private $root_path;

    public function __construct() {
        // Asumiendo /html/wp-content/themes/ikigai/install.php
        $this->root_path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))); // ... /www/dabyd/html
        
        // Determinar ubicación de wp-config.php (Raíz o Padre)
        $parent_config = dirname($this->root_path) . '/wp-config.php';
        $root_config = $this->root_path . '/wp-config.php';

        if (file_exists($parent_config)) {
            $this->config_path = $parent_config;
        } elseif (file_exists($root_config)) {
            $this->config_path = $root_config;
        } else {
            if (is_writable(dirname($this->root_path))) {
                $this->config_path = $parent_config;
            } else {
                $this->config_path = $root_config;
            }
        }
    }

    public function run() {
        $this->handle_request();
    }

    private function handle_request() {
        // Verificar si ya tenemos WP cargado
        $wp_loaded = false;
        if (!defined('ABSPATH')) {
            $wp_load = $this->root_path . '/wp-load.php'; 
            if (file_exists($wp_load)) {
                define('WP_USE_THEMES', false);
                @include_once($wp_load); 
            }
        }
        if (defined('ABSPATH')) $wp_loaded = true;

        if (isset($_POST['ikg_action'])) {
            $this->handle_ajax();
        } elseif (isset($_POST['save_config'])) {
            $this->save_config();
        } elseif (isset($_POST['run_install']) && $wp_loaded) {
             // Fallback or deprecated
        } else {
            // Render UI
            $current_values = [];
            if (file_exists($this->config_path)) {
                $content = file_get_contents($this->config_path);
                $current_values = $this->parse_config($content);
            }
            $this->render_ui($current_values, $wp_loaded);
        }
    }

    private function render_ui($current_values = [], $wp_loaded = false) {
        $options = $this->get_config_options();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Instalador Ikigai</title>
            <link rel="stylesheet" href="admin/css/install.css">
            <script>
                function openTab(evt, tabName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("ikg-tab-content");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("ikg-tab-link");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }
                    document.getElementById(tabName).style.display = "block";
                    evt.currentTarget.className += " active";
                }
                
                // Auto-retain tab based on hash or default
                window.onload = function() {
                    if(window.location.hash === '#install' && <?php echo $wp_loaded ? 'true' : 'false'; ?>) {
                        document.querySelector('[onclick*="tab-install"]').click();
                    } else {
                        document.getElementById("tab-config").style.display = "block";
                    }
                };
            </script>
        </head>
        <body>
            <div class="ikg-install-container">
                <h1>
                    Instalador Ikigai
                    <?php if (file_exists($this->config_path)): ?>
                        <span class="ikg-install-status-badge">✅ wp-config.php existe</span>
                    <?php else: ?>
                        <span class="ikg-install-status-badge ikg-install-missing">⚠️ No wp-config.php</span>
                    <?php endif; ?>
                </h1>

                <div class="ikg-tabs">
                    <button class="ikg-tab-link active" onclick="openTab(event, 'tab-config')">1. Configuración (wp-config)</button>
                    <button class="ikg-tab-link" onclick="openTab(event, 'tab-install')">2. Instalación (Plugins & Datos)</button>
                </div>
                
                <div class="ikg-install-path-info">
                    <strong>Ubicación wp-config:</strong> <?php echo htmlspecialchars($this->config_path); ?>
                </div>

                <!-- TAB 1: CONFIG -->
                <div id="tab-config" class="ikg-tab-content" style="display:block;">
                    <form method="post">
                        <?php foreach ($options as $group_key => $group): ?>
                            <div class="ikg-install-group-section">
                                <h3 class="ikg-install-group-title"><?php echo $group['title']; ?></h3>
                                <div class="ikg-install-grid">
                                    <?php foreach ($group['fields'] as $key => $field): ?>
                                        <div class="ikg-install-form-control">
                                            <?php 
                                                $val = $field['default'];
                                                if (isset($current_values[$key])) $val = $current_values[$key];
                                                if (isset($_POST[$key])) $val = $_POST[$key]; 
                                                
                                                if ($field['type'] === 'bool_or_path' && is_string($val)) {
                                                    $val = str_replace(["__DIR__ . '/", "__DIR__ . \"/", "__DIR__"], "", $val);
                                                    $val = trim($val, " '\"");
                                                }
                                            ?>
                                            
                                            <?php if ($field['type'] === 'bool'): ?>
                                                <div class="ikg-install-switch-wrapper">
                                                    <label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label>
                                                    <label class="ikg-install-switch">
                                                        <input type="hidden" name="<?php echo $key; ?>" value="0">
                                                        <input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php ikg_checked($val, 1); ?>>
                                                        <span class="ikg-install-slider"></span>
                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label>
                                                <?php if ($field['type'] === 'bool_or_path' || $field['type'] === 'bool_or_int'): ?>
                                                    <input type="text" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($val); ?>" placeholder="true, false o ruta relativa">
                                                <?php else: ?>
                                                    <input type="<?php echo ($field['type'] === 'password' ? 'text' : $field['type']); ?>" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="<?php echo htmlspecialchars($val); ?>">
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            
                                            <?php if (isset($field['help'])): ?>
                                                <span class="ikg-install-help-text"><?php echo $field['help']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="ikg-install-actions">
                             <button type="submit" name="save_config" class="ikg-install-btn ikg-install-btn-primary">Guardar wp-config.php</button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: INSTALL -->
                <div id="tab-install" class="ikg-tab-content">
                    <div class="ikg-install-group-section">
                        <h3 class="ikg-install-group-title">Instalación Modular</h3>
                        <p>Ejecuta los pasos de forma secuencial o individual. Sigue el log para ver el progreso.</p>

                        <?php if ($wp_loaded): ?>
                            <div class="ikg-actions-toolbar">
                                <button type="button" onclick="runFullInstall()" class="ikg-install-btn ikg-install-btn-primary">🚀 EJECUTAR INSTALACIÓN COMPLETA</button>
                                <button type="button" onclick="clearLog()" class="ikg-install-btn ikg-install-btn-secondary">Limpiar Log</button>
                            </div>

                            <div class="ikg-install-layout">
                                <!-- Steps List -->
                                <div class="ikg-steps-list">
                                    <div class="ikg-step-item" id="step-1">
                                        <div class="ikg-step-info">
                                            <strong>1. Limpiar Plugins</strong>
                                            <span>Desactivar todos los plugins activos.</span>
                                        </div>
                                        <button type="button" onclick="runStep('step_cleanup_plugins', 1)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-2">
                                        <div class="ikg-step-info">
                                            <strong>2. Instalar Plugins Locales</strong>
                                            <span>Instalar desde <code>admin/plugins/*.zip</code></span>
                                        </div>
                                        <button type="button" onclick="runStep('step_install_local_plugins', 2)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-3">
                                        <div class="ikg-step-info">
                                            <strong>3. Activar Todos los Plugins</strong>
                                            <span>Activa todo lo instalado.</span>
                                        </div>
                                        <button type="button" onclick="runStep('step_activate_all_plugins', 3)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-4">
                                        <div class="ikg-step-info">
                                            <strong>4. Crear 'Ikigai' Options Page</strong>
                                            <span>Registro en <code>acf-ui-options-page</code></span>
                                        </div>
                                        <button type="button" onclick="runStep('step_create_options_page', 4)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-5">
                                        <div class="ikg-step-info">
                                            <strong>5. Importar JSONs ACF</strong>
                                            <span>Desde <code>admin/acf-json/*.json</code></span>
                                        </div>
                                        <button type="button" onclick="runStep('step_import_acf_groups', 5)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-6">
                                        <div class="ikg-step-info">
                                            <strong>6. Activar Tema Ikigai</strong>
                                            <span>Cambiar tema activo.</span>
                                        </div>
                                        <button type="button" onclick="runStep('step_activate_theme', 6)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                    <div class="ikg-step-item" id="step-7">
                                        <div class="ikg-step-info">
                                            <strong>7. Borrar Temas Default</strong>
                                            <span>Eliminar <code>twentytwenty*</code></span>
                                        </div>
                                        <button type="button" onclick="runStep('step_delete_default_themes', 7)" class="ikg-step-btn">Ejecutar</button>
                                        <span class="ikg-step-status"></span>
                                    </div>
                                </div>

                                <!-- Log Console -->
                                <div class="ikg-log-console" id="install-log">
                                    <div class="ikg-log-header">LOG DE INSTALACIÓN</div>
                                    <div class="ikg-log-content">Esperando acciones...</div>
                                </div>
                            </div>

                        <?php else: ?>
                            <p class="ikg-install-text-error">⚠️ WordPress no está cargado. Por favor, configura y guarda el wp-config.php primero.</p>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
            
            <script>
            // JS Controller for Installation
            const steps = [
                { action: 'step_cleanup_plugins', id: 1 },
                { action: 'step_install_local_plugins', id: 2 },
                { action: 'step_activate_all_plugins', id: 3 },
                { action: 'step_create_options_page', id: 4 },
                { action: 'step_import_acf_groups', id: 5 },
                { action: 'step_activate_theme', id: 6 },
                { action: 'step_delete_default_themes', id: 7 }
            ];


            function log(msg, type='info') {
                const consoleEl = document.querySelector('#install-log .ikg-log-content');
                const line = document.createElement('div');
                line.className = 'log-line log-' + type;
                line.innerHTML = '[' + new Date().toLocaleTimeString() + '] ' + msg.replace(/\n/g, '<br>');
                consoleEl.appendChild(line);
                consoleEl.scrollTop = consoleEl.scrollHeight;
            }

            function clearLog() {
                document.querySelector('#install-log .ikg-log-content').innerHTML = '';
            }

            function setStepStatus(stepId, status) {
                const indicator = document.querySelector('#step-' + stepId + ' .ikg-step-status');
                if(status === 'loading') indicator.innerHTML = '⏳';
                else if(status === 'success') indicator.innerHTML = '✅';
                else if(status === 'error') indicator.innerHTML = '❌';
                else indicator.innerHTML = '';
            }

            async function runStep(action, stepId) {
                setStepStatus(stepId, 'loading');
                log('Iniciando paso ' + stepId + ': ' + action + '...', 'info');
                
                const formData = new FormData();
                formData.append('ikg_action', action);

                try {
                    const response = await fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        log(data.message, 'success');
                        setStepStatus(stepId, 'success');
                        return true;
                    } else {
                        log('Error: ' + data.message, 'error');
                        setStepStatus(stepId, 'error');
                        return false;
                    }
                } catch (error) {
                    log('Error de red o servidor: ' + error, 'error');
                    setStepStatus(stepId, 'error');
                    return false;
                }
            }

            async function runFullInstall() {
                if(!confirm('¿Estás seguro de ejecutar la instalación completa? Esto modificará tu sitio.')) return;
                
                clearLog();
                log('🚀 Iniciando secuencia completa...', 'highlight');

                for (const step of steps) {
                    const success = await runStep(step.action, step.id);
                    if (!success) {
                        log('⛔ Secuencia detenida por error en paso ' + step.id, 'error');
                        return; // Stop on error
                    }
                    // Small delay for UI
                    await new Promise(r => setTimeout(r, 500));
                }
                
                log('✨ ¡Secuencia Completada Exitosamente!', 'highlight');
                alert('Instalación completa.');
            }
            </script>
        </body>
        </html>
        <?php
        exit;
    }
    
    // Helper recuperado
    private function find_plugin_main_file($dir) {
        if (!is_dir($dir)) return false;
        foreach (scandir($dir) as $f) {
            if (substr($f, -4) === '.php' && strpos(file_get_contents("$dir/$f"), 'Plugin Name:') !== false) return $f;
        }
        return false;
    }


    // --- AJAX / ACTIONS Handlers ---

    public function handle_ajax() {
        // Prevent stray output
        ob_start();

        if (!isset($_POST['ikg_action'])) {
             ob_end_clean();
             return;
        }

        $action = $_POST['ikg_action'];
        $response = ['success' => false, 'message' => 'Acción desconocida'];

        try {
            switch ($action) {
                case 'step_cleanup_plugins':
                    $response = $this->step_cleanup_plugins();
                    break;
                case 'step_install_local_plugins':
                    $response = $this->step_install_local_plugins();
                    break;
                case 'step_activate_all_plugins':
                    $response = $this->step_activate_all_plugins();
                    break;
                case 'step_create_options_page':
                    $response = $this->step_create_options_page();
                    break;
                case 'step_import_acf_groups':
                    $response = $this->step_import_acf_groups();
                    break;
                case 'step_activate_theme':
                    $response = $this->step_activate_theme();
                    break;
                case 'step_delete_default_themes':
                    $response = $this->step_delete_default_themes();
                    break;
            }
        } catch (Exception $e) {
            $response = ['success' => false, 'message' => 'Error Excepción: ' . $e->getMessage()];
        } catch (Error $e) {
             $response = ['success' => false, 'message' => 'Error Fatal: ' . $e->getMessage()];
        }

        // Clean buffer to remove any PHP notices or HTML
        ob_end_clean();
        
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // --- STEP METHODS (Return array ['success'=>bool, 'message'=>string]) ---

    private function step_cleanup_plugins() {
        // Desactivar todos los plugins activos
        $active_plugins = get_option('active_plugins');
        if (empty($active_plugins)) {
            return ['success' => true, 'message' => 'ℹ️ No había plugins activos para desactivar.'];
        }
        
        deactivate_plugins($active_plugins);
        
        // Verificar
        $active_now = get_option('active_plugins');
        if (empty($active_now)) {
            return ['success' => true, 'message' => '✅ Todos los plugins han sido desactivados.'];
        } else {
            return ['success' => false, 'message' => '⚠️ Algunos plugins no se pudieron desactivar.'];
        }
    }

    private function step_install_local_plugins() {
        $messages = [];
        $plugins_dir = dirname(dirname(__FILE__)) . '/plugins/';
        if (!file_exists($plugins_dir)) { 
            return ['success' => false, 'message' => '❌ No existe el directorio admin/plugins.']; 
        }

        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
        require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        WP_Filesystem();

        $zips = glob($plugins_dir . '*.zip');
        if (!$zips) {
             $messages[] = "ℹ️ No hay plugins ZIP para instalar.";
        } else {
             // 1. Install from ZIPs
             $existing_dirs = array_map('basename', glob(WP_PLUGIN_DIR . '/*', GLOB_ONLYDIR));

             foreach ($zips as $zip) {
                 $zip_name = basename($zip, '.zip');
                 $naive_dest = WP_PLUGIN_DIR . '/' . $zip_name;
                 $target_plugin_folder = '';
     
                 if (is_dir($naive_dest)) {
                     $target_plugin_folder = $naive_dest;
                     $messages[] = "ℹ️ La carpeta $zip_name ya existe.";
                 } else {
                     $unzip_result = unzip_file($zip, WP_PLUGIN_DIR);
                     if (is_wp_error($unzip_result)) {
                         $messages[] = "❌ Error descomprimiendo $zip_name: " . $unzip_result->get_error_message();
                         continue;
                     }
                     
                     $current_dirs = array_map('basename', glob(WP_PLUGIN_DIR . '/*', GLOB_ONLYDIR));
                     $new_dirs = array_diff($current_dirs, $existing_dirs);
                     
                     if (!empty($new_dirs)) {
                         $folder_name = reset($new_dirs);
                         $target_plugin_folder = WP_PLUGIN_DIR . '/' . $folder_name;
                         $messages[] = "📦 $zip_name extraído en '$folder_name'.";
                         $existing_dirs = $current_dirs;
                     } else {
                         $target_plugin_folder = $naive_dest; 
                         $messages[] = "⚠️ No se detectó carpeta nueva para $zip_name. Asumiendo $zip_name...";
                     }
                 }
                 
                 // Try to activate immediately after unzip?
                 // No, user requested a separate step for ALL later.
                 // But we can leave 'Force ACF' check if specific. 
                 // Actually, let's defer full activation to the new step for cleanness, 
                 // but previous logic had checks for Activate immediate.
                 // User said "una vez instalados... activalos todos". 
                 // We will keep install logic pure install here if possible, but activating ACF is handy for next steps.
                 // Let's keep existing logic in this step as 'Install' mainly, but maybe just unzip.
                 // HOWEVER, step 4 relies on ACF. So Step 3 (Activate All) must run before Step 4.
             }
        }
        
        return ['success' => true, 'message' => "✅ Plugins procesados/instalados.\n" . implode("\n", $messages)];
    }

    private function step_activate_all_plugins() {
         require_once ABSPATH . 'wp-admin/includes/plugin.php';
         wp_clean_plugins_cache();
         
         $all_plugins = get_plugins();
         $activate_list = array_keys($all_plugins);
         $messages = [];
         
         if (empty($activate_list)) {
             return ['success' => true, 'message' => "ℹ️ No hay plugins instalados para activar."];
         }
         
         $messages[] = "Activando " . count($activate_list) . " plugins...";
         
         // activate_plugins (plural) accepts array
         $result = activate_plugins($activate_list);
         
         if (is_wp_error($result)) {
              return ['success' => false, 'message' => "❌ Error activando plugins: " . $result->get_error_message()];
         }
         
         foreach($all_plugins as $p) {
             $messages[] = "⚡ " . $p['Name'] . " activo.";
         }
         
         return ['success' => true, 'message' => "✅ Plugins Activados.\n" . implode("\n", $messages)];
    }

    private function step_create_options_page() {
        // Criterios: post_type = acf-ui-options-page, post_excerpt = ikigai, post_title = Ikigai
        
        // 1. Limpieza de posibles duplicados o 'no title' creados anteriormente
        $all_candidates = get_posts([
            'post_type' => 'acf-ui-options-page',
            'post_status' => 'any',
            'numberposts' => -1
        ]);
        
        $target_id = 0;
        
        foreach ($all_candidates as $p) {
            // Si encontramos uno que ya es el correcto
            if ($p->post_title === 'Ikigai' && $p->post_excerpt === 'ikigai') {
                $target_id = $p->ID;
                break;
            }
            // Si encontramos uno con título vacío o 'Auto Draft', asumimos que fue el intento fallido y lo reciclamos
            if (empty($p->post_title) || $p->post_title === 'Auto Draft') {
                 $target_id = $p->ID;
                 // No break, seguimos buscando por si hay uno "Bueno" real más adelante
            }
        }
        
        $post_data = [
            'post_title'    => 'Ikigai',
            'post_excerpt'  => 'ikigai', // ACFE usa esto a veces como slug interno o identificador
            'post_name'     => 'ikigai',
            'post_type'     => 'acf-ui-options-page',
            'post_status'   => 'publish',
            'comment_status'=> 'closed',
            'ping_status'   => 'closed'
        ];

        if ($target_id) {
            $post_data['ID'] = $target_id;
            $id = wp_update_post($post_data, true);
            if (is_wp_error($id)) {
                return ['success' => false, 'message' => "❌ Error actualizando Options Page existente: " . $id->get_error_message()];
            }
            return ['success' => true, 'message' => "✨ Página de opciones 'Ikigai' corregida/actualizada (ID: $id)."];
        } else {
            $id = wp_insert_post($post_data, true);
            if (is_wp_error($id)) {
                return ['success' => false, 'message' => "❌ Error creando Options Page: " . $id->get_error_message()];
            }
            return ['success' => true, 'message' => "✨ Página de opciones 'Ikigai' creada (ID: $id)."];
        }
    }

    private function step_import_acf_groups() {
        // Fail-safe: Try to load ACF manual if missing
        if (!function_exists('acf_import_field_group')) {
             $this->force_load_acf();
        }

        if (!function_exists('acf_import_field_group')) {
             return ['success' => false, 'message' => "❌ Error: La función 'acf_import_field_group' no existe. ACF no se pudo cargar."];
        }

        $acf_dir = dirname(dirname(__FILE__)) . '/acf-json/';
        $files = glob($acf_dir . '*.json');
        
        if (!$files) {
             return ['success' => true, 'message' => "ℹ️ No se encontraron JSONs en $acf_dir."];
        }

        $log = [];
        foreach ($files as $file) {
            $json = json_decode(file_get_contents($file), true);
            if ($json && isset($json['key'])) {
                $res = acf_import_field_group($json);
                if ($res) $log[] = "✅ Importado: {$json['title']}";
                else $log[] = "❌ Falló: {$json['title']}";
            }
        }
        
        return ['success' => true, 'message' => "Proceso ACF Finalizado.\n" . implode("\n", $log)];
    }

    private function force_load_acf() {
        // Try to find ACF Pro or ACF in plugins
        $possible_paths = [
            'advanced-custom-fields-pro/acf.php',
            'advanced-custom-fields/acf.php',
        ];
        foreach ($possible_paths as $path) {
            $full = WP_PLUGIN_DIR . '/' . $path;
            if (file_exists($full)) {
                include_once($full);
                return;
            }
        }
        // Scan?
        foreach(glob(WP_PLUGIN_DIR . '/advanced-custom-fields*/acf.php') as $f) {
            include_once($f);
            return;
        }
    }

    private function step_activate_theme() {
        $theme_slug = 'ikigai'; // Debe coincidir con la carpeta
        
        $current = get_option('stylesheet');
        if ($current === $theme_slug) {
            return ['success' => true, 'message' => "ℹ️ El tema '$theme_slug' ya está activo."];
        }
        
        // Verificar que existe
        $theme = wp_get_theme($theme_slug);
        if (!$theme->exists()) {
             return ['success' => false, 'message' => "❌ El tema '$theme_slug' no parece existir en themes/."];
        }
        
        switch_theme($theme_slug);
        
        return ['success' => true, 'message' => "🎨 Tema '$theme_slug' activado correctamente."];
    }

    private function step_delete_default_themes() {
        $themes = wp_get_themes();
        $log = [];
        
        foreach ($themes as $slug => $theme) {
            if (strpos($slug, 'twentytwenty') === 0) {
                // Es uno de los defaults
                // NO borrar si es el activo (aunque ya activamos ikigai antes)
                if ($slug === get_option('stylesheet')) {
                    $log[] = "⚠️ No se borró '$slug' porque sigue activo.";
                    continue;
                }
                
                // Borrar
                // delete_theme($slug) requiere librerias de admin
                // include_once ABSPATH . 'wp-admin/includes/theme.php'; // ya incluido quizas
                // Pero delete_theme comprueba credenciales y cosas.
                // Mejor borrado filesystem si tenemos acceso directo, o usar WP API
                
                // Intento WP API
                // delete_theme no es funcion standard global, suele estar en updates o theme.php
                 include_once ABSPATH . 'wp-admin/includes/theme.php';
                 include_once ABSPATH . 'wp-admin/includes/file.php';
                 WP_Filesystem();
                 
                 // delete_theme intenta usar filesystem api
                 $res = delete_theme($slug);
                 if (is_wp_error($res)) {
                      $log[] = "❌ Error borrando '$slug': " . $res->get_error_message();
                 } else {
                      $log[] = "🗑️ Tema '$slug' eliminado.";
                 }
            }
        }
        
        if (empty($log)) return ['success' => true, 'message' => "ℹ️ No se encontraron temas 'twentytwenty...' para borrar."];
        
        return ['success' => true, 'message' => implode("\n", $log)];
    }



    // Config Helpers
    private function get_config_options() {
        return [
            'db' => [
                'title' => 'Base de Datos',
                'fields' => [
                    'DB_NAME' => ['label' => 'Nombre BD', 'default' => 'wordpress_db', 'type' => 'text'],
                    'DB_USER' => ['label' => 'Usuario', 'default' => 'root', 'type' => 'text'],
                    'DB_PASSWORD' => ['label' => 'Contraseña', 'default' => 'root', 'type' => 'password'],
                    'DB_HOST' => ['label' => 'Host', 'default' => 'localhost', 'type' => 'text'],
                    'DB_CHARSET' => ['label' => 'Charset', 'default' => 'utf8', 'type' => 'text'],
                    'DB_COLLATE' => ['label' => 'Collate', 'default' => '', 'type' => 'text'],
                    '$table_prefix' => ['label' => 'Prefijo Tabla', 'default' => 'wp_', 'type' => 'text', 'is_var' => true],
                ]
            ],
            'debug' => [
                'title' => 'Depuración',
                'fields' => [
                    'WP_DEBUG' => ['label' => 'WP_DEBUG', 'default' => false, 'type' => 'bool'],
                    'WP_DEBUG_LOG' => ['label' => 'WP_DEBUG_LOG', 'default' => true, 'type' => 'bool_or_path', 'help' => 'True o ruta absoluta'],
                    'WP_DEBUG_DISPLAY' => ['label' => 'WP_DEBUG_DISPLAY', 'default' => true, 'type' => 'bool'],
                    'SCRIPT_DEBUG' => ['label' => 'SCRIPT_DEBUG', 'default' => true, 'type' => 'bool'],
                    'SAVEQUERIES' => ['label' => 'SAVEQUERIES', 'default' => true, 'type' => 'bool'],
                ]
            ],
            'memory' => [
                'title' => 'Memoria y Rendimiento',
                'fields' => [
                    'WP_MEMORY_LIMIT' => ['label' => 'WP_MEMORY_LIMIT', 'default' => '256M', 'type' => 'text'],
                    'WP_MAX_MEMORY_LIMIT' => ['label' => 'WP_MAX_MEMORY_LIMIT', 'default' => '512M', 'type' => 'text'],
                    'WP_CACHE' => ['label' => 'WP_CACHE', 'default' => false, 'type' => 'bool'],
                    'COMPRESS_CSS' => ['label' => 'COMPRESS_CSS', 'default' => false, 'type' => 'bool'],
                    'COMPRESS_SCRIPTS' => ['label' => 'COMPRESS_SCRIPTS', 'default' => false, 'type' => 'bool'],
                    'CONCATENATE_SCRIPTS' => ['label' => 'CONCATENATE_SCRIPTS', 'default' => false, 'type' => 'bool'],
                ]
            ],
            'content' => [
                'title' => 'Contenido y Subidas',
                'fields' => [
                    'WP_POST_REVISIONS' => ['label' => 'WP_POST_REVISIONS', 'default' => true, 'type' => 'bool_or_int', 'help' => 'True, False o número'],
                    'AUTOSAVE_INTERVAL' => ['label' => 'AUTOSAVE_INTERVAL (seg)', 'default' => 60, 'type' => 'number'],
                    'EMPTY_TRASH_DAYS' => ['label' => 'EMPTY_TRASH_DAYS', 'default' => 30, 'type' => 'number'],
                    'ALLOW_UNFILTERED_UPLOADS' => ['label' => 'ALLOW_UNFILTERED_UPLOADS', 'default' => true, 'type' => 'bool'],
                ]
            ],
            'security' => [
                'title' => 'Seguridad y Actualizaciones',
                'fields' => [
                    'DISALLOW_FILE_EDIT' => ['label' => 'DISALLOW_FILE_EDIT', 'default' => false, 'type' => 'bool'],
                    'DISALLOW_FILE_MODS' => ['label' => 'DISALLOW_FILE_MODS', 'default' => false, 'type' => 'bool'],
                    'AUTOMATIC_UPDATER_DISABLED' => ['label' => 'AUTOMATIC_UPDATER_DISABLED', 'default' => false, 'type' => 'bool'],
                    'FORCE_SSL_ADMIN' => ['label' => 'FORCE_SSL_ADMIN', 'default' => false, 'type' => 'bool'],
                    'DISABLE_WP_CRON' => ['label' => 'DISABLE_WP_CRON', 'default' => false, 'type' => 'bool'],
                ]
            ]
        ];
    }
    
    private function parse_config($content) {
        $values = [];
        $options = $this->get_config_options();
        foreach ($options as $group) {
            foreach ($group['fields'] as $key => $meta) {
                if (isset($meta['is_var']) && $meta['is_var']) {
                    if (preg_match('/\$' . preg_quote($key) . '\s*=\s*[\'"](.*?)[\'"]\s*;/', $content, $matches)) {
                         $values[$key] = $matches[1];
                    }
                } else {
                    $pattern = "/define\s*\(\s*['\"]" . preg_quote($key) . "['\"]\s*,\s*(.*?)\s*\)\s*;/";
                    if (preg_match($pattern, $content, $matches)) {
                        $raw_val = trim($matches[1]);
                        if ((strpos($raw_val, "'") === 0 && strrpos($raw_val, "'") === strlen($raw_val)-1) || 
                            (strpos($raw_val, '"') === 0 && strrpos($raw_val, '"') === strlen($raw_val)-1)) {
                            $values[$key] = substr($raw_val, 1, -1);
                        } elseif (strtolower($raw_val) === 'true') {
                            $values[$key] = '1'; 
                        } elseif (strtolower($raw_val) === 'false') {
                            $values[$key] = '0'; 
                        } else {
                            $values[$key] = $raw_val; 
                        }
                    }
                }
            }
        }
        return $values;
    }

    private function save_config() {
        $options = $this->get_config_options();
        $content = "<?php\n/**\n * Configuración generada por Ikigai Installer\n */\n\n";

        foreach ($options as $group_key => $group) {
             $content .= "// " . strtoupper($group['title']) . "\n";
             foreach ($group['fields'] as $key => $field) {
                 $val = isset($_POST[$key]) ? $_POST[$key] : $field['default'];
                 $val = trim($val);

                 if (isset($field['is_var']) && $field['is_var']) {
                     $content .= "$key = '$val';\n";
                 } else {
                     if ($field['type'] === 'bool') {
                         $bool_str = ($val == '1') ? 'true' : 'false';
                         $content .= "define('$key', $bool_str);\n";
                     } elseif ($field['type'] === 'bool_or_path' || $field['type'] === 'bool_or_int') {
                         if (strtolower($val) === 'true' || $val === '1') {
                             $content .= "define('$key', true);\n";
                         } elseif (strtolower($val) === 'false' || $val === '0') {
                             $content .= "define('$key', false);\n";
                         } elseif (is_numeric($val)) {
                             $content .= "define('$key', $val);\n";
                         } else {
                             $relative_path = ltrim($val, '/');
                             $target_dir = dirname($this->config_path) . '/' . dirname($relative_path);
                             if (!file_exists($target_dir)) { @mkdir($target_dir, 0755, true); }
                             $content .= "define('$key', __DIR__ . '/$relative_path');\n";
                         }
                     } elseif ($field['type'] === 'number') {
                         $content .= "define('$key', $val);\n";
                     } else {
                         $content .= "define('$key', '$val');\n";
                     }
                 }
             }
             $content .= "\n";
        }

        $content .= "// KEYS & SALTS\n";
        $keys = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
        foreach ($keys as $k) {
            $salt = base64_encode(random_bytes(64));
            $content .= "define('$k', '$salt');\n";
        }
        $content .= "\n";

        $content .= "/* Absolute path to the WordPress directory. */\n";
        $content .= "if ( ! defined( 'ABSPATH' ) ) {\n";
        $content .= "\tdefine( 'ABSPATH', dirname( __FILE__ ) . '/' );\n";
        $content .= "}\n\n";
        
        $content .= "require_once ABSPATH . 'wp-settings.php';\n";

        if (file_put_contents($this->config_path, $content)) {
            echo "<script>alert('Configuración guardada!'); window.location.href = window.location.href;</script>";
            exit;
        } else {
            wp_die("Error escribiendo en " . $this->config_path);
        }
    }
}
function ikg_checked($current, $value) {
    echo ($current == $value) ? 'checked' : '';
}
