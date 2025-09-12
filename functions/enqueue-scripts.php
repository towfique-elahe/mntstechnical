<?php
function mntstechnical_register_styles() {
    $version = wp_get_theme()->get('Version');

    wp_enqueue_style('ionicons', 'https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/ionicons.min.css', [], null);

    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], null);

    wp_enqueue_style('mntstechnical-style', get_stylesheet_uri(), [], $version);

    $styles = [
        'mntstechnical-root-style'          => 'assets/css/root.css',
    ];

    foreach ($styles as $handle => $path) {
        wp_enqueue_style($handle, get_template_directory_uri() . '/' . $path, [], $version);
    }

    if (did_action('elementor/loaded')) {
        wp_enqueue_style('elementor-frontend');
        if (class_exists('ElementorPro\Plugin')) {
            wp_enqueue_style('elementor-pro-frontend');
        }
    }
}
add_action('wp_enqueue_scripts', 'mntstechnical_register_styles');

function mntstechnical_register_scripts() {
    $version = wp_get_theme()->get('Version');

    wp_enqueue_script('jquery');

    wp_enqueue_script('ionicons-esm', 'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js', [], null, true);

    wp_enqueue_script('ionicons-nomodule', 'https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js', [], null, true);

    foreach ($scripts as $handle => $path) {
        wp_enqueue_script($handle, get_template_directory_uri() . '/' . $path, [], $version, true);
    }
}
add_action('wp_enqueue_scripts', 'mntstechnical_register_scripts');