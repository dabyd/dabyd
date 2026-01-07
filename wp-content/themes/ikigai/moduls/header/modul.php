<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<!-- Header/Navigation -->
<header class="header <?php ikg_get_variant( 'header' ); ?>" id="header">
    <nav class="nav container">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-logo">
            <?php
            $logo = ikg_get_option('logo');
            if ( '' != $logo ) {
                $logo = wp_get_attachment_image( $logo, 'full' );
            } else {
                $logo = bloginfo('name');
            }
            echo $logo;
            ?>
        </a>

        <?php
        $menu_id = ikg_get_option('menu_header');
        if ( is_array( $menu_id ) ) {
            $menu_id = $menu_id[0];
        }
        $items = wp_get_nav_menu_items($menu_id);
        $current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        
        // Organizar items en estructura de árbol
        $menu_tree = array();
        $submenu_items = array();
        
        if ($items) {
            // Separar items padre e hijos
            foreach ($items as $item) {
                if ($item->menu_item_parent == 0) {
                    $menu_tree[$item->ID] = $item;
                    $menu_tree[$item->ID]->children = array();
                } else {
                    $submenu_items[$item->menu_item_parent][] = $item;
                }
            }
            
            // Asignar hijos a sus padres
            foreach ($submenu_items as $parent_id => $children) {
                if (isset($menu_tree[$parent_id])) {
                    $menu_tree[$parent_id]->children = $children;
                }
            }
        }
        ?>

        <div class="nav-menu" id="nav-menu">
            <ul class="nav-list">
                <?php if (!empty($menu_tree)) : ?>
                    <?php foreach ($menu_tree as $item) : ?>
                        <?php
                        // Obtener el metadato "resaltado"
                        $resaltado = get_post_meta($item->ID, 'resaltado', true);
                        
                        // Verificar si tiene hijos
                        $has_children = !empty($item->children);
                        
                        // Preparar las clases del item
                        $item_classes = ['nav-item'];
                        if ($has_children) {
                            $item_classes[] = 'has-submenu';
                        }
                        
                        // Preparar las clases del enlace
                        $link_classes = ['nav-link'];
                        
                        // Verificar si es la página actual
                        $is_current = (rtrim($item->url, '/') === rtrim($current_url, '/') || 
                                      ($item->url === home_url('/') && is_front_page()));
                        
                        if ($is_current) {
                            $link_classes[] = 'active';
                        }
                        
                        // Verificar si tiene resaltado
                        if ($resaltado == 1) {
                            $link_classes[] = 'nav-cta';
                        }
                        
                        // Añadir clase si tiene hijos
                        if ($has_children) {
                            $link_classes[] = 'has-children';
                        }
                        
                        $item_class_string = implode(' ', $item_classes);
                        $link_class_string = implode(' ', $link_classes);
                        
                        // Verificar si se abre en nueva ventana
                        $target = $item->target ? ' target="' . esc_attr($item->target) . '"' : '';
                        ?>
                        
                        <li class="<?php echo esc_attr($item_class_string); ?>">
                            <?php if ($has_children) : ?>
                                <!-- Enlace padre con hijos -->
                                <a href="<?php echo esc_url($item->url); ?>" class="<?php echo esc_attr($link_class_string); ?>"<?php echo $target; ?>>
                                    <?php echo esc_html($item->title); ?>
                                </a>
                                
                                <!-- Submenú -->
                                <ul class="sub-menu">
                                    <?php foreach ($item->children as $child) : ?>
                                        <?php
                                        $child_is_current = (rtrim($child->url, '/') === rtrim($current_url, '/'));
                                        $child_classes = ['sub-menu-link'];
                                        if ($child_is_current) {
                                            $child_classes[] = 'active';
                                        }
                                        $child_class_string = implode(' ', $child_classes);
                                        $child_target = $child->target ? ' target="' . esc_attr($child->target) . '"' : '';
                                        ?>
                                        <li class="sub-menu-item">
                                            <a href="<?php echo esc_url($child->url); ?>" 
                                               class="<?php echo esc_attr($child_class_string); ?>"<?php echo $child_target; ?>>
                                                <?php echo esc_html($child->title); ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else : ?>
                                <!-- Enlace simple sin hijos -->
                                <a href="<?php echo esc_url($item->url); ?>" class="<?php echo esc_attr($link_class_string); ?>"<?php echo $target; ?>>
                                    <?php echo esc_html($item->title); ?>
                                </a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>

        <div class="nav-toggle" id="nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
</header>