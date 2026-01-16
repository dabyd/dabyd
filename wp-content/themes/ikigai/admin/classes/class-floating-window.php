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
                var win = document.getElementById('{$this->id}');
                if (win) {
                    setTimeout(function() {
                        IkigaiFloatingWindow.bringToFront('{$this->id}');
                    }, 10);
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
     */
    private static function getAssets()
        {
            return <<<'ASSETS'
<!-- Estilos cargados desde admin.css -->
<script>
// Variables globales para drag y resize
if (typeof window.fwDragData === 'undefined') {
    window.fwDragData = {
        isDragging: false,
        currentWindow: null,
        startX: 0,
        startY: 0,
        startTop: 0,
        startLeft: 0
    };
}

if (typeof window.fwResizeData === 'undefined') {
    window.fwResizeData = {
        isResizing: false,
        currentWindow: null,
        direction: null,
        startX: 0,
        startY: 0,
        startWidth: 0,
        startHeight: 0,
        startTop: 0,
        startLeft: 0
    };
}

// Control de z-index para traer ventanas al frente
if (typeof window.fwTopZIndex === 'undefined') {
    window.fwTopZIndex = 999999;
}

if (typeof window.IkigaiFloatingWindow === 'undefined') {
    window.IkigaiFloatingWindow = {
        bringToFront: function(id) {
            var win = document.getElementById(id);
            if (!win) return;
            
            // Remover clase activa de todas las ventanas
            var allWindows = document.querySelectorAll('.floating-window');
            allWindows.forEach(function(w) {
                w.classList.remove('fw-active');
            });
            
            // Incrementar z-index global
            window.fwTopZIndex++;
            
            // Asignar nuevo z-index a esta ventana
            win.style.zIndex = window.fwTopZIndex;
            
            // Marcar como activa
            win.classList.add('fw-active');
        },
        
        minimize: function(id) {
            var win = document.getElementById(id);
            if (win) win.classList.toggle('fw-minimized');
        },
        
        maximize: function(id) {
            var win = document.getElementById(id);
            if (!win) return;
            
            if (win.classList.contains('fw-maximized')) {
                win.classList.remove('fw-maximized');
                if (win.dataset.originalStyle) {
                    var style = JSON.parse(win.dataset.originalStyle);
                    win.style.top = style.top;
                    win.style.left = style.left;
                    win.style.right = style.right;
                    win.style.bottom = style.bottom;
                    win.style.width = style.width;
                    win.style.height = style.height;
                }
            } else {
                win.dataset.originalStyle = JSON.stringify({
                    top: win.style.top,
                    left: win.style.left,
                    right: win.style.right,
                    bottom: win.style.bottom,
                    width: win.style.width,
                    height: win.style.height
                });
                win.classList.add('fw-maximized');
            }
        },
        
        close: function(id) {
            var win = document.getElementById(id);
            if (!win) return;
            
            win.style.animation = 'fwSlideOut 0.2s ease-out';
            setTimeout(function() {
                win.remove();
            }, 200);
        },
        
        startDrag: function(e, id) {
            if (e.target.closest('.fw-btn')) return;
            
            var win = document.getElementById(id);
            if (!win || win.classList.contains('fw-maximized')) return;
            
            // Traer al frente
            IkigaiFloatingWindow.bringToFront(id);
            
            window.fwDragData.isDragging = true;
            window.fwDragData.currentWindow = win;
            window.fwDragData.startX = e.clientX;
            window.fwDragData.startY = e.clientY;
            
            var rect = win.getBoundingClientRect();
            window.fwDragData.startTop = rect.top;
            window.fwDragData.startLeft = rect.left;
            
            win.classList.add('fw-dragging');
            e.preventDefault();
        },
        
        startResize: function(e, id, direction) {
            var win = document.getElementById(id);
            if (!win) return;
            
            // Traer al frente
            IkigaiFloatingWindow.bringToFront(id);
            
            window.fwResizeData.isResizing = true;
            window.fwResizeData.currentWindow = win;
            window.fwResizeData.direction = direction;
            window.fwResizeData.startX = e.clientX;
            window.fwResizeData.startY = e.clientY;
            
            var rect = win.getBoundingClientRect();
            window.fwResizeData.startWidth = rect.width;
            window.fwResizeData.startHeight = rect.height;
            window.fwResizeData.startTop = rect.top;
            window.fwResizeData.startLeft = rect.left;
            
            win.classList.add('fw-dragging');
            e.preventDefault();
            e.stopPropagation();
        }
    };
}

// Event listeners globales (solo agregar una vez)
if (typeof window.fwListenersAdded === 'undefined') {
    window.fwListenersAdded = true;
    
    document.addEventListener('mousemove', function(e) {
        // Manejar arrastre
        if (window.fwDragData.isDragging) {
            var data = window.fwDragData;
            var deltaX = e.clientX - data.startX;
            var deltaY = e.clientY - data.startY;
            
            var newTop = data.startTop + deltaY;
            var newLeft = data.startLeft + deltaX;
            
            newTop = Math.max(0, Math.min(newTop, window.innerHeight - 50));
            newLeft = Math.max(0, Math.min(newLeft, window.innerWidth - 100));
            
            data.currentWindow.style.top = newTop + 'px';
            data.currentWindow.style.left = newLeft + 'px';
            data.currentWindow.style.right = 'auto';
            data.currentWindow.style.bottom = 'auto';
        }
        
        // Manejar redimensionado
        if (window.fwResizeData.isResizing) {
            var data = window.fwResizeData;
            var deltaX = e.clientX - data.startX;
            var deltaY = e.clientY - data.startY;
            var dir = data.direction;
            var win = data.currentWindow;
            
            var newWidth = data.startWidth;
            var newHeight = data.startHeight;
            var newTop = data.startTop;
            var newLeft = data.startLeft;
            
            if (dir.includes('e')) {
                newWidth = Math.max(300, data.startWidth + deltaX);
            } else if (dir.includes('w')) {
                newWidth = Math.max(300, data.startWidth - deltaX);
                newLeft = data.startLeft + deltaX;
                if (newWidth === 300) newLeft = data.startLeft + (data.startWidth - 300);
            }
            
            if (dir.includes('n')) {
                newHeight = Math.max(200, data.startHeight - deltaY);
                newTop = data.startTop + deltaY;
                if (newHeight === 200) newTop = data.startTop + (data.startHeight - 200);
            } else if (dir.includes('s')) {
                newHeight = Math.max(200, data.startHeight + deltaY);
            }
            
            win.style.width = newWidth + 'px';
            win.style.height = newHeight + 'px';
            win.style.top = newTop + 'px';
            win.style.left = newLeft + 'px';
            win.style.right = 'auto';
            win.style.bottom = 'auto';
        }
    });
    
    document.addEventListener('mouseup', function() {
        if (window.fwDragData.isDragging) {
            window.fwDragData.currentWindow.classList.remove('fw-dragging');
            window.fwDragData.isDragging = false;
            window.fwDragData.currentWindow = null;
        }
        
        if (window.fwResizeData.isResizing) {
            window.fwResizeData.currentWindow.classList.remove('fw-dragging');
            window.fwResizeData.isResizing = false;
            window.fwResizeData.currentWindow = null;
        }
    });
}
</script>
ASSETS;
    }
}