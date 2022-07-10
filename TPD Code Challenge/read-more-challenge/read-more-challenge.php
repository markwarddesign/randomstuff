<?php
/**
 * Plugin Name:       Read More Challenge
 * Description:       Simple Gutenberg Block to add Read More Link for a Selected Post
 * Requires at least: 5.9
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Mark Ward
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       read-more-challenge
 *
 * @package           create-block
 */


function create_block_read_more_challenge_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', 'create_block_read_more_challenge_block_init' );
