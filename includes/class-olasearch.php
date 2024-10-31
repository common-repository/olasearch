<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 */

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
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 * @author     Ola Search <hello@olasearch.com>
 */
class OlaSearch {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      OlaSearch_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

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

		$this->plugin_name = 'olasearch';
		$this->version     = '2.0.5';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - OlaSearch_Loader. Orchestrates the hooks of the plugin.
	 * - OlaSearch_i18n. Defines internationalization functionality.
	 * - OlaSearch_Admin. Defines all hooks for the admin area.
	 * - OlaSearch_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch-loader.php';

		/**
		 *  The class responsible for sending data to ola search
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch-indexer.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'admin/class-olasearch-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'public/class-olasearch-public.php';


		/**
		 * The class responsible for defining all templates.
		 */
		require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'public/class-olasearch-pagetemplater.php';

		$this->loader = new OlaSearch_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the OlaSearch_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new OlaSearch_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new OlaSearch_Admin( $this->get_plugin_name(), $this->get_version() );

		// Add menu item
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );

		// Add settings link to the plugin
		$plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_name . '.php' );
		$this->loader->add_filter( 'plugin_action_links_' . $plugin_basename, $plugin_admin, 'add_action_links' );

		// Save/update our plugin options
		$this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );


		// Index content using ajax call
		$this->loader->add_action( 'wp_ajax_do_index', $plugin_admin, 'ajax_do_index' );


		//load css and scripts when needed
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new OlaSearch_Public( $this->get_plugin_name(), $this->get_version() );

		// trigger action for content changes
		$this->loader->add_action( 'save_post', $plugin_public, 'post_saved' );
		$this->loader->add_action( 'trash_post', $plugin_public, 'post_trashed' );
		$this->loader->add_action( 'delete_post', $plugin_public, 'post_permanently_deleted' );
		$this->loader->add_action( 'transition_post_status', $plugin_public, 'post_status_changed', 10, 3 );

		// redirect to search page
		$this->loader->add_action( 'template_redirect', $plugin_public, 'search_template_redirect' );

		// disable pagination url rewrite for search page
		$this->loader->add_action( 'parse_query', $plugin_public, 'disable_canonical_redirect' );

		// overwrites the search form (targeted for the widget)
		$this->loader->add_filter( 'get_search_form', $plugin_public, 'ola_search_form', 100 );

		// add custom template for search
		$this->loader->add_action( 'plugins_loaded', 'OlaSearch_PageTemplater', 'get_instance' );


		// insert chatbot html tag in the footer
		$this->loader->add_action( 'wp_footer', $plugin_public, 'add_chatbot_tag' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    OlaSearch_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
