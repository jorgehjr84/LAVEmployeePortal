<?php 

/**
 * Dashboard Views
 *
 * Display employee schedules in the dashboard.
 *
 * @package WordPress
 * @subpackage Employee Scheduler
 * @since 1.6
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * View schedules in the dashboard.
 *
 * Display a page in the dashboard that lets site admins view the master schedule or any employee's schedule.
 *
 * @since 1.6
 *
 * @see Shortcodes in views.php
 */
function wpaesm_view_schedules() { ?>
	
		<div class="wrap">
		
		<h1><?php _e( 'View Employee Schedules', 'wpaesm' ); ?></h1>

		<form method='post' action='<?php echo admin_url( 'edit.php?post_type=shift&page=view-schedules'); ?>' id='view-schedule'>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Employee', 'wpaesm' ) ?>:</th>
					<td>
						<select name="employee">
							<option value=""></option>
							<?php $employees = array_merge( get_users( 'role=employee&orderby=nicename' ), get_users( 'role=administrator&orderby=nicename' ) );
							usort( $employees, 'wpaesm_alphabetize' );
							foreach ( $employees as $employee ) { ?>
								<option value="<?php echo $employee->ID; ?>" ><?php echo $employee->display_name; ?></option>
							<?php } ?>
						</select>
						<p><?php _e( 'Leave this blank to see the master schedule for all employees.', 'wpaesm' ); ?>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Date Range', 'wpaesm' ) ?>:</th>
					<td>
						From <input type="text" size="10" name="thisdate" id="thisdate" value="" /> to <input type="text" size="10" name="repeatuntil" id="repeatuntil" value="" />
					</td>					
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'View Schedule', 'wpaesm' ); ?>" />
			</p>
		</form>

		<?php if( $_POST ) { 
			if( ( $_POST['thisdate'] == '____-__-__' ) || ( $_POST['repeatuntil'] == '____-__-__' ) ) {
				_e( 'You must enter both a start date and an end date to create a report.', 'wpaesm' );
			} elseif( $_POST['thisdate'] > $_POST['repeatuntil'] ) {
				_e( 'The report end date must be after the report begin date.', 'wpaesm' );
			} elseif( '' == $_POST['employee'] ) {
				$reportstart = $_POST['thisdate'];
				$reportend = $_POST['repeatuntil'];
				echo do_shortcode( '[master_schedule begin="' . $reportstart . '" end="' . $reportend . '"]' );
			} else {
				$reportstart = $_POST['thisdate'];
				$reportend = $_POST['repeatuntil'];
				echo do_shortcode( '[your_schedule begin="' . $reportstart . '" end="' . $reportend . '" employee="' . $_POST['employee'] . '"]' );
			}
		} ?>

	</div>

<?php }

?>