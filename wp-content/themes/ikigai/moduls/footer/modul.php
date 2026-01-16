<?php
//    ikg_show_all_metas();

    $menu_id = ikg_get_option('menu_footer_1');
    if ( is_array( $menu_id ) ) {
        $menu_id = $menu_id[0];
    }
    $items_1 = wp_get_nav_menu_items($menu_id);
    $menu_id = ikg_get_option('menu_footer_2');
    if ( is_array( $menu_id ) ) {
        $menu_id = $menu_id[0];
    }
    $items_2 = wp_get_nav_menu_items($menu_id);
    $menu_id = ikg_get_option('menu_paginas_legales');
    if ( is_array( $menu_id ) ) {
        $menu_id = $menu_id[0];
    }
    $paginas = wp_get_nav_menu_items($menu_id);
    $frase = ikg_get_option('frase_final');
?>

        <!-- Footer -->
        <footer class="footer <?php ikg_get_variant( 'footer' ); ?>">
            <div class="container">
                <div class="footer-content">
                    <div class="footer-col">
                        <?php echo $frase; ?>
                        <div class="footer-social">
                            <?php 
                                ikg_render_social_link('facebook');
                                ikg_render_social_link('instagram');
                                ikg_render_social_link('substack');
                                ikg_render_social_link('whatsapp');
                                ikg_render_social_link('telegram');
                                ikg_render_social_link('mail');
                            ?>
                        </div>
                    </div>
                    
                    <div class="footer-col">
                        <?php if ( count( $items_1 ) > 0 ): ?>
                            <h4><?php echo ikg_get_option('titol_menu_footer_1' ); ?></h4>
                            <ul>
                                <?php foreach( $items_1 as $item ): ?>
                                    <li><a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div class="footer-col">
                        <?php if ( count( $items_2 ) > 0 ): ?>
                            <h4><?php echo ikg_get_option('titol_menu_footer_2' ); ?></h4>
                            <ul>
                                <?php foreach( $items_2 as $item ): ?>
                                    <li><a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="footer-legal">
                    <?php if ( count( $paginas ) > 0 ): ?>
                        <ul>
                            <?php foreach( $paginas as $item ): ?>
                                <li><a href="<?php echo esc_url($item->url); ?>"><?php echo esc_html($item->title); ?></a></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
                
                <div class="footer-bottom">
                    <p>&copy; <?php echo date('Y'); ?> <?php echo ikg_get_option( 'copyright' ); ?></p>
                </div>
            </div>
        </footer>
        <?php wp_footer(); ?>
    </body>
</html>