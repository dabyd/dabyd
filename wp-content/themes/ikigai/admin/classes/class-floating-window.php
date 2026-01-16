<?php

/**
 * IkigaiFloatingWindow - Sistema de ventanas flotantes interactivas
 *
 * Características:
 * - Arrastrables
 * - Redimensionables (8 direcciones)
 * - Minimizables
 * - Maximizables
 * - CSS y JavaScript incrustados
 *
 * @version 1.1 - Mejorado contraste y tamaño de fuente
 */
class IkigaiFloatingWindow {
    private static $instanceCount = 0;
    private static $assetsLoaded = false;
    
    private $id;
    private $title;
    private $content;
    private $options;
    
    /**
     * Constructor
     *
     * @param string $title Título de la ventana
     * @param string $content Contenido HTML de la ventana
     * @param array $options Opciones de configuración
     */
    public function __construct($title, $content, $options = [])
    {
        self::$instanceCount++;
        $this->id = 'floating-window-' . self::$instanceCount;
        $this->title = $title;
        $this->content = $content;
        
        // Opciones por defecto
        $this->options = array_merge([
            'width' => '600px',
            'height' => '500px',
            'icon' => '📋',
            'position' => 'auto', // 'auto', 'center', ['top' => '20px', 'left' => '20px']
            'closable' => true,
            'minimizable' => true,
            'maximizable' => true,
            'resizable' => true,
            'draggable' => true,
            'visible' => true, // Nueva opción de visibilidad
            'contentClass' => '', // Clase CSS adicional para el contenido
            'headerClass' => '', // Clase CSS adicional para el header
        ], $options);
    }

    /**
     * Retorna el ID de la ventana
     * @return string
     */
    public function getId() {
        return $this->id;
    }
    
    /**
     * Renderiza la ventana y retorna el HTML
     *
     * @return string HTML de la ventana
     */
    public function render()
    {
        $html = '';
        
        // Incluir assets solo una vez
        if (!self::$assetsLoaded) {
            $html .= self::getAssets();
            self::$assetsLoaded = true;
        }
        
        $html .= $this->generateWindow();
        
        return $html;
    }
    
    /**
     * Muestra la ventana directamente (echo)
     */
    public function show()
    {
        echo $this->render();
    }
    
    /**
     * Genera el HTML de la ventana
     */
    private function generateWindow()
    {
        $position = $this->calculatePosition();
        $draggableAttr = $this->options['draggable'] ? 
            "onmousedown=\"IkigaiFloatingWindow.startDrag(event, '{$this->id}')\"" : '';
        
        $html = <<<HTML
<div class="floating-window" id="{$this->id}" style="width: {$this->options['width']}; height: {$this->options['height']}; {$position}" onclick="IkigaiFloatingWindow.bringToFront('{$this->id}')">
    <div class="fw-header {$this->options['headerClass']}" {$draggableAttr}>
        <div class="fw-title">
            <span class="fw-icon">{$this->options['icon']}</span>
            <span class="fw-title-text">{$this->title}</span>
        </div>
        <div class="fw-controls">
HTML;
        
        if ($this->options['minimizable']) {
            $html .= <<<HTML
            <button class="fw-btn fw-minimize-btn" onclick="IkigaiFloatingWindow.minimize('{$this->id}')" title="Minimizar">
                <span class="fw-minimize-icon">_</span>
            </button>
HTML;
        }
        
        if ($this->options['maximizable']) {
            $html .= <<<HTML
            <button class="fw-btn fw-maximize-btn" onclick="IkigaiFloatingWindow.maximize('{$this->id}')" title="Maximizar">
                <span class="fw-maximize-icon">□</span>
            </button>
HTML;
        }
        
        if ($this->options['closable']) {
            $html .= <<<HTML
            <button class="fw-btn fw-close-btn" onclick="IkigaiFloatingWindow.close('{$this->id}')" title="Cerrar">
                ✕
            </button>
HTML;
        }
        
        $html .= <<<HTML
        </div>
    </div>
    <div class="fw-content {$this->options['contentClass']}">
        {$this->content}
    </div>
HTML;
        
        // Agregar handles de resize si está habilitado
        if ($this->options['resizable']) {
            $html .= $this->generateResizeHandles();
        }
        
        $html .= "</div>\n";
        
        // Script para traer al frente automáticamente al crear
        $html .= "<script>
            (function() {
                function initWin() {
                    var win = document.getElementById('{$this->id}');
                    if (win && typeof IkigaiFloatingWindow !== 'undefined') {
                        setTimeout(function() {
                            IkigaiFloatingWindow.bringToFront('{$this->id}');
                        }, 10);
                    }
                }
                
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initWin);
                } else {
                    initWin();
                }
            })();
        </script>\n";
        
        return $html;
    }
    
    /**
     * Genera los handles de redimensionado
     */
    private function generateResizeHandles()
    {
        $directions = ['n', 's', 'e', 'w', 'ne', 'nw', 'se', 'sw'];
        $html = '';
        
        foreach ($directions as $dir) {
            $html .= "<div class=\"fw-resize-handle fw-resize-{$dir}\" onmousedown=\"IkigaiFloatingWindow.startResize(event, '{$this->id}', '{$dir}')\"></div>\n";
        }
        
        return $html;
    }
    
    /**
     * Calcula la posición inicial de la ventana
     */
    private function calculatePosition()
    {
        if ($this->options['position'] === 'center') {
            $style = 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
        } elseif (is_array($this->options['position'])) {
            $styles = [];
            foreach ($this->options['position'] as $prop => $value) {
                $styles[] = "{$prop}: {$value}";
            }
            $style = implode('; ', $styles) . ';';
        } else {
            // Posición automática escalonada
            $offset = (self::$instanceCount - 1) * 30;
            $top = 20 + $offset;
            $right = 20 + $offset;
            $style = "top: {$top}px; right: {$right}px;";
        }
        
        if (!$this->options['visible']) {
            $style .= ' display: none;';
        }
        
        return $style;
    }
    
    /**
     * Método estático para mostrar una ventana rápidamente
     *
     * @param string $title Título de la ventana
     * @param string $content Contenido HTML
     * @param array $options Opciones
     */
    public static function quick($title, $content, $options = [])
    {
        $window = new self($title, $content, $options);
        $window->show();
    }
    
    /**
     * Reinicia el contador de instancias (útil para testing)
     */
    public static function resetInstanceCount()
    {
        self::$instanceCount = 0;
        self::$assetsLoaded = false;
    }
    
    /**
     * Retorna los assets (CSS y JavaScript)
     * CSS movido a admin/css/admin.css
     * JS movido a admin/js/floating-window.js
     */
    private static function getAssets()
        {
            return "<!-- Assets de Floating Window cargados vía wp_enqueue_script y admin.css -->";
    }
}