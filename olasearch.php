<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://olasearch.com
 * @since             1.0.0
 * @package           OlaSearch
 *
 * @wordpress-plugin
 * Plugin Name:       Ola Searchbot
 * Plugin URI:        https://olasearch.com/products/searchbot-wordpress
 * Description:       Use our conversational searchbot to increase engagement by allowing your visitors to find and explore more of your website.
 * Version:           2.0.5
 * Author:            Ola Search
 * Author URI:        https://olasearch.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       olasearch
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
if ( ! defined( 'OLA_SEARCH_PLUGIN_DIRECTORY' ) ) {
	define( 'OLA_SEARCH_PLUGIN_DIRECTORY', plugin_dir_path( __FILE__ ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-olasearch-activator.php
 */
function activate_olasearch() {
	require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch-activator.php';
	OlaSearch_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-olasearch-deactivator.php
 */
function deactivate_olasearch() {
	require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch-deactivator.php';
	OlaSearch_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_olasearch' );
register_deactivation_hook( __FILE__, 'deactivate_olasearch' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
// Include the main OlaSearch class.
if ( ! class_exists( 'OlaSearch' ) ) {
	require_once OLA_SEARCH_PLUGIN_DIRECTORY . 'includes/class-olasearch.php';
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_olasearch() {

	$plugin = new OlaSearch();
	$plugin->run();

}

run_olasearch();
