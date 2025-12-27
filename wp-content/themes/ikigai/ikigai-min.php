<?php
/**
 * ikigai-min.php
 * Cargador inteligente de recursos por ID de página o Global con soporte de dependencias.
 * 
 * Uso: 
 * - ikigai-min.php?t=css&i=123 (Página específica)
 * - ikigai-min.php?t=css (Global)
 * 
 * Nuevo: Sistema de dependencias
 * - Si en la carpeta de un módulo existe un archivo dependencies.txt,
 *   se cargarán automáticamente los módulos listados en él (uno por línea)
 * - Evita duplicados automáticamente
 * - Carga las dependencias antes del módulo que las requiere
 * 
 * @version 2.0 - Con sistema de dependencias
 */

define('ENABLE_COMPRESSION', false);

// 1. Cargar el entorno de WordPress
$wp_load_path = __DIR__ . '/../../../wp-load.php'; 
if (file_exists($wp_load_path)) {
    require_once($wp_load_path);
} else {
    exit('WordPress no encontrado');
}

// Incluir funciones
require_once(get_template_directory() . '/ikigai-functions.php');

// 2. Parámetros
$type    = isset($_GET['t']) ? $_GET['t'] : 'css';
$post_id = isset($_GET['i']) ? intval($_GET['i']) : 0;

if ($type !== 'css' && $type !== 'js') exit;

$main_dir    = get_template_directory() . '/' . $type;
$moduls_dir  = get_template_directory() . '/moduls';
$extension   = '.' . $type;

// Arrays para control de archivos y módulos cargados
$files_to_load = [];
$loaded_modules = []; // Para evitar duplicados

// Array global para detectar dependencias circulares
$circular_dependency_chain = [];
$circular_errors = [];

/**
 * Función recursiva para cargar dependencias de un módulo con detección de circularidad
 * 
 * @param string $module_name Nombre del módulo
 * @param string $moduls_dir Ruta al directorio de módulos
 * @param string $type Tipo de archivo (css o js)
 * @param array &$loaded_modules Array de módulos ya cargados (por referencia)
 * @param array &$files_to_load Array de archivos a cargar (por referencia)
 * @param array &$circular_chain Cadena actual de dependencias para detectar ciclos
 * @param array &$circular_errors Array de errores circulares detectados
 * @return bool True si se cargó correctamente, false si se detectó circularidad
 */
function load_module_with_dependencies($module_name, $moduls_dir, $type, &$loaded_modules, &$files_to_load, &$circular_chain = [], &$circular_errors = []) {
    // Si el módulo ya fue cargado, no hacer nada (no es error)
    if (in_array($module_name, $loaded_modules)) {
        return true;
    }
    
    // DETECCIÓN DE CIRCULARIDAD: Si el módulo está en la cadena actual, hay un ciclo
    if (in_array($module_name, $circular_chain)) {
        // Encontrar dónde empieza el ciclo
        $cycle_start = array_search($module_name, $circular_chain);
        $cycle = array_slice($circular_chain, $cycle_start);
        $cycle[] = $module_name; // Cerrar el ciclo
        
        $error = [
            'module' => $module_name,
            'chain' => $circular_chain,
            'cycle' => $cycle,
            'full_path' => implode(' → ', $cycle)
        ];
        
        $circular_errors[] = $error;
        
        return false;
    }
    
    $module_path = $moduls_dir . '/' . $module_name;
    
    // Verificar que el directorio del módulo existe
    if (!is_dir($module_path)) {
        return true; // No es error, simplemente no existe
    }
    
    // Añadir el módulo actual a la cadena de dependencias
    $circular_chain[] = $module_name;
    
    // Buscar archivo de dependencias
    $dependencies_file = $module_path . '/dependencies.txt';
    
    if (file_exists($dependencies_file)) {
        // Leer dependencias
        $dependencies_content = file_get_contents($dependencies_file);
        $dependencies = array_filter(array_map('trim', explode("\n", $dependencies_content)));
        
        // Cargar cada dependencia recursivamente (primero las dependencias)
        foreach ($dependencies as $dependency) {
            // Ignorar líneas vacías y comentarios
            if (empty($dependency) || strpos($dependency, '#') === 0 || strpos($dependency, '//') === 0) {
                continue;
            }
            
            // Llamada recursiva con la cadena actual
            load_module_with_dependencies($dependency, $moduls_dir, $type, $loaded_modules, $files_to_load, $circular_chain, $circular_errors);
        }
    }
    
    // Quitar el módulo actual de la cadena (backtracking)
    array_pop($circular_chain);
    
    // Ahora cargar el módulo actual (después de sus dependencias)
    $filename = ($type === 'js') ? 'codi.js' : 'estils.css';
    $module_file = $module_path . '/' . $filename;
    
    if (file_exists($module_file)) {
        $files_to_load[] = $module_file;
        $loaded_modules[] = $module_name;
    }
    
    return true;
}

// --- A. BASE (Siempre primero) ---
$base_file = $main_dir . '/base' . $extension;
if (file_exists($base_file)) {
    $files_to_load[] = $base_file;
}

// --- B. HEADER Y FOOTER (Siempre se cargan) ---
if ($type === 'css') {
    if (file_exists($moduls_dir . '/header/estils.css')) {
        $files_to_load[] = $moduls_dir . '/header/estils.css';
        $loaded_modules[] = 'header';
    }
    if (file_exists($moduls_dir . '/footer/estils.css')) {
        $files_to_load[] = $moduls_dir . '/footer/estils.css';
        $loaded_modules[] = 'footer';
    }
} else {
    if (file_exists($moduls_dir . '/header/codi.js')) {
        $files_to_load[] = $moduls_dir . '/header/codi.js';
        $loaded_modules[] = 'header';
    }
    if (file_exists($moduls_dir . '/footer/codi.js')) {
        $files_to_load[] = $moduls_dir . '/footer/codi.js';
        $loaded_modules[] = 'footer';
    }
}

// --- C. CARPETA CSS/JS PRINCIPAL (Alfabético, excluyendo base y last) ---
if (is_dir($main_dir)) {
    $main_files = glob($main_dir . '/*' . $extension);
    sort($main_files);
    foreach ($main_files as $file) {
        $name = basename($file);
        if ($name !== 'base' . $extension && 
            $name !== 'last' . $extension && 
            $name !== 'debug' . $extension) {
            $files_to_load[] = $file;
        }
    }
}

// --- D. MÓDULOS CON DEPENDENCIAS (Lógica Condicional) ---
if ($post_id > 0) {
    // MODO INTELIGENTE: Solo los módulos del post/página
    global $post;
    $post = get_post($post_id);
    setup_postdata($post);

    $active_modules = ikg_get_acf_modules();

    if (!empty($active_modules) && is_array($active_modules)) {
        foreach ($active_modules as $module_name) {
            // Cargar módulo con sus dependencias (recursivo)
            load_module_with_dependencies($module_name, $moduls_dir, $type, $loaded_modules, $files_to_load, $circular_dependency_chain, $circular_errors);
        }
    }
    
    wp_reset_postdata();
    
} else {
    // MODO GLOBAL: Cargar todos los módulos
    if (is_dir($moduls_dir)) {
        $module_dirs = glob($moduls_dir . '/*', GLOB_ONLYDIR);
        sort($module_dirs);
        
        foreach ($module_dirs as $module_dir) {
            $module_name = basename($module_dir);
            
            // Saltar header y footer (ya se cargaron antes)
            if ($module_name === 'header' || $module_name === 'footer') {
                continue;
            }
            
            // Cargar módulo con sus dependencias
            load_module_with_dependencies($module_name, $moduls_dir, $type, $loaded_modules, $files_to_load, $circular_dependency_chain, $circular_errors);
        }
    }
}

// --- F. GENERAR AVISOS DE ERRORES CIRCULARES ---
if (!empty($circular_errors)) {
    $error_output = "";
    
    if ($type === 'css') {
        $error_output .= "/* ============================================\n";
        $error_output .= "   ⚠️ ERRORES DETECTADOS: DEPENDENCIAS CIRCULARES\n";
        $error_output .= "   ============================================\n\n";
        
        foreach ($circular_errors as $i => $error) {
            $error_output .= "   ERROR #" . ($i + 1) . ":\n";
            $error_output .= "   Módulo: " . $error['module'] . "\n";
            $error_output .= "   Ciclo detectado: " . $error['full_path'] . "\n\n";
            $error_output .= "   Cómo solucionarlo:\n";
            $error_output .= "   1. Revisa el archivo dependencies.txt en cada módulo del ciclo\n";
            $error_output .= "   2. Crea un módulo base del que ambos puedan depender\n";
            $error_output .= "   3. Elimina la referencia circular\n\n";
            $error_output .= "   Módulos involucrados:\n";
            foreach ($error['cycle'] as $mod) {
                $error_output .= "   - " . $mod . "\n";
            }
            $error_output .= "\n   " . str_repeat('-', 70) . "\n\n";
        }
        
        $error_output .= "   ============================================ */\n\n";
    } else {
        $error_output .= "// ============================================\n";
        $error_output .= "// ⚠️ ERRORES DETECTADOS: DEPENDENCIAS CIRCULARES\n";
        $error_output .= "// ============================================\n\n";
        
        foreach ($circular_errors as $i => $error) {
            $error_output .= "// ERROR #" . ($i + 1) . ":\n";
            $error_output .= "// Módulo: " . $error['module'] . "\n";
            $error_output .= "// Ciclo detectado: " . $error['full_path'] . "\n\n";
            $error_output .= "// Cómo solucionarlo:\n";
            $error_output .= "// 1. Revisa el archivo dependencies.txt en cada módulo del ciclo\n";
            $error_output .= "// 2. Crea un módulo base del que ambos puedan depender\n";
            $error_output .= "// 3. Elimina la referencia circular\n\n";
            $error_output .= "// Módulos involucrados:\n";
            foreach ($error['cycle'] as $mod) {
                $error_output .= "// - " . $mod . "\n";
            }
            $error_output .= "\n// " . str_repeat('-', 70) . "\n\n";
        }
        
        $error_output .= "// ============================================\n\n";
        
        // Añadir también alerta en consola para JavaScript
        $error_output .= "console.error('⚠️ DEPENDENCIAS CIRCULARES DETECTADAS');\n";
        $error_output .= "console.group('Errores de dependencias circulares');\n";
        foreach ($circular_errors as $i => $error) {
            $error_output .= "console.error('Error #" . ($i + 1) . ": " . $error['full_path'] . "');\n";
        }
        $error_output .= "console.groupEnd();\n\n";
    }
    
    $final_content = $error_output . $final_content;
    
    // También escribir en el log de errores de WordPress
    if (function_exists('error_log')) {
        error_log('[Ikigai Loader] Dependencias circulares detectadas: ' . json_encode($circular_errors));
    }
}

// --- E. LAST (Al final de todo) ---
$last_file = $main_dir . '/last' . $extension;
if (file_exists($last_file)) {
    $files_to_load[] = $last_file;
}

// --- 3. CONCATENACIÓN Y MINIFICACIÓN ---
$final_content = "";

// Añadir comentario informativo al inicio
if ($type === 'css') {
    $final_content .= "/* ============================================\n";
    $final_content .= "   Ikigai Dynamic Loader v2.0\n";
    $final_content .= "   Type: CSS\n";
    $final_content .= "   Mode: " . ($post_id > 0 ? "Specific Page (ID: $post_id)" : "Global") . "\n";
    $final_content .= "   Files loaded: " . count($files_to_load) . "\n";
    $final_content .= "   Generated: " . date('Y-m-d H:i:s') . "\n";
    $final_content .= "   ============================================ */\n\n";
} else {
    $final_content .= "// ============================================\n";
    $final_content .= "// Ikigai Dynamic Loader v2.0\n";
    $final_content .= "// Type: JavaScript\n";
    $final_content .= "// Mode: " . ($post_id > 0 ? "Specific Page (ID: $post_id)" : "Global") . "\n";
    $final_content .= "// Files loaded: " . count($files_to_load) . "\n";
    $final_content .= "// Generated: " . date('Y-m-d H:i:s') . "\n";
    $final_content .= "// ============================================\n\n";
}

if (!ENABLE_COMPRESSION) {
    $final_content .= "\n\n/*\n\n";
    foreach ($files_to_load as $file) {
        $final_content .= ">> $file <<\n";
    }
    $final_content .= "\n\n*/\n\n";
}

foreach ($files_to_load as $file) {
    if ($type === 'css') {
        $final_content .= "\n\n/* ============================================\n";
        $final_content .= "     Source: " . str_replace(get_template_directory(), '', $file) . "\n";
        $final_content .= "   ============================================ */\n\n";
    } else {
        $final_content .= "\n\n// ============================================\n";
        $final_content .= "//   Source: " . str_replace(get_template_directory(), '', $file) . "\n";
        $final_content .= "// ============================================\n\n";
    }
    $final_content .= file_get_contents($file) . "\n";
}

// --- 4. CABECERAS Y COMPRESIÓN ---
if ($type === 'css') {
    header("Content-type: text/css; charset: UTF-8");
    if (ENABLE_COMPRESSION) {
        // Eliminar comentarios
        $final_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $final_content);
        // Eliminar espacios en blanco
        $final_content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    '), '', $final_content);
        // Comprimir selectores y propiedades
        $final_content = preg_replace(array('/\s*([\{\};,>])\s*/', '/\s+([\{])/'), '$1', $final_content);
    }
} else {
    header("Content-type: application/javascript; charset: UTF-8");
    if (ENABLE_COMPRESSION) {
        // Eliminar comentarios de bloque
        $final_content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $final_content);
        // Eliminar comentarios de línea
        $final_content = preg_replace('/^\s*\/\/.*/m', '', $final_content);
        // Comprimir espacios en blanco
        $final_content = str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $final_content);
        $final_content = preg_replace('/\s+/', ' ', $final_content);
    }
}

// Cabeceras de caché (opcional pero recomendado)
header("Cache-Control: public, max-age=3600"); // Cache 1 hora
header("Expires: " . gmdate("D, d M Y H:i:s", time() + 3600) . " GMT");

echo $final_content;