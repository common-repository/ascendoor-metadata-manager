<?php
/**
 * The admin-specific terms meta data manage functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package Ascendoor_Metadata_Manager
 */

$meta_id = isset( $meta_data['umeta_id'] ) ? $meta_data['umeta_id'] : $meta_data['meta_id'];
?>

<td class="meta-key">
	<?php echo esc_html( $meta_data['meta_key'] ); ?>
</td>
<td class="meta-value">
	<?php
	if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
		echo '<pre style="width: 100%; max-height: 200px; overflow-y: scroll;">';
		print_r( $meta_value ); // phpcs:ignore
		echo '</pre>';
	} elseif ( is_bool( $meta_value ) ) {
		echo $meta_value ? 'TRUE' : 'FALSE';
	} elseif ( is_null( $meta_value ) ) {
		echo 'NULL';
	} elseif ( is_resource( $meta_value ) ) {
		echo '';
	} elseif ( is_string( $meta_value ) ) {
		$lines = count( explode( "\n", $meta_value ) );

		$strlen       = strlen( $meta_value );
		$strlen_lines = ceil( $strlen / 50 );

		$max_lines = max( $lines, $strlen_lines );

		$rows = min( 10, $max_lines );
		?>
		<textarea disabled rows="<?php echo esc_attr( $rows ); ?>" data-rows="<?php echo esc_attr( $rows ); ?>"><?php echo esc_html( htmlentities( $meta_value ) ); ?></textarea>
		<div class="edit-action" style="display: none;">
			<a href="javascript:void(0);" data-cancel>
				<?php esc_html_e( 'Cancel', 'ascendoor-metadata-manager' ); ?>
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a
				href="javascript:void(0);"
				data-update="<?php echo esc_attr( wp_create_nonce( "amdm-update_{$meta_id}" ) ); ?>"
			>
				<?php esc_html_e( 'Update', 'ascendoor-metadata-manager' ); ?>
			</a>
			&nbsp;&nbsp;
			<span class="meta-edit-info">
				<svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<span class="meta-edit-info-content">
					<?php
					echo sprintf(
						/* translators: %1$s - WordPress sanitization Function Name with Anchor Tag, %2$s - WordPress sanitization Function Name with Anchor Tag */
						esc_html__( 'While updating, meta value will be sanitized and filtered with %1$s (if meta value contains HTML tags) or %2$s.', 'ascendoor-metadata-manager' ),
						'<a href="https://developer.wordpress.org/reference/functions/wp_kses_post/" target="_blank" rel="nofollow,noindex">wp_kses_post</a>',
						'<a href="https://developer.wordpress.org/reference/functions/sanitize_textarea_field/" target="_blank" rel="nofollow,noindex">sanitize_textarea_field</a>'
					);
					?>
				</span>
			</span>
		</div>
		<?php
	} else {
		?>
		<textarea disabled><?php echo esc_html( $meta_value ); ?></textarea>
		<div class="edit-action" style="display: none;">
			<a href="javascript:void(0);" data-cancel>
				<?php esc_html_e( 'Cancel', 'ascendoor-metadata-manager' ); ?>
			</a>
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<a
				href="javascript:void(0);"
				data-update="<?php echo esc_attr( wp_create_nonce( "amdm-update_{$meta_id}" ) ); ?>"
			>
				<?php esc_html_e( 'Update', 'ascendoor-metadata-manager' ); ?>
			</a>
			&nbsp;&nbsp;
			<span class="meta-edit-info">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
				</svg>
				<span class="meta-edit-info-content">
					<?php
					echo sprintf(
						/* translators: %1$s - WordPress sanitization Function Name with Anchor Tag, %2$s - WordPress sanitization Function Name with Anchor Tag */
						esc_html__( 'While updating, meta value will be sanitized and filtered with %1$s (if meta value contains HTML tags) or %2$s.', 'ascendoor-metadata-manager' ),
						'<a href="https://developer.wordpress.org/reference/functions/wp_kses_post/" target="_blank" rel="nofollow,noindex">wp_kses_post</a>',
						'<a href="https://developer.wordpress.org/reference/functions/sanitize_textarea_field/" target="_blank" rel="nofollow,noindex">sanitize_textarea_field</a>'
					);
					?>
				</span>
			</span>
		</div>
		<?php
	}
	?>
</td>
<td class="ascendoor-metadata-action meta-action">
	<div title="<?php echo esc_attr__( 'Action', 'ascendoor-metadata-manager' ); ?>">
		<a 
			href="javascript:void(0);"
			title="<?php echo esc_attr__( 'Delete meta key and value', 'ascendoor-metadata-manager' ); ?>"
			data-delete="<?php echo esc_attr( wp_create_nonce( "amdm-delete_{$meta_id}" ) ); ?>"
		>
			<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
			</svg>
		</a>

		<?php if ( ! is_array( $meta_value ) && ! is_object( $meta_value ) ) { ?>
			<a
				href="javascript:void(0);"
				title="<?php echo esc_attr__( 'Edit meta value', 'ascendoor-metadata-manager' ); ?>"
				data-edit
			>
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
				</svg>
			</a>
		<?php } ?>
	</button>
</td>
