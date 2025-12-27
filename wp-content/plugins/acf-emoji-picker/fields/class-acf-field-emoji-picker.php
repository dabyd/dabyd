<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class: acf_field_emoji_picker
 *
 * A basic ACF field type which displays an emoji picker in the admin.
 */
class acf_field_emoji_picker extends acf_field
{
    public function __construct($settings) {
        $this->name = 'emoji_picker';
        $this->label = __('Emoji Picker', 'acf-emoji-picker');
        $this->category = 'choice';
        $this->defaults = array(
            'placeholder' => '',
            'return_format' => 'emoji', // or 'shortcode' or 'codepoint'
        );
        $this->settings = $settings;
        parent::__construct();
    }

    public function render_field_settings( $field ) {
        // example setting: return format
        acf_render_field_setting( $field, array(
            'label'         => __('Return format','acf-emoji-picker'),
            'instructions'  => __('What to save in the database.','acf-emoji-picker'),
            'type'          => 'select',
            'name'          => 'return_format',
            'choices'       => array(
                'emoji' => 'Emoji character (e.g. 😄)',
                'codepoint' => 'Unicode codepoint (e.g. U+1F604)',
                'shortcode' => 'Shortcode (e.g. :smile:) - only if provided by a source'
            )
        ));
    }

    public function render_field( $field ) {

        // set defaults
        $field = array_merge($this->defaults, $field);

        $value = isset($field['value']) ? $field['value'] : '';

        // ID and name
        $id = esc_attr($field['id']);
        $name = esc_attr($field['name']);

        // Output input + picker container
        echo '<div class="acf-emoji-picker-wrap">';
        echo '<input type="text" class="acf-emoji-input regular-text" id="'. $id .'" name="'. $name .'" value="'. esc_attr($value) .'" placeholder="'. esc_attr($field['placeholder']) .'">';
        echo '<button type="button" class="button acf-emoji-open" data-target="#'. $id .'">'. __('Select emoji', 'acf-emoji-picker') .'</button>';
        echo '<div class="acf-emoji-grid" style="display:none;" data-return="'. esc_attr($field['return_format']) .'">';
        echo '<div class="acfp-search-wrap"><input class="acfp-search" placeholder="'. esc_attr__('Search emojis...','acf-emoji-picker') .'"></div>';
        echo '<div class="acfp-categories"></div>';
        echo '<div class="acfp-grid"></div>';
        echo '</div>'; // grid
        echo '</div>';
    }

    public function input_admin_enqueue_scripts() {
        // enqueue the scripts and styles for admin
        wp_enqueue_style( 'acf-emoji-picker-css' );
        // We'll use a small helper JS we include with the plugin
        wp_enqueue_script( 'acf-emoji-picker-js' );

        // Twemoji for rendering uniformly across platforms (cdn loaded by the JS but we expose the default CDN here)
        // wp_enqueue_script('twemoji-cdn', 'https://unpkg.com/twemoji@14.0.2/dist/twemoji.min.js', array(), '14.0.2', true);

        wp_enqueue_script(
            'twemoji-cdn',
            'https://unpkg.com/twemoji@14.0.2/dist/twemoji.min.js',
            array(),
            '14.0.2',
            true
        );

        // Provide localized data for JS
        $data = array(
            'pluginUrl' => plugin_dir_url(__FILE__) . '../assets/',
            // emoji list CDN - fallback to unpkg emoji.json package which contains many emojis and metadata
            'emojiDataUrl' => 'https://unpkg.com/emoji.json@13.1.0/emoji.json',
            'strings' => array(
                'loading' => __('Loading emojis...', 'acf-emoji-picker'),
            ),
        );
        wp_localize_script('acf-emoji-picker-js', 'acfEmojiPicker', $data);
    }

    // no extra ajax in this simple version, but could be added for skin tones, etc.
}

// initialize
new acf_field_emoji_picker( array(
    'version' => '1.0.0',
    'url'     => plugin_dir_url( __FILE__ )
) );
