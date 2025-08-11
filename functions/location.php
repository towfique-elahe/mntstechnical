<?php
/**
 * Location CPT with depth-based permalinks (hard-coded bases)
 * URLs:
 *  /state/%postname%
 *  /city/%postname%
 *  /area/%postname%
 */

/** HARD-CODED BASES */
if ( ! function_exists('mnts_location_bases') ) {
	function mnts_location_bases() {
		return [
			'top'        => 'state',
			'child'      => 'city',
			'grandchild' => 'area',
		];
	}
}

/** Register CPT */
add_action('init', function () {
	$labels = [
		'name'               => _x('Locations', 'post type general name', 'mntstechnical'),
		'singular_name'      => _x('Location', 'post type singular name', 'mntstechnical'),
		'menu_name'          => _x('Locations', 'admin menu', 'mntstechnical'),
		'name_admin_bar'     => _x('Location', 'add new on admin bar', 'mntstechnical'),
		'add_new'            => _x('Add New', 'location', 'mntstechnical'),
		'add_new_item'       => __('Add New Location', 'mntstechnical'),
		'new_item'           => __('New Location', 'mntstechnical'),
		'edit_item'          => __('Edit Location', 'mntstechnical'),
		'view_item'          => __('View Location', 'mntstechnical'),
		'all_items'          => __('All Locations', 'mntstechnical'),
		'search_items'       => __('Search Locations', 'mntstechnical'),
		'parent_item_colon'  => __('Parent Locations:', 'mntstechnical'),
		'not_found'          => __('No locations found.', 'mntstechnical'),
		'not_found_in_trash' => __('No locations found in Trash.', 'mntstechnical'),
	];

    $args = [
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-location',
        'supports'           => ['title','editor','excerpt','author','thumbnail','comments','revisions','custom-fields','page-attributes'],
        'has_archive'        => false,
        'hierarchical'       => true,
        'publicly_queryable' => true,
        'query_var'          => true,
        'rewrite'            => false,
        'show_in_nav_menus'  => true,
        'taxonomies'         => ['category','post_tag'], // ← add this
    ];

    register_post_type('location', $args);

    add_action('init', function () {
        register_taxonomy_for_object_type('category', 'location');
        register_taxonomy_for_object_type('post_tag', 'location');
    }, 11);
}, 5);

/** Generate permalinks based on depth */
function mnts_location_post_type_link( $permalink, $post, $leavename, $sample ) {
	if ( $post->post_type !== 'location' ) return $permalink;

	$bases = mnts_location_bases();

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
add_filter( 'post_type_link', 'mnts_location_post_type_link', 10, 4 );

/** Inject rewrite rules during flush */
add_filter('generate_rewrite_rules', function( $wp_rewrite ) {
	$b = mnts_location_bases();
	$new = [];
	$new['^' . preg_quote($b['top'], '/')        . '/([^/]+)/?$'] = 'index.php?post_type=location&name=$matches[1]';
	$new['^' . preg_quote($b['child'], '/')      . '/([^/]+)/?$'] = 'index.php?post_type=location&name=$matches[1]';
	$new['^' . preg_quote($b['grandchild'], '/') . '/([^/]+)/?$'] = 'index.php?post_type=location&name=$matches[1]';
	$wp_rewrite->rules = $new + $wp_rewrite->rules;
	return $wp_rewrite;
});

/** Flush once on theme switch */
add_action('after_switch_theme', function () {
	delete_option('rewrite_rules');
	flush_rewrite_rules(false);
});