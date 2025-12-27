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
            'contentClass' => '', // Clase CSS adicional para el contenido
            'headerClass' => '', // Clase CSS adicional para el header
        ], $options);
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
            return 'top: 50%; left: 50%; transform: translate(-50%, -50%);';
        } elseif (is_array($this->options['position'])) {
            $styles = [];
            foreach ($this->options['position'] as $prop => $value) {
                $styles[] = "{$prop}: {$value}";
            }
            return implode('; ', $styles) . ';';
        } else {
            // Posición automática escalonada
            $offset = (self::$instanceCount - 1) * 30;
            $top = 20 + $offset;
            $right = 20 + $offset;
            return "top: {$top}px; right: {$right}px;";
        }
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
     */
    private static function getAssets()
        {
            return <<<'ASSETS'
<style>
.floating-window {
    position: fixed;
    min-width: 300px;
    min-height: 200px;
    background: #ffffff;
    border: 1px solid #d0dae6;
    border-radius: 8px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    z-index: 999999;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
    font-size: 18px;
    color: #000000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: all 0.3s ease;
    cursor: default;
}

.floating-window:hover {
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.2);
}

.floating-window.fw-active {
    box-shadow: 0 20px 60px rgba(102, 126, 234, 0.3);
    border-color: #667eea;
}

.floating-window.fw-dragging {
    transition: none;
    box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
}

.floating-window.fw-minimized {
    height: auto !important;
    width: 300px !important;
}

.floating-window.fw-minimized .fw-content,
.floating-window.fw-minimized .fw-resize-handle {
    display: none;
}

.floating-window.fw-maximized {
    top: 0 !important;
    left: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    border-radius: 0;
    max-width: 100vw !important;
    max-height: 100vh !important;
    transform: none !important;
}

.fw-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    cursor: move;
    user-select: none;
    flex-shrink: 0;
}

.fw-header:active {
    cursor: grabbing;
}

.fw-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 20px;
    pointer-events: none;
}

.fw-icon {
    font-size: 22px;
}

.fw-controls {
    display: flex;
    gap: 4px;
}

.fw-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    font-size: 18px;
    padding: 0;
}

.fw-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: scale(1.05);
}

.fw-minimize-icon {
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
    margin-bottom: 8px;
}

.fw-maximize-icon {
    font-size: 16px;
    line-height: 1;
}

.floating-window.fw-maximized .fw-maximize-icon::before {
    content: '❐';
}

.fw-content {
    flex: 1;
    overflow: auto;
    padding: 16px;
    background: #ffffff;
    color: #1a1a1a;
    font-size: 16px; /* Tamaño base aumentado a 16px */
    line-height: 1.6; /* Mejor legibilidad */
}

/* ============================================ */
/* ESTILOS MEJORADOS PARA ALTO CONTRASTE */
/* ============================================ */

/* Contenido general - mayor contraste */
.fw-content pre,
.fw-content code {
    font-size: 16px;
    line-height: 1.5;
    color: #1a1a1a;
}

/* Arrays y dumps de debug con fondo suave */
.fw-content pre {
    background: #f8f9fa;
    padding: 12px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    color: #212529;
}

/* Clases Symfony Dump - Alto contraste */
.fw-content .sf-dump {
    font-size: 16px !important;
    line-height: 1.5 !important;
}

.fw-content .sf-dump-expanded,
.fw-content .sf-dump-compact {
    color: #212529 !important;
}

/* Keys de arrays - Azul oscuro fuerte */
.fw-content .sf-dump-key {
    color: #0056b3 !important;
    font-weight: 600 !important;
}

/* Valores numéricos - Magenta oscuro */
.fw-content .sf-dump-num {
    color: #c92a80 !important;
    font-weight: 600 !important;
}

/* Strings - Verde oscuro */
.fw-content .sf-dump-str {
    color: #146c43 !important;
    font-weight: 500 !important;
}

/* Null, true, false - Púrpura oscuro */
.fw-content .sf-dump-const {
    color: #5310c2 !important;
    font-weight: 600 !important;
}

/* ============================================ */
/* SOBRESCRITURA DE ESTILOS INLINE CLAROS */
/* ============================================ */

/* Azules claros → Azul oscuro */
.fw-content [style*="color:#00"],
.fw-content [style*="color:#05a"],
.fw-content [style*="color:#06a"],
.fw-content [style*="color:#08f"],
.fw-content [style*="color: rgb(0, 170"],
.fw-content span[style*="color"] {
    color: #0056b3 !important;
    font-weight: 500 !important;
}

/* Verdes claros → Verde oscuro */
.fw-content [style*="color:#080"],
.fw-content [style*="color:#0a0"],
.fw-content [style*="color:green"],
.fw-content [style*="color:#5c5"],
.fw-content [style*="color: rgb(0, 136"] {
    color: #146c43 !important;
    font-weight: 500 !important;
}

/* Grises claros → Gris muy oscuro */
.fw-content [style*="color:#666"],
.fw-content [style*="color:#777"],
.fw-content [style*="color:#888"],
.fw-content [style*="color:#999"],
.fw-content [style*="color:#aaa"],
.fw-content [style*="color:gray"],
.fw-content [style*="color: rgb(102, 102"],
.fw-content [style*="color: rgb(136, 136"] {
    color: #2d2d2d !important;
    font-weight: 500 !important;
}

/* Rojos claros → Rojo oscuro */
.fw-content [style*="color:#c00"],
.fw-content [style*="color:red"],
.fw-content [style*="color:#d00"] {
    color: #b30000 !important;
    font-weight: 600 !important;
}

/* Naranjas claros → Naranja oscuro */
.fw-content [style*="color:#f60"],
.fw-content [style*="color:orange"] {
    color: #cc5200 !important;
    font-weight: 600 !important;
}

/* Tipos de datos (array, string, int, etc.) */
.fw-content [title="array"],
.fw-content [title="string"],
.fw-content [title="int"],
.fw-content [title="float"],
.fw-content [title="bool"] {
    color: #0056b3 !important;
    font-weight: 600 !important;
}

/* Mejora general para elementos con atributos style inline */
.fw-content span[style],
.fw-content span[class] {
    text-shadow: 0 0 0.5px currentColor; /* Ligero refuerzo visual */
}

/* ============================================ */
/* FIN ESTILOS DE ALTO CONTRASTE */
/* ============================================ */

/* Resize handles */
.fw-resize-handle {
    position: absolute;
    background: transparent;
    z-index: 10;
}

.fw-resize-n, .fw-resize-s {
    width: 100%;
    height: 8px;
    cursor: ns-resize;
    left: 0;
}

.fw-resize-n { top: 0; }
.fw-resize-s { bottom: 0; }

.fw-resize-e, .fw-resize-w {
    height: 100%;
    width: 8px;
    cursor: ew-resize;
    top: 0;
}

.fw-resize-e { right: 0; }
.fw-resize-w { left: 0; }

.fw-resize-ne, .fw-resize-nw, .fw-resize-se, .fw-resize-sw {
    width: 16px;
    height: 16px;
}

.fw-resize-ne {
    top: 0;
    right: 0;
    cursor: nesw-resize;
}

.fw-resize-nw {
    top: 0;
    left: 0;
    cursor: nwse-resize;
}

.fw-resize-se {
    bottom: 0;
    right: 0;
    cursor: nwse-resize;
}

.fw-resize-sw {
    bottom: 0;
    left: 0;
    cursor: nesw-resize;
}

.floating-window.fw-maximized .fw-resize-handle {
    display: none;
}

/* Scrollbar personalizado */
.fw-content::-webkit-scrollbar {
    width: 12px;
    height: 12px;
}

.fw-content::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.fw-content::-webkit-scrollbar-thumb {
    background: #8892a0;
    border-radius: 6px;
}

.fw-content::-webkit-scrollbar-thumb:hover {
    background: #6c7580;
}

/* Animación de entrada */
@keyframes fwSlideIn {
    from {
        transform: scale(0.9);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.floating-window {
    animation: fwSlideIn 0.2s ease-out;
}

@keyframes fwSlideOut {
    to {
        transform: scale(0.9);
        opacity: 0;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .floating-window {
        width: 95vw !important;
        max-width: 95vw !important;
    }
}
</style>

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