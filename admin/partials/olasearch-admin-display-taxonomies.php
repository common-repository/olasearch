<?php

// taxonomies
$taxonomy_groups = $this->taxonomies();
$built_in_groups = [];
foreach ( OlaSearch_Admin::$section_defaults['post_fields'] as $field => $default ) {
	if ( $default['type'] == OlaSearch_Admin::TAXONOMY_ENTITY_TYPE ) {
		$built_in_groups[] = $default['group'];
	}
}

$row_index = 0;

?>
<form method="post">
	<input type="hidden" name="updated" value="true"/>
	<?php wp_nonce_field($this->plugin_name . '_update', $this->section()); ?>

	<p>
		<span class="description">&nbsp; Select the taxonomy type (taxonomy or entity). Any changes made in this tab will require you to re-index your content.</span>
	</p>

	<table class="widefat">
		<thead>
			<tr>
				<th class="row-title" colspan="3"><strong>Custom taxonomy and entity groups</strong></th>
			</tr>
			<tr>
				<th class="row-title"><strong>Name</strong></th>
				<th class="row-title"><strong>Group</strong></th>
				<th class="row-title"><strong>Type</strong></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $taxonomy_groups as $taxonomy) { ?>
				<?php if ( !in_array($taxonomy['group'], $built_in_groups) ) { ?>
					<tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
						<td><?php echo $taxonomy['label']; ?></td>
						<td><?php echo $taxonomy['group']; ?></td>
                        <td><?php OlaSearch_Admin::select( [$taxonomy['var'], $taxonomy['group']], 'type', OlaSearch_Admin::$taxonomy_field_types, $taxonomy['type'] ); ?></td>
					</tr>
				<?php } ?>
			<?php } ?>
			<tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>><td colspan="3">&nbsp;</td></tr>
		</tbody>

		<thead>
			<tr>
				<th class="row-title" colspan="3"><strong>Built-in taxonomy and entity groups</strong></th>
			</tr>
			<tr>
				<th class="row-title"><strong>Name</strong></th>
				<th class="row-title"><strong>Group</strong></th>
				<th class="row-title"><strong>Type</strong></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $taxonomy_groups as $taxonomy) { ?>
				<?php if ( in_array($taxonomy['group'], $built_in_groups) ) { ?>
					<tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>>
                        <td><?php echo $taxonomy['label']; ?></td>
                        <td><?php echo $taxonomy['group']; ?></td>
                        <td><?php OlaSearch_Admin::select( [$taxonomy['var'], $taxonomy['group']], 'type', OlaSearch_Admin::$taxonomy_field_types, $taxonomy['type'] ); ?></td>
					</tr>
				<?php } ?>
			<?php } ?>
			<tr<?php if ( $row_index++ % 2 == 0 ) echo ' class="alternate"' ?>><td colspan="3">&nbsp;</td></tr>
		</tbody>
	</table>

	<?php submit_button('Save changes', 'primary', 'submit', true); ?>
</form>
