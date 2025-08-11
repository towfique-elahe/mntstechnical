<?php
/**
 * Service CPT with depth-based permalinks (hard-coded bases)
 * URLs:
 *  /services/%postname%
 *  /sub-services/%postname%
 *  /grand-sub-services/%postname%
 */

/** HARD-CODED BASES */
if ( ! function_exists('mnts_service_bases') ) {
	function mnts_service_bases() {
		return [
			'top'        => 'services',
			'child'      => 'sub-services',
			'grandchild' => 'grand-sub-services',
		];
	}
}

/** Register CPT */
add_action('init', function () {
	$labels = [
		'name'               => _x('Services', 'post type general name', 'mntstechnical'),
		'singular_name'      => _x('Service', 'post type singular name', 'mntstechnical'),
		'menu_name'          => _x('Services', 'admin menu', 'mntstechnical'),
		'name_admin_bar'     => _x('Service', 'add new on admin bar', 'mntstechnical'),
		'add_new'            => _x('Add New', 'service', 'mntstechnical'),
		'add_new_item'       => __('Add New Service', 'mntstechnical'),
		'new_item'           => __('New Service', 'mntstechnical'),
		'edit_item'          => __('Edit Service', 'mntstechnical'),
		'view_item'          => __('View Service', 'mntstechnical'),
		'all_items'          => __('All Services', 'mntstechnical'),
		'search_items'       => __('Search Services', 'mntstechnical'),
		'parent_item_colon'  => __('Parent Services:', 'mntstechnical'),
		'not_found'          => __('No services found.', 'mntstechnical'),
		'not_found_in_trash' => __('No services found in Trash.', 'mntstechnical'),
	];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-hammer',
        'supports'           => ['title','editor','excerpt','author','thumbnail','comments','revisions','custom-fields','page-attributes'],
        'has_archive'        => false,
        'hierarchical'       => true,
        'publicly_queryable' => true,
        'query_var'          => true,
        'rewrite'            => false,
        'show_in_nav_menus'  => true,
        'taxonomies'         => ['category','post_tag'],
    ];

	register_post_type('service', $args);
    
    add_action('init', function () {
        register_taxonomy_for_object_type('category', 'service');
        register_taxonomy_for_object_type('post_tag', 'service');
    }, 11);
}, 5);

/** Generate permalinks based on depth */
function mnts_service_post_type_link( $permalink, $post, $leavename, $sample ) {
	if ( $post->post_type !== 'service' ) return $permalink;

	$bases = mnts_service_bases();

	$depth = 0;
	$parent = (int) $post->post_parent;
	while ( $parent ) {
		$depth++;
		$parent = (int) get_post_field( 'post_parent', $parent );
		if ( $depth > 20 ) break;
	}

	$base = ( $depth === 0 ) ? $bases['top'] : ( $depth === 1 ? $bases['child'] : $bases['grandchild'] );
	$slug = $post->post_name;

	return home_url( user_trailingslashit( trailingslashit( $base ) . $slug ) );
}
add_filter( 'post_type_link', 'mnts_service_post_type_link', 10, 4 );

/** Inject rewrite rules during flush */
add_filter('generate_rewrite_rules', function( $wp_rewrite ) {
	$b = mnts_service_bases();
	$new = [];
	$new['^' . preg_quote($b['top'], '/')        . '/([^/]+)/?$'] = 'index.php?post_type=service&name=$matches[1]';
	$new['^' . preg_quote($b['child'], '/')      . '/([^/]+)/?$'] = 'index.php?post_type=service&name=$matches[1]';
	$new['^' . preg_quote($b['grandchild'], '/') . '/([^/]+)/?$'] = 'index.php?post_type=service&name=$matches[1]';
	$wp_rewrite->rules = $new + $wp_rewrite->rules;
	return $wp_rewrite;
});

/** Flush once on theme switch (so rules exist) */
add_action('after_switch_theme', function () {
	delete_option('rewrite_rules');
	flush_rewrite_rules(false);
});