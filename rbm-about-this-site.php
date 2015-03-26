<?php
/*
Plugin Name: RBM About This Site
Description: Creates an "About This Site" page for Real Big Marketing.
Version: 1.0.0
Author: joelworsham
Author URI: http://joelworsham.com
*/

if ( ! class_exists( 'RBM_AboutThisSite' ) ) {

	class RBM_AboutThisSite {

		/**
		 * The plugin version.
		 *
		 * @since 0.1.0
		 *
		 * @var string
		 */
		protected $version = '0.1.0';

		/**
		 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		private function __clone() { }

		/**
		 * Private unserialize method to prevent unserializing of the *Singleton* instance.
		 *
		 * @since 0.1.0
		 *
		 * @return void
		 */
		private function __wakeup() { }

		/**
		 * Returns the *Singleton* instance of this class.
		 *
		 * @since     0.1.0
		 *
		 * @staticvar Singleton $instance The *Singleton* instances of this class.
		 *
		 * @return RBM_AboutThisSite The *Singleton* instance.
		 */
		public static function getInstance() {

			static $instance = null;
			if ( null === $instance ) {
				$instance = new static();
			}

			return $instance;
		}

		/**
		 * Protected constructor to prevent creating a new instance of the *Singleton* via the `new` operator from
		 * outside of this class.
		 *
		 * @since 0.1.0
		 */
		protected function __construct() {

			// Initialize the plugin
			$this::_add_actions();
		}

		/**
		 * All initialize hooks.
		 *
		 * @since 0.1.0
		 */
		private function _add_actions() {

			// Replace the content on the "About This Site" page
			add_filter( 'the_content', array( $this, '_replace_content' ) );

			// Create / Make sure the page exists
			add_action( 'after_setup_theme', array( $this, '_create_page' ) );

			// Hide the page from admin
			add_action( 'pre_get_posts', array( $this, '_hide_page_from_admin' ) );
		}

		/**
		 * Swaps out the old content with the "About This Site" content, if on the correct page.
		 *
		 * @since 0.1.0
		 *
		 * @param string $content The old content.
		 *
		 * @return string The new or old content.
		 */
		function _replace_content( $content = '' ) {

			global $post;

			// Fail-safe
			if ( ! $post || ! ( $post instanceof WP_Post ) ) {
				return $content;
			}

			// Must be on "About This Site" page
			if ( $post->post_name != 'about-this-site' ) {
				return $content;
			}

			// Figure out which template to use
			if ( file_exists( get_stylesheet_directory() . '/rbm-about-this-site.html' ) ) {
				$template = file_get_contents( get_stylesheet_directory() . '/rbm-about-this-site.html' );

			} else if ( file_exists( get_template_directory() . '/rbm-about-this-site.html' ) ) {
				$template = file_get_contents( get_template_directory() . '/rbm-about-this-site.html' );

			} else {
				$template = file_get_contents( __DIR__ . '/template.html' );
			}

			return $template;
		}

		/**
		 * Creates the page, and ensures it always exists.
		 *
		 * @since 0.1.0
		 */
		function _create_page() {

			$title = 'About This Site';
			$slug  = 'about-this-site';

			// First, try to get the page
			$page = get_page_by_title( $title, OBJECT, 'page' );

			// If the page doesn't exist, create it
			if ( $page == null ) {

				// Create the page saving the ID
				wp_insert_post(
					array(
						'comment_status' => 'closed',
						'ping_status'    => 'closed',
						'post_author'    => 1,
						'post_title'     => $title,
						'post_name'      => $slug,
						'post_status'    => 'publish',
						'post_type'      => 'page'
					)
				);

				// Otherwise, if the page is in the trash, update its status
			} elseif ( strtolower( $page->post_status ) == 'trash' ) {

				$page->post_status = 'publish';
				wp_update_post( $page );
			}
		}

		/**
		 * Hides the page from the admin list, so it can't be edited.
		 *
		 * @since 0.1.0
		 *
		 * @param $query
		 *
		 * @return mixed The Query.
		 */
		function _hide_page_from_admin( $query ) {

			if ( ! is_admin() ) {
				return $query;
			}

			global $pagenow, $post_type;

			$page = get_page_by_title( 'About This Site', OBJECT, 'page' );

			if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {
				$query->query_vars['post__not_in'] = array( $page->ID );
			}
		}
	}

	global $RBM_AboutThisSite;
	$RBM_AboutThisSite = RBM_AboutThisSite::getInstance();
}