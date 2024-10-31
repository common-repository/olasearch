<?php

if ( isset( $this->options['api_key'] ) ) {

	if ( isset( $_POST['wipe_data'] ) ) {
		$result = OlaSearch_Indexer::wipe();
		if ( $result['code'] == 200 ) {
			OlaSearch_Admin::notice( 'Deleted all content in the Ola Search.', 'success' );
		} else {
			OlaSearch_Admin::notice( 'Failed to delete all content in the Ola Search.<br>Error: ' . $result['error'] . ( $result['code'] ? '(' . $result['code'] . ' error)' : '' ), 'error' );
		}
	}
}
add_thickbox();

?>
<form id="index" method="post">
    <input type="hidden" name="updated" value="true"/>
	<?php wp_nonce_field( $this->plugin_name . '_update', $this->section() ); ?>
    <table class="widefat">
        <thead>
            <tr>
                <th class="row-title"><strong>Integrate with Ola Search</strong></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    Thank you for installing the Ola Search Plugin for Wordpress. Please enter
                    your <?php echo OlaSearch_Admin::API_KEY_LABEL; ?> in the field below and click 'Save' to get started.<br><br>
                    <ul>
                        <li>
                            <label><strong><?php echo OlaSearch_Admin::API_KEY_LABEL; ?>:</strong></label>
                            <input class="regular-text" type="text" id="api_key" name="api_key" placeholder=""
                                   value="<?php echo isset( $this->options['api_key'] ) ? $this->options['api_key'] : '' ?>"
                                   required="required">
                        </li>
                        <li>
                            <label><?php echo str_repeat('&nbsp;', 15);?></label>
                            <label for="enable_search">
                                <input name="enable_search" type="checkbox" id="enable_search" value="1" <?php echo isset( $this->options['enable_search'] ) && $this->options['enable_search'] ? 'checked' : '' ?>>
                                Enable search for your site</label>
                            <br>
                            <br>
                        </li>
                        <?php if ( isset($_GET['server']) ) { ?>
                            <li>
                                <fieldset>
                                    <label><strong>Server:</strong></label><?php echo $this->options['api_server']; ?>
                                    <legend class="screen-reader-text"><span>Production</span></legend>
                                    <label for="api_server_production">
                                        <input type="radio" id="api_server_production" name="api_server" value="<?php echo OlaSearch_Admin::OLA_SERVER ?>" <?php echo !isset( $this->options['api_server'] ) || $this->options['api_server'] == OlaSearch_Admin::OLA_SERVER ? 'checked' : '' ?>>
                                        <span>Production</span>
                                    </label>

                                    <legend class="screen-reader-text"><span>Internal</span></legend>
                                    <label for="api_server_internal">
                                        <input type="radio" id="api_server_internal" name="api_server" value="<?php echo OlaSearch_Admin::OLA_SERVER_INTERNAL ?>" <?php echo isset( $this->options['api_server'] ) && $this->options['api_server'] == OlaSearch_Admin::OLA_SERVER_INTERNAL ? 'checked' : '' ?>>
                                        <span>Internal</span>
                                    </label>

                                    <legend class="screen-reader-text"><span>Local</span></legend>
                                    <label for="api_server_local">
                                        <input type="radio" id="api_server_local" name="api_server" value="<?php echo OlaSearch_Admin::OLA_SERVER_LOCAL ?>" <?php echo isset( $this->options['api_server'] ) && $this->options['api_server'] == OlaSearch_Admin::OLA_SERVER_LOCAL ? 'checked' : '' ?>>
                                        <span>Local</span>
                                    </label>
                                </fieldset>
                            </li>
                        <?php } ?>
                        <li>
                            <label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</label>
							<?php submit_button( 'Save', 'primary', 'submit', false, ['class' => 'button-primary'] ); ?>
                        </li>
                    </ul>
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    <hr>
    <br>

  <table class="widefat">
      <thead>
          <tr>
              <th class="row-title"><strong>Manage Indexing</strong></th>
              <th class="row-title"></th>
          </tr>
      </thead>
      <tbody>
          <tr>
              <td>
				  <?php if ( ! empty( $this->options['api_key'] ) ) { ?>
                      <div class="wrap">
						  <?php if ( OlaSearch_Indexer::can_index() ) { ?>
                              <input type="submit" name="index_data" id="index-data"
                                     class="button button-primary button-hero"
                                     value="Send content for indexing">
                              <p>
                                  <span class="description">This will populate the Ola Search with all content that has been modified since the last indexing.</span>
                              </p>
                              <hr>
						  <?php } ?>

						  <?php if ( OlaSearch_Indexer::can_reindex() ) { ?>
                              <input type="submit" name="re_index_data" id="re-index-data"
                                     class="button button-primary button-hero"
                                     value="Send all content for re-indexing">
                              <p>
                                  <span class="description">This will re-populate the Ola Search with all content marked for indexing.</span>
                              </p>
						  <?php } ?>

                          <input type="submit" name="wipe_data" id="wipe-data"
                                 class="button button-secondary button-hero"
                                 style="color: red" value="Clear all indexed data">
                          <p><span class="description">This will delete all content in the Ola Search.</span></p>
                      </div>
				  <?php } else { ?>
                      <p>
                          <span class="description">Please enter <code><?php echo OlaSearch_Admin::API_KEY_LABEL; ?></code> to see indexing options.</span>
                      </p>
				  <?php } ?>
              </td>
              <td></td>
          </tr>
      </tbody>
  </table>

    <br>
    <hr>
    <br>

  <?php include 'olasearch-admin-display-shortcodes.php'; ?>

</form>
<br class="clear"/>
<div id="progress-window" style="display:none;">
    <section>
        <p><span id="progress-info">batch</span></p>
        <div id="progress-bar" class="default">
            <div></div>
        </div>
    </section>
</div>