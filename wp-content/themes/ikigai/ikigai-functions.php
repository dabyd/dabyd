<?php

require_once get_template_directory() . '/ikigai-media.php';
require_once get_template_directory() . '/ikigai-videos.php';
require_once get_template_directory() . '/ikigai-floating-window.php';
require_once get_template_directory() . '/ikigai-error-handler.php';
require_once get_template_directory() . '/ikigai-debug-handler.php';

// Inicializar el error handler
add_action('init', 'ikigai_init_error_handler');
// Encolar el CSS del error handler
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

function ikg_reset() {
    ikg_setbase();
}

function ikg_setbase( $base = '' ) {
	global $__ikg_base;
	$__ikg_base = $base;
}

function ikg_get_image( $datos ) {
    global $ikg_media;
    $ikg_media->img( $datos );
    return;
}

function ikg_get_video( $datos ) {
    global $ikg_videos;
    $ikg_videos->video( $datos );
    return;
}

function ikg_get_option( $id ) {
    $tmp = get_option( 'options_' . $id );
    if ( is_array( $tmp ) ) {
        $tmp = $tmp[0];
    }
    return $tmp;
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

function ikg_show_all_metas() {
    global $__ikg_acf_module, $__ikg_modul_name, $__ikg_values;

    // Solo ejecutamos en entorno de desarrollo
    if ( isDevelopedEnvironment()) {
        $post_id = get_the_ID();
        $tots = get_post_meta($post_id);
        $final = [];
        $key_prefix = 'moduls_' . $__ikg_acf_module . '_';
        
        foreach( $tots as $k => $value ) {
            if ( str_starts_with( $k, $key_prefix ) ) {
                $final[ str_replace( $key_prefix, '', $k ) ] = $value;
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
