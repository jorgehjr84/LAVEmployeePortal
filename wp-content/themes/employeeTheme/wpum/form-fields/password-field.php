<?php
/**
 * WPUM Template: Password Field Template.
 *
 * @package     wp-user-manager
 * @copyright   Copyright (c) 2015, Alessandro Tesoro
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
?>
<input
	type="password"
	class="input-text <?php echo ! empty( $field['class'] ) ? $field['class'] : ''; ?> col-md-4 col-md-offset-4"
	name="<?php echo esc_attr( isset( $field['name'] ) ? $field['name'] : $key ); ?>"
	id="<?php echo esc_attr( $key ); ?>"
	placeholder="<?php echo ! empty( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : ''; ?>"
	value="<?php echo isset( $field['value'] ) ? esc_attr( $field['value'] ) : ''; ?>"
	maxlength="<?php echo ! empty( $field['maxlength'] ) ? $field['maxlength'] : ''; ?>"
	/>
<?php if ( ! empty( $field['description'] ) ) : ?><small class="description"><?php echo $field['description']; ?></small><?php endif; ?>
