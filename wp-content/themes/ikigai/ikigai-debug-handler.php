<?php

/**
 * IkigaiDebugger - Sistema de debug con ventanas flotantes
 * 
 * Clase para debugging avanzado con ventanas flotantes interactivas
 * Incluye búsqueda, copia, syntax highlighting y múltiples formatos
 * 
 * @version 1.0
 * @requires IkigaiFloatingWindow
 */
class IkigaiDebugger {
    
    private static $assetsLoaded = false;
    private static $instanceCount = 0;
    
    /**
     * Configuración por defecto
     */
    private static $defaultConfig = [
        'format' => 'auto',           // 'auto', 'print_r', 'var_dump', 'var_export', 'json'
        'icon' => '🐛',
        'width' => '800px',
        'height' => '600px',
        'position' => 'auto',
        'syntax_highlight' => true,
        'show_type' => true,
        'search' => true,
        'copy_button' => true,
        'show_backtrace' => true,
        'theme' => 'dark',            // 'dark', 'light'
    ];
    
    /**
     * Dump principal con todas las opciones
     * 
     * @param mixed $data Datos a mostrar
     * @param string|null $title Título de la ventana
     * @param array $options Opciones adicionales
     * @param bool $die Si debe detener la ejecución
     */
    public static function dump($data, $title = null, $options = [], $die = false) {
        self::$instanceCount++;
        
        // Cargar assets si es necesario
        if (!self::$assetsLoaded) {
            echo self::getAssets();
            self::$assetsLoaded = true;
        }
        
        // Título por defecto con información de origen
        if ($title === null) {
            $title = self::generateDefaultTitle();
        }
        
        // Combinar opciones
        $config = array_merge(self::$defaultConfig, $options);
        
        // Generar contenido
        $content = self::generateContent($data, $config);
        
        // Configuración de la ventana flotante
        $windowOptions = [
            'icon' => $config['icon'],
            'width' => $config['width'],
            'height' => $config['height'],
            'position' => $config['position'],
            'contentClass' => 'ikg-debug-content',
            'headerClass' => 'ikg-debug-header',
        ];
        
        // Crear y mostrar la ventana
        $window = new IkigaiFloatingWindow($title, $content, $windowOptions);
        $window->show();
        
        if ($die) {
            die();
        }
    }
    
    /**
     * Dump and die - Alias corto
     * Soporta múltiples pares título-valor: dd('Título1', $val1, 'Título2', $val2, ...)
     */
    public static function dd(...$args) {
        if (empty($args)) {
            die();
        }
        
        // Si es un solo argumento, usar comportamiento estándar
        if (count($args) === 1) {
            self::dump($args[0], null, [], true);
            return;
        }
        
        // Si son 2 argumentos y el segundo es array, asumir opciones
        if (count($args) === 2 && is_array($args[1]) && self::looksLikeOptions($args[1])) {
            self::dump($args[0], null, $args[1], true);
            return;
        }
        
        // Si son 3 argumentos: data, title, options
        if (count($args) === 3 && is_string($args[1]) && is_array($args[2])) {
            self::dump($args[0], $args[1], $args[2], true);
            return;
        }
        
        // Caso múltiple: pares título-valor
        self::dumpMultiple($args, true);
    }
    
    /**
     * Dump sin detener ejecución - Alias corto
     * Soporta múltiples pares título-valor: d('Título1', $val1, 'Título2', $val2, ...)
     */
    public static function d(...$args) {
        if (empty($args)) {
            return;
        }
        
        // Si es un solo argumento, usar comportamiento estándar
        if (count($args) === 1) {
            self::dump($args[0], null, [], false);
            return;
        }
        
        // Si son 2 argumentos y el segundo es array, asumir opciones
        if (count($args) === 2 && is_array($args[1]) && self::looksLikeOptions($args[1])) {
            self::dump($args[0], null, $args[1], false);
            return;
        }
        
        // Si son 3 argumentos: data, title, options
        if (count($args) === 3 && is_string($args[1]) && is_array($args[2])) {
            self::dump($args[0], $args[1], $args[2], false);
            return;
        }
        
        // Caso múltiple: pares título-valor
        self::dumpMultiple($args, false);
    }
    
    /**
     * Compara múltiples variables lado a lado
     * 
     * @param array ...$datasets Array de datos o arrays con ['title' => '', 'data' => '']
     */
    public static function compare(...$datasets) {
        if (!self::$assetsLoaded) {
            echo self::getAssets();
            self::$assetsLoaded = true;
        }
        
        $html = '<div class="ikg-compare-grid">';
        
        foreach ($datasets as $index => $dataset) {
            $title = is_array($dataset) && isset($dataset['title']) 
                ? $dataset['title'] 
                : "Variable " . ($index + 1);
            
            $data = is_array($dataset) && isset($dataset['data']) 
                ? $dataset['data'] 
                : $dataset;
            
            $typeInfo = self::getTypeInfo($data);
            $formattedData = htmlspecialchars(print_r($data, true));
            
            $html .= <<<HTML
<div class="ikg-compare-item">
    <h4 class="ikg-compare-title">{$title}</h4>
    <div class="ikg-compare-type">{$typeInfo}</div>
    <pre class="ikg-compare-pre">{$formattedData}</pre>
</div>
HTML;
        }
        
        $html .= '</div>';
        
        $window = new IkigaiFloatingWindow(
            '🔍 Comparación de Variables',
            $html,
            [
                'width' => '90vw',
                'height' => '80vh',
                'position' => 'center',
                'contentClass' => 'ikg-debug-content ikg-compare-content',
            ]
        );
        $window->show();
    }
    
    /**
     * Maneja dump múltiple con pares título-valor
     * Formato: 'Título1', $valor1, 'Título2', $valor2, ...
     * 
     * @param array $args Argumentos variables
     * @param bool $die Si debe detener la ejecución
     */
    private static function dumpMultiple($args, $die = false) {
        if (!self::$assetsLoaded) {
            echo self::getAssets();
            self::$assetsLoaded = true;
        }
        
        $items = [];
        $pairs = [];
        
        // Agrupar en pares título-valor
        for ($i = 0; $i < count($args); $i++) {
            if ($i < count($args) - 1 && is_string($args[$i])) {
                // Si es string seguido de cualquier valor, es un par
                $pairs[] = [
                    'title' => $args[$i],
                    'value' => $args[$i + 1]
                ];
                $i++; // Saltar el siguiente elemento
            } else {
                // Valor sin título
                $pairs[] = [
                    'title' => 'Variable ' . (count($pairs) + 1),
                    'value' => $args[$i]
                ];
            }
        }
        
        // Generar contenido con tabs/pestañas si hay múltiples valores
        if (count($pairs) === 1) {
            // Si solo hay uno, mostrar normal
            self::dump($pairs[0]['value'], $pairs[0]['title'], [], $die);
            return;
        }
        
        // Múltiples valores: crear interfaz con tabs
        $html = self::generateMultiTabContent($pairs);
        
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $caller = isset($backtrace[1]) ? $backtrace[1] : [];
        $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
        $line = isset($caller['line']) ? $caller['line'] : '?';
        
        $window = new IkigaiFloatingWindow(
            "🐛 Debug Multiple · {$file}:{$line}",
            $html,
            [
                'width' => '900px',
                'height' => '700px',
                'position' => 'auto',
                'contentClass' => 'ikg-debug-content ikg-multi-debug',
                'headerClass' => 'ikg-debug-header',
            ]
        );
        $window->show();
        
        if ($die) {
            die();
        }
    }
    
    /**
     * Genera contenido con pestañas para múltiples dumps
     */
    private static function generateMultiTabContent($pairs) {
        $tabButtons = '';
        $tabContents = '';
        
        foreach ($pairs as $index => $pair) {
            $active = $index === 0 ? 'ikg-tab-active' : '';
            $display = $index === 0 ? 'block' : 'none';
            $tabId = 'ikg-tab-' . uniqid();
            
            $safeTitle = htmlspecialchars($pair['title']);
            $typeInfo = self::getTypeInfo($pair['value']);
            $formattedData = htmlspecialchars(print_r($pair['value'], true));
            
            // Aplicar syntax highlighting
            $formattedData = self::applySyntaxHighlight($formattedData);
            
            // Tab button
            $tabButtons .= <<<HTML
<button class="ikg-tab-btn {$active}" onclick="IkigaiDebugger.switchTab(this, '{$tabId}')">
    {$safeTitle}
</button>
HTML;
            
            // Tab content
            $tabContents .= <<<HTML
<div id="{$tabId}" class="ikg-tab-content" style="display: {$display};">
    <div class="ikg-debug-type-info">{$typeInfo}</div>
    <div class="ikg-debug-toolbar">
        <input type="text" class="ikg-debug-search" placeholder="🔍 Buscar..." onkeyup="IkigaiDebugger.search(this)">
        <button class="ikg-debug-copy-btn" onclick="IkigaiDebugger.copy(this)">📋 Copiar</button>
    </div>
    <pre class="ikg-debug-pre ikg-theme-dark"><code class="ikg-debug-code">{$formattedData}</code></pre>
</div>
HTML;
        }
        
        return <<<HTML
<div class="ikg-multi-wrapper">
    <div class="ikg-tabs-header">
        {$tabButtons}
    </div>
    <div class="ikg-tabs-container">
        {$tabContents}
    </div>
</div>
HTML;
    }
    
    /**
     * Verifica si un array parece ser de opciones
     */
    private static function looksLikeOptions($array) {
        $optionKeys = ['format', 'icon', 'width', 'height', 'position', 'syntax_highlight', 
                       'show_type', 'search', 'copy_button', 'show_backtrace', 'theme'];
        
        foreach (array_keys($array) as $key) {
            if (in_array($key, $optionKeys)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Muestra un trace de la pila de llamadas
     */
    public static function trace($title = 'Stack Trace', $options = []) {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($backtrace); // Remover la llamada a este método
        
        self::dump($backtrace, $title, array_merge([
            'icon' => '📍',
            'format' => 'print_r'
        ], $options));
    }
    
    /**
     * Dump de todas las variables globales
     */
    public static function globals($filter = null, $options = []) {
        $globals = $GLOBALS;
        
        if ($filter) {
            $globals = array_filter($globals, function($key) use ($filter) {
                return strpos($key, $filter) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }
        
        self::dump($globals, '🌍 Variables Globales', array_merge([
            'icon' => '🌍',
            'width' => '1000px',
            'height' => '700px'
        ], $options));
    }
    
    /**
     * Dump del estado de la sesión
     */
    public static function session($options = []) {
        if (session_status() === PHP_SESSION_ACTIVE) {
            self::dump($_SESSION, '🔐 Sesión', array_merge([
                'icon' => '🔐'
            ], $options));
        } else {
            self::dump('No hay sesión activa', '🔐 Sesión', array_merge([
                'icon' => '🔐'
            ], $options));
        }
    }
    
    /**
     * Genera el título por defecto con información de backtrace
     */
    private static function generateDefaultTitle() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        
        if (isset($backtrace[1])) {
            $caller = $backtrace[1];
            $file = isset($caller['file']) ? basename($caller['file']) : 'unknown';
            $line = isset($caller['line']) ? $caller['line'] : '?';
            return "Debug · {$file}:{$line}";
        }
        
        return "Debug #" . self::$instanceCount;
    }
    
    /**
     * Genera el contenido HTML completo
     */
    private static function generateContent($data, $config) {
        $html = '<div class="ikg-debug-wrapper">';
        
        // Barra de herramientas
        if ($config['copy_button'] || $config['search']) {
            $html .= self::generateToolbar($config);
        }
        
        // Información del tipo
        if ($config['show_type']) {
            $typeInfo = self::getTypeInfo($data);
            $html .= '<div class="ikg-debug-type-info">' . $typeInfo . '</div>';
        }
        
        // Contenido principal
        $html .= '<div class="ikg-debug-content-area">';
        
        $format = $config['format'] === 'auto' 
            ? self::detectBestFormat($data) 
            : $config['format'];
        
        $dumpContent = self::generateFormattedDump($data, $format, $config);
        
        $themeClass = $config['theme'] === 'light' ? 'ikg-theme-light' : 'ikg-theme-dark';
        
        $html .= '<pre class="ikg-debug-pre ' . $themeClass . '"><code class="ikg-debug-code">' 
            . $dumpContent 
            . '</code></pre>';
        
        $html .= '</div>'; // .ikg-debug-content-area
        $html .= '</div>'; // .ikg-debug-wrapper
        
        return $html;
    }
    
    /**
     * Genera la barra de herramientas
     */
    private static function generateToolbar($config) {
        $html = '<div class="ikg-debug-toolbar">';
        
        if ($config['search']) {
            $html .= '<input type="text" class="ikg-debug-search" placeholder="🔍 Buscar..." onkeyup="IkigaiDebugger.search(this)">';
        }
        
        if ($config['copy_button']) {
            $html .= '<button class="ikg-debug-copy-btn" onclick="IkigaiDebugger.copy(this)">📋 Copiar</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Obtiene información del tipo de dato
     */
    private static function getTypeInfo($data) {
        $type = gettype($data);
        $info = "<strong>Tipo:</strong> <span class='ikg-type-badge'>{$type}</span>";
        
        if (is_array($data)) {
            $count = count($data);
            $info .= " · <strong>Elementos:</strong> {$count}";
        } elseif (is_string($data)) {
            $length = strlen($data);
            $info .= " · <strong>Longitud:</strong> {$length}";
        } elseif (is_object($data)) {
            $class = get_class($data);
            $info .= " · <strong>Clase:</strong> <code>{$class}</code>";
        } elseif (is_resource($data)) {
            $resourceType = get_resource_type($data);
            $info .= " · <strong>Recurso:</strong> {$resourceType}";
        }
        
        return $info;
    }
    
    /**
     * Detecta el mejor formato automáticamente
     */
    private static function detectBestFormat($data) {
        if (is_object($data)) {
            return 'var_dump';
        } elseif (is_array($data)) {
            return 'print_r';
        } else {
            return 'var_export';
        }
    }
    
    /**
     * Genera el dump formateado
     */
    private static function generateFormattedDump($data, $format, $config) {
        ob_start();
        
        switch ($format) {
            case 'print_r':
                print_r($data);
                break;
                
            case 'var_dump':
                var_dump($data);
                break;
                
            case 'var_export':
                var_export($data);
                break;
                
            case 'json':
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                break;
                
            default:
                print_r($data);
        }
        
        $output = ob_get_clean();
        $output = htmlspecialchars($output, ENT_QUOTES, 'UTF-8');
        
        if ($config['syntax_highlight']) {
            $output = self::applySyntaxHighlight($output);
        }
        
        return $output;
    }
    
    /**
     * Aplica resaltado de sintaxis
     */
    private static function applySyntaxHighlight($text) {
        // Strings entre comillas
        $text = preg_replace(
            '/(["\'])([^\1]*?)\1/',
            '<span class="ikg-string">$1$2$1</span>',
            $text
        );
        
        // Números
        $text = preg_replace(
            '/\b(\d+\.?\d*)\b/',
            '<span class="ikg-number">$1</span>',
            $text
        );
        
        // Keywords
        $keywords = ['null', 'true', 'false', 'bool', 'int', 'string', 'array', 'object', 'float', 'resource', 'NULL', 'TRUE', 'FALSE'];
        foreach ($keywords as $keyword) {
            $text = preg_replace(
                '/\b(' . preg_quote($keyword, '/') . ')\b/',
                '<span class="ikg-keyword">$1</span>',
                $text
            );
        }
        
        // Array keys
        $text = preg_replace(
            '/^\s*\[([^\]]+)\]/m',
            '<span class="ikg-array-key">[$1]</span>',
            $text
        );
        
        return $text;
    }
    
    /**
     * Retorna los assets CSS y JS
     */
    private static function getAssets() {
        return self::getCSS() . self::getJS();
    }
    
    /**
     * Retorna solo el CSS
     */
    public static function getCSS() {
        return <<<'CSS'
<style>
/* ========================================== */
/* IKIGAI DEBUGGER - ESTILOS CSS */
/* ========================================== */

.ikg-debug-header {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
}

.ikg-debug-wrapper {
    display: flex;
    flex-direction: column;
    height: 100%;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
}

/* Barra de herramientas */
.ikg-debug-toolbar {
    display: flex;
    gap: 8px;
    padding: 12px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    flex-shrink: 0;
}

.ikg-debug-search {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    transition: all 0.2s;
}

.ikg-debug-search:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.ikg-debug-copy-btn {
    padding: 8px 16px;
    background: #667eea;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    white-space: nowrap;
}

.ikg-debug-copy-btn:hover {
    background: #5568d3;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.ikg-debug-copy-btn:active {
    transform: translateY(0);
}

/* Información de tipo */
.ikg-debug-type-info {
    padding: 12px 16px;
    background: #e7f3ff;
    border-bottom: 1px solid #b8daff;
    font-size: 14px;
    color: #004085;
    flex-shrink: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ikg-type-badge {
    display: inline-block;
    padding: 2px 8px;
    background: #004085;
    color: white;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.ikg-debug-type-info code {
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 13px;
}

/* Área de contenido */
.ikg-debug-content-area {
    flex: 1;
    overflow: auto;
    padding: 0;
}

.ikg-debug-pre {
    margin: 0;
    padding: 16px;
    font-size: 14px;
    line-height: 1.6;
    height: 100%;
    overflow: auto;
}

/* Tema oscuro */
.ikg-theme-dark {
    background: #1e1e1e;
    color: #d4d4d4;
}

.ikg-theme-dark .ikg-string {
    color: #ce9178;
}

.ikg-theme-dark .ikg-number {
    color: #b5cea8;
}

.ikg-theme-dark .ikg-keyword {
    color: #569cd6;
    font-weight: 600;
}

.ikg-theme-dark .ikg-array-key {
    color: #9cdcfe;
    font-weight: 600;
}

/* Tema claro */
.ikg-theme-light {
    background: #ffffff;
    color: #1a1a1a;
    border-left: 1px solid #e0e0e0;
}

.ikg-theme-light .ikg-string {
    color: #a31515;
}

.ikg-theme-light .ikg-number {
    color: #098658;
}

.ikg-theme-light .ikg-keyword {
    color: #0000ff;
    font-weight: 600;
}

.ikg-theme-light .ikg-array-key {
    color: #001080;
    font-weight: 600;
}

/* Código */
.ikg-debug-code {
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
    display: block;
}

/* Highlight de búsqueda */
.ikg-highlight {
    background-color: #ffc107;
    color: #000;
    padding: 2px 0;
    font-weight: bold;
    border-radius: 2px;
}

/* Scrollbar personalizado para tema oscuro */
.ikg-theme-dark::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.ikg-theme-dark::-webkit-scrollbar-track {
    background: #252526;
}

.ikg-theme-dark::-webkit-scrollbar-thumb {
    background: #424242;
    border-radius: 6px;
}

.ikg-theme-dark::-webkit-scrollbar-thumb:hover {
    background: #4e4e4e;
}

/* Scrollbar para tema claro */
.ikg-theme-light::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.ikg-theme-light::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.ikg-theme-light::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 6px;
}

.ikg-theme-light::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* ========================================== */
/* ESTILOS PARA COMPARACIÓN */
/* ========================================== */

.ikg-compare-content {
    padding: 0 !important;
}

.ikg-compare-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
    padding: 16px;
    height: 100%;
    overflow: auto;
}

.ikg-compare-item {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    display: flex;
    flex-direction: column;
    min-height: 0;
}

.ikg-compare-title {
    margin: 0 0 8px 0;
    color: #495057;
    font-size: 16px;
    font-weight: 600;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ikg-compare-type {
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 12px;
    padding: 4px 8px;
    background: #e9ecef;
    border-radius: 3px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.ikg-compare-pre {
    margin: 0;
    font-size: 13px;
    background: #1e1e1e;
    color: #d4d4d4;
    padding: 12px;
    border-radius: 4px;
    overflow: auto;
    flex: 1;
    font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', 'Consolas', monospace;
    line-height: 1.5;
}

/* Responsive para comparación */
@media (max-width: 768px) {
    .ikg-compare-grid {
        grid-template-columns: 1fr;
    }
}

/* ========================================== */
/* ESTILOS PARA DUMP MÚLTIPLE CON TABS */
/* ========================================== */

.ikg-multi-debug {
    padding: 0 !important;
}

.ikg-multi-wrapper {
    display: flex;
    flex-direction: column;
    height: 100%;
}

.ikg-tabs-header {
    display: flex;
    gap: 4px;
    padding: 8px 8px 0 8px;
    background: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    overflow-x: auto;
    flex-shrink: 0;
}

.ikg-tabs-header::-webkit-scrollbar {
    height: 6px;
}

.ikg-tabs-header::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.ikg-tabs-header::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.ikg-tab-btn {
    padding: 10px 20px;
    background: #e9ecef;
    border: none;
    border-radius: 6px 6px 0 0;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: #495057;
    transition: all 0.2s;
    white-space: nowrap;
    border-bottom: 2px solid transparent;
}

.ikg-tab-btn:hover {
    background: #dee2e6;
    color: #212529;
}

.ikg-tab-btn.ikg-tab-active {
    background: #ffffff;
    color: #667eea;
    font-weight: 600;
    border-bottom: 2px solid #667eea;
}

.ikg-tabs-container {
    flex: 1;
    overflow: hidden;
    background: #ffffff;
}

.ikg-tab-content {
    height: 100%;
    display: flex;
    flex-direction: column;
}

.ikg-tab-content .ikg-debug-type-info {
    flex-shrink: 0;
}

.ikg-tab-content .ikg-debug-toolbar {
    flex-shrink: 0;
}

.ikg-tab-content .ikg-debug-pre {
    flex: 1;
    overflow: auto;
    margin: 0;
}
</style>
CSS;
    }
    
    /**
     * Retorna solo el JavaScript
     */
    public static function getJS() {
        return <<<'JAVASCRIPT'
<script>
/* ========================================== */
/* IKIGAI DEBUGGER - JAVASCRIPT */
/* ========================================== */

if (typeof window.IkigaiDebugger === 'undefined') {
    window.IkigaiDebugger = {
        /**
         * Función de búsqueda en tiempo real
         */
        search: function(input) {
            var searchText = input.value.toLowerCase();
            var wrapper = input.closest('.ikg-debug-wrapper');
            var codeElement = wrapper.querySelector('.ikg-debug-code');
            
            if (!codeElement) return;
            
            // Guardar texto original
            if (!codeElement.dataset.originalText) {
                codeElement.dataset.originalText = codeElement.innerHTML;
            }
            
            var originalText = codeElement.dataset.originalText;
            
            if (searchText === '') {
                codeElement.innerHTML = originalText;
                return;
            }
            
            // Escapar caracteres especiales para regex
            var escapedSearch = searchText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            
            // Buscar y resaltar (ignorando tags HTML)
            var regex = new RegExp('(' + escapedSearch + ')', 'gi');
            
            // Dividir por tags HTML para no afectar el markup
            var parts = originalText.split(/(<[^>]+>)/);
            var result = parts.map(function(part) {
                // Si es un tag HTML, no modificar
                if (part.match(/^<[^>]+>$/)) {
                    return part;
                }
                // Si es texto, aplicar highlight
                return part.replace(regex, '<span class="ikg-highlight">$1</span>');
            }).join('');
            
            codeElement.innerHTML = result;
            
            // Scroll al primer resultado
            var firstHighlight = codeElement.querySelector('.ikg-highlight');
            if (firstHighlight) {
                firstHighlight.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        },
        
        /**
         * Función de copiar al portapapeles
         */
        copy: function(button) {
            var wrapper = button.closest('.ikg-debug-wrapper');
            var codeElement = wrapper.querySelector('.ikg-debug-code');
            
            if (!codeElement) return;
            
            // Obtener texto plano (sin HTML)
            var text = codeElement.textContent || codeElement.innerText;
            
            // Copiar usando API moderna o fallback
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(function() {
                    IkigaiDebugger._showCopyFeedback(button, true);
                }).catch(function(err) {
                    console.error('Error al copiar:', err);
                    IkigaiDebugger._fallbackCopy(text, button);
                });
            } else {
                IkigaiDebugger._fallbackCopy(text, button);
            }
        },
        
        /**
         * Feedback visual al copiar
         */
        _showCopyFeedback: function(button, success) {
            var originalText = button.textContent;
            var originalBg = button.style.background;
            
            if (success) {
                button.textContent = '✓ Copiado!';
                button.style.background = '#28a745';
            } else {
                button.textContent = '✗ Error';
                button.style.background = '#dc3545';
            }
            
            setTimeout(function() {
                button.textContent = originalText;
                button.style.background = originalBg || '#667eea';
            }, 2000);
        },
        
        /**
         * Fallback para navegadores antiguos
         */
        _fallbackCopy: function(text, button) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            textarea.style.top = '0';
            textarea.style.left = '0';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            
            try {
                var successful = document.execCommand('copy');
                IkigaiDebugger._showCopyFeedback(button, successful);
            } catch (err) {
                console.error('Fallback copy failed:', err);
                IkigaiDebugger._showCopyFeedback(button, false);
            }
            
            document.body.removeChild(textarea);
        }
    };
}
</script>
JAVASCRIPT;
    }
    
    /**
     * Reinicia el estado (útil para testing)
     */
    public static function reset() {
        self::$instanceCount = 0;
        self::$assetsLoaded = false;
    }
    
    /**
     * Configura opciones globales
     */
    public static function configure($options) {
        self::$defaultConfig = array_merge(self::$defaultConfig, $options);
    }
}

// ========================================
// FUNCIONES HELPER GLOBALES (OPCIONALES)
// ========================================

/**
 * Dump and die - Atajo global
 */
if (!function_exists('ikg_dd')) {
    function ikg_dd($data, $title = null, $options = []) {
        IkigaiDebugger::dd($data, $title, $options);
    }
}

/**
 * Dump - Atajo global
 */
if (!function_exists('ikg_d')) {
    function ikg_d($data, $title = null, $options = []) {
        IkigaiDebugger::d($data, $title, $options);
    }
}

/**
 * Dump de sesión - Atajo global
 */
if (!function_exists('ikg_session')) {
    function ikg_session($options = []) {
        IkigaiDebugger::session($options);
    }
}

/**
 * Dump de globals - Atajo global
 */
if (!function_exists('ikg_globals')) {
    function ikg_globals($filter = null, $options = []) {
        IkigaiDebugger::globals($filter, $options);
    }
}