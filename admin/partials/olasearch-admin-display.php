<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://olasearch.com
 * @since      1.0.0
 *
 * @package    OlaSearch
 * @subpackage OlaSearch/admin/partials
 */
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="wrap">

    <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <section id="ajax-note"></section>
	<?php
	settings_errors();

	$current = OlaSearch_Admin::tabs();

	echo '<h2></h2>';

	include_once OLA_SEARCH_PLUGIN_DIRECTORY . 'admin/partials/olasearch-admin-display-' . $current . '.php';

	?>

</div>