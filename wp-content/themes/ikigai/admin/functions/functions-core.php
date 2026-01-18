<?php
/**
 * Ikigai Core Functions
 * Funciones principales del tema Ikigai
 */

/**
 * Ikigai Core Functions
 * Funciones principales del tema Ikigai
 */
 
// =============================================================================
// ENTORNO Y ROBOTS
// =============================================================================

if (!function_exists('isDevelopedEnvironment')) {
    function isDevelopedEnvironment() {
        $is = false;
        if ((isset($_SERVER['HTTP_HOST']) && ('.local' == strtolower(substr($_SERVER['HTTP_HOST'], -6)) || '.test' == strtolower(substr($_SERVER['HTTP_HOST'], -5)))) ||
            (isset($_SERVER['HTTP_REFERER']) && (strpos(strtolower($_SERVER['HTTP_REFERER']), '/localhost'))) ||
            (isset($_SERVER['HTTP_REFERER']) && (strpos(strtolower($_SERVER['HTTP_REFERER']), '.developedby.gold')))
        ) {
            $is = true;
        }
        return $is;
    }
}

if (!function_exists('isLocalDevelopedEnvironmet')) {
    function isLocalDevelopedEnvironmet() {
        $is = false;
        if ((isset($_SERVER['HTTP_HOST']) && ('.local' == strtolower(substr($_SERVER['HTTP_HOST'], -6)) || '.test' == strtolower(substr($_SERVER['HTTP_HOST'], -5)))) ||
            (isset($_SERVER['HTTP_REFERER']) && (strpos(strtolower($_SERVER['HTTP_REFERER']), '/localhost')))
        ) {
            $is = true;
        }
        return $is;
    }
}

function ikg_auto_disable_search_engines() {
    if ( isDevelopedEnvironment() ) {
        // Verifica si la opción ya es 0 para evitar escribir en DB en cada carga
        if ( get_option('blog_public') != '0' ) {
            update_option('blog_public', '0');
        }
    }
}
add_action('init', 'ikg_auto_disable_search_engines');

// Inicializar el error handler
add_action('init', 'ikigai_init_error_handler');
// Encolar el CSS del error handler (debug.css)
add_action('wp_enqueue_scripts', 'ikigai_enqueue_error_handler_styles');
add_action('admin_enqueue_scripts', 'ikigai_enqueue_error_handler_styles');


$__ikg_acf_module = 0;
$__ikg_current_id = 0;
$__ikg_modul_name = '';
$__ikg_base       = '';
$__ikg_values     = [];

$ikg_media_definition = array(
	'breakpoints'			=> array(
		'ikg-img-xs' => array( 480, 0, true, 'petals' ),
		'ikg-img-l'  => array( 1440, 0, true, 'petals' ),
		'ikg-img-xl' => array( 1920, 0, true, 'petals' ),
	)
);
$ikg_media = new IkigaiMediaClass( $ikg_media_definition );
$ikg_videos = new IkigaiVideoClass();

// Restauración de Sistema
require_once get_template_directory() . '/admin/classes/class-restore.php';
$ikg_restore = new IkigaiRestore();



function ikg_get_text( $id, $from_options = false ) {
    return  nl2br( ikg_get_acf_value( $id, $from_options ) );
}

function ikg_get_lista( $lista, $item, $from_options = false ) {
	$total = ikg_get_acf_value( $lista );
    $ret = '';
	for( $n = 0; $n < $total; $n++ ):

        $ret .= '<li>' . ikg_get_acf_value( $lista . '_' . $n . '_' . $item,  $from_options ) . '</li>';
	endfor;
    return $ret;
}

function ikg_get_text_to_p_raw( $id, $class = '', $from_options = false ) {
	$text = ikg_get_acf_value( $id, $from_options );
    if (empty(trim($text))) {
        return '';
    }
    
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $paragraphs = preg_split('/\n\s*\n/', $text);
    $paragraphs = array_filter(array_map('trim', $paragraphs));
    
    if (empty($paragraphs)) {
        return '';
    }
    
    if ( '' != $class ) {
        $class = ' class="' . $class . '" ';
    }
    $html = '';
    foreach ($paragraphs as $paragraph) {
        $paragraph = preg_replace('/\s+/', ' ', $paragraph);
        $html .= "                <p" . $class . ">\n";
        $html .= "                    " . $paragraph . "\n";
        $html .= "                </p>\n";
    }
    
    return rtrim($html);
}

function ikg_value( $id, $post_id = null, $debug = false ) {
    $tmp = ikg_get_acf_value( $id, false, $debug, $post_id );
    if ( ! ikg_is_html( $tmp ) ) {
        $tmp = str_replace(PHP_EOL, '<br>', $tmp);
    }
    echo $tmp;
}

/**
 * Comprueba si un campo ACF tiene un valor no vacío.
 * 
 * @param string $id El identificador del campo ACF.
 * @param bool $from_options Si se debe buscar en las opciones globales.
 * @return bool True si el campo tiene un valor, false en caso contrario.
 */
function ikg_has_value( $id, $from_options = false ) {
    $value = ikg_get_acf_value( $id, $from_options );
    
    // Comprobar si está vacío (considera cadenas vacías, null, 0, arrays vacíos)
    if ( is_array( $value ) ) {
        return ! empty( $value );
    }
    
    return ( $value !== '' && $value !== null && $value !== false );
}

function ikg_reset() {
    ikg_setbase();
}

function ikg_setbase( $base = '' ) {
	global $__ikg_base;
	$__ikg_base = $base;
}

function ikg_get_option( $id ) {
    $tmp = get_option( 'options_' . $id );
//    if ( is_array( $tmp ) ) {
//        $tmp = $tmp[0];
//    }
    return $tmp;
}

/**
 * Genera el enlace social con SVG basado en el tipo y la configuración
 * * @param string $type facebook|instagram|substack|whatsapp|telegram|mail
 * @return string HTML del enlace o cadena vacía si no hay datos
 */
function ikg_render_social_link($type) {
    // Obtenemos la opción mediante tu función
    $option = ikg_get_option($type);

    // Si no hay nada, no pintamos nada
    if (empty($option)) {
        echo '';
        return;
    }

    $url = '';
    $aria_label = ucfirst($type);
    $target = '_blank';
    $rel = 'noopener noreferrer';
    $default_text = rawurlencode("Quiero más información sobre ");

    // 1. Procesamos la URL según si es Array o String
    if (is_array($option)) {
        $url = $option['url'] ?? '';
        $target = $option['target'] ?? '_blank';
        $aria_label = $option['title'] ?? $aria_label;
    } else {
        // Es un string (teléfono, usuario o mail)
        $clean_val = trim($option);
        $target = '_blank'; // Por defecto lo abro en otra ventana
        
        switch ($type) {
            case 'whatsapp':
                // Limpiamos espacios o símbolos del teléfono
                $phone = preg_replace('/[^0-9]/', '', $clean_val);
                $url = "https://wa.me/{$phone}?text={$default_text}";
                break;
            case 'telegram':
                // Quitamos la @ si el usuario la incluyó
                $user = ltrim($clean_val, '@');
                $url = "https://t.me/{$user}"; 
                // Nota: Telegram no soporta texto predefinido vía URL de forma estándar como WA
                break;
            case 'mail':
                $url = "mailto:{$clean_val}?subject={$default_text}";
                $target = '_self'; // El mail no suele abrirse en blank
                break;
            case 'facebook':
                $url = "https://facebook.com/{$clean_val}";
                break;
            case 'instagram':
                $user = ltrim($clean_val, '@');
                $url = "https://instagram.com/{$user}";
                break;
            case 'substack':
                $user = ltrim($clean_val, '@');
                $url = "https://substack.com/@{$user}";
                break;
        }
    }

    if (empty($url)) return '';

    // 2. Definición de los SVGs
    $svgs = [
        'facebook'  => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>',
        'instagram' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>',
        'substack'  => '<path d="M22.539 8.242H1.46V5.406h21.08v2.836zM1.46 10.812V24L12 18.11 22.54 24V10.812H1.46zM22.54 0H1.46v2.836h21.08V0z"/>',
        'whatsapp'  => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>',
        'telegram'  => '<path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>',
        'mail'      => '<path d="M0 3v18h24V3H0zm6.623 7.929L2.26 6.405A1.01 1.01 0 0 1 2.25 5h19.5c.004 0 .007.001.011.001L17.377 10.93c-1.33 1.163-3.414 1.171-4.755.013l-1.015-.877-1.015.877c-1.34 1.157-3.426 1.15-4.754-.014zM2 19V8.528l5.964 5.253a5.412 5.412 0 0 0 3.447 1.22c1.272 0 2.503-.437 3.465-1.23L22 8.528V19H2z"/>'
    ];

    $path = $svgs[$type] ?? '';

    // 3. Montamos el HTML final
    echo sprintf(
        '<a href="%s" target="%s" rel="%s" aria-label="%s">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                %s
            </svg>
        </a>',
        esc_url($url),
        esc_attr($target),
        esc_attr($rel),
        esc_attr($aria_label),
        $path
    );
    return;
}

/**
 * Formatea un número de teléfono según su código de país
 * 
 * @param string $phone Número de teléfono (puede incluir +, espacios, guiones, etc.)
 * @return string Número formateado o el original si no se reconoce el formato
 */
function ikg_format_phone_number( $phone ) {
    // Limpiar el número: eliminar espacios, guiones, paréntesis, puntos
    $clean = preg_replace('/[^0-9+]/', '', $phone);
    
    // Si está vacío, devolver cadena vacía
    if ( empty($clean) ) {
        return '';
    }
    
    // Detectar y formatear según el código de país
    
    // España (+34) - 9 dígitos
    if ( preg_match('/^\+?34(\d{9})$/', $clean, $matches) ) {
        return '+34 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // España sin prefijo - asumir que es +34
    if ( preg_match('/^(\d{9})$/', $clean, $matches) ) {
        return '+34 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // Estados Unidos / Canadá (+1) - 10 dígitos
    if ( preg_match('/^\+?1(\d{10})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+1 (' . substr($num, 0, 3) . ') ' . substr($num, 3, 3) . '-' . substr($num, 6);
    }
    
    // Reino Unido (+44) - 10 dígitos
    if ( preg_match('/^\+?44(\d{10})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+44 ' . substr($num, 0, 4) . ' ' . substr($num, 4, 3) . ' ' . substr($num, 7);
    }
    
    // Francia (+33) - 9 dígitos
    if ( preg_match('/^\+?33(\d{9})$/', $clean, $matches) ) {
        return '+33 ' . chunk_split($matches[1], 2, ' ');
    }
    
    // Alemania (+49) - Variable, pero común 10-11 dígitos
    if ( preg_match('/^\+?49(\d{10,11})$/', $clean, $matches) ) {
        return '+49 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // Italia (+39) - 10 dígitos
    if ( preg_match('/^\+?39(\d{10})$/', $clean, $matches) ) {
        return '+39 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // México (+52) - 10 dígitos
    if ( preg_match('/^\+?52(\d{10})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+52 ' . substr($num, 0, 3) . ' ' . substr($num, 3, 3) . ' ' . substr($num, 6);
    }
    
    // Argentina (+54) - 10 dígitos
    if ( preg_match('/^\+?54(\d{10})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+54 9 ' . substr($num, 0, 3) . ' ' . substr($num, 3, 4) . '-' . substr($num, 7);
    }
    
    // Colombia (+57) - 10 dígitos
    if ( preg_match('/^\+?57(\d{10})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+57 ' . substr($num, 0, 3) . ' ' . substr($num, 3, 3) . ' ' . substr($num, 6);
    }
    
    // Chile (+56) - 9 dígitos
    if ( preg_match('/^\+?56(\d{9})$/', $clean, $matches) ) {
        return '+56 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // Perú (+51) - 9 dígitos
    if ( preg_match('/^\+?51(\d{9})$/', $clean, $matches) ) {
        return '+51 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // Brasil (+55) - 11 dígitos
    if ( preg_match('/^\+?55(\d{11})$/', $clean, $matches) ) {
        $num = $matches[1];
        return '+55 (' . substr($num, 0, 2) . ') ' . substr($num, 2, 5) . '-' . substr($num, 7);
    }
    
    // Portugal (+351) - 9 dígitos
    if ( preg_match('/^\+?351(\d{9})$/', $clean, $matches) ) {
        return '+351 ' . chunk_split($matches[1], 3, ' ');
    }
    
    // Formato genérico para otros países
    // Si tiene +XX al inicio, formatear con espacios cada 3 dígitos
    if ( preg_match('/^\+(\d{1,3})(\d+)$/', $clean, $matches) ) {
        return '+' . $matches[1] . ' ' . chunk_split($matches[2], 3, ' ');
    }
    
    // Si no coincide con ningún patrón, devolver el número limpio con +
    if ( strpos($clean, '+') === 0 ) {
        return $clean;
    }
    
    // Si no tiene +, asumir España (+34) por defecto
    return '+34 ' . chunk_split($clean, 3, ' ');
}

function ikg_put_input_with_label( $type, $id, $label, $req, $placeholder, $instrucciones ) {
    echo '<div class="form-group">';
    if ( '' != $label ) {
        echo '<label for="' . $id . '">' . $label . ($req ? ' *' : '') . '</label>';
    }
    if ( 'textarea' == $type ) {
        echo '<textarea id="' . $id . '" name="' . $id . '" ' . ($req ? 'required' : '') . $placeholder .'></textarea>';
    } else {
        echo '<input type="' . $type . '" id="' . $id . '" name="' . $id . '" ' . ($req ? 'required' : '') . $placeholder .'>';
    }
    echo $instrucciones;
    echo '</div>';
}

function ikg_put_option($opt) {
    $opt = explode(' : ', $opt);
    $value = $opt[0];
    $label = $opt[0];
    if (2 == count($opt)) {
        $value = $opt[0];
        $label = $opt[1];
    }
    echo '<option value="' . $value . ' ">' . $label . '</option>';
}

function ikg_get_acf_modules( $post_id = 0 ) {
    if ( 0 == $post_id ) {
        $post_id = get_the_ID();
    }
    $ret = [];
    if ( is_array( get_post_meta( $post_id, 'moduls', true ) ) ) {
        $ret = get_post_meta( $post_id, 'moduls', true );
    }
    return $ret;
}

function ikg_put_buttons() {
    global $__ikg_base;
    $tmp = $__ikg_base;
    $base = $__ikg_base;;
    if ( '' != $base ) {
        $base .= '_';
    }
    $botons = ikg_get_acf_value( 'botons' );
    for( $n = 0; $n < $botons; $n++ ) {
        $key = str_replace( '__', '_', $base . 'botons_' . $n . '_' );
        ikg_setbase( $key );
        $tipus = ikg_get_acf_value( 'tipus' );
        $modificacions = ikg_get_acf_value( 'modificacions' );
        ikg_put_link( 'boto', 'btn ' . $tipus . ' ' . $modificacions ); 
    }
    $__ikg_base = $tmp;
}

function ikg_set_acf_module( $id, $curent_id = 0 ) {
	global $__ikg_acf_module, $__ikg_current_id;
	$__ikg_acf_module = $id;
	if ( 0 == $curent_id ) {
		$curent_id = get_the_ID();
	}
	$__ikg_current_id = $curent_id;
}

function ikg_get_acf_value( $id, $from_options = false, $trace = false, $post_id = null ) {
	global $__ikg_acf_module, $__ikg_current_id, $__ikg_values, $__ikg_base;

    $prefix = 'moduls_' . $__ikg_acf_module . '_';
    if ( is_null( $post_id ) ) { 
		$post_id = get_the_ID();
    } else {
        $prefix = '';
	}
	if (0 == $__ikg_current_id ) {
		$__ikg_current_id = $post_id;
	}
    $value = '';
    $key = '';
    if ( $from_options ) {
        $key = 'options_' . $id;
        $value = get_option( $key );
    } else {
	    $key = $prefix . $__ikg_base . $id;
        if ( isset( $__ikg_values[ $id  ] ) ) {
            $value = $__ikg_values[ $id ];
        } else {
            $value = get_post_meta( $post_id,  $key, true );
        }
    }
	if ( $trace ) {
		echo '<pre>';
        if ( $from_options ) {
            echo '<h3>From options</h3>';
        } else {
            echo '<h3>From metadata</h3>';
    		echo '<b>$__ikg_current_id:</b> ' . $__ikg_current_id . '<br>';
	    	echo '<b>$__ikg_acf_module:</b> ' . $__ikg_acf_module . '<br>';
		    echo '<b>$id:</b> ' . $id . '<br>';
        }
		echo '<h4>key: ' . $key . '</h4>';
		echo '<h4>valor: *' . $value . '*</h4>';
		echo '</pre>';
	}
	
    return $value;
}

function ikg_pinta_pagina_acf() {
	$modules = ikg_get_acf_modules();
    foreach( $modules as $key => $module ) {
        ikg_set_acf_module( $key );
        ikg_load_modul($module);
    }
}

function ikg_load_modul($module) {
    global $__ikg_modul_name;
    $__ikg_modul_name = $module;
    $file = get_template_directory() . '/moduls/' . $module . '/modul.php';
    if (!file_exists($file)) {
        echo '<section class="module-not-found sam-floating-content">';
        echo '<h3>El módulo '. $module . ' no existe en (' . $file . ')</h3>';
        echo '</section>';
    } else {
		ikg_reset();
        ikg_info_module( $module, $file );
        require( $file );
    }
}

function ikg_invoque_module($module, $params) {
    global $__ikg_values;
    $__ikg_values = $params;
    ikg_load_modul($module);
    $__ikg_values = [];
}

function ikg_put_link( $id, $clase ) {
    $link = ikg_get_acf_value( $id );
    if ( ! is_array( $link ) ) {
        $link = array( 
            'title' => 'No title',
            'url' => '#',
            'target' => ''
        );
    }
    if ( !isset( $link['url'] ) ) {
        $link['title'] = 'No title';
    }
    if ( !isset( $link['url'] ) ) {
        $link['url'] = '#';
    }
    if ( !isset( $link['target'] ) ) {
        $link['target'] = '';
    }
    echo '<a class="' . $clase . '" href="' . $link['url'] . '" target="' . $link['target'] . '">' . $link['title'] . '</a>';
}

function ikg_get_current_type_page() {
    // What current page is Category: is_product_category() [true (term) | false (post)]

    $returnType = 'post';
    if ( is_tax() || is_category() ) {
        $returnType = 'term';
    }

    $show_test = false;
    if ( $show_test ) {
        echo '<h1>getCurrentTypePage</h1>';

        $funcs = array(
            'is_404',
            'is_archive',
            'is_attachment',
            'is_category',
            'is_front_page',
            'is_home',
            'is_login',
            'is_page',
            'is_page_template',
            'is_single',
            'is_singular',
            'is_tag',
            'is_tax',
        );
        foreach ( $funcs as $func ) {
            echo 'probando: ' . $func . '<br>';
            $tmp = '$tmp = ' . $func . '();';
            eval( $tmp );
            if ( $tmp ) {
                echo '<h1>' . $func . '</h1>';
            }
        }
        echo '<h3>return: ' . $returnType . '</h3>';
    }

    return $returnType;
}

/**
 * Comprueba si el texto pasado es HTML
 */
function ikg_is_html($string){
    return ($string != strip_tags($string));
}

function ikg_show_all_metas( $id = null, $is_modul = true ) {
    global $__ikg_acf_module, $__ikg_modul_name, $__ikg_values;

    // Solo ejecutamos en entorno de desarrollo
    if ( isDevelopedEnvironment()) {
        $post_id = $id ? $id : get_the_ID();
        $tots = get_post_meta($post_id);
        $final = [];
        $key_prefix = 'moduls_' . $__ikg_acf_module . '_';
        
        foreach( $tots as $k => $value ) {
            if ( $is_modul && str_starts_with( $k, $key_prefix ) ) {
                $final[ str_replace( $key_prefix, '', $k ) ] = $value;
            } else {
                $final[ $k ] = $value;
            }
        }

        foreach( $final as $key => $value ) {
            if ( isset( $__ikg_values[ $key ] ) ) {
                $final[ $key ] = [ 'value' => $__ikg_values[ $key ], 'origin' => '$__ikg_values' ];
            } else {
                $final[ $key ] = [ 'value' => $value, 'origin' => 'postmeta' ];
            }
        }

        // Iniciamos el buffer para capturar el HTML
        ob_start();
        echo '<div class="sam-floating-content">';

        foreach ($final as $key => $row) {
            echo '<div class="meta-item">';
            if ( is_array( $row['value'] ) ) {
                $_tmp = is_serialized( $row['value'][0] ) ? unserialize( $row['value'][0] ) : $row['value'][0];

                if ( is_array( $_tmp ) ) {
                    echo '<h3>' . $key . ' (Array [' . count( $_tmp ) . ']) [origin: ' . $row['origin'] . ']</h3>';
                    echo '<pre>'; 
                    print_r( $_tmp ); 
                    echo '</pre>';
                } else {
                    echo '<h3>' . $key . ' (';
                    if ( ikg_is_html( $_tmp ) ) {
                        echo 'HTML [' . strlen( $_tmp ) . ']';
                        $display_value = htmlspecialchars($_tmp);
                    } else {
                        if ( is_numeric( $_tmp ) ) echo 'Numeric';
                        elseif ( is_string( $_tmp ) ) echo 'String [' . strlen( $_tmp ) . ']';
                        echo ') [origin: ' . $row['origin'] . ']</h3>';
                        $display_value = $_tmp;
                    }
                    echo $display_value;
                }
            }
            echo '</div>';
        }
        echo '</div>';
        $content = ob_get_clean();

        // Creamos e instanciamos la ventana flotante
        $title = "Debug: " . $__ikg_modul_name . " (#" . $__ikg_acf_module . ")";
        $win = new IkigaiFloatingWindow($title, $content, [
            'width' => '50%',
            'height' => '90%',
            'icon' => '🛠️',
            'headerClass' => 'sam-header-custom', // Puedes personalizar el color del header si quieres
        ]);

        $win->show();
    }
}

function ikg_info_module( $module, $file ) {
    $mostrar = ikg_get_option('mostrar_els_moduls');
    if ( WP_DEBUG && $mostrar ) {
        $content = '<div class="ikg-info-module" style="padding-bottom: 40px;">';
        $content .= '<h3>Module: ' . $module .'</h3>';
        $content .= '<h5>Paths: ' . $file .'</h5>';
        $content .= '<button class="fw-btn" style="position: absolute; bottom: 10px; right: 10px; width: auto; padding: 5px 10px; background: #667eea; font-size: 14px;" onclick="this.closest(\'.floating-window\').style.display=\'none\'">Ocultar</button>';
        $content .= '</div>';

        $title = "Info Module: " . $module;
        
        $win = new IkigaiFloatingWindow($title, $content, [
            'width' => '400px',
            'height' => 'auto',
            'icon' => 'ℹ️',
            'headerClass' => 'ikg-info-header',
            'minimizable' => true,
            'visible' => false,
        ]);
        
        $id = $win->getId();
        echo '<div style="cursor: pointer; display: inline-block; margin: 0 5px; float: left;" onclick="document.getElementById(\'' . $id . '\').style.display=\'flex\'">❓</div>';
        
        $win->show();
    }
}

function ikg_get_variant( $tipus = '' ) {    
    ikg_setbase('');

    $clase = '';
    $clase2 = '';
    switch ($tipus) {
        case 'header':
            $clase = ikg_get_acf_value('aspecte_header_aspecte', true );
            $clase2 = ikg_get_acf_value('aspecte_header_espaciat', true );
            break;
        
        case 'footer':
            $clase = ikg_get_acf_value('aspecte_footer_aspecte', true );
            $clase2 = ikg_get_acf_value('aspecte_footer_espaciat', true );
            break;

        default:
            $clase = ikg_get_acf_value('aspecte' );
            $clase2 = ikg_get_acf_value('espaciat' );
            break;
    }
    if ( '-' == $clase ) {
        $clase = '';
    }
    if ( '-' == $clase2 ) {
        $clase2 = '';
    }
    $clase3 = '';
    $mostrar = ikg_get_option('mostrar_els_moduls');
    if ( WP_DEBUG && $mostrar ) {
        $clase3 = 'ikg-show-module';
    }
    echo trim( $clase . ' ' . $clase2 . ' ' . $clase3 );
}

/**
 * Obtiene el número de orden de un post dentro de su mismo post type.
 * * @param int $post_id El ID del post.
 * @return int|bool El número de posición (1-based) o false si no se encuentra.
 */
function ikg_get_post_position($post_id) {
    // 1. Obtener el tipo de post del ID pasado
    $post_type = get_post_type($post_id);
    
    if (!$post_type) return false;

    // 2. Consultar todos los IDs de ese post type
    // Usamos 'fields => ids' para que sea una consulta muy rápida y ligera
    $args = array(
        'post_type'      => $post_type,
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
        'orderby'        => 'date', // Ordenar por fecha como hace WP por defecto
        'order'          => 'ASC',  // De más antiguo a más nuevo
    );

    $all_ids = get_posts($args);

    // 3. Buscar la posición del ID actual en el array de IDs
    $index = array_search($post_id, $all_ids);

    if ($index !== false) {
        // Sumamos 1 porque los arrays en PHP empiezan en 0
        return $index + 1;
    }

    return false;
}


/**
 * AJAX handler para vaciar el cache de CSS/JS
 */
function ikg_ajax_clear_cache() {
    // Verificar nonce de seguridad
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ikg_clear_cache_nonce' ) ) {
        wp_send_json_error( 'Nonce inválido' );
    }
    
    // Verificar permisos
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sin permisos' );
    }
    
    $cache_dir = get_template_directory() . '/cache-ikigai';
    
    if ( ! is_dir( $cache_dir ) ) {
        wp_send_json_success( 'La carpeta de cache no existe o ya está vacía' );
    }
    
    $files = glob( $cache_dir . '/*' );
    $deleted = 0;
    $errors = 0;
    
    foreach ( $files as $file ) {
        if ( is_file( $file ) ) {
            if ( unlink( $file ) ) {
                $deleted++;
            } else {
                $errors++;
            }
        }
    }
    
    if ( $errors > 0 ) {
        wp_send_json_error( "Se han borrado $deleted archivos, pero hubo $errors errores" );
    } else {
        wp_send_json_success( "Cache vaciado con éxito. $deleted archivos eliminados" );
    }
}
add_action( 'wp_ajax_ikg_clear_cache', 'ikg_ajax_clear_cache' );

/**
 * Cargar script para el botón ACF de vaciar cache en admin
 */
/**
 * Enqueue Admin Scripts
 */
function ikg_enqueue_admin_scripts() {
    $should_load = false;
    
    // 1. Siempre cargar en admin
    if ( is_admin() ) {
        $should_load = true;
    } 
    // 2. Cargar en front si es desarrollo Y la opción de desactivar QM está activa
    else {
        $is_dev = function_exists('isDevelopedEnvironment') && isDevelopedEnvironment();
        $option_active = ikg_get_option('desactivar_automaticamente_query_monitor');
        
        if ( $is_dev && $option_active ) {
            $should_load = true;
        }
    }
    
    if ( ! $should_load ) {
        return;
    }

    // Admin JS principal 
    wp_enqueue_script(
        'ikg-admin-js', 
        get_template_directory_uri() . '/admin/js/admin.js', 
        array('jquery'), 
        filemtime(get_template_directory() . '/admin/js/admin.js'), 
        true
    );
    
    // Floating Window JS
    wp_enqueue_script(
        'ikg-floating-window', 
        get_template_directory_uri() . '/admin/js/floating-window.js', 
        array(), 
        filemtime(get_template_directory() . '/admin/js/floating-window.js'), 
        true
    );
    
    // Variables para JS
    wp_localize_script('ikg-admin-js', 'ikgAdmin', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'cacheNonce' => wp_create_nonce('ikg_clear_cache_nonce'),
        'logViewerNonce' => wp_create_nonce('ikg_log_viewer_nonce'),
        'exportNonce' => wp_create_nonce('ikg_export_nonce')
    ));
}
add_action('admin_enqueue_scripts', 'ikg_enqueue_admin_scripts');
add_action('wp_enqueue_scripts', 'ikg_enqueue_admin_scripts');

/**
 * Desactivar Query Monitor automáticamente en producción
 * Solo si la opción 'desactivar_automaticamente_query_monitor' está activada
 */
function ikg_auto_deactivate_query_monitor() {
    // Solo ejecutar en admin y si no estamos en desarrollo
    if ( ! is_admin() ) {
        return;
    }
    
    // Verificar si estamos en entorno de desarrollo
    if ( function_exists( 'isDevelopedEnvironment' ) && isDevelopedEnvironment() ) {
        return; // No hacer nada en desarrollo
    }
    
    // Verificar la opción
    $auto_deactivate = ikg_get_option( 'desactivar_automaticamente_query_monitor' );
    
    if ( ! $auto_deactivate ) {
        return; // La opción no está activada
    }
    
    // Cargar funciones de plugins si no están disponibles
    if ( ! function_exists( 'is_plugin_active' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }
    
    // Lista de plugins de desarrollo a desactivar en producción
    $plugins_to_deactivate = array(
        'query-monitor/query-monitor.php',
        'profiling-tool-for-wp/profiling-tool-for-wp.php',
    );
    
    foreach ( $plugins_to_deactivate as $plugin_file ) {
        if ( is_plugin_active( $plugin_file ) ) {
            deactivate_plugins( $plugin_file );
            
            if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
                error_log( 'Ikigai: ' . $plugin_file . ' desactivado automáticamente en producción' );
            }
        }
    }
}
add_action( 'admin_init', 'ikg_auto_deactivate_query_monitor' );

// =============================================================================
// LOG VIEWER SYSTEM FOR BACKOFFICE
// =============================================================================

/**
 * AJAX: Obtener lista de archivos de log
 */
function ikg_ajax_get_log_files() {
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ikg_log_viewer_nonce' ) ) {
        wp_send_json_error( 'Nonce inválido' );
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sin permisos' );
    }
    
    $type = sanitize_text_field( $_POST['log_type'] ); // 'error', 'debug' o 'accessibility'
    $upload_dir = wp_upload_dir();
    
    switch ($type) {
        case 'error':
            $log_dir = $upload_dir['basedir'] . '/error-logs';
            $pattern = 'errors-*.log';
            break;
        case 'accessibility':
            $log_dir = $upload_dir['basedir'] . '/accessibility-logs';
            $pattern = 'accessibility-*.log';
            break;
        default: // debug
            $log_dir = $upload_dir['basedir'] . '/debug-logs';
            $pattern = 'debug-*.log';
    }
    
    $files = [];
    if ( is_dir( $log_dir ) ) {
        $log_files = glob( $log_dir . '/' . $pattern );
        foreach ( $log_files as $file ) {
            $files[] = [
                'name' => basename( $file ),
                'path' => $file,
                'size' => size_format( filesize( $file ) ),
                'date' => date( 'Y-m-d H:i:s', filemtime( $file ) )
            ];
        }
        // Ordenar por fecha descendente
        usort( $files, function( $a, $b ) {
            return strcmp( $b['date'], $a['date'] );
        });
    }
    
    wp_send_json_success( $files );
}
add_action( 'wp_ajax_ikg_get_log_files', 'ikg_ajax_get_log_files' );

/**
 * AJAX: Obtener contenido de un archivo de log
 */
function ikg_ajax_get_log_content() {
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ikg_log_viewer_nonce' ) ) {
        wp_send_json_error( 'Nonce inválido' );
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sin permisos' );
    }
    
    $filename = sanitize_text_field( $_POST['filename'] );
    $type = sanitize_text_field( $_POST['log_type'] );
    $upload_dir = wp_upload_dir();
    
    switch ($type) {
        case 'error':
            $log_dir = $upload_dir['basedir'] . '/error-logs';
            break;
        case 'accessibility':
            $log_dir = $upload_dir['basedir'] . '/accessibility-logs';
            break;
        default: // debug
            $log_dir = $upload_dir['basedir'] . '/debug-logs';
    }
    
    $filepath = $log_dir . '/' . $filename;
    
    // Verificar que el archivo existe y está dentro del directorio permitido
    if ( ! file_exists( $filepath ) || strpos( realpath( $filepath ), realpath( $log_dir ) ) !== 0 ) {
        wp_send_json_error( 'Archivo no encontrado' );
    }
    
    $content = file_get_contents( $filepath );
    
    wp_send_json_success( [
        'content' => $content,
        'size' => size_format( filesize( $filepath ) ),
        'lines' => substr_count( $content, "\n" )
    ]);
}
add_action( 'wp_ajax_ikg_get_log_content', 'ikg_ajax_get_log_content' );

/**
 * AJAX: Borrar archivos de log
 */
function ikg_ajax_delete_logs() {
    if ( ! wp_verify_nonce( $_POST['nonce'], 'ikg_log_viewer_nonce' ) ) {
        wp_send_json_error( 'Nonce inválido' );
    }
    
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( 'Sin permisos' );
    }
    
    $type = sanitize_text_field( $_POST['log_type'] );
    $filename = isset( $_POST['filename'] ) ? sanitize_text_field( $_POST['filename'] ) : '';
    $upload_dir = wp_upload_dir();
    
    switch ($type) {
        case 'error':
            $log_dir = $upload_dir['basedir'] . '/error-logs';
            $pattern = 'errors-*.log';
            break;
        case 'accessibility':
            $log_dir = $upload_dir['basedir'] . '/accessibility-logs';
            $pattern = 'accessibility-*.log';
            break;
        default: // debug
            $log_dir = $upload_dir['basedir'] . '/debug-logs';
            $pattern = 'debug-*.log';
    }
    
    $deleted = 0;
    $errors = 0;
    
    if ( ! empty( $filename ) ) {
        // Borrar archivo específico
        $filepath = $log_dir . '/' . $filename;
        if ( file_exists( $filepath ) && strpos( realpath( $filepath ), realpath( $log_dir ) ) === 0 ) {
            if ( unlink( $filepath ) ) {
                $deleted = 1;
            } else {
                $errors = 1;
            }
        }
    } else {
        // Borrar todos los logs de este tipo
        $files = glob( $log_dir . '/' . $pattern );
        foreach ( $files as $file ) {
            if ( unlink( $file ) ) {
                $deleted++;
            } else {
                $errors++;
            }
        }
    }
    
    if ( $errors > 0 ) {
        wp_send_json_error( "Se borraron $deleted archivos, pero hubo $errors errores" );
    } else {
        wp_send_json_success( "Se han borrado $deleted archivo(s)" );
    }
}
add_action( 'wp_ajax_ikg_delete_logs', 'ikg_ajax_delete_logs' );

/**
 * Cargar el script del visor de logs en admin
 */
// JS del log viewer movido a admin/js/admin.js

/**
 * Exportar configuración completa de ACF a JSON
 * Hooked al botón de ACFE 'exportar_acf_completo'
 */
/**
 * Exportar configuración completa de ACF a JSON (AJAX)
 * Guarda los archivos JSON en admin/acf-json/ sin generar ZIP ni descarga.
 */
function ikg_ajax_export_acf_completo() {
    // Comprobar nonce
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'ikg_export_nonce') ) {
        wp_send_json_error('Permiso denegado (Nonce)');
    }

    // Comprobar permisos
    if ( ! current_user_can('manage_options') ) {
        wp_send_json_error('Sin permisos');
    }

    // Comprobar si ACF está activo
    if ( ! function_exists('acf_get_field_groups') ) {
        wp_send_json_error('ACF no está activo.');
    }

    // Obtener todos los grupos de campos
    $groups = acf_get_field_groups();
    
    // Directorio de guardado (admin/acf-json)
    $relative_path = '/admin/acf-json/';
    $save_dir = get_template_directory() . $relative_path;

    if ( ! file_exists( $save_dir ) ) {
        mkdir( $save_dir, 0755, true );
    }

    $count = 0;

    if ( $groups ) {
        foreach ( $groups as $group ) {
            // Obtener campos para cada grupo
            $fields = acf_get_fields( $group['key'] );

            // Preparar grupo para exportación
            if ( function_exists('acf_prepare_field_group_for_export') ) {
                $group = acf_prepare_field_group_for_export( $group );
            }

            // Añadir campos al grupo
            $group['fields'] = $fields;
            
            // Generar contenido JSON
            $json_content = json_encode( $group, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
            
            // Generar nombre de archivo basado en el Título
            $filename = sanitize_title( $group['title'] ) . '.json';
            
            // Guardar en servidor
            file_put_contents( $save_dir . $filename, $json_content );
            $count++;
        }
    }

    wp_send_json_success([
        'message' => "Exportación completada. $count archivos guardados en $relative_path"
    ]);
}
add_action('wp_ajax_ikg_export_acf_completo', 'ikg_ajax_export_acf_completo');

/**
 * Exportar Plugins a ZIPs individuales (AJAX)
 * Guarda los zips en admin/plugins/
 */
function ikg_ajax_export_plugins() {
    // Comprobar nonce
     /* Utilizaremos el mismo nonce de exportación por simplicidad, o podríamos crear uno nuevo.
        Como en admin.js usan ikgAdmin.exportNonce para ACF, podemos reusarlo o registrar uno para plugins.
        Mirando admin.js, se usa ikgAdmin.exportNonce. Reutilizaremos ese para "acciones de exportación". */
    if ( ! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'ikg_export_nonce') ) {
        wp_send_json_error('Permiso denegado (Nonce)');
    }

    if ( ! current_user_can('manage_values') && ! current_user_can('manage_options') ) {
        wp_send_json_error('Sin permisos');
    }

    if ( ! function_exists( 'get_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = get_plugins();
    $export_dir = get_template_directory() . '/admin/plugins/';
    
    if ( ! file_exists( $export_dir ) ) {
        mkdir( $export_dir, 0755, true );
    }

    $count = 0;
    $errors = [];

    foreach ( $all_plugins as $path => $data ) {
        // $path es algo como "akismet/akismet.php" o "hello.php"
        $plugin_slug = dirname($path);
        
        // Si es ".", significa que es un archivo suelto en /plugins/ (ej: hello.php)
        if ( $plugin_slug === '.' ) {
            $plugin_slug = pathinfo($path, PATHINFO_FILENAME);
            $source_path = WP_PLUGIN_DIR . '/' . $path;
            $is_dir = false;
        } else {
            // Es una carpeta
            $source_path = WP_PLUGIN_DIR . '/' . $plugin_slug;
            $is_dir = true;
        }

        $zip_file = $export_dir . $plugin_slug . '.zip';
        
        $zip = new ZipArchive();
        if ( $zip->open( $zip_file, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === TRUE ) {
            if ( $is_dir ) {
                ikg_recursive_zip( $source_path, $zip, $plugin_slug ); // $plugin_slug como raíz interna
            } else {
                $zip->addFile( $source_path, basename($source_path) );
            }
            $zip->close();
            $count++;
        } else {
            $errors[] = "No se pudo crear ZIP para $plugin_slug";
        }
    }

    if ( $count > 0 ) {
        wp_send_json_success([
            'message' => "Se han exportado $count plugins en /admin/plugins/"
        ]);
    } else {
        wp_send_json_error("No se exportó ningún plugin. " . implode(", ", $errors));
    }
}
add_action('wp_ajax_ikg_export_plugins', 'ikg_ajax_export_plugins');

/**
 * Helper para zip recursivo
 * @param string $source Ruta absoluta del directorio a comprimir
 * @param ZipArchive $zip Instancia del zip
 * @param string $prefix Prefijo dentro del zip (ej: nombre-plugin/)
 */
function ikg_recursive_zip( $source, $zip, $prefix = '' ) {
    if ( ! is_dir( $source ) ) return;

    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );

    foreach ( $files as $file ) {
        // Ruta absoluta
        $filePath = $file->getRealPath();
        
        // Ruta relativa desde la carpeta 'plugins' origen
        // Si queremos que dentro del zip esté la carpeta contenedora 'nombre-plugin/ archivo.php':
        // $relativePath = substr($filePath, strlen($source) + 1);
        // $zip->addFile($filePath, $prefix . '/' . $relativePath);
        
        // Pero normalmente un zip de plugin WP:
        // Si descomprimes, DEBE crear la carpeta. 
        // Si el zip se llama 'akismet.zip', al descomprimir suele crear 'akismet/'.
        // Así que sí, usamos el prefijo.
        
        $localPath = substr($filePath, strlen($source) + 1);
        $zip->addFile($filePath, $prefix . '/' . $localPath);
    }
}