<?php
/*
Plugin Name: ACF Emoji Picker
Description: Adds an 'Emoji Picker' field type for Advanced Custom Fields. Uses Twemoji for consistent rendering and can load a full emoji dataset from a CDN.
Version: 1.0.0
Author: ChatGPT (generated)
Text Domain: acf-emoji-picker
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'ACF_EMOJI_PICKER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ACF_EMOJI_PICKER_URL', plugin_dir_url( __FILE__ ) );

/**
 * Include the field type class when ACF is loading its fields.
 * For ACF < 5.0 or different versions the hook name may differ.
 */
add_action('acf/include_field_types', 'acf_emoji_picker_include_field'); // v5+
add_action('acf/register_fields', 'acf_emoji_picker_include_field'); // v4

function acf_emoji_picker_include_field() {
    include_once ACF_EMOJI_PICKER_PATH . 'fields/class-acf-field-emoji-picker.php';
}

// Register assets for direct linking (optional)
add_action('init', function(){
    // register scripts/styles so WP can enqueue them from other contexts if needed
    wp_register_script('acf-emoji-picker-js', ACF_EMOJI_PICKER_URL . 'assets/js/emoji-picker-admin.js', array('jquery'), '1.0.0', true);
    wp_register_style('acf-emoji-picker-css', ACF_EMOJI_PICKER_URL . 'assets/css/emoji-picker-admin.css', array(), '1.0.0');
});

// Esta función no es necesaria porque el campo ACF ya encola sus propios scripts
// mediante el método input_admin_enqueue_scripts() en la clase del campo
/*
function acf_emoji_picker_assets() {
    // Twemoji des de unpkg o jsdelivr
    wp_enqueue_script(
        'twemoji',
        'https://unpkg.com/twemoji@14.0.2/dist/twemoji.min.js',
        array(),
        '14.0.2',
        true
    );

    // El nostre script del picker
    wp_enqueue_script(
        'acf-emoji-picker',
        plugin_dir_url(__FILE__) . 'assets/js/emoji-picker.js',
        array('twemoji'),
        '1.0.0',
        true
    );

    // Estils bàsics
    wp_enqueue_style(
        'acf-emoji-picker',
        plugin_dir_url(__FILE__) . 'assets/css/emoji-picker.css',
        array(),
        '1.0.0'
    );
}
add_action('admin_enqueue_scripts', 'acf_emoji_picker_assets');
*/
