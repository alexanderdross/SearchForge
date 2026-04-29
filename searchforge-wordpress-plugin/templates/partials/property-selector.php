<?php
defined( 'ABSPATH' ) || exit;

$properties = SearchForge\Models\Property::get_all();
if ( count( $properties ) < 2 ) {
	return;
}
$active_id = SearchForge\Models\Property::get_active_property_id();
?>
<div class="sf-property-selector" style="margin: 10px 0 16px; display: flex; align-items: center; gap: 8px;">
	<label for="sf-property-selector"><?php esc_html_e( 'Property:', 'searchforge' ); ?></label>
	<select id="sf-property-selector" data-nonce="<?php echo esc_attr( wp_create_nonce( 'searchforge_nonce' ) ); ?>">
		<?php foreach ( $properties as $prop ) : ?>
			<option value="<?php echo esc_attr( $prop['id'] ); ?>" <?php selected( (int) $prop['id'], $active_id ); ?>>
				<?php echo esc_html( $prop['label'] ); ?> (<?php echo esc_html( $prop['domain'] ); ?>)
			</option>
		<?php endforeach; ?>
	</select>
</div>
