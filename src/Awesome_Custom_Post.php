<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://hiteshmakvana.com
 * @since      1.0.0
 *
 * @package    Awesome_Custom_Post
 */

namespace Awesome_Custom_Post;

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Awesome_Custom_Post
 * @author     Justin Vogt <mail@juvo-design.de>
 */
class Awesome_Custom_Post {



	const PLUGIN_NAME    = 'awesome-custompost';
	const PLUGIN_VERSION = '1.0.0';

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin
	 *
	 * @var Loader
	 */
	protected Loader $loader;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies(): void {

		$this->loader = new Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale(): void {

		$plugin_i18n = new I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 */
	private function define_admin_hooks(): void {

		add_action(
			'admin_enqueue_scripts',
			function () {
				$this->enqueue_bud_entrypoint( 'awesome-custompost-admin' );
			},
			100
		);

		// Add Setup Command
		$this->loader->add_cli( 'setup', new Cli\Setup() );
	}

	/**
	 * Enqueue a bud entrypoint
	 *
	 * @param string              $entry Name if the entrypoint defined in bud.js .
	 * @param array<string,mixed> $localize_data Array of associated data. See https://developer.wordpress.org/reference/functions/wp_localize_script/ .
	 */
	private function enqueue_bud_entrypoint( string $entry, array $localize_data = array() ): void {
		$entrypoints_manifest = AWESOME_CUSTOM_POST_PATH . '/dist/entrypoints.json';

		// Try to get WordPress filesystem. If not possible load it.
		global $wp_filesystem;
		if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$filesystem = new \WP_Filesystem_Direct( false );
		if ( ! $filesystem->exists( $entrypoints_manifest ) ) {
			return;
		}

		// parse json file
		$entrypoints = json_decode( $filesystem->get_contents( $entrypoints_manifest ) );

		// Iterate entrypoint groups
		foreach ( $entrypoints as $key => $bundle ) {

			// Only process the entrypoint that should be enqueued per call
			if ( $key !== $entry ) {
				continue;
			}

			// Iterate js and css files
			foreach ( $bundle as $type => $files ) {
				foreach ( $files as $file ) {
					if ( 'js' === $type ) {
						wp_enqueue_script(
							self::PLUGIN_NAME . "/$file",
							AWESOME_CUSTOM_POST_URL . 'dist/' . $file,
							$bundle->dependencies ?? array(),
							self::PLUGIN_VERSION,
							true,
						);

							// Maybe localize js
						if ( ! empty( $localize_data ) ) {
							wp_localize_script( self::PLUGIN_NAME . "/$file", str_replace( '-', '_', self::PLUGIN_NAME ), $localize_data );

							// Unset after localize since we only need to localize one script per bundle so on next iteration will be skipped
							unset( $localize_data );
						}
					}

					if ( 'css' === $type ) {
						wp_enqueue_style(
							self::PLUGIN_NAME . "/$file",
							AWESOME_CUSTOM_POST_URL . 'dist/' . $file,
							array(),
							self::PLUGIN_VERSION,
						);
					}
				}
			}
		}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks(): void {

		add_action(
			'wp_enqueue_scripts',
			function () {
				$this->enqueue_bud_entrypoint( 'awesome-custompost-frontend' );
			},
			100
		);

		add_action( 'init', array( $this, 'awesome_custompost_register_posts' ) );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 */
	public function run(): void {
		$this->loader->run();
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return Loader Orchestrates the hooks of the plugin.
	 */
	public function get_loader(): Loader {
		return $this->loader;
	}

	/**
	 * Register Custom post type
	 *
	 * @return void
	 */
	public function awesome_custompost_register_posts(): void {

		register_post_type(
			'book',
			array(
				'label'               => '', // Add this to satisfy PHPStan
				'labels'              => array(
					'name'                  => _x( 'Books', 'Post type general name', 'awesome-custompost' ),
					'singular_name'         => _x( 'Book', 'Post type singular name', 'awesome-custompost' ),
					'menu_name'             => _x( 'Books', 'Admin Menu text', 'awesome-custompost' ),
					'name_admin_bar'        => _x( 'Book', 'Add New on Toolbar', 'awesome-custompost' ),
					'add_new'               => __( 'Add New', 'awesome-custompost' ),
					'add_new_item'          => __( 'Add New Book', 'awesome-custompost' ),
					'new_item'              => __( 'New Book', 'awesome-custompost' ),
					'edit_item'             => __( 'Edit Book', 'awesome-custompost' ),
					'view_item'             => __( 'View Book', 'awesome-custompost' ),
					'all_items'             => __( 'All Books', 'awesome-custompost' ),
					'search_items'          => __( 'Search Books', 'awesome-custompost' ),
					'parent_item_colon'     => __( 'Parent Books:', 'awesome-custompost' ),
					'not_found'             => __( 'No books found.', 'awesome-custompost' ),
					'not_found_in_trash'    => __( 'No books found in Trash.', 'awesome-custompost' ),
					'featured_image'        => _x( 'Book Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'awesome-custompost' ),
					'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'awesome-custompost' ),
					'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'awesome-custompost' ),
					'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'awesome-custompost' ),
					'archives'              => _x( 'Book archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'awesome-custompost' ),
					'insert_into_item'      => _x( 'Insert into book', 'Overrides the “Insert into post”/”Insert into page” phrase. Added in 4.4', 'awesome-custompost' ),
					'uploaded_to_this_item' => _x( 'Uploaded to this book', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase. Added in 4.4', 'awesome-custompost' ),
					'filter_items_list'     => _x( 'Filter books list', 'Screen reader text for the filter links heading on the post type listing screen. Added in 4.4', 'awesome-custompost' ),
					'items_list_navigation' => _x( 'Books list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Added in 4.4', 'awesome-custompost' ),
					'items_list'            => _x( 'Books list', 'Screen reader text for the items list heading on the post type listing screen. Added in 4.4', 'awesome-custompost' ),
				),
				'description'         => '', // Add this to satisfy PHPStan
				'public'              => true,
				'hierarchical'        => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'query_var'           => true,
				'rewrite'             => array( 'slug' => 'book' ),
				'capability_type'     => 'post',
				'has_archive'         => true,
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' ),
			)
		);
	}
}
