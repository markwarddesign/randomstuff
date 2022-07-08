<?php
/**
 * Neve functions.php file
 *
 * Author:          Andrei Baicus <andrei@themeisle.com>
 * Created on:      17/08/2018
 *
 * @package Neve
 */

define( 'NEVE_VERSION', '3.3.1' );
define( 'NEVE_INC_DIR', trailingslashit( get_template_directory() ) . 'inc/' );
define( 'NEVE_ASSETS_URL', trailingslashit( get_template_directory_uri() ) . 'assets/' );
define( 'NEVE_MAIN_DIR', get_template_directory() . '/' );

if ( ! defined( 'NEVE_DEBUG' ) ) {
	define( 'NEVE_DEBUG', false );
}
define( 'NEVE_NEW_DYNAMIC_STYLE', true );
/**
 * Buffer which holds errors during theme inititalization.
 *
 * @var WP_Error $_neve_bootstrap_errors
 */
global $_neve_bootstrap_errors;

$_neve_bootstrap_errors = new WP_Error();

if ( version_compare( PHP_VERSION, '7.0' ) < 0 ) {
	$_neve_bootstrap_errors->add(
		'minimum_php_version',
		sprintf(
		/* translators: %s message to upgrade PHP to the latest version */
			__( "Hey, we've noticed that you're running an outdated version of PHP which is no longer supported. Make sure your site is fast and secure, by %1\$s. Neve's minimal requirement is PHP%2\$s.", 'neve' ),
			sprintf(
			/* translators: %s message to upgrade PHP to the latest version */
				'<a href="https://wordpress.org/support/upgrade-php/">%s</a>',
				__( 'upgrading PHP to the latest version', 'neve' )
			),
			'7.0'
		)
	);
}
/**
 * A list of files to check for existance before bootstraping.
 *
 * @var array Files to check for existance.
 */

$_files_to_check = defined( 'NEVE_IGNORE_SOURCE_CHECK' ) ? [] : [
	NEVE_MAIN_DIR . 'vendor/autoload.php',
	NEVE_MAIN_DIR . 'style-main-new.css',
	NEVE_MAIN_DIR . 'assets/js/build/modern/frontend.js',
	NEVE_MAIN_DIR . 'assets/apps/dashboard/build/dashboard.js',
	NEVE_MAIN_DIR . 'assets/apps/customizer-controls/build/controls.js',
];
foreach ( $_files_to_check as $_file_to_check ) {
	if ( ! is_file( $_file_to_check ) ) {
		$_neve_bootstrap_errors->add(
			'build_missing',
			sprintf(
			/* translators: %s: commands to run the theme */
				__( 'You appear to be running the Neve theme from source code. Please finish installation by running %s.', 'neve' ), // phpcs:ignore WordPress.Security.EscapeOutput
				'<code>composer install --no-dev &amp;&amp; yarn install --frozen-lockfile &amp;&amp; yarn run build</code>'
			)
		);
		break;
	}
}
/**
 * Adds notice bootstraping errors.
 *
 * @internal
 * @global WP_Error $_neve_bootstrap_errors
 */
function _neve_bootstrap_errors() {
	global $_neve_bootstrap_errors;
	printf( '<div class="notice notice-error"><p>%1$s</p></div>', $_neve_bootstrap_errors->get_error_message() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

if ( $_neve_bootstrap_errors->has_errors() ) {
	/**
	 * Add notice for PHP upgrade.
	 */
	add_filter( 'template_include', '__return_null', 99 );
	switch_theme( WP_DEFAULT_THEME );
	unset( $_GET['activated'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	add_action( 'admin_notices', '_neve_bootstrap_errors' );

	return;
}

/**
 * Themeisle SDK filter.
 *
 * @param array $products products array.
 *
 * @return array
 */
function neve_filter_sdk( $products ) {
	$products[] = get_template_directory() . '/style.css';

	return $products;
}

add_filter( 'themeisle_sdk_products', 'neve_filter_sdk' );

require_once 'globals/migrations.php';
require_once 'globals/utilities.php';
require_once 'globals/hooks.php';
require_once 'globals/sanitize-functions.php';
require_once get_template_directory() . '/start.php';

/**
 * If the new widget editor is available,
 * we re-assign the widgets to hfg_footer
 */
if ( neve_is_new_widget_editor() ) {
	/**
	 * Re-assign the widgets to hfg_footer
	 *
	 * @param array  $section_args The section arguments.
	 * @param string $section_id The section ID.
	 * @param string $sidebar_id The sidebar ID.
	 *
	 * @return mixed
	 */
	function neve_customizer_custom_widget_areas( $section_args, $section_id, $sidebar_id ) {
		if ( strpos( $section_id, 'widgets-footer' ) ) {
			$section_args['panel'] = 'hfg_footer';
		}
		return $section_args;
	}
	add_filter( 'customizer_widgets_section_args', 'neve_customizer_custom_widget_areas', 10, 3 );
}

require_once get_template_directory() . '/header-footer-grid/loader.php';


/**
 * Custom Post Types
 */
require get_template_directory() . '/inc/cpt.php';


/** 
* Retrieve Related Posts
*/
function ci_get_related_posts( $post_id, $related_count, $args = array() ) {
   $args = wp_parse_args( (array) $args, array(
       'orderby' => array(
                'date' =>'DESC',
             ),
       'return'  => 'query',
   ) );
 
   $related_args = array(
       'post_type'      => get_post_type( $post_id ),
       'posts_per_page' => $related_count,
       'post_status'    => 'publish',
       'orderby'        => $args['orderby'],
       'tax_query'      => array(),
	   'date_query' => array(
			array(
				'after' => '-30 days',
				'column' => 'post_date',
				),
	    ),
   );
 
   $post       = get_post( $post_id );
   $taxonomies = get_object_taxonomies( $post, 'names' );
 
   foreach ( $taxonomies as $taxonomy ) {
       $terms = get_the_terms( $post_id, $taxonomy );
       if ( empty( $terms ) ) {
           continue;
       }
       $term_list = wp_list_pluck( $terms, 'slug' );
       $related_args['tax_query'][] = array(
           'taxonomy' => $taxonomy,
           'field'    => 'slug',
           'terms'    => $term_list
       );
   }
 
   if ( count( $related_args['tax_query'] ) > 1 ) {
       $related_args['tax_query']['relation'] = 'OR';
   }
 
   if ( $args['return'] == 'query' ) {
       return new WP_Query( $related_args );
   } else {
       return $related_args;
   }
}

/**
* Display Related Posts
*/
function tpd_related_posts(){
	$related = ci_get_related_posts( get_the_ID(), 5 );

		if( $related->have_posts() ):
	?>
		<div class="post-navigation">
			<h3>Related posts</h3>
			<ul>
				<?php while( $related->have_posts() ): $related->the_post(); ?>
				<li><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" target="_self"><?php echo get_the_date(); ?> - <?php the_title(); ?></a></li>
				<?php endwhile; ?>
			</ul>
		</div>
		<?php
		endif;
		wp_reset_postdata();
}
add_action('neve_after_post_content','tpd_related_posts');