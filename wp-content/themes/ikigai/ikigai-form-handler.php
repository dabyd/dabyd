<?php
/**
 * Ikigai Form Handler
 * Gestiona el envío AJAX de formularios, almacenamiento y envío de emails
 */

// Registrar endpoints AJAX
add_action('wp_ajax_ikg_submit_form', 'ikg_handle_form_submission');
add_action('wp_ajax_nopriv_ikg_submit_form', 'ikg_handle_form_submission');

/**
 * Procesa la submission del formulario
 */
function ikg_handle_form_submission() {
    // 1. Verificar nonce
    if (!isset($_POST['ikg_form_nonce']) || 
        !wp_verify_nonce($_POST['ikg_form_nonce'], 'ikg_form_submit')) {
        wp_send_json_error(['message' => 'Error de seguridad. Recarga la página.']);
    }

    // 2. Obtener ID del formulario
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    if (!$form_id || get_post_type($form_id) !== 'formularios') {
        wp_send_json_error(['message' => 'Formulario no válido.']);
    }

    // 3. Obtener email destinatario del formulario
    $email_destinatario = get_post_meta($form_id, 'email_destinatario', true);
    if (empty($email_destinatario)) {
        $email_destinatario = get_option('admin_email');
    }

    // 4. Recoger y sanitizar datos del formulario
    $form_data = [];
    
    foreach ($_POST as $key => $value) {
        if (!in_array($key, ['action', 'ikg_form_nonce', 'form_id', 'privacy', '_wp_http_referer'])) {
            $form_data[sanitize_key($key)] = sanitize_textarea_field($value);
        }
    }

    // 5. Crear título para la submission
    $count = wp_count_posts('ikg_submission');
    $total = isset($count->publish) ? $count->publish : 0;
    
    $titulo = sprintf(
        'Submission #%d - %s',
        $total + 1,
        wp_date('d/m/Y H:i')
    );

    // 6. Guardar en base de datos como CPT
    $submission_id = wp_insert_post([
        'post_type'   => 'ikg_submission',
        'post_title'  => $titulo,
        'post_status' => 'publish',
        'post_parent' => $form_id,
        'meta_input'  => [
            '_ikg_form_data'    => $form_data,
            '_ikg_form_id'      => $form_id,
            '_ikg_submitted_at' => current_time('mysql'),
            '_ikg_user_ip'      => ikg_get_user_ip(),
            '_ikg_user_agent'   => sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? ''),
        ]
    ]);

    if (is_wp_error($submission_id)) {
        wp_send_json_error(['message' => 'Error al guardar el formulario.']);
    }

    // 7. Preparar y enviar email
    $formulario_nombre = get_the_title($form_id);
    $subject = sprintf('[%s] Nueva submission: %s', get_bloginfo('name'), $formulario_nombre);
    
    $body = ikg_build_email_body($form_data, $formulario_nombre, $submission_id);
    
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
    ];

    // Añadir Reply-To si hay email en el formulario
    foreach ($form_data as $key => $value) {
        if (is_email($value)) {
            $headers[] = 'Reply-To: ' . $value;
            break;
        }
    }

    $email_sent = wp_mail($email_destinatario, $subject, $body, $headers);

    // 8. Guardar estado del email
    update_post_meta($submission_id, '_ikg_email_sent', $email_sent ? 'yes' : 'no');
    update_post_meta($submission_id, '_ikg_email_to', $email_destinatario);

    // 9. Responder al frontend
    wp_send_json_success([
        'message' => '¡Mensaje enviado correctamente! Te contactaremos pronto.',
        'submission_id' => $submission_id,
        'email_sent' => $email_sent
    ]);
}

/**
 * Construye el cuerpo del email en HTML
 */
function ikg_build_email_body($form_data, $form_name, $submission_id) {
    $body = '<html><body style="font-family: Arial, sans-serif; color: #333; max-width: 600px; margin: 0 auto;">';
    $body .= '<div style="background: linear-gradient(135deg, #4A7C59 0%, #6B9B7A 100%); padding: 20px; border-radius: 8px 8px 0 0;">';
    $body .= '<h2 style="color: #fff; margin: 0;">Nueva submission: ' . esc_html($form_name) . '</h2>';
    $body .= '</div>';
    $body .= '<div style="padding: 20px; background: #fff; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px;">';
    $body .= '<table style="width: 100%; border-collapse: collapse; margin: 0 0 20px 0;">';
    
    foreach ($form_data as $key => $value) {
        $label = ucfirst(str_replace(['-', '_'], ' ', $key));
        $body .= '<tr>';
        $body .= '<td style="padding: 12px; border-bottom: 1px solid #eee; background: #f9f9f9; font-weight: bold; width: 30%; vertical-align: top;">' . esc_html($label) . '</td>';
        $body .= '<td style="padding: 12px; border-bottom: 1px solid #eee; vertical-align: top;">' . nl2br(esc_html($value)) . '</td>';
        $body .= '</tr>';
    }
    
    $body .= '</table>';
    $body .= '<div style="background: #f5f5f5; padding: 10px; border-radius: 4px; font-size: 12px; color: #666;">';
    $body .= '<p style="margin: 0 0 5px 0;"><strong>Submission ID:</strong> #' . $submission_id . '</p>';
    $body .= '<p style="margin: 0 0 5px 0;"><strong>Fecha:</strong> ' . wp_date('d/m/Y H:i:s') . '</p>';
    $body .= '<p style="margin: 0;"><strong>IP:</strong> ' . ikg_get_user_ip() . '</p>';
    $body .= '</div>';
    $body .= '</div>';
    $body .= '</body></html>';
    
    return $body;
}

/**
 * Obtiene la IP del usuario
 */
function ikg_get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return sanitize_text_field($_SERVER['HTTP_CLIENT_IP']);
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return sanitize_text_field(trim($ips[0]));
    }
    return sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
}
