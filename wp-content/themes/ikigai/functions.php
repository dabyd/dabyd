<?php
require_once( __DIR__ . '/ikigai-functions.php' );

function ikigai_cargar_recursos() {
    $minifier_url = get_stylesheet_directory_uri() . '/ikigai-min.php?i=' . get_the_ID() . '&t=';

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
    // El 'true' al final hace que el script se cargue en el footer (mejor para SEO)
    wp_enqueue_script('ikigai-scripts', $minifier_url . 'js');
}

add_action('wp_enqueue_scripts', 'ikigai_cargar_recursos');

function ikigai_configurar_tema() {
    // Esto activa la opción de "Menús" en el panel de Apariencia
    register_nav_menus( array(
        'menu-principal' => 'Menú Principal Ikigai',
        'menu-footer'    => 'Menú del Pie de Página'
    ) );
}

add_action( 'after_setup_theme', 'ikigai_configurar_tema' );

function cc_mime_types($mimes) {
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
}
add_filter('upload_mimes', 'cc_mime_types');

require_once( __DIR__ . '/cpt.php' );