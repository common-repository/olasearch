<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    OlaSearch
 * @subpackage OlaSearch/includes
 * @author     Ola Search <hello@olasearch.com>
 */
class OlaSearch_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
		// remove all meta keys
		OlaSearch_Indexer::remove_post_meta_keys();
		// delete search page
		OlaSearch_Admin::delete_search_page();
	}

}
