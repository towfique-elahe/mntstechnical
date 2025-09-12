<?php

add_action('init', function () {
    $labels = [
        'name'               => _x('Projects', 'post type general name', 'mntstechnical'),
        'singular_name'      => _x('Project', 'post type singular name', 'mntstechnical'),
        'menu_name'          => _x('Projects', 'admin menu', 'mntstechnical'),
        'name_admin_bar'     => _x('Project', 'add new on admin bar', 'mntstechnical'),
        'add_new'            => _x('Add New', 'project', 'mntstechnical'),
        'add_new_item'       => __('Add New Project', 'mntstechnical'),
        'new_item'           => __('New Project', 'mntstechnical'),
        'edit_item'          => __('Edit Project', 'mntstechnical'),
        'view_item'          => __('View Project', 'mntstechnical'),
        'all_items'          => __('All Projects', 'mntstechnical'),
        'search_items'       => __('Search Projects', 'mntstechnical'),
        'not_found'          => __('No projects found.', 'mntstechnical'),
        'not_found_in_trash' => __('No projects found in Trash.', 'mntstechnical'),
    ];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => ['title','editor','excerpt','author','thumbnail','comments','revisions','custom-fields'],
        'has_archive'        => true,
        'hierarchical'       => false,
        'publicly_queryable' => true,
        'query_var'          => true,
        'rewrite'            => ['slug' => 'projects', 'with_front' => false],
        'show_in_nav_menus'  => true,
        'taxonomies'         => ['category','post_tag'],
    ];

    register_post_type('project', $args);

    add_action('init', function () {
        register_taxonomy_for_object_type('category', 'project');
        register_taxonomy_for_object_type('post_tag', 'project');
    }, 11);
}, 5);