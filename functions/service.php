<?php
/**
 * Service CPT with hierarchy-based permalinks under /services/...
 * Examples:
 *  /services/parent
 *  /services/parent/child
 *  /services/grandparent/parent/child
 */

/** SINGLE ROOT BASE */
if ( ! function_exists('mnts_service_bases') ) {
	function mnts_service_bases() {
		return [
			'root' => 'services',
		];
	}
}

/** Register CPT (unchanged except rewrite=false so we control rules) */
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
		'rewrite'            => false, // keep false; we inject our own rules
		'show_in_nav_menus'  => true,
		'taxonomies'         => ['category','post_tag'],
	];

	register_post_type('service', $args);

	add_action('init', function () {
		register_taxonomy_for_object_type('category', 'service');
		register_taxonomy_for_object_type('post_tag', 'service');
	}, 11);
}, 5);

/** Helper: get ancestor slugs from top-most to immediate parent */
if ( ! function_exists('mnts_service_ancestor_slugs') ) {
	function mnts_service_ancestor_slugs( $post_id ) {
		$ancestors = get_post_ancestors( $post_id );     // returns array of IDs from closest parent upward
		if ( empty( $ancestors ) ) return [];
		$ancestors = array_reverse( $ancestors );         // top-most first
		$slugs = [];
		foreach ( $ancestors as $aid ) {
			$slugs[] = get_post_field( 'post_name', $aid );
		}
		return array_filter( $slugs );
	}
}

/** Generate permalinks based on full depth under /services/... */
function mnts_service_post_type_link( $permalink, $post, $leavename, $sample ) {
	if ( $post->post_type !== 'service' ) return $permalink;

	$base  = mnts_service_bases()['root'];
	$parts = mnts_service_ancestor_slugs( $post->ID ); // [grandparent, parent]
	$parts[] = $post->post_name;                       // append current
	$path  = trailingslashit( $base ) . implode( '/', $parts );

	return home_url( user_trailingslashit( $path ) );
}
add_filter( 'post_type_link', 'mnts_service_post_type_link', 10, 4 );

/**
 * Rewrite:
 * Match any depth under /services/ and capture the last segment as the slug.
 * NOTE: This resolves by the final slug only. Ensure slugs are unique within the 'service' CPT.
 */
add_filter('generate_rewrite_rules', function( $wp_rewrite ) {
	$b = mnts_service_bases();
	$new = [];

	// Match: /services/foo OR /services/a/b/c/foo
	$new['^' . preg_quote($b['root'], '/') . '/(?:.+/)?([^/]+)/?$'] = 'index.php?post_type=service&name=$matches[1]';

	$wp_rewrite->rules = $new + $wp_rewrite->rules;
	return $wp_rewrite;
});

/** Flush once on theme switch (so rules exist) */
add_action('after_switch_theme', function () {
	delete_option('rewrite_rules');
	flush_rewrite_rules(false);
});