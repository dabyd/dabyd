<?php

class IkigaiInstaller {

    private $config_path;
    private $root_path;

    public function __construct() {
        // Asumiendo /html/wp-content/themes/ikigai/install.php
        $this->root_path = dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))); // ... /www/dabyd/html
        
        // Determinar ubicación de wp-config.php (Raíz o Padre)
        // Por defecto miramos en el padre como pide el usuario
        $parent_config = dirname($this->root_path) . '/wp-config.php';
        $root_config = $this->root_path . '/wp-config.php';

        if (file_exists($parent_config)) {
            $this->config_path = $parent_config;
        } elseif (file_exists($root_config)) {
            $this->config_path = $root_config;
        } else {
            // Si no existe, preferimos crear en padre si es posible, sino en raíz
            if (is_writable(dirname($this->root_path))) {
                $this->config_path = $parent_config;
            } else {
                $this->config_path = $root_config;
            }
        }
    }

    public function run() {
        // 1. Config Setup / Edit
        // Si no existe CONFIG o si pedimos editarlo (e.g. ?edit_config=1)
        // Pero el usuario dice: "si no existe... pida datos", "si ya existe... que al entrar... lea los parámetros y permita modificarlos"
        // Por tanto, la pantalla de setup siempre debe estar disponible en install.php
        
        $this->handle_config_setup();

        // Si llegamos aqui es que se ha guardado o cancelado edición, 
        // pero handle_config_setup hace exit tras guardar.
        // Si ya existía y no se ha enviado formulario, handle_config_setup muestra el form y hace exit.
        
        // Para continuar con la instalación (plugins/ACF), el usuario debería poder "Saltar" la config si ya está bien, 
        // o el script debería detectar que ya hay WP cargado y el usuario quiere "Reinstalar/Verificar".
        
        // Vamos a modificar el flujo:
        // Render Form siempre, pre-cargado con valores. 
        // Botón "Guardar Configuración".
        // Botón "Continuar Instalación (Plugins/ACF)" (Solo si existe config válida).
    }

    private function handle_config_setup() {
        // Verificar si ya tenemos WP cargado (significa que el config funciona)
        $wp_loaded = false;
        if (!defined('ABSPATH')) {
            $wp_load = $this->root_path . '/html/wp-load.php'; 
            if (file_exists($wp_load)) {
                define('WP_USE_THEMES', false);
                @include_once($wp_load); 
            }
        }
        if (defined('ABSPATH')) $wp_loaded = true;

        if (isset($_POST['save_config'])) {
            $this->save_config();
        } elseif (isset($_POST['run_install']) && $wp_loaded) {
             // Ejecutar resto del instalador
             $this->run_install_steps();
        } else {
            // Leer configuración actual si existe
            $current_values = [];
            if (file_exists($this->config_path)) {
                $content = file_get_contents($this->config_path);
                $current_values = $this->parse_config($content);
            }
            
            $this->render_form($current_values, $wp_loaded);
        }
    }

    private function run_install_steps() {
        echo "<!DOCTYPE html><html><head><title>Instalando Ikigai</title>";
        echo "<link rel='stylesheet' href='admin/css/install.css'>";
        echo "</head><body>";
        echo "<div class='ikg-install-container'>";
        echo "<h1>🚀 Procesando Instalación...</h1>";
        
        $this->install_plugins();
        $this->import_acfs();

        echo "<hr><p>✨ <strong>¡Proceso Completado!</strong> <a href='" . home_url() . "'>Ir a la portada</a> | <a href='" . admin_url() . "'>Ir al Admin</a> | <a href='install.php'>Volver al Configurador</a></p>";
        echo "</div></body></html>";
    }

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
                // Regex para defines: define('CLAVE', valor); o define("CLAVE", valor);
                // Regex para variables: $table_prefix = 'valor';
                
                if (isset($meta['is_var']) && $meta['is_var']) {
                    if (preg_match('/\$' . preg_quote($key) . '\s*=\s*[\'"](.*?)[\'"]\s*;/', $content, $matches)) {
                         $values[$key] = $matches[1];
                    }
                } else {
                    // Buscar define
                    // Puede ser string 'val', bool true/false, o numero
                    $pattern = "/define\s*\(\s*['\"]" . preg_quote($key) . "['\"]\s*,\s*(.*?)\s*\)\s*;/";
                    if (preg_match($pattern, $content, $matches)) {
                        $raw_val = trim($matches[1]);
                        // Limpiar comillas si es string
                        if ((strpos($raw_val, "'") === 0 && strrpos($raw_val, "'") === strlen($raw_val)-1) || 
                            (strpos($raw_val, '"') === 0 && strrpos($raw_val, '"') === strlen($raw_val)-1)) {
                            $values[$key] = substr($raw_val, 1, -1);
                        } elseif (strtolower($raw_val) === 'true') {
                            $values[$key] = '1'; // Checkbox checked
                        } elseif (strtolower($raw_val) === 'false') {
                            $values[$key] = '0'; // Checkbox unchecked
                        } else {
                            $values[$key] = $raw_val; // Numeros u otros
                        }
                    }
                }
            }
        }
        return $values;
    }

    private function render_form($current_values = [], $wp_loaded = false) {
        $options = $this->get_config_options();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Configuración de Ikigai (wp-config.php)</title>
            <link rel="stylesheet" href="admin/css/install.css">
        </head>
        <body>
            <div class="ikg-install-container">
                <h1>
                    Configuración de Ikigai
                    <?php if (file_exists($this->config_path)): ?>
                        <span class="ikg-install-status-badge">✅ wp-config.php detectado</span>
                    <?php else: ?>
                        <span class="ikg-install-status-badge ikg-install-missing">⚠️ Archivo no encontrado</span>
                    <?php endif; ?>
                </h1>
                
                <div class="ikg-install-path-info">
                    <strong>Ubicación del archivo:</strong> <?php echo htmlspecialchars($this->config_path); ?>
                </div>

                <form method="post">
                    <?php foreach ($options as $group_key => $group): ?>
                        <div class="ikg-install-group-section">
                            <h3 class="ikg-install-group-title"><?php echo $group['title']; ?></h3>
                            <div class="ikg-install-grid">
                                <?php foreach ($group['fields'] as $key => $field): ?>
                                    <div class="ikg-install-form-control">
                                        <?php 
                                            // Determine val: POST > Current Loaded > Default
                                            $val = $field['default'];
                                            if (isset($current_values[$key])) $val = $current_values[$key];
                                            if (isset($_POST[$key])) $val = $_POST[$key]; 
                                            
                                            // Handling Relative Paths cleanup for display
                                            // Remove __DIR__ . '/ or '
                                            if ($field['type'] === 'bool_or_path' && is_string($val)) {
                                                $val = str_replace("__DIR__ . '/", "", $val);
                                                $val = str_replace("__DIR__ . \"/", "", $val);
                                                $val = str_replace("__DIR__", "", $val);
                                                $val = trim($val, " '\""); // Trim quotes and spaces
                                            }
                                        ?>
                                        
                                        <?php if ($field['type'] === 'bool'): ?>
                                            <div class="ikg-install-switch-wrapper">
                                                <label for="<?php echo $key; ?>"><?php echo $field['label']; ?></label>
                                                <label class="ikg-install-switch">
                                                    <input type="hidden" name="<?php echo $key; ?>" value="0">
                                                    <input type="checkbox" id="<?php echo $key; ?>" name="<?php echo $key; ?>" value="1" <?php checked($val, 1); ?>>
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
                         <?php if ($wp_loaded): ?>
                            <button type="submit" name="run_install" class="ikg-install-btn ikg-install-btn-secondary">Continuar (Instalar Plugins/ACF) &rarr;</button>
                         <?php endif; ?>
                    </div>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
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
                     // Defines
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
                             // IT IS A PATH STRING
                             // 1. Ensure absolute path for saving
                             // We want to save as: __DIR__ . '/path'
                             
                             // 2. Ensure directory exists
                             $relative_path = ltrim($val, '/'); // ensure it doesnt start with /
                             $target_dir = dirname($this->config_path) . '/' . dirname($relative_path);
                             
                             if (!file_exists($target_dir)) {
                                 // Intentar crear
                                 if (!mkdir($target_dir, 0755, true)) {
                                     // No pudimos crear, quizás permisos. 
                                     // No bloqueamos, pero es un aviso.
                                 }
                             }
                             
                             $content .= "define('$key', __DIR__ . '/$relative_path');\n";
                         }
                     } elseif ($field['type'] === 'number') {
                         $content .= "define('$key', $val);\n";
                     } else {
                         // Default string
                         $content .= "define('$key', '$val');\n";
                     }
                 }
             }
             $content .= "\n";
        }

        // Add Salts 
        $content .= "// KEYS & SALTS (Generados automáticamente)\n";
        $keys = ['AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT'];
        foreach ($keys as $k) {
            $salt = base64_encode(random_bytes(64));
            $content .= "define('$k', '$salt');\n";
        }
        $content .= "\n";

        // Add Absolute Path
        $content .= "/* Absolute path to the WordPress directory. */\n";
        $content .= "if ( ! defined( 'ABSPATH' ) ) {\n";
        $content .= "\tdefine( 'ABSPATH', dirname( __FILE__ ) . '/' );\n";
        $content .= "}\n\n";
        
        $content .= "require_once ABSPATH . 'wp-settings.php';\n";

        if (file_put_contents($this->config_path, $content)) {
            echo "<script>alert('Configuración guardada en: " . addslashes($this->config_path) . "'); window.location.href = window.location.href;</script>";
            exit;
        } else {
            wp_die("Error escribiendo en " . $this->config_path);
        }
    }

    // Reuse existing plugin/acf logic
    private function install_plugins() {
         echo "<h3>📦 Comprobando Plugins...</h3>";
         $plugins_dir = dirname(dirname(__FILE__)) . '/plugins/';
         if (!file_exists($plugins_dir)) { echo "<p>No hay directorio backups.</p>"; return; }
         
         require_once ABSPATH . 'wp-admin/includes/plugin.php';
         require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
         require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
         require_once ABSPATH . 'wp-admin/includes/file.php';
         WP_Filesystem();
         
         $zips = glob($plugins_dir . '*.zip');
         if (!$zips) { echo "<p>No hay plugins.</p>"; return; }
         
         foreach ($zips as $zip) {
             $name = basename($zip, '.zip');
             $dest = WP_PLUGIN_DIR . '/' . $name;
             if (!is_dir($dest)) {
                 $unzip = unzip_file($zip, WP_PLUGIN_DIR);
                 if (is_wp_error($unzip)) echo "<p class='ikg-install-text-error'>❌ $name error.</p>";
                 else echo "<p class='ikg-install-text-success'>✅ $name instalado.</p>";
             }
             $main = $this->find_plugin_main_file($dest);
             if ($main && !is_plugin_active("$name/$main")) {
                 activate_plugin("$name/$main");
                 echo "<p>⚡ $name activado.</p>";
             }
         }
    }
    
    private function find_plugin_main_file($dir) {
        if (!is_dir($dir)) return false;
        foreach (scandir($dir) as $f) {
            if (substr($f, -4) === '.php' && strpos(file_get_contents("$dir/$f"), 'Plugin Name:') !== false) return $f;
        }
        return false;
    }

    private function import_acfs() {
        echo "<h3>📋 Comprobando ACF...</h3>";
        if (!function_exists('acf_get_field_groups')) return;
        $acfs = acf_get_local_json_files();
        foreach ($acfs as $file) {
            $json = json_decode(file_get_contents($file), true);
            if ($json && !acf_get_field_group($json['key'])) {
                acf_import_field_group($json);
                echo "<p>✅ '{$json['title']}' importado.</p>";
            }
        }
    }
}

// Helpers for view
function checked($current, $value) {
    echo ($current == $value) ? 'checked' : '';
}
