<?php
/**
 * Ikigai Theme - Functions Entry Point
 * 
 * Este archivo carga todos los módulos del core del tema
 */

// ============================================
// CARGAR CORE DEL TEMA
// ============================================

// Clases
require_once __DIR__ . '/admin/classes/class-media.php';
require_once __DIR__ . '/admin/classes/class-videos.php';
require_once __DIR__ . '/admin/classes/class-floating-window.php';
require_once __DIR__ . '/admin/classes/class-error-handler.php';
require_once __DIR__ . '/admin/classes/class-debug-handler.php';

// Funciones
require_once __DIR__ . '/admin/functions/functions-core.php';
require_once __DIR__ . '/admin/functions/functions-form-handler.php';
require_once __DIR__ . '/admin/functions/functions-form-admin.php';

// Custom Post Types
require_once __DIR__ . '/cpt.php';

// ============================================
// CARGAR RECURSOS DEL TEMA
// ============================================

function ikigai_cargar_recursos() {
    $minifier_url = get_stylesheet_directory_uri() . '/admin/functions/functions-minifier.php?i=' . get_the_ID() . '&t=';

    // 1. Preconnect para Google Fonts (Mejora de rendimiento)
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

    // 2. Cargar Google Fonts
    wp_enqueue_style('ikigai-fonts', 'https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,500;1,400&display=swap', array(), null);
    if ( isDevelopedEnvironment() ) {
        wp_enqueue_style('ikigai-fonts-debug', 'https://fonts.googleapis.com/css2?family=Space+Mono:ital,wght@0,400;0,700;1,400;1,700&display=swap', array(), null);
    } 

    // 3. Cargar CSS personalizado (ikigai-styles)
    wp_enqueue_style('ikigai-styles', $minifier_url . 'css');

    // 4. Cargar JS personalizado (ikigai-scripts)
    wp_enqueue_script('ikigai-scripts', $minifier_url . 'js');
    
    // 5. Pasar variables PHP a JavaScript para AJAX
    wp_localize_script('ikigai-scripts', 'ikigaiAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('ikg_ajax_nonce')
    ));
}

add_action('wp_enqueue_scripts', 'ikigai_cargar_recursos');

// ============================================
// CARGAR ESTILOS DE ADMIN
// ============================================

function ikigai_admin_styles() {
    $should_load = false;
    
    // 1. Siempre cargar en admin
    if ( is_admin() ) {
        $should_load = true;
    } 
    // 2. Cargar en front si es desarrollo Y la opción de desactivar QM está activa
    else {
        $is_dev = function_exists('isDevelopedEnvironment') && isDevelopedEnvironment();
        // Nota: ikg_get_option está en functions-core.php que ya está cargado
        $option_active = function_exists('ikg_get_option') && ikg_get_option('desactivar_automaticamente_query_monitor');
        
        if ( $is_dev && $option_active ) {
            $should_load = true;
        }
    }
    
    if ( ! $should_load ) {
        return;
    }

    wp_enqueue_style(
        'ikigai-admin-styles',
        get_template_directory_uri() . '/admin/css/admin.css',
        array(),
        '1.0.0'
    );
}

add_action('admin_enqueue_scripts', 'ikigai_admin_styles');
add_action('wp_enqueue_scripts', 'ikigai_admin_styles');

// ============================================
// CONFIGURACIÓN DEL TEMA
// ============================================

function ikigai_configurar_tema() {
    register_nav_menus( array(
        'menu-principal' => 'Menú Principal Ikigai',
        'menu-footer'    => 'Menú del Pie de Página'
    ) );
}

add_action( 'after_setup_theme', 'ikigai_configurar_tema' );

// ============================================
// FILTROS
// ============================================

function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');
