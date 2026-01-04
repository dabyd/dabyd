<?php
/**
 * IkigaiVideoClass - Gestión de videos con lógica de entrada inteligente
 */
class IkigaiVideoClass {

    public function __construct() {
        add_filter('upload_mimes', [$this, 'custom_video_mimes']);
    }

    public function custom_video_mimes($mimes) {
        $mimes['webm'] = 'video/webm';
        $mimes['mp4']  = 'video/mp4';
        return $mimes;
    }

    /**
     * Lógica idéntica a obtener_todos_los_tamanos_de_imagen pero para video
     */
    public function obtener_datos_video($video_id) {
        if (!$video_id || !is_numeric($video_id)) return null;

        $video_url = wp_get_attachment_url($video_id);
        if (!$video_url) return null;

        $path = get_attached_file($video_id);
        $info = pathinfo($path);
        
        // Estructura de directorios para buscar versiones WebM/Ogv
        $base_path = $info['dirname'] . '/' . $info['filename'];
        $base_url  = dirname($video_url) . '/' . $info['filename'];

        $resultados = [
            'main' => [
                'url'  => $video_url,
                'type' => get_post_mime_type($video_id),
                'file' => $path
            ],
            'sources' => []
        ];

        // Buscar versiones optimizadas físicamente en el servidor
        foreach (['webm' => 'video/webm', 'ogv' => 'video/ogg'] as $ext => $mime) {
            if (file_exists($base_path . '.' . $ext)) {
                $resultados['sources'][] = [
                    'url'  => $base_url . '.' . $ext,
                    'type' => $mime
                ];
            }
        }

        // Buscar poster (miniatura de WP)
        $poster_id = get_post_thumbnail_id($video_id);
        if ($poster_id) {
            $resultados['poster_url'] = wp_get_attachment_image_url($poster_id, 'full');
        }

        return $resultados;
    }

    public function video($valores) {
        $base = [
            'video_id'    => '',
            'poster_id'   => '',
            'autoplay'    => false,
            'loop'        => false,
            'muted'       => false,
            'controls'    => true,
            'playsinline' => true,
            'preload'     => 'metadata',
            'class'       => '',
            'clase'       => '',
            'src'         => '',
            'poster_src'  => '',
            'echo'        => true,
            'debug'       => false,
        ];

        // --- LÓGICA DE ENTRADA INTELIGENTE (REPLICADA DE TU CLASE MEDIA) ---
        if (!is_array($valores)) {
            if (is_numeric($valores)) {
                $valores = ['video_id' => $valores];
            } else if (is_string($valores)) {
                // Si es una ruta de archivo o URL
                if (preg_match('#^(/|\.{1,2}/|[a-zA-Z]:\\\\|http)#', $valores)) {
                    $valores = ['src' => $valores];
                } else {
                    // Si es un nombre de variable ACF
                    $valores = ['video_id' => ikg_get_acf_value($valores)];
                }
            }
        }
        
        $valores = array_merge($base, $valores);
        $valores['class'] = trim($valores['class'] . ' ' . $valores['clase']);

        $output = '';
        $poster_url = '';

        // Procesar origen del video
        if (!empty($valores['video_id'])) {
            $datos = $this->obtener_datos_video($valores['video_id']);
            
            if ($datos) {
                // Prioridad de Poster: poster_id > poster_src > miniatura automática
                if ($valores['poster_id']) {
                    $poster_url = wp_get_attachment_image_url($valores['poster_id'], 'full');
                } else if ($valores['poster_src']) {
                    $poster_url = $valores['poster_src'];
                } else if (isset($datos['poster_url'])) {
                    $poster_url = $datos['poster_url'];
                }

                $attr_poster = $poster_url ? ' poster="' . esc_url($poster_url) . '"' : '';
                $attrs = $this->build_attributes($valores);

                $output .= '<video ' . $attrs . $attr_poster . '>';
                
                // Formatos alternativos detectados
                foreach ($datos['sources'] as $source) {
                    $output .= '<source src="' . esc_url($source['url']) . '" type="' . esc_attr($source['type']) . '">';
                }
                
                // Formato principal
                $output .= '<source src="' . esc_url($datos['main']['url']) . '" type="' . esc_attr($datos['main']['type']) . '">';
                $output .= '</video>';
            }
        } else if (!empty($valores['src'])) {
            // Caso URL directa (estática o externa)
            $poster_url = $valores['poster_src'] ?: '';
            $attr_poster = $poster_url ? ' poster="' . esc_url($poster_url) . '"' : '';
            $attrs = $this->build_attributes($valores);
            
            $output .= '<video src="' . esc_url($valores['src']) . '" ' . $attrs . $attr_poster . '></video>';
        }

        // Debug (Integrable con tu IkigaiFloatingWindow si lo deseas)
        if ($valores['debug']) {
            echo '<pre style="background:#222; color:#fff; padding:10px; font-size:11px;">';
            print_r($valores);
            echo '</pre>';
        }

        if ($valores['echo']) {
            echo $output;
        }

        return $output;
    }

    private function build_attributes($v) {
        $a = [];
        if ($v['autoplay'])    $a[] = 'autoplay';
        if ($v['loop'])        $a[] = 'loop';
        if ($v['muted'])       $a[] = 'muted';
        if ($v['controls'])    $a[] = 'controls';
        if ($v['playsinline']) $a[] = 'playsinline';
        if ($v['class'])       $a[] = 'class="' . esc_attr($v['class']) . '"';
        $a[] = 'preload="' . esc_attr($v['preload']) . '"';
        return implode(' ', $a);
    }
}