<?php
/**
 * Plugin Name: Divi Blog Category Sorter
 * Plugin URI: https://github.com/jalevin/divi-category-sorter
 * Description: This plugin uses Divi blog formatting to display posts from many categories
 * version: 1 
 * Author: Jeff Levin
 * Author URI: http://levinology.com
 * License: GPL2
 */


//takes array of categories and builds into validated category string 'salads+snacks'
function build_category_string($categories){
	$valid_category_slugs = '';

	$index = -1; //track position in array
	//verify categories slug is valid
	foreach($categories as $category){
		$index++;
		if( get_term_by('slug', $category, 'category') ){
			if ($index == 0){ // skip + if first category
				$valid_category_slugs .= $category;
			}else{
				$valid_category_slugs .= "+$category";
			}
		}
	}
	return $valid_category_slugs;
}


//button generation
function divi_category_button_template($atts = [], $content = null, $tag = ''){
		$atts = array_change_key_case((array)$atts, CASE_LOWER);
		$category = $atts['category'];
		$img_url = $atts['img_url'];
		$img_hover_url = $atts['img_hover_url'];
		$output = '';

		// if categories query string exists, rebuild it
		if($_GET['categories']){ 

			//query strings foo+bar actually gets pulled as "foo bar".
			//thus we explode on a blank space since the category slug will never have a space in it
			$categories = explode(' ', $_GET['categories']);

			if(in_array($category, $categories)){
				//remove from category if it's there
				$categories = array_diff($categories, [$category]);
				//category is selected and in query string. Use hover image
				$active_img = $img_hover_url; 
				$hover_img = $img_url;
			}else{
				//add to category
				$categories[] = $category;
				//category not in query string. use normal image
				$active_img = $img_url;
				$hover_img = $img_hover_url;
			}
		}else{
			$categories[] = $category;
			$active_img = $img_url;
			$hover_img = $img_hover_url;
		}

		//https://developer.wordpress.org/reference/functions/add_query_arg/
		//replace categories value with new build string
		$url =  add_query_arg( 'categories', build_category_string($categories)); 

		$output .= "<a href='$url'><img src='$active_img'	onmouseover=\"this.src='$hover_img'\" onmouseout=\"this.src='$active_img'\" />";
		return $output;
}
add_shortcode('divi_category_button','divi_category_button_template');

function divi_category_template($atts = [], $content = null, $tag = '') {
/** Displays posts of certain category on page using Divi Blog Template
 *  Handle Pagination
 *  Verifies category slug and gets query
 *  Outputs all posts and pagination buttons
**/

	$atts = array_change_key_case((array)$atts, CASE_LOWER);
	$default_category = $atts['default_category'];

	$output = ''; //total output from function. put here in case need to debug

	//accept param for default categories

	//pagination
	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;

	//get categories
	$categories = ( $_GET['categories'] ) ? $_GET['categories'] : $default_category;
	$categories = explode('+', $categories);

	$valid_category_slugs = build_category_string($categories);
	//$output .= $valid_category_slugs; //for debugging

	$args = array (
		'category_name' => $valid_category_slugs
		//'paged' => $paged,
		//'posts_per_page' => 6 
	);

	$query = new WP_QUERY( $args );

	if ($query->have_posts()) {
		while ( $query->have_posts() ) {
			$query->the_post();
			//loop over posts
				
			$post_id = get_the_id();
			$title = esc_attr(get_the_title());
			$url = get_page_uri();
			$date = get_the_date('M d, Y', $post_id);
			$number_of_comments = get_comments_number();
			$excerpt = get_the_excerpt();
			$image_url = get_the_post_thumbnail_url();

			$output .= "<article id='post-$post_id' class='et_pb_post post-917 post type-post status-publish format-standard has-post-thumbnail hentry'>";
			if ( has_post_thumbnail() ) {
				$output .= "<a href='$url' class='entry-featured-image-url'><img src='$image_url' alt='$title'></a>";
			}
			$output .= "<h2 class='entry-title'><a href='$url'>$title</a></h2>";
			$output .= "<p class='post-meta'><span class='published'>$date</span>    |  $number_of_comments Comments</p>";
			$output .= "<p>$excerpt</p>";
			$output .= "<a href='$url' class='more-link'>read more</a></article>";
		}


		//pagination buttons
		//pagination doesn't work
		//$previous = get_previous_posts_link('Previous');
		//$next = get_next_posts_link('Next', $the_query->max_num_pages);
		//$output .= $previous . '  ' . $next;
	}

	return $output;

}

add_shortcode('divi_categories','divi_categories_template');
?>
