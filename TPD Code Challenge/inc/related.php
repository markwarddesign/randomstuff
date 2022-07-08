<?php

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
