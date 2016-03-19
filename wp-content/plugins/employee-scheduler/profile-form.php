<?php  
/**
 * Profile Form
 *
 * HTML template for the form to edit user profile fields
 *
 * @package WordPress
 * @subpackage Employee Scheduler
 * @since 1.3
 */

if ( ! defined( 'ABSPATH' ) ) exit;
if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) { ?>
	<p class='error'><?php implode('<br />', $error); ?></p>
		<form method="post" id="adduser" action="<?php the_permalink(); ?>">
	        <table class="form-table">
				<tr>
					<th><label for="first-name"><?php _e('First Name', 'wpaesm'); ?></label></th>
					<td>
						<input type="text" name="first-name" id="first-name" value="<?php echo get_the_author_meta( 'first_name', $current_user->ID ); ?>" class="regular-text" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="last-name"><?php _e('Last Name', 'wpaesm'); ?></label></th>
					<td>
						<input type="text" name="last-name" id="last-name" value="<?php echo get_the_author_meta( 'last_name', $current_user->ID ); ?>" class="regular-text" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="last-name"><?php _e('E-mail', 'wpaesm'); ?></label></th>
					<td>
						<input type="text" name="email" id="email" value="<?php echo get_the_author_meta( 'user_email', $current_user->ID ); ?>" class="regular-text" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="pass1"><?php _e('Password', 'wpaesm'); ?></label></th>
					<td>
						<input type="password" name="pass1" id="pass1" class="regular-text" /><br />
					</td>
				</tr>
				<tr>
					<th><label for="pass2"><?php _e('Repeat Password', 'wpaesm'); ?></label></th>
					<td>
						<input type="password" name="pass2" id="pass2" class="regular-text" /><br />
					</td>
				</tr>
			</table>

        <?php do_action( 'edit_user_profile', $current_user );  ?>
        <p class='form-submit'>
        <input name='updateuser' type='submit' id='updateuser' class='submit button' value='<?php _e('Update', 'wpaesm'); ?>' />
        <?php wp_nonce_field( 'update-user' ); ?>
        <input name='action' type='hidden' id='action' value='update-user' />
        </p><!-- .form-submit -->
        </form><!-- #adduser -->
<?php } else { ?>
		<p><?php _e( 'You must be logged in to view this page.', 'wpaesm' ); ?></p>
		<?php $args = array(
	        'echo' => true,
		); 
		wp_login_form($args); 
	} ?>