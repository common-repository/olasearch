<?php

$built_in_post_types = OlaSearch_Admin::get_post_types_filtered_by_screen_options( true );
$custom_post_types   = OlaSearch_Admin::get_post_types_filtered_by_screen_options( false );

$row_index           = 0;

?>
<form method="post">
    <input type="hidden" name="updated" value="true"/>
    <?php wp_nonce_field($this->plugin_name . '_update', $this->section()); ?>

    <p>
        <span class="description">&nbsp; Select the post types to index. Any changes made in this tab will require you to re-index your content.</span>
    </p>

    <table class="widefat">
        <thead>
            <tr>
                <th class="row-title"><strong>Custom post types</strong></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $custom_post_types as $post_type ) { ?>
            <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                <td><?php OlaSearch_Admin::checkbox( 'post_types', $post_type->name, 1, $post_type->label . " ($post_type->name)" ); ?></td>
            </tr>
        <?php } ?>

            <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>><td>&nbsp;</td></tr>
        </tbody>
        <thead>
            <tr>
                <th class="row-title"><strong>Built-in post types</strong></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $built_in_post_types as $post_type ) { ?>
            <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                <td><?php OlaSearch_Admin::checkbox( 'post_types', $post_type->name, 1, $post_type->label . " ($post_type->name)" ); ?></td>
            </tr>
        <?php } ?>

            <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>><td>&nbsp;</td></tr>
        </tbody>
    </table>

    <?php submit_button('Save changes', 'primary', 'submit', true); ?>
</form>
