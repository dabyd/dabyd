<?php
/**
 * Ikigai Form Admin
 * Añade metabox para ver submissions en el CPT formularios
 */

// Añadir metabox al editar un formulario
add_action('add_meta_boxes', 'ikg_add_submissions_metabox');

function ikg_add_submissions_metabox() {
    add_meta_box(
        'ikg_submissions_list',
        '📧 Submissions Recibidas',
        'ikg_render_submissions_metabox',
        'formularios',
        'normal',
        'high'
    );
}

function ikg_render_submissions_metabox($post) {
    // Obtener submissions de este formulario
    $submissions = get_posts([
        'post_type'      => 'ikg_submission',
        'post_parent'    => $post->ID,
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    if (empty($submissions)) {
        echo '<p style="color: #666; padding: 20px 0;">No hay submissions todavía para este formulario.</p>';
        return;
    }

    echo '<style>
        .ikg-submissions-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .ikg-submissions-table th, .ikg-submissions-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .ikg-submissions-table th { background: #f1f1f1; font-weight: 600; }
        .ikg-submissions-table tr:hover { background: #f9f9f9; }
        .ikg-submission-detail { display: none; background: #fafafa; }
        .ikg-submission-detail.active { display: table-row; }
        .ikg-toggle-btn { cursor: pointer; color: #0073aa; text-decoration: underline; }
        .ikg-toggle-btn:hover { color: #005a87; }
        .ikg-data-table { width: 100%; margin: 10px 0; border-collapse: collapse; }
        .ikg-data-table td { padding: 10px; vertical-align: top; border: 1px solid #e0e0e0; }
        .ikg-data-table td:first-child { font-weight: bold; width: 180px; background: #f5f5f5; }
        .ikg-email-status { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 16px; }
        .ikg-email-status.sent { background: #d4edda; color: #155724; }
        .ikg-email-status.failed { background: #f8d7da; color: #721c24; }
        .ikg-meta-info { padding: 10px; background: #f0f0f0; border-radius: 4px; margin-top: 10px; font-size: 18px; color: #666; }
    </style>';

    echo '<p><strong>Total submissions:</strong> ' . count($submissions) . '</p>';
    
    echo '<table class="ikg-submissions-table">';
    echo '<thead><tr>';
    echo '<th style="width: 30px;">#</th>';
    echo '<th>Fecha</th>';
    echo '<th>Resumen</th>';
    echo '<th>Email</th>';
    echo '<th>IP</th>';
    echo '<th style="width: 100px;">Acciones</th>';
    echo '</tr></thead>';
    echo '<tbody>';

    foreach ($submissions as $index => $submission) {
        $form_data = get_post_meta($submission->ID, '_ikg_form_data', true);
        $submitted_at = get_post_meta($submission->ID, '_ikg_submitted_at', true);
        $user_ip = get_post_meta($submission->ID, '_ikg_user_ip', true);
        $email_sent = get_post_meta($submission->ID, '_ikg_email_sent', true);
        
        // Crear resumen (primeros 2 campos)
        $resumen = [];
        if (is_array($form_data)) {
            $count = 0;
            foreach ($form_data as $key => $value) {
                if ($count >= 2) break;
                $label = ucfirst(str_replace(['-', '_'], ' ', $key));
                $resumen[] = '<strong>' . esc_html($label) . ':</strong> ' . esc_html(wp_trim_words($value, 5));
                $count++;
            }
        }

        $email_status_class = $email_sent === 'yes' ? 'sent' : 'failed';
        $email_status_text = $email_sent === 'yes' ? '✓ Enviado' : '✗ Falló';

        echo '<tr>';
        echo '<td>' . ($index + 1) . '</td>';
        echo '<td>' . esc_html($submitted_at ?: $submission->post_date) . '</td>';
        echo '<td>' . implode(' | ', $resumen) . '</td>';
        echo '<td><span class="ikg-email-status ' . $email_status_class . '">' . $email_status_text . '</span></td>';
        echo '<td>' . esc_html($user_ip) . '</td>';
        echo '<td><span class="ikg-toggle-btn" onclick="ikg_toggle_detail(' . $submission->ID . ')">Ver detalles</span></td>';
        echo '</tr>';

        // Fila de detalle (oculta por defecto)
        echo '<tr class="ikg-submission-detail" id="ikg-detail-' . $submission->ID . '">';
        echo '<td colspan="6">';
        echo '<table class="ikg-data-table">';
        if (is_array($form_data)) {
            foreach ($form_data as $key => $value) {
                $label = ucfirst(str_replace(['-', '_'], ' ', $key));
                echo '<tr>';
                echo '<td>' . esc_html($label) . '</td>';
                echo '<td>' . nl2br(esc_html($value)) . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        
        $user_agent = get_post_meta($submission->ID, '_ikg_user_agent', true);
        $email_to = get_post_meta($submission->ID, '_ikg_email_to', true);
        
        echo '<div class="ikg-meta-info">';
        echo '<p style="margin: 0 0 5px 0;"><strong>Email enviado a:</strong> ' . esc_html($email_to) . '</p>';
        echo '<p style="margin: 0;"><strong>User Agent:</strong> ' . esc_html($user_agent) . '</p>';
        echo '</div>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    echo '<script>
        function ikg_toggle_detail(id) {
            var row = document.getElementById("ikg-detail-" + id);
            row.classList.toggle("active");
        }
    </script>';
}

// Añadir enlace al menú de Formularios para ver todas las submissions
add_action('admin_menu', 'ikg_add_submissions_submenu');

function ikg_add_submissions_submenu() {
    add_submenu_page(
        'edit.php?post_type=formularios',
        'Todas las Submissions',
        '📧 Submissions',
        'manage_options',
        'ikg-all-submissions',
        'ikg_render_all_submissions_page'
    );
}

function ikg_render_all_submissions_page() {
    echo '<div class="wrap">';
    echo '<h1>📧 Todas las Submissions</h1>';
    
    echo '<style>
        .ikg-admin-card { background: #fff; border: 1px solid #ccd0d4; border-radius: 4px; margin-bottom: 20px; }
        .ikg-admin-card-header { padding: 15px 20px; background: #f6f7f7; border-bottom: 1px solid #ccd0d4; }
        .ikg-admin-card-header h2 { margin: 0; font-size: 20px; }
        .ikg-admin-card-body { padding: 0; }
        .ikg-empty-message { padding: 20px; color: #666; }
    </style>';
    
    // Obtener todas las submissions agrupadas por formulario
    $formularios = get_posts([
        'post_type' => 'formularios',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    if (empty($formularios)) {
        echo '<p>No hay formularios creados todavía.</p>';
        echo '</div>';
        return;
    }

    foreach ($formularios as $form) {
        $submissions = get_posts([
            'post_type'      => 'ikg_submission',
            'post_parent'    => $form->ID,
            'posts_per_page' => 20,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        $total_submissions = wp_count_posts('ikg_submission');
        
        echo '<div class="ikg-admin-card">';
        echo '<div class="ikg-admin-card-header">';
        echo '<h2>' . esc_html($form->post_title) . ' <span style="color: #666; font-weight: normal;">(' . count($submissions) . ' submissions)</span></h2>';
        echo '</div>';
        echo '<div class="ikg-admin-card-body">';
        
        if (!empty($submissions)) {
            echo '<table class="wp-list-table widefat fixed striped">';
            echo '<thead><tr><th style="width: 150px;">Fecha</th><th>Datos</th><th style="width: 80px;">Email</th><th style="width: 120px;">IP</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($submissions as $sub) {
                $data = get_post_meta($sub->ID, '_ikg_form_data', true);
                $email_sent = get_post_meta($sub->ID, '_ikg_email_sent', true);
                
                // Crear resumen de datos
                $summary_parts = [];
                if (is_array($data)) {
                    foreach (array_slice($data, 0, 3) as $key => $value) {
                        $label = ucfirst(str_replace(['-', '_'], ' ', $key));
                        $summary_parts[] = '<strong>' . esc_html($label) . ':</strong> ' . esc_html(wp_trim_words($value, 8));
                    }
                }
                $summary = implode(' | ', $summary_parts);
                
                $email_icon = $email_sent === 'yes' ? '✓' : '✗';
                
                echo '<tr>';
                echo '<td>' . esc_html($sub->post_date) . '</td>';
                echo '<td>' . $summary . '</td>';
                echo '<td style="text-align: center;">' . $email_icon . '</td>';
                echo '<td>' . esc_html(get_post_meta($sub->ID, '_ikg_user_ip', true)) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            
            // Link para editar el formulario y ver todas las submissions
            echo '<p style="padding: 10px 20px; margin: 0; background: #f9f9f9; border-top: 1px solid #eee;">';
            echo '<a href="' . get_edit_post_link($form->ID) . '">Ver todas las submissions de este formulario →</a>';
            echo '</p>';
        } else {
            echo '<p class="ikg-empty-message">No hay submissions para este formulario.</p>';
        }
        
        echo '</div>';
        echo '</div>';
    }
    
    echo '</div>';
}

// Añadir columna de submissions al listado de formularios
add_filter('manage_formularios_posts_columns', 'ikg_add_submissions_column');
add_action('manage_formularios_posts_custom_column', 'ikg_render_submissions_column', 10, 2);

function ikg_add_submissions_column($columns) {
    $new_columns = [];
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['submissions'] = 'Submissions';
        }
    }
    return $new_columns;
}

function ikg_render_submissions_column($column, $post_id) {
    if ($column === 'submissions') {
        $count = count(get_posts([
            'post_type'      => 'ikg_submission',
            'post_parent'    => $post_id,
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]));
        
        echo '<span style="background: #f0f0f0; padding: 3px 8px; border-radius: 3px;">' . $count . '</span>';
    }
}
