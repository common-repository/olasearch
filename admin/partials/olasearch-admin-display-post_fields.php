<?php

// meta field groups
$meta_field_groups = $this->meta_field_groups();

// built-in fields
$built_in_fields = $this->built_in_fields();

$select_field_types = OlaSearch_Admin::$field_types;
unset( $select_field_types[OlaSearch_Admin::TAXONOMY_ENTITY_TYPE] );

$row_index = 0;

?>
<form method="post">
    <input type="hidden" name="updated" value="true"/>
	<?php wp_nonce_field( $this->plugin_name . '_update', $this->section() ); ?>

    <p>
        <span class="description">&nbsp; Select the fields to index and choose the field type. Any changes made in this tab will require you to re-index your content.</span>
    </p>

    <table class="widefat">

		<?php foreach ( $meta_field_groups as $meta_field_group_type => $meta_field_group ) { ?>
			<?php if ( count( $meta_field_group['fields'] ) ) { ?>
                <thead>
                    <tr>
                        <th class="row-title" colspan="4"><strong><?php echo $meta_field_group['label'] ?></strong></th>
                    </tr>
                </thead>
                <thead>
                    <tr>
                        <th class="row-title"><strong>Name</strong></th>
                        <th class="row-title"><strong>Field</strong></th>
                        <th class="row-title"><strong>Type</strong></th>
                        <th class="row-title"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $meta_field_group['fields'] as $field ) { ?>
                        <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                            <td><?php OlaSearch_Admin::checkbox( [ 'meta_fields', $field['var'], $field['name'] ], 'checked', 1, $field['label'] ); ?></td>
                            <td><?php echo OlaSearch_Admin::get_field_name_by_type( $field['name'], $field['type'] ); ?></td>
                            <td>
                                <?php if ($field['type'] == OlaSearch_Admin::TAXONOMY_ENTITY_TYPE) { ?>
                                    <code><?php echo OlaSearch_Admin::$field_types[$field['type']]; ?></code>
                                    <input type="hidden" name="olasearch[meta_fields][<?php echo $field['var'] ?>][<?php echo $field['name'] ?>][type]" value="<?php echo $field['type'] ?>">
                                    <input type="hidden" name="olasearch[meta_fields][<?php echo $field['var'] ?>][<?php echo $field['name'] ?>][group]" value="<?php echo $field['group'] ?>">
                                <?php } else { ?>
                                    <?php OlaSearch_Admin::select( [ 'meta_fields', $field['var'], $field['name'] ], 'type', $select_field_types, $field['type'] ); ?>
                                <?php } ?>
                            <td>
                            <td><?php echo implode( ', ', $field['post_types'] ); ?></td>
                        </tr>
                    <?php } ?>
                    <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                        <td colspan="4">&nbsp;</td>
                    </tr>
                </tbody>
			<?php } ?>
		<?php } ?>


        <thead>
            <tr>
                <th class="row-title" colspan="4"><strong>Built-in fields</strong></th>
            </tr>
        </thead>
        <thead>
            <tr>
                <th class="row-title"><strong>Name</strong></th>
                <th class="row-title"><strong>Field</strong></th>
                <th class="row-title"><strong>Type</strong></th>
                <th class="row-title"></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ( $built_in_fields as $field) { ?>
                <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                    <td><?php OlaSearch_Admin::checkbox( [$field['var'], $field['name']], 'checked', 1, $field['label'] ); ?></td>
                    <td><?php echo OlaSearch_Admin::get_field_name_by_type( $field['name'], $field['type'] ); ?></td>
                    <td>
                        <code><?php echo OlaSearch_Admin::$field_types[$field['type']]; ?></code>
                        <input type="hidden" name="olasearch[<?php echo $field['var'] ?>][<?php echo $field['name'] ?>][type]" value="<?php echo $field['type'] ?>">
                        <?php if ($field['type'] == OlaSearch_Admin::TAXONOMY_ENTITY_TYPE) { ?>
                            <input type="hidden" name="olasearch[<?php echo $field['var'] ?>][<?php echo $field['name'] ?>][group]" value="<?php echo $field['group'] ?>">
                        <?php } ?>
                    </td>
                    <td><?php echo implode(', ', $field['post_types']); ?></td>
                </tr>
            <?php } ?>
            <tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>><td colspan="4">&nbsp;</td></tr>
        </tbody>
    </table>

	<?php submit_button( 'Save changes', 'primary', 'submit', true ); ?>
</form>