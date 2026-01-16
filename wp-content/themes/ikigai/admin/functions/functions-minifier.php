<?php
/**
 * ikigai-min.php v2.5 - Compresión avanzada de selectores y espacios
 */

// --- 1. CONFIGURACIÓN BÁSICA ---
// Obtener la ruta del tema (2 niveles arriba desde admin/functions/)
$THEME_DIR = dirname(dirname(__DIR__));

define('CACHE_DIR', $THEME_DIR . '/cache-ikigai');
define('MODULS_DIR', $THEME_DIR . '/moduls');
define('ASSETS_DIR', $THEME_DIR . '/assets');

$version = isset($_GET['v']) ? $_GET['v'] : '1.0'; 
$clear_cache = isset($_GET['clear_cache']);
$type    = isset($_GET['t']) ? $_GET['t'] : 'css';
$post_id = isset($_GET['i']) ? intval($_GET['i']) : 0;

if (!is_dir(CACHE_DIR)) { mkdir(CACHE_DIR, 0755, true); }

if ($clear_cache) {
    $files = glob(CACHE_DIR . '/*');
    foreach ($files as $file) { if (is_file($file)) unlink($file); }
    if (!isset($_GET['t'])) exit('Caché borrada correctamente.');
}

if ($type !== 'css' && $type !== 'js') exit;

// --- 2. CARGAR ENTORNO ---
$wp_load_path = $THEME_DIR . '/../../../wp-load.php'; 
if (file_exists($wp_load_path)) { require_once($wp_load_path); } else { exit('WordPress no encontrado'); }

require_once($THEME_DIR . '/admin/functions/functions-core.php');

// --- 3. CONFIGURACIÓN DESDE OPTIONS ---
$ENABLE_COMPRESSION = (bool) ikg_get_option('comprimir_css_js');
$CACHE_ENABLED = (bool) ikg_get_option('activar_cache_css_js');

$cache_filename = "cache_{$post_id}_{$type}_v" . str_replace('.', '-', $version) . ".{$type}";
$cache_path = CACHE_DIR . '/' . $cache_filename;

if ($CACHE_ENABLED && file_exists($cache_path) && !$clear_cache) {
    header("Content-type: " . ($type === 'css' ? 'text/css' : 'application/javascript') . "; charset: UTF-8");
    header("X-Cache: HIT - Ikigai Cache");
    echo file_get_contents($cache_path);
    exit;
}

// Usar la nueva estructura de assets
$main_dir    = ASSETS_DIR . '/' . $type;
$extension   = '.' . $type;

$files_to_load = []; 
$loaded_modules = []; 

function add_file_to_load($path, &$files_to_load) {
    if (file_exists($path) && !in_array($path, $files_to_load)) {
        $files_to_load[] = $path;
        return true;
    }
    return false;
}

function load_module_with_dependencies($module_name, $moduls_dir, $type, &$loaded_modules, &$files_to_load, &$circular_chain = []) {
    if (in_array($module_name, $loaded_modules)) return true;
    if (in_array($module_name, $circular_chain)) return false; 
    
    $module_path = $moduls_dir . '/' . $module_name;
    if (!is_dir($module_path)) return true;
    
    $circular_chain[] = $module_name;
    $dependencies_file = $module_path . '/dependencies.txt';
    
    if (file_exists($dependencies_file)) {
        $dependencies = array_filter(array_map('trim', explode("\n", file_get_contents($dependencies_file))));
        foreach ($dependencies as $dependency) {
            if (empty($dependency) || strpos($dependency, '#') === 0) continue;
            load_module_with_dependencies($dependency, $moduls_dir, $type, $loaded_modules, $files_to_load, $circular_chain);
        }
    }
    
    array_pop($circular_chain);
    $filename = ($type === 'js') ? 'codi.js' : 'estils.css';
    $module_file = $module_path . '/' . $filename;
    
    if (add_file_to_load($module_file, $files_to_load)) {
        $loaded_modules[] = $module_name;
    }
    return true;
}

// --- 3. RECOLECCIÓN ---
$first_order = ['reset', 'base', 'debug'];
foreach ($first_order as $file) {
    add_file_to_load($main_dir . '/' . $file . $extension, $files_to_load);
}

$hf_filename = ($type === 'js') ? 'codi.js' : 'estils.css';
add_file_to_load(MODULS_DIR . "/header/$hf_filename", $files_to_load);

if (is_dir($main_dir)) {
    $main_files = glob($main_dir . '/*' . $extension);
    sort($main_files);
    foreach ($main_files as $file) {
        $name = basename($file);
        if (!in_array($name, ['base'.$extension, 'last'.$extension, 'debug'.$extension, 'reset'.$extension])) {
            add_file_to_load($file, $files_to_load);
        }
    }
    
    // Cargar variantes si existen (solo CSS)
    $variants_dir = $main_dir . '/variants';
    if ($type === 'css' && is_dir($variants_dir)) {
        $variant_files = glob($variants_dir . '/*' . $extension);
        if ($variant_files) {
            sort($variant_files);
            foreach ($variant_files as $file) {
                add_file_to_load($file, $files_to_load);
            }
        }
    }
}

if ($post_id > 0) {
    $active_modules = ikg_get_acf_modules($post_id);
    if (!empty($active_modules)) {
        foreach ($active_modules as $module_name) {
            load_module_with_dependencies($module_name, MODULS_DIR, $type, $loaded_modules, $files_to_load);
        }
    }
} else {
    $module_dirs = glob(MODULS_DIR . '/*', GLOB_ONLYDIR);
    foreach ($module_dirs as $module_dir) {
        $module_name = basename($module_dir);
        if ($module_name === 'header' || $module_name === 'footer') continue;
        load_module_with_dependencies($module_name, MODULS_DIR, $type, $loaded_modules, $files_to_load);
    }
}

add_file_to_load(MODULS_DIR . "/footer/$hf_filename", $files_to_load);
add_file_to_load($main_dir . '/last' . $extension, $files_to_load);

// --- 4. GENERACIÓN DE SALIDA ---

$header_comment = ($type === 'css') ? "/*\n" : "/*\n";
$header_comment .= "   Cache: MISS | Files: " . count($files_to_load) . " | Generated: " . date('Y-m-d H:i:s') . "\n";
$header_comment .= "   --------------------------------------------------------------------------\n";
$header_comment .= "   LISTADO DE ARCHIVOS (ORDEN DE CARGA):\n";

foreach ($files_to_load as $index => $file) {
    $header_comment .= "   " . ($index + 1) . ". " . $file . "\n";
}

$header_comment .= ($type === 'css') ? "*/\n" : "*/\n";

$final_content = $header_comment;

foreach ($files_to_load as $file) {
    if ($type === 'css') {
        $final_content .= "\n\n/* ============================================\n";
        $final_content .= "     ARCHIVO: " . $file . "\n";
        $final_content .= "   ============================================ */\n\n";
    } else {
        $final_content .= "\n\n/* ============================================\n";
        $final_content .= "     ARCHIVO: " . $file . "\n";
        $final_content .= "   ============================================ */\n\n";
    }
    $final_content .= file_get_contents($file);
}

// --- 5. MINIFICACIÓN Y LIMPIEZA DE ESPACIOS ---
if ($ENABLE_COMPRESSION) {
    if ($type === 'css') {
        // Eliminar comentarios de bloque
        $final_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $final_content);
        
        // Eliminar saltos de línea, tabulaciones y espacios múltiples
        $final_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    '), '', $final_content);
        
        // Eliminar espacios alrededor de caracteres especiales: { } ; , >
        $final_content = preg_replace(array('/\s*([\{\};,>])\s*/', '/\s+([\{])/'), '$1', $final_content);
        
        // COMPRESIÓN DE DOS PUNTOS: Eliminar espacios antes y después
        $final_content = str_replace(array(' :', ': '), ':', $final_content);
        
    } else {
        // Minificación básica JS
        $final_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $final_content);
        $final_content = preg_replace('/^\s*\/\/.*/m', '', $final_content);
        $final_content = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $final_content);
        $final_content = preg_replace('/\s+/', ' ', $final_content);
    }
}

if ($CACHE_ENABLED && !empty($final_content)) { file_put_contents($cache_path, $final_content); }

header("Content-type: " . ($type === 'css' ? 'text/css' : 'application/javascript') . "; charset: UTF-8");
echo $final_content;