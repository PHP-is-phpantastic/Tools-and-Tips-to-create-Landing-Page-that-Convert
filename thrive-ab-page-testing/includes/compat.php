<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package ab-page-testing
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

/**
 * Rank Math Breadcrumbs shortcode compatibility - remove the ab-testing parent page from the Rank Math breadcrumbs list
 * The issue is that the original page where the variations are run is shown in the breadcrumb path since it's the parent of the variation.
 * So through this filter, "Home -> page_being_tested -> variation" should become "Home -> variation"
 */
add_filter( 'rank_math/frontend/breadcrumb/items', function ( $breadcrumbs ) {
	$breadcrumbs_count = count( $breadcrumbs );

	$index_to_delete = 0;

	for ( $i = 0; $i < $breadcrumbs_count; $i ++ ) {
		$breadcrumb = $breadcrumbs[ $i ];
		/* Rank Math stores the page URL at the '1' index */
		$page_url = $breadcrumb[1];
		$id       = url_to_postid( $page_url );

		if ( ! empty( $id ) ) {
			$post_parent = get_post_parent( $id );

			if ( $post_parent !== null ) {
				$has_ab_testing_parent = ! empty( get_post_meta( $post_parent->ID, 'thrive_ab_running_test_id', true ) );

				/* if this is the variation, then the parent is located at the previous index and we mark it to be removed */
				if ( $has_ab_testing_parent ) {
					$index_to_delete = $i - 1;
				}
			}
		}
	}

	if ( ! empty( $index_to_delete ) ) {
		unset( $breadcrumbs[ $index_to_delete ] );
		/* re-index */
		$breadcrumbs = array_values( $breadcrumbs );
	}

	return $breadcrumbs;
} );
