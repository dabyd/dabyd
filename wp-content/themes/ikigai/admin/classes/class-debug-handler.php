<?php
/**
 * Sistema de Debug Log para WordPress
 * 
 * Características:
 * - Guarda logs de debug en archivos diarios
 * - Permite loguear variables con títulos
 * - Soporta arrays, objetos y variables simples
 * - Soporta múltiples categorías de log (debug, accessibility)
 * 
 * Uso:
 *   ikg_debug('Mi título', $mi_variable);
 *   ikg_debug('Con backtrace', $variable, true);
 *   ikg_accessibility('Imagen sin alt', ['id' => 123]);
 */

class IkigaiDebugLogger {
    
    private static $instance = null;
    private $base_dir;
    private $log_dirs = [];
    private $log_files = [];
    
    // Categorías de log soportadas
    const CATEGORY_DEBUG = 'debug';
    const CATEGORY_ACCESSIBILITY = 'accessibility';
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        $upload_dir = wp_upload_dir();
        $this->base_dir = $upload_dir['basedir'];
        
        // Configurar directorios para cada categoría
        $this->setup_category(self::CATEGORY_DEBUG, 'debug-logs');
        $this->setup_category(self::CATEGORY_ACCESSIBILITY, 'accessibility-logs');
    }
    
    /**
     * Configurar directorio para una categoría de log
     */
    private function setup_category($category, $folder_name) {
        $dir = $this->base_dir . '/' . $folder_name;
        
        // Crear directorio si no existe
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
            
            // Crear .htaccess para proteger los logs
            $htaccess = $dir . '/.htaccess';
            if (!file_exists($htaccess)) {
                file_put_contents($htaccess, "Deny from all\n");
            }
        }
        
        $this->log_dirs[$category] = $dir;
        $this->log_files[$category] = $dir . '/' . $category . '-' . date('Y-m-d') . '.log';
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
     * Loguear una variable con un título
     * 
     * @param string $title Título descriptivo
     * @param mixed $variable Variable a loguear (puede ser array, objeto o escalar)
     * @param bool $include_backtrace Incluir información de dónde se llamó
     * @param string $category Categoría de log (debug, accessibility)
     * @return bool True si se guardó correctamente
     */
    public function log($title, $variable = null, $include_backtrace = false, $category = self::CATEGORY_DEBUG) {
        $timestamp = date('Y-m-d H:i:s');
        
        // Validar categoría
        if (!isset($this->log_files[$category])) {
            $category = self::CATEGORY_DEBUG;
        }
        
        // Construir entrada de log
        $log_entry = "\n";
        $log_entry .= str_repeat('=', 80) . "\n";
        $log_entry .= "[$timestamp] $title\n";
        $log_entry .= str_repeat('-', 80) . "\n";
        
        // Formatear la variable según su tipo
        if ($variable !== null) {
            $log_entry .= $this->format_variable($variable);
        }
        
        // Añadir backtrace si se solicita
        if ($include_backtrace) {
            $log_entry .= "\n" . str_repeat('-', 40) . " BACKTRACE " . str_repeat('-', 28) . "\n";
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
            // Saltar el primer elemento (esta función)
            array_shift($backtrace);
            foreach ($backtrace as $i => $trace) {
                $file = isset($trace['file']) ? $this->shorten_path($trace['file']) : '[internal]';
                $line = isset($trace['line']) ? $trace['line'] : '?';
                $function = isset($trace['function']) ? $trace['function'] : '';
                $class = isset($trace['class']) ? $trace['class'] . $trace['type'] : '';
                $log_entry .= sprintf("  #%d %s:%s - %s%s()\n", $i, $file, $line, $class, $function);
            }
        }
        
        $log_entry .= str_repeat('=', 80) . "\n";
        
        // Guardar en archivo de la categoría correspondiente
        return file_put_contents($this->log_files[$category], $log_entry, FILE_APPEND) !== false;
    }
    
    /**
     * Loguear problema de accesibilidad
     * 
     * @param string $title Título descriptivo (ej: "Imagen sin alt")
     * @param mixed $data Datos del elemento (id, url, etc)
     * @param bool $include_backtrace Incluir origen
     * @return bool
     */
    public function log_accessibility($title, $data = null, $include_backtrace = true) {
        return $this->log('[Accesibilidad] ' . $title, $data, $include_backtrace, self::CATEGORY_ACCESSIBILITY);
    }
    
    /**
     * Formatear variable para el log
     */
    private function format_variable($variable) {
        $output = '';
        
        // Detectar tipo y formatear
        $type = gettype($variable);
        
        switch ($type) {
            case 'array':
                $output .= "(Array) [" . count($variable) . " elementos]\n";
                $output .= print_r($variable, true);
                break;
                
            case 'object':
                $class = get_class($variable);
                $output .= "(Object) $class\n";
                $output .= print_r($variable, true);
                break;
                
            case 'boolean':
                $output .= "(Boolean) " . ($variable ? 'true' : 'false') . "\n";
                break;
                
            case 'NULL':
                $output .= "(NULL)\n";
                break;
                
            case 'resource':
                $output .= "(Resource) " . get_resource_type($variable) . "\n";
                break;
                
            case 'string':
                $length = strlen($variable);
                if ($length > 1000) {
                    $output .= "(String) [$length caracteres] [truncado]\n";
                    $output .= substr($variable, 0, 1000) . "...\n";
                } else {
                    $output .= "(String) [$length caracteres]\n";
                    $output .= $variable . "\n";
                }
                break;
                
            case 'integer':
            case 'double':
                $output .= "($type) $variable\n";
                break;
                
            default:
                $output .= "($type)\n";
                $output .= print_r($variable, true);
        }
        
        return $output;
    }
    
    /**
     * Acortar ruta de archivo
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
     * Obtener ruta del archivo de log actual
     * @param string $category Categoría de log
     */
    public function get_log_file($category = self::CATEGORY_DEBUG) {
        return isset($this->log_files[$category]) ? $this->log_files[$category] : $this->log_files[self::CATEGORY_DEBUG];
    }
    
    /**
     * Obtener directorio de logs
     * @param string $category Categoría de log
     */
    public function get_log_dir($category = self::CATEGORY_DEBUG) {
        return isset($this->log_dirs[$category]) ? $this->log_dirs[$category] : $this->log_dirs[self::CATEGORY_DEBUG];
    }
    
    /**
     * Limpiar logs antiguos (más de X días)
     * @param int $days Días de antigüedad
     * @param string|null $category Categoría específica o null para todas
     */
    public function cleanup_old_logs($days = 30, $category = null) {
        $cutoff = time() - ($days * 24 * 60 * 60);
        $deleted = 0;
        
        $categories = $category ? [$category] : array_keys($this->log_dirs);
        
        foreach ($categories as $cat) {
            if (!isset($this->log_dirs[$cat])) continue;
            
            $files = glob($this->log_dirs[$cat] . '/*.log');
            foreach ($files as $file) {
                if (filemtime($file) < $cutoff) {
                    if (unlink($file)) {
                        $deleted++;
                    }
                }
            }
        }
        
        return $deleted;
    }
}

/**
 * Función helper principal para debug log
 * 
 * Uso:
 *   ikg_debug('Mi título', $mi_variable);
 *   ikg_debug('Con backtrace', $variable, true);
 * 
 * @param string $title Título descriptivo
 * @param mixed $variable Variable a loguear
 * @param bool $backtrace Incluir información de origen
 * @return bool
 */
function ikg_debug($title, $variable = null, $backtrace = false) {
    return IkigaiDebugLogger::get_instance()->log($title, $variable, $backtrace);
}

/**
 * Función helper para logs de accesibilidad
 * 
 * Uso:
 *   ikg_accessibility('Imagen sin alt', ['id' => 123, 'url' => '...']);
 * 
 * @param string $title Título descriptivo
 * @param mixed $data Datos del problema
 * @param bool $backtrace Incluir origen (default: true)
 * @return bool
 */
function ikg_accessibility($title, $data = null, $backtrace = true) {
    return IkigaiDebugLogger::get_instance()->log_accessibility($title, $data, $backtrace);
}

/**
 * Obtener la ruta del log de debug actual
 */
function ikg_get_debug_log_path() {
    return IkigaiDebugLogger::get_instance()->get_log_file();
}

/**
 * Obtener la ruta del log de accesibilidad actual
 */
function ikg_get_accessibility_log_path() {
    return IkigaiDebugLogger::get_instance()->get_log_file(IkigaiDebugLogger::CATEGORY_ACCESSIBILITY);
}

/**
 * Obtener instancia del debug logger
 */
function ikg_debug_logger() {
    return IkigaiDebugLogger::get_instance();
}