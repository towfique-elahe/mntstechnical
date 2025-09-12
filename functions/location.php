<?php
/**
 * Location CPT with hierarchy-based permalinks under /locations/...
 * Examples:
 *  /locations/parent
 *  /locations/parent/child
 *  /locations/grandparent/parent/child
 */

/** SINGLE ROOT BASE */
if ( ! function_exists('mnts_location_bases') ) {
	function mnts_location_bases() {
		return [
			'root' => 'locations',
		];
	}
}

/** Register CPT (keep rewrite=false; we inject our own rules) */
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
		'rewrite'            => false, // custom rules below
		'show_in_nav_menus'  => true,
		'taxonomies'         => ['category','post_tag'],
	];

	register_post_type('location', $args);

	add_action('init', function () {
		register_taxonomy_for_object_type('category', 'location');
		register_taxonomy_for_object_type('post_tag', 'location');
	}, 11);
}, 5);

/** Helper: ancestor slugs from top-most to immediate parent */
if ( ! function_exists('mnts_location_ancestor_slugs') ) {
	function mnts_location_ancestor_slugs( $post_id ) {
		$ancestors = get_post_ancestors( $post_id ); // closest parent upward
		if ( empty( $ancestors ) ) return [];
		$ancestors = array_reverse( $ancestors );     // top-most first
		$slugs = [];
		foreach ( $ancestors as $aid ) {
			$slugs[] = get_post_field( 'post_name', $aid );
		}
		return array_filter( $slugs );
	}
}

/** Generate permalinks based on full depth under /locations/... */
function mnts_location_post_type_link( $permalink, $post, $leavename, $sample ) {
	if ( $post->post_type !== 'location' ) return $permalink;

	$base  = mnts_location_bases()['root'];
	$parts = mnts_location_ancestor_slugs( $post->ID );
	$parts[] = $post->post_name;
	$path  = trailingslashit( $base ) . implode( '/', $parts );

	return home_url( user_trailingslashit( $path ) );
}
add_filter( 'post_type_link', 'mnts_location_post_type_link', 10, 4 );

/**
 * Rewrite:
 * Match any depth under /locations/ and resolve by final slug.
 * NOTE: Ensure 'location' slugs are globally unique to avoid collisions.
 */
add_filter('generate_rewrite_rules', function( $wp_rewrite ) {
	$b = mnts_location_bases();
	$new = [];

	// Matches /locations/foo or /locations/a/b/c/foo
	$new['^' . preg_quote($b['root'], '/') . '/(?:.+/)?([^/]+)/?$'] = 'index.php?post_type=location&name=$matches[1]';

	$wp_rewrite->rules = $new + $wp_rewrite->rules;
	return $wp_rewrite;
});

/** Flush once on theme switch */
add_action('after_switch_theme', function () {
	delete_option('rewrite_rules');
	flush_rewrite_rules(false);
});