<?php
/**
 * Sistema de Captura de Errores para WordPress
 * 
 * Características:
 * - Captura todos los errores PHP (warnings, notices, deprecated, fatal errors)
 * - Los muestra en ventana flotante con la clase IkigaiFloatingWindow
 * - Guarda todos los errores en un archivo de log
 * - Solo se muestra para administradores
 * - Incluye información detallada (archivo, línea, tipo de error, traza)
 * 
 * @requires IkigaiFloatingWindow class
 */

class IkigaiErrorHandler {
    
    private static $instance = null;
    private $errors = [];
    private $log_file;
    private $is_admin;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        // Definir archivo de log
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/error-logs';
        
        // Crear directorio si no existe
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        $this->log_file = $log_dir . '/errors-' . date('Y-m-d') . '.log';
        
        // Verificar si el usuario es administrador
        $this->is_admin = current_user_can('manage_options');
        
        // Registrar handlers solo si es admin
        if ($this->is_admin) {
            $this->register_handlers();
        }
    }
    
    /**
     * Obtener instancia única (Singleton)
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Registrar los manejadores de errores
     */
    private function register_handlers() {
        // Error handler personalizado
        set_error_handler([$this, 'handle_error']);
        
        // Exception handler
        set_exception_handler([$this, 'handle_exception']);
        
        // Shutdown handler para errores fatales
        register_shutdown_function([$this, 'handle_fatal_error']);
        
        // Hook para mostrar ventana al final de la página
        add_action('wp_footer', [$this, 'display_error_window'], 999999);
        add_action('admin_footer', [$this, 'display_error_window'], 999999);
    }
    
    /**
     * Manejador de errores PHP
     */
    public function handle_error($errno, $errstr, $errfile, $errline) {
        // No procesar si error_reporting está desactivado para este error
        if (!(error_reporting() & $errno)) {
            return false;
        }
        
        $error_type = $this->get_error_type($errno);
        
        $error = [
            'type' => $error_type,
            'message' => $errstr,
            'file' => $errfile,
            'line' => $errline,
            'time' => date('Y-m-d H:i:s'),
            'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];
        
        $this->errors[] = $error;
        $this->log_error($error);
        
        // No impedir el manejo normal del error
        return false;
    }
    
    /**
     * Manejador de excepciones
     */
    public function handle_exception($exception) {
        $error = [
            'type' => 'EXCEPTION',
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'time' => date('Y-m-d H:i:s'),
            'trace' => $exception->getTrace()
        ];
        
        $this->errors[] = $error;
        $this->log_error($error);
    }
    
    /**
     * Manejador de errores fatales
     */
    public function handle_fatal_error() {
        $error = error_get_last();
        
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            $fatal_error = [
                'type' => 'FATAL ERROR',
                'message' => $error['message'],
                'file' => $error['file'],
                'line' => $error['line'],
                'time' => date('Y-m-d H:i:s'),
                'trace' => []
            ];
            
            $this->errors[] = $fatal_error;
            $this->log_error($fatal_error);
        }
    }
    
    /**
     * Convertir código de error a texto legible
     */
    private function get_error_type($errno) {
        $error_types = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING',
            E_PARSE => 'PARSE ERROR',
            E_NOTICE => 'NOTICE',
            E_CORE_ERROR => 'CORE ERROR',
            E_CORE_WARNING => 'CORE WARNING',
            E_COMPILE_ERROR => 'COMPILE ERROR',
            E_COMPILE_WARNING => 'COMPILE WARNING',
            E_USER_ERROR => 'USER ERROR',
            E_USER_WARNING => 'USER WARNING',
            E_USER_NOTICE => 'USER NOTICE',
            E_STRICT => 'STRICT',
            E_RECOVERABLE_ERROR => 'RECOVERABLE ERROR',
            E_DEPRECATED => 'DEPRECATED',
            E_USER_DEPRECATED => 'USER DEPRECATED'
        ];
        
        return isset($error_types[$errno]) ? $error_types[$errno] : 'UNKNOWN ERROR';
    }
    
    /**
     * Guardar error en archivo de log
     */
    private function log_error($error) {
        $log_entry = sprintf(
            "[%s] %s: %s in %s on line %d\n",
            $error['time'],
            $error['type'],
            $error['message'],
            $error['file'],
            $error['line']
        );
        
        // Añadir traza si existe
        if (!empty($error['trace'])) {
            $log_entry .= "Stack trace:\n";
            foreach ($error['trace'] as $i => $trace) {
                $file = isset($trace['file']) ? $trace['file'] : '[internal function]';
                $line = isset($trace['line']) ? $trace['line'] : '';
                $function = isset($trace['function']) ? $trace['function'] : '';
                $class = isset($trace['class']) ? $trace['class'] : '';
                $type = isset($trace['type']) ? $trace['type'] : '';
                
                $log_entry .= sprintf(
                    "  #%d %s(%s): %s%s%s()\n",
                    $i,
                    $file,
                    $line,
                    $class,
                    $type,
                    $function
                );
            }
        }
        
        $log_entry .= str_repeat('-', 80) . "\n\n";
        
        // Guardar en archivo
        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }
    
    /**
     * Mostrar ventana flotante con errores
     */
    public function display_error_window() {
        // Solo mostrar si hay errores y el usuario es admin
        if (empty($this->errors) || !$this->is_admin) {
            return;
        }
        
        // Verificar que la clase IkigaiFloatingWindow existe
        if (!class_exists('IkigaiFloatingWindow')) {
            error_log('IkigaiFloatingWindow class not found');
            return;
        }
        
        // Generar contenido HTML de los errores
        $content = $this->generate_errors_html();
        
        // Crear y mostrar ventana flotante
        $window = new IkigaiFloatingWindow(
            '⚠️ Errores Detectados (' . count($this->errors) . ')',
            $content,
            [
                'width' => '900px',
                'height' => '600px',
                'icon' => '🐛',
                'position' => ['top' => '20px', 'right' => '20px'],
                'contentClass' => 'sam-content-debug',
                'headerClass' => 'sam-header-custom',
                'closable' => true,
                'minimizable' => true,
                'maximizable' => true,
                'resizable' => true
            ]
        );
        
        $window->show();
    }
    
    /**
     * Generar HTML de los errores
     */
    private function generate_errors_html() {
        $html = '<div class="sam-floating-content">';
        
        foreach ($this->errors as $index => $error) {
            $error_class = $this->get_error_severity_class($error['type']);
            
            $html .= '<div class="meta-item">';
            $html .= '<h3 style="color: ' . $this->get_error_color($error['type']) . ';">';
            $html .= '🚨 ' . esc_html($error['type']);
            $html .= '</h3>';
            
            $html .= '<div style="margin-top: 10px;">';
            $html .= '<strong style="color: #50054c;">Mensaje:</strong><br>';
            $html .= '<span style="color: #780c73;">' . esc_html($error['message']) . '</span>';
            $html .= '</div>';
            
            $html .= '<div style="margin-top: 10px;">';
            $html .= '<strong style="color: #50054c;">Archivo:</strong><br>';
            $html .= '<code style="background: rgba(120, 12, 115, 0.1); padding: 2px 6px; border-radius: 3px;">';
            $html .= esc_html($this->shorten_path($error['file']));
            $html .= '</code>';
            $html .= '</div>';
            
            $html .= '<div style="margin-top: 10px;">';
            $html .= '<strong style="color: #50054c;">Línea:</strong> ';
            $html .= '<span style="color: #780c73; font-weight: bold;">' . esc_html($error['line']) . '</span>';
            $html .= '</div>';
            
            $html .= '<div style="margin-top: 10px;">';
            $html .= '<strong style="color: #50054c;">Hora:</strong> ';
            $html .= '<span style="color: #780c73;">' . esc_html($error['time']) . '</span>';
            $html .= '</div>';
            
            // Mostrar stack trace si existe
            if (!empty($error['trace'])) {
                $html .= '<details style="margin-top: 10px;">';
                $html .= '<summary style="cursor: pointer; color: #50054c; font-weight: bold;">📋 Stack Trace</summary>';
                $html .= '<pre style="margin-top: 10px; max-height: 200px; overflow-y: auto;">';
                $html .= esc_html($this->format_trace($error['trace']));
                $html .= '</pre>';
                $html .= '</details>';
            }
            
            $html .= '</div>';
        }
        
        // Información adicional
        $html .= '<div style="margin-top: 30px; padding: 15px; background: rgba(120, 12, 115, 0.1); border-radius: 5px; border-left: 4px solid #780c73;">';
        $html .= '<strong style="color: #50054c;">📁 Archivo de Log:</strong><br>';
        $html .= '<code style="font-size: 14px; color: #780c73;">' . esc_html($this->log_file) . '</code>';
        $html .= '<div style="margin-top: 10px; font-size: 14px; color: #780c73;">';
        $html .= 'Todos los errores se guardan automáticamente en este archivo.';
        $html .= '</div>';
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtener color según tipo de error
     */
    private function get_error_color($type) {
        $colors = [
            'FATAL ERROR' => '#d32f2f',
            'ERROR' => '#d32f2f',
            'WARNING' => '#f57c00',
            'NOTICE' => '#1976d2',
            'DEPRECATED' => '#7b1fa2',
            'STRICT' => '#0288d1'
        ];
        
        return isset($colors[$type]) ? $colors[$type] : '#780c73';
    }
    
    /**
     * Obtener clase de severidad
     */
    private function get_error_severity_class($type) {
        if (in_array($type, ['FATAL ERROR', 'ERROR'])) {
            return 'error-critical';
        } elseif (in_array($type, ['WARNING', 'USER WARNING'])) {
            return 'error-warning';
        } else {
            return 'error-notice';
        }
    }
    
    /**
     * Acortar ruta de archivo (mostrar solo ruta relativa desde wp-content)
     */
    private function shorten_path($file) {
        $wp_content_dir = WP_CONTENT_DIR;
        if (strpos($file, $wp_content_dir) === 0) {
            return '...wp-content' . substr($file, strlen($wp_content_dir));
        }
        
        $abspath = ABSPATH;
        if (strpos($file, $abspath) === 0) {
            return '...' . substr($file, strlen($abspath));
        }
        
        return $file;
    }
    
    /**
     * Formatear stack trace
     */
    private function format_trace($trace) {
        $output = '';
        foreach ($trace as $i => $step) {
            $file = isset($step['file']) ? $this->shorten_path($step['file']) : '[internal function]';
            $line = isset($step['line']) ? $step['line'] : '';
            $function = isset($step['function']) ? $step['function'] : '';
            $class = isset($step['class']) ? $step['class'] : '';
            $type = isset($step['type']) ? $step['type'] : '';
            
            $output .= sprintf(
                "#%d %s(%s): %s%s%s()\n",
                $i,
                $file,
                $line,
                $class,
                $type,
                $function
            );
        }
        return $output;
    }
    
    /**
     * Obtener la ruta del archivo de log actual
     */
    public function get_log_file() {
        return $this->log_file;
    }
    
    /**
     * Obtener todos los errores capturados
     */
    public function get_errors() {
        return $this->errors;
    }
    
    /**
     * Limpiar errores (útil para testing)
     */
    public function clear_errors() {
        $this->errors = [];
    }
}

/**
 * Función de inicialización
 * Llamar esto desde functions.php
 */
function ikigai_init_error_handler() {
    // Solo activar si es admin o tiene capacidad de ver errores
    if (current_user_can('manage_options')) {
        IkigaiErrorHandler::get_instance();
    }
}

/**
 * Función helper para obtener la instancia
 */
function ikigai_error_handler() {
    return IkigaiErrorHandler::get_instance();
}

/**
 * Función para obtener la ruta del log actual
 */
function ikigai_get_error_log_path() {
    return ikigai_error_handler()->get_log_file();
}

function ikigai_enqueue_error_handler_styles() {
    if ( isDevelopedEnvironment()) {
	    wp_enqueue_style('ikigai-debug', get_template_directory_uri() . '/css/debug.css', array(), '1.0.0');
    }
}
