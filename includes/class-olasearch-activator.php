<?php

/**
 * Fired during plugin activation
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 * @author     Ola Search <hello@olasearch.com>
 */
class OlaSearch_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( ! get_option( 'olasearch' ) ) {
			//make sure we have default values for all sections;
			update_option( 'olasearch', OlaSearch_Admin::$section_defaults );
			// mark all as not indexed
			OlaSearch_Indexer::reset_posts_to_index();
		}
		// create search page
		OlaSearch_Admin::create_search_page();
	}

}
