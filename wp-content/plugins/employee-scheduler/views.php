<?php
/**
 * Public Views
 *
 * Contains filters and shortcodes for front-end views.
 *
 * @package WordPress
 * @subpackage Employee Scheduler
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Single Shift Title.
 *
 * Change the title on the single shift view to "Shift Details.""
 *
 * @since 1.0
 *
 * @param string $title The post title.
 *
 * @return string $title The filtered post title
 */

function wpaesm_single_shift_title( $title ) {
	global $post;
    if( is_singular('shift') && $title == $post->post_title && is_main_query() ) {
        $title = __( 'Shift Details', 'wpaesm' );
    }
    return $title;
}
add_filter( 'the_title', 'wpaesm_single_shift_title', 10, 2 );

/**
 * Enqueue geolocation.
 *
 * Check options to see if we need to record geolocation, and if so, enqueue geolocation script on singular shift view.
 *
 * @since 1.0
 *
 */
$options = get_option('wpaesm_options');
if( isset( $options['geolocation'] ) && $options['geolocation'] == 1 ) {
	add_action('wp_enqueue_scripts', 'wpaesm_geolocation_script', 11);
}

function wpaesm_geolocation_script() {
	if ( is_singular( 'shift' ) ) {
        wp_enqueue_script( 'geolocation', plugins_url() . '/employee-scheduler/js/geolocation.js' );
    }
}

/**
 * Single shift stylesheet.
 *
 * Enqueue stylesheet on single shift view.
 *
 * @since 1.0
 */
add_action( 'wp_enqueue_scripts', 'wpaesm_single_shift_css' );

function wpaesm_single_shift_css() {
	if( is_singular( 'shift' ) ) {
		wp_enqueue_style( 'wpaesm-style', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );
	}
}

/**
 * Single shift view.
 *
 * Filter single shift content to display extra information.
 *
 * @since 1.0
 *
 * @global object  $post  The post object.
 * @global object  $shift_metabox  The shift metabox from WP Alchemy
 *
 * @param string  $content  The post content.
 * @return string  The post content, along with shift information.
 */
add_filter( 'the_content', 'wpaesm_single_shift_view' );

function wpaesm_single_shift_view( $content ) {
	if( is_singular( 'shift' ) && is_main_query() ) {
		if( is_user_logged_in() && ( wpaesm_check_user_role( 'employee' ) || wpaesm_check_user_role( 'administrator' ) ) ) { // only show this if current user is admin or employee
			global $post;

			// get employee associated with this shift
			$users = get_users( array(
				'connected_type' => 'shifts_to_employees',
				'connected_items' => $post->ID
			) );
			$employee = '';
			foreach( $users as $user ) {
				$employee = $user->display_name;
				$employeeid = $user->ID;
			}
			if( !isset( $employeeid ) ) {
				$employeeid = 'Unassigned';
			}

			// Process forms, if we need to
			// status change
			if( isset( $_POST['form_name'] ) && "status" == ( $_POST['form_name'] ) ) {
				wpaesm_change_shift_status( $post, $employee );
			}

			// if employee left a note
			if( isset( $_POST['form_name'] ) && "employee_note" == ( $_POST['form_name'] ) ) {
				wpaesm_save_employee_note( $post, $employee );
			}

			// If employee just pushed the clock in button
			if( isset( $_POST['form_name'] ) && "clockin" == ( $_POST['form_name'] ) ) {
				wpaesm_clock_in( $post );
			}

			// If employee just pushed the clock out button
			if( isset( $_POST['form_name'] ) && "clockout" == ( $_POST['form_name'] ) ) {
				wpaesm_clock_out( $post );
			}

			// gather all of the variables we need
			$current_user = wp_get_current_user(); // get current user - we'll need it later
			global $shift_metabox; // get metabox data
			$meta = $shift_metabox->the_meta(); 
			
			// get job associated with this shift
			$jobs = get_posts( array(
				'connected_type' => 'shifts_to_jobs',
				'connected_items' => $post->ID,
				'nopaging' => true,
				'suppress_filters' => false
			) );
			$jobname = '';
			if( !empty( $jobs ) ) {
				foreach ( $jobs as $job ) {
					$jobname = $job->post_title;
				}
			}
			$options = get_option( 'wpaesm_options' ); // get options
			$starttime = date( "g:i a", strtotime( $meta['starttime'] ) );
			$endtime = date( "g:i a", strtotime( $meta['endtime'] ) );
			if( isset( $meta['clockin'] ) ) {
				$clockin = date( "g:i a", strtotime($meta['clockin'] ) );
			}
			if( isset( $meta['clockout'] ) ) {
				$clockout = date( "g:i a", strtotime($meta['clockout'] ) );
			}
			$date = date( "D M j", strtotime( $meta['date'] ) );
			$typelist =  wp_get_post_terms( $post->ID, 'shift_type' );
			$types = '';
			foreach($typelist as $type) {
				$types .= $type->name;
			}
			$statuslist =  wp_get_post_terms( $post->ID, 'shift_status' );
			$statuses = '';
			foreach($statuslist as $status) {
				$statuses .= $status->name;
			}
			$locationlist =  wp_get_post_terms( $post->ID, 'location' );
			$locations = '';
			foreach( $locationlist as $location ) {
				$locations .= $location->name;
				$address = get_tax_meta( $location->term_id, 'location_address');
				if( isset( $address ) && '' !== $address ) {
					$locations .= '<br />' . nl2br( $address ) . '<br />';
				}
			}



			// BEGIN SHIFT VIEW
			$shiftcontent = '';

			// if the employee is viewing the shift, and if it is today, show clock in/out buttons
			$today = current_time( "Y-m-d" );
			
			if( isset( $employeeid ) && $employeeid == $current_user->ID && $today == $meta['date'] && empty($meta['clockout'] ) ) {
				if( isset( $meta['clockin'] ) && '' !== $meta['clockin'] ) { // employee has already clocked in, so show the clock out button
					$shiftcontent .= "<form method='post' action='" . get_the_permalink() . "' id='clock'>";
					$shiftcontent .= "<input type='hidden' name='form_name' value='clockout'>";
					$shiftcontent .= "<input type='hidden' name='clock-out' value='clock-out'>";
					if( isset( $options['geolocation'] ) && $options['geolocation'] == 1 ) { // geolocation field, if we're using it
						$shiftcontent .= "<input type='hidden' id='latitude' name='latitude' value=''>";
						$shiftcontent .= "<input type='hidden' id='longitude' name='longitude' value=''>";
					}
					$shiftcontent .= "<p>" . __( 'You clocked in at', 'wpaesm') . "&nbsp;" . $meta['clockin'] . "</p>";
					$shiftcontent .= "<input name='wpaesm_clockout_nonce' id='wpaesm_clockout_nonce' type='hidden' value='" . wp_create_nonce( 'wpaesm_clockout_nonce' ) . "'>";
					$shiftcontent .= "<input type='submit' value='" . __('Clock Out', 'wpaesm') . "' id='clock-out'>";
					$shiftcontent .= "</form>";
				} else { // employee has not clocked in, so show the clock in button
					$shiftcontent .= "<form method='post' action='" . get_the_permalink() . "' id='clock'>";
					$shiftcontent .= "<input type='hidden' name='form_name' value='clockin'>";
					$shiftcontent .= "<input type='hidden' name='clock-in' value='clock-in'>";
					if( isset( $options['geolocation'] ) && $options['geolocation'] == 1 ) { // geolocation field, if we're using it
						$shiftcontent .= "<input type='hidden' id='latitude' name='latitude' value=''>";
						$shiftcontent .= "<input type='hidden' id='longitude' name='longitude' value=''>";
					}
					$shiftcontent .= "<input name='wpaesm_clockin_nonce' id='wpaesm_clockin_nonce' type='hidden' value='" . wp_create_nonce( 'wpaesm_clockin_nonce' ) . "'>";
					$shiftcontent .= "<input type='submit' value='" . __('Clock In', 'wpaesm') .  "' id='clock-in'>";
					$shiftcontent .= "</form>";
				}
				
			}


			if( isset( $employee ) && '' !== $employee ) {
				$shiftcontent .= "<p><strong>" . __( 'Employee:', 'wpaesm' ) . "</strong> " . $employee . "</p>";
			}
			if( isset( $jobname ) && '' !== $jobname ) {
				$shiftcontent .= "<p><strong>" . __( 'Job:', 'wpaesm' ) . "</strong> " . $jobname . "</p>";
			}
			$shiftcontent .= "<p><strong>" . __( 'When: ', 'wpaesm' ) . "</strong> " . $date;
			if( 'Extra' !== $types ) {
				$shiftcontent .= ", " . __( 'from ', 'wpaesm' ) . $starttime . __(' to ', 'wpaesm') . $endtime ;
			} 
			$shiftcontent .= "</p>";
			if( isset( $clockin ) ) {
				$shiftcontent .= "<p><strong>" . __( 'Hours Worked', 'wpaesm' ) .  ": </strong>" . $clockin . " to " . $clockout . "</p>";
			}
			if( !empty( $locations ) ) {
				$shiftcontent .= "<p><strong>" . __( 'Location: ', 'wpaesm' ) . "</strong> " . $locations . "</p>";
			}
			if( '' !== $types ) {
				$shiftcontent .= "<p><strong>" . __( 'Type: ', 'wpaesm' ) . "</strong> " . $types . "</p>";
			}
			if( '' !== $statuses ) {
				$shiftcontent .= "<p><strong>" . __( 'Status: ', 'wpaesm' ) . "</strong> " . $statuses . "</p>";
			}
			// let employee change status 
//			if( isset( $employeeid ) && $employeeid == $current_user->ID ) {
//				$shiftcontent .= "<form method='post' action='" . get_the_permalink() . "' id='shift-status'>";
//				$shiftcontent .= "<input type='hidden' name='form_name' value='status'>";
//				$shiftcontent .= "<input name='wpaesm_shift_status_nonce' id='wpaesm_shift_status_nonce' type='hidden' value='" . wp_create_nonce( 'wpaesm_shift_status_nonce' ) . "'>";
//				$shiftcontent .= "<label>" . __('Change shift status:', 'wpaesm') . "</label>";
//				$shiftcontent .= "<select name='status'><option></option>";
//				$statuses = get_terms('shift_status', 'hide_empty=0');
//				foreach( $statuses as $status ) {
//					$shiftcontent .= "<option value='" . $status->term_id . "'>" . $status->name . "</option>";
//				}
//				$shiftcontent .= "</select><input type='submit' value='" . __('Update Status', 'wpaesm') . "'>";
//				$shiftcontent .= "</form>";
//			}

			// display employee notes, if any exist
			if( isset( $meta['employeenote'] ) && is_array( $meta['employeenote'] ) ) {
				$employeenotes = $meta['employeenote'];
				$shiftcontent .= "<strong>" . __( 'Notes', 'wpaesm' ) . "</strong>";
				foreach( $employeenotes as $note ) {
					if( isset( $note['notedate'] ) && isset( $note['notetext'] ) ) {
						$shiftcontent .= "<p><strong>" . $note['notedate'] . ":</strong> " . $note['notetext'] . "</p>";
					}
				}
			}

			// display the form for employee to add notes (only visible to employee assigned to shift)
			if( isset( $employeeid ) && $employeeid == $current_user->ID ) {
				$shiftcontent .= "<form method='post' action='" . get_the_permalink() . "' id='shift-note'>";
				$shiftcontent .= "<input type='hidden' name='form_name' value='employee_note'>";
				$shiftcontent .= "<label>" . __('Add a note about this shift, such as corrections to your clock-in and clock-out times.', 'wpaesm') . "</label>";
				if(isset($options['admin_notify_note']) && $options['admin_notify_note'] == 1) {
					$shiftcontent .= "<p>" . __('The site admin will receive an email with your note', 'wpaesm') . "</p>";
				}
				$shiftcontent .= "<textarea name='note'></textarea>";
				$shiftcontent .= "<input name='wpaesm_employee_note_nonce' id='wpaesm_employee_note_nonce' type='hidden' value='" . wp_create_nonce( 'wpaesm_employee_note_nonce' ) . "'>";
				$shiftcontent .= "<input type='submit' value='" . __('Add Note', 'wpaesm') . "'>";
				$shiftcontent .= "</form>";
			}

			// Show edit shift link to admins
			if ( current_user_can( 'edit_post', $post->ID ) ) {
				$shiftcontent .= "<p class='wpaesm-edit'><a href='" . get_edit_post_link() . "'>" . __('Edit this shift', 'wpaesm') . "</a></p>";
			}

			$shiftcontent = apply_filters( 'wpaesm_filter_single_shift_view', $shiftcontent, 10, get_the_id(), $employeeid );
			$content .= $shiftcontent;
		} else {
			$shiftcontent = "<p>" . __('You must be logged in to view this page.', 'wpaesm') . "</p>";
			$args = array(
		        'echo' => false,
			); 
			$shiftcontent .= wp_login_form($args);

			$content .= $shiftcontent;
		}
	} 

	return $content;
}


/**
 * Change shift status.
 *
 * If employee filled out the "change shift status" form, change the shift status.
 *
 * @since 1.0
 *
 * @see wpaesm_single_shift_view()
 *
 * @param int  $post  The ID of the shift.
 * @param int  $employee  The ID of the employee.
 */
function wpaesm_change_shift_status( $post, $employee ) {
	if ( !wp_verify_nonce( $_POST['wpaesm_shift_status_nonce'], "wpaesm_shift_status_nonce")) {
        exit( "Permission error." );
    }

    // get the old status
    $old_status = '';
    $old_status_list = wp_get_post_terms( $post->ID, 'shift_status' );
    foreach( $old_status_list as $status ) {
    	$old_status = $status->name;
    }
	
	// change the shift status
	wp_set_post_terms( $post->ID, array( $_POST['status'] ), 'shift_status', 0 );
	// if admin wants email notifications, send an email
	$options = get_option('wpaesm_options');
	if(isset($options['admin_notify_status']) && $options['admin_notify_status'] == 1) {
		if(isset($options['admin_notification_email'])) {
			$to = $options['admin_notification_email'];
		} else {
			$to = get_bloginfo('admin_email');
		}
		$subject = __( 'Shift Status Change Notification', 'wpaesm' );

		$message = '<p>' . __( 'An employee has changed the status of their shift.  Details: ') . '</p>';
		if( isset( $employee ) ) {
			$message .= '<p><strong>' . __( 'Employee: ', 'wpaesm' ) . '</strong>' . $employee . '</p>';
		}
		if( isset( $old_status ) ) {
			$message .= '<p><strong>' . __( 'Old Status: ', 'wpaesm' ) . '</strong>' . $old_status . '</p>';
		}
		$newstatus = get_term_by( 'id', $_POST['status'], 'shift_status' );
		$message .= '<p><strong>' . __( 'New Status: ', 'wpaesm' ) . '</strong>' . $newstatus->name . '</p>';
		
		$message .= '<p><strong>' . __( 'View shift: ', 'wpaesm' ) . '</strong><a href="' . get_the_permalink( $post->ID ) . '">' . get_the_permalink( $post->ID ) . '</a></p>';
		$message .= '<p><strong>' . __( 'Edit shift: ', 'wpaesm' ) . '</strong><a href="' . get_edit_post_link( $post ->ID ) . '">' . get_edit_post_link( $post ->ID ) . '</a></p>';

		$from = $options['notification_from_name'] . "<" . $options['notification_from_email'] . ">";
		wpaesm_send_email( $from, $to, '', $subject, $message );
	}

	do_action( 'wpaesm_change_shift_status_action' );

	unset($_POST);
}

/**
 * Save employee note.
 *
 * If employee filled out the "leave shift note" form, save the data.
 *
 * @since 1.0
 *
 * @see wpaesm_single_shift_view()
 *
 * @param int  $post  ID of the shift.
 * @param int  $employee  ID of the employee.
 */
function wpaesm_save_employee_note( $post, $employee ) {
	if ( !wp_verify_nonce( $_POST['wpaesm_employee_note_nonce'], "wpaesm_employee_note_nonce")) {
        exit( "Permission error." );
    }

	// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
	$fields = array('_wpaesm_employeenote', '_wpaesm_date', '_wpaesm_starttime', '_wpaesm_endtime', '_wpaesm_notify', '_wpaesm_clockin', '_wpaesm_clockout', '_wpaesm_lastnote');
	$str = $fields;
	update_post_meta( $post->ID, 'shift_meta_fields', $str );
	// enter the date so we know when the last note was left
	$now = time();
	update_post_meta( $post->ID, '_wpaesm_lastnote', $now );
	
	// Put note text in the array format wpalchemy expects
	$notes2 = get_post_meta($post->ID, '_wpaesm_employeenote', true);
	delete_post_meta( $post->ID, '_wpaesm_employeenote' );

	if(!isset($notes2) || !is_array($notes2)) {
		$notes2 = array();
	}
	$now = current_time( 'Y-m-d');

 	$tempnotes['notedate'] = $now;
    $tempnotes['notetext'] = sanitize_text_field($_POST['note']);
    array_push( $notes2, $tempnotes );
	add_post_meta( $post->ID, '_wpaesm_employeenote', $notes2 );
	// if admin wants email notifications, send an email
	if(isset($options['admin_notify_note']) && $options['admin_notify_note'] == 1) {
		if(isset($options['admin_notification_email'])) {
			$to = $options['admin_notification_email'];
		} else {
			$to = get_bloginfo('admin_email');
		}
		$date = get_post_meta( $post->ID, '_wpaesm_date', true );
		$subject = $employee . " left a note on their shift on " . $date;
		$message = '<p>' . $employee . " left the following note on their shift that is scheduled for " . $date . ':</p>';
		$message .= $_POST['note'];
		$from = $options['notification_from_name'] . "<" . $options['notification_from_email'] . ">";
		wpaesm_send_email( $from, $to, '', $subject, $message );
	}

	do_action( 'wpaesm_save_employee_note_action' );

	unset($_POST);
}

/**
 * Clock in.
 *
 * If employee pushed the "clock in" button, save their clock in time.
 *
 * @since 1.0
 *
 * @see wpaesm_single_shift_view()
 *
 * @param int  $post  The shift ID.
 */
function wpaesm_clock_in( $post ) {
	if ( !wp_verify_nonce( $_POST['wpaesm_clockin_nonce'], "wpaesm_clockin_nonce")) {
        exit( "Permission error." );
    }
	// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
	$fields = array('_wpaesm_employeenote', '_wpaesm_date', '_wpaesm_starttime', '_wpaesm_endtime', '_wpaesm_notify', '_wpaesm_clockin', '_wpaesm_clockout', '_wpaesm_location_in', '_wpaesm_location_out', '_wpaesm_location_in', '_wpaesm_location_out');
	$str = $fields;
	update_post_meta( $post->ID, 'shift_meta_fields', $str );
	// save clock in time
	$clockin = current_time("H:i");
	update_post_meta( $post->ID, '_wpaesm_clockin', $clockin );

	$testing_meta = get_post_meta( $post->ID, '_wpaesm_clockin', true );
	if( !isset( $testing_meta ) || '' == $testing_meta ) {
		wp_die( __( 'Something has gone wrong.  Please use the back button to try to clock in again.  If you continue to receive this error, contact the site administrator.', 'wpaesm' ) );
	}


	// save address
	if(isset($_POST['latitude']) && isset($_POST['longitude'])) {
		$lat = $_POST['latitude'];
		$long = $_POST['longitude'];
		$json = file_get_contents("http://maps.google.com/maps/api/geocode/json?latlng=$lat,$long");
		$json = json_decode($json, true);
		if(is_array($json) && $json['status'] == 'OK') {
			$address = $json['results'][0]['formatted_address'];
			update_post_meta( $post->ID, '_wpaesm_location_in', $address );
		} else {
			$error = "Unable to retrieve location data";
			update_post_meta( $post->ID, '_wpaesm_location_in', $error );
		}				
	}

	do_action( 'wpaesm_clock_in_action' );

	unset($_POST);
}

/**
 * Clock out.
 *
 * If employee clicked the "clock out" button, record the time and mark the shift as worked.
 *
 * @since 1.0
 *
 * @see wpaesm_single_shift_view()
 *
 * @param int  $post  ID of the shift.
 */
function wpaesm_clock_out( $post ) {
	if ( !wp_verify_nonce( $_POST['wpaesm_clockout_nonce'], "wpaesm_clockout_nonce")) {
        exit( "Permission error." );
    }
	// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
	$fields = array('_wpaesm_employeenote', '_wpaesm_date', '_wpaesm_starttime', '_wpaesm_endtime', '_wpaesm_notify', '_wpaesm_clockin', '_wpaesm_clockout', '_wpaesm_location_in', '_wpaesm_location_out');
	$str = $fields;
	update_post_meta( $post->ID, 'shift_meta_fields', $str );
	// save clock out time
	$clockout = current_time( "H:i" );
	update_post_meta( $post->ID, '_wpaesm_clockout', $clockout );

	$testing_meta = get_post_meta( $post->ID, '_wpaesm_clockout', true );
	if( !isset( $testing_meta ) || '' == $testing_meta ) {
		wp_die( __( 'Something has gone wrong.  Please use the back button to try to clock in again.  If you continue to receive this error, contact the site administrator.', 'wpaesm' ) );
	}

	// save location
	if( isset( $_POST['latitude'] ) && isset( $_POST['longitude'] ) ) {
		$lat = $_POST['latitude'];
		$long = $_POST['longitude'];
		$json = file_get_contents( "http://maps.google.com/maps/api/geocode/json?latlng=$lat,$long" );
		$json = json_decode( $json, true );
		if( is_array( $json ) && $json['status'] == 'OK') {
			$address = $json['results'][0]['formatted_address'];
			update_post_meta( $post->ID, '_wpaesm_location_out', $address );
		} else {
			$error = "Unable to retrieve location data";
			update_post_meta( $post->ID, '_wpaesm_location_out', $error );
		}				
	}		
	$worked = wp_set_object_terms( $post->ID, 'worked', 'shift_status', false );

	// send email, if admin wants to receive clockout email
	$options = get_option( 'wpaesm_options' );
	if( '1' == $options['admin_notify_clockout'] ) {
		$users = get_users( array(
			'connected_type' => 'shifts_to_employees',
			'connected_items' => $post->ID
		) );
		foreach( $users as $user ) {
			$employeename = $user->display_name;
		}
		if( !isset( $employeename ) ) {
			$employeename = __( 'An Employee', 'wpaesm' ); 
		}

		$from = $options['notification_from_name'] . " <" . $options['notification_from_email'] . ">";
		if( isset( $options['admin_notification_email'] ) ) {
			$to = $options['admin_notification_email'];
		} else {
			$to = get_bloginfo( 'admin_email' );
		}
		$subject = sprintf( __( '%s has just clocked out', 'wpaesm' ), $employeename ); 
		$message = '<p>' . sprintf( __( '%s has just clocked out', 'wpaesm' ), $employeename ) . '</p>';
		$message .= '<p><strong>' . __( 'Scheduled hours', 'wpaesm' ) . ': </strong>' . get_post_meta( $post->ID, '_wpaesm_starttime', true ) . ' - ' . get_post_meta( $post->ID, '_wpaesm_endtime', true) . '</p>';
		$message .= '<p><strong>' . __( 'Worked hours', 'wpaesm' ) . ': </strong>' . get_post_meta( $post->ID, '_wpaesm_clockin', true ) . ' - ' . get_post_meta( $post->ID, '_wpaesm_clockout', true) . '</p>';
		$message .= '<p><a href="' . get_the_permalink( $post->ID ) . '">' . __( 'View Shift', 'wpaesm' ) . '</a></p>';
		$message .= '<p><a href="' . get_edit_post_link( $post->ID ) . '">' . __( 'Edit Shift', 'wpaesm' ) . '</a></p>';
		wpaesm_send_email( $from, $to, '', $subject, $message );
	}

	do_action( 'wpaesm_clock_out_action' );

	unset( $_POST );
}

/**
 * Master Schedule Shortcode.
 *
 * [master_schedule] displays a weekly work schedule with all employees' shifts.
 *
 * @since 1.0
 *
 * @param array $atts {
 *     Shortcode attributes: begin date and end date
 * }
 * @return string  HTML for master schedule.
 */
function wpaesm_master_schedule_shortcode( $atts ) {

	// Attributes
	extract( shortcode_atts(
		array(
			'begin' => '',
			'end' => '',
			'type' => '',
			'status' => '',
			'location' => '',
		), $atts )
	);

	// Enqueue script to make table sortable
	wp_enqueue_script( 'stupid-table', plugin_dir_url(__FILE__) . 'js/stupidtable.min.js', array( 'jquery' ) );

	// Enqueue style to make everything look right
	wp_enqueue_style( 'employee-scheduler', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );

	// must be logged in as administrator or employee to view this
	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {
		$week = array();

		// see if we have shortcode attributes
		if( '' !== $begin && '' !== $end ) {
			$nav = 'off';
			$schedulebegin = $begin;
			$scheduleend = $end;
			$i = 0;
			$thisday = strtotime( $begin );
			$lastday = strtotime( $end );
			while( $thisday <= $lastday ) {
				$thisday = strtotime( '+ ' . $i . 'days', $thisday );
				$week[date( "Y-m-d", $thisday )] = array();
				$i++;
			}

		} else {
			$nav = 'on';
			// we don't have shortcode attributes, so we'll use default dates
			// get the appropriate date
			if( isset( $_GET['week'] ) ) {
				$thisweek = $_GET['week'];
				$nextweek = strtotime("+1 week", $thisweek);
				$lastweek = strtotime("-1 week", $thisweek);
			} else {
				$thisweek = current_time("timestamp");
				$nextweek = strtotime("+1 week");
				$lastweek = strtotime("-1 week");
			}
			
			$options = get_option('wpaesm_options');

			// get the range of dates for this week

			// find out what day of the week today is
			$today = date("l", $thisweek);

			if($today == $options['week_starts_on']) { // today is first day of the week
				$weekstart = $thisweek;
			} else { // find the most recent first day of the week
				$sunday = 'last ' . $options['week_starts_on'];
				$weekstart = strtotime($sunday, $thisweek);
			}

			// from the first day of the week, add one day 7 times to get all the days of the week
			$i = 0;
			while( $i < 7 ) {
				$week[date("Y-m-d", strtotime('+ ' . $i . 'days', $weekstart))] = array();
				if($i == 0) {
					$schedulebegin = date('F j, Y', strtotime('+ ' . $i . 'days', $weekstart));
				} elseif ($i == 6) {
					$scheduleend = date('F j, Y', strtotime('+ ' . $i . 'days', $weekstart));
				}
				$i++;
			}
		}

		$mschedule = "<h3>" . sprintf( __( 'Schedule for %s through %s', 'wpaesm' ), $schedulebegin, $scheduleend ) . "</h3>";
		if( 'on' == $nav ) {
			$mschedule .= "<nav class='wpaesm-schedule'><ul><li class='previous'><a href='" . get_the_permalink() . "?week=" . $lastweek . "'>" . __( 'Previous Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='this'><a href='" . get_the_permalink() . "'>" . __( 'This Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='next'><a href='" . get_the_permalink() . "?week=" . $nextweek . "'>" . __( 'Next Week', 'wpaesm' ) . "</a></li></ul></nav>";
		}

		// collect all the shifts
		foreach( $week as $day => $shifts ) {
			$args = array( 
				'post_type' => 'shift',
				'meta_query' => array(
					array(
						'key'     => '_wpaesm_date',
						'value'   => $day,
					),
				),
				'tax_query' => array(
					array(
						'taxonomy' => 'shift_type',
						'field'    => 'slug',
						'terms'    => array( 'extra', 'pto' ),
						'operator' => 'NOT IN',
					),
				),
				'posts_per_page' => -1,
				'meta_key' => '_wpaesm_starttime',
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
			);

			if( '' !== $type || '' !== $status || '' !== $location ) {
				$args['tax_query'] = array(
					'relation' => 'AND',
				);
				if( '' !== $type ) {
					$args['tax_query'][] =
						array(
								'taxonomy' => 'shift_type',
								'field'    => 'slug',
								'terms'    => $type,
						);
				}
				if( '' !== $status ) {
					$args['tax_query'][] =
						array(
								'taxonomy' => 'shift_status',
								'field'    => 'slug',
								'terms'    => $status,
						);
				}
				if( '' !== $location ) {
					$args['tax_query'][] =
						array(
								'taxonomy' => 'location',
								'field'    => 'slug',
								'terms'    => $location,
						);
				}
			}
			
			$msquery = new WP_Query( $args );
			$i = 0;
			if ( $msquery->have_posts() ) :
				while ( $msquery->have_posts() ) : $msquery->the_post();
					global $shift_metabox;
					$meta = $shift_metabox->the_meta();
					$id = get_the_id();
					$week[$day][$i]['id'] = $id;
					$week[$day][$i]['permalink'] = get_the_permalink();
					$week[$day][$i]['starttime'] = $meta['starttime'];
					$week[$day][$i]['endtime'] = $meta['endtime'];
					$week[$day][$i]['date'] = $meta['date'];
					$statuses = get_the_terms($id, 'shift_status');
					if(is_array($statuses)) {
						foreach($statuses as $shift_status) {
							$week[$day][$i]['status'] = $shift_status->slug;
							$color = get_tax_meta($shift_status->term_id, 'status_color');
							$week[$day][$i]['color'] = $color;
						}
					} 

					$users = get_users( array(
						'connected_type' => 'shifts_to_employees',
						'connected_items' => $id,
						'orderby'      => 'display_name',
					) );
					if( empty( $users ) ) {
						$week[$day][$i]['employee'] = __( 'Unassigned', 'wpaesm' );
					} else {
						foreach($users as $user) {
							$week[$day][$i]['employee'] = $user->id;
						}
					}
					$jobs = get_posts( array(
					  'connected_type' => 'shifts_to_jobs',
					  'connected_items' => $id,
					  'nopaging' => true,
					  'suppress_filters' => false
					) );
					if( empty( $jobs ) ) {
						$week[$day][$i]['job'] = __( 'No job assigned', 'wpaesm' );
						$week[$day][$i]['joblink'] = '#';
					} else {
						foreach($jobs as $job) {
							$week[$day][$i]['job'] = $job->post_title;
							$week[$day][$i]['joblink'] = site_url() . "/job/" . $job->post_name;
						}
					}
					$i++;
				endwhile;
			endif;
			wp_reset_postdata();

		}

		// go through the shifts and collect all the employees
		$employeearray = array();
		foreach( $week as $day => $shifts ) {
			foreach( $shifts as $shift ) {
				if( isset( $shift['employee'] ) ) {
					$employeearray[] = $shift['employee'];
				}
			}
		}

		// take out all the duplicate employees
		$employeearray = array_unique( $employeearray );

		// display table
		if( 'off' == $nav ) {
			$class = 'class="wp-list-table widefat fixed posts striped"';
		} else {
			$class = '';
		}
		$mschedule .= "<table id='master-schedule'" . $class . "><thead><tr>";
		$mschedule .= "<th data-sort='string'><span>" . __( 'Employee', 'wpaesm' ) . "</span></th>";
		foreach( $week as $day => $shifts ) {
			$mschedule .= "<th data-sort='string'><span>" . date("D M j", strtotime($day)) . "</span></th>";
		}
		$mschedule .= "</tr></thead><tbody>";
		foreach( $employeearray as $employee ) {
			if( 'Unassigned' == $employee ) {
				$employee_cell = $employee;
			} else {
				$employeeinfo = get_user_by('id', $employee);
				$employee_cell = $employeeinfo->display_name;
				if( isset( $employeeinfo->user_email ) ) {
					$employee_cell .= "<br /><a href='mailto:" . $employeeinfo->user_email . "'>" . $employeeinfo->user_email . "</a>";
				}
				$phone = get_user_meta($employee, 'phone', true);
				if( isset( $phone ) ) {
					$employee_cell .= "<br /><a href='tel:" . $phone . "'>" . $phone . "</a>";
				}
			}
			$mschedule .= "<tr><th scope='row'>" . $employee_cell . "</th>";
			foreach( $week as $day => $shifts ) {
				$mschedule .= "<td>";
				$shift_text = '';
				foreach( $shifts as $shift ) {
					if( isset( $shift['employee'] ) && $employee == $shift['employee'] ) {
						$shift_text = "<div";
						if( isset( $shift['status'] ) ) {
							$shift_text .= " class='wpaesm-" . $shift['status'] . "'";
						}
						if( isset( $shift['color'] ) ) {
							$shift_text .= " style='background: " . $shift['color'] . "'";
						}
						$shift_text .= ">";
						if( isset( $shift['job'] ) ) {
							$shift_text .= "<span class='wpaesm-job'><a href='" . $shift['joblink'] . "'>" . $shift['job'] . "</a></span>"; 
						} else {
							$shift_text .= get_the_title( $shift['id'] );
						}
						$shift_text .= "<br /><span class='wpaesm-time'>" . date( "g:i", strtotime( $shift['starttime'] ) ) . " - " . date( "g:i", strtotime( $shift['endtime'] ) ) . "</span>";
						$shift_text .= "<br /><a class='wpaesm-details' href='" . $shift['permalink'] . "'>" . __( 'View Shift Details', 'wpaesm' ) . "</a>";
						$shift_text .= "</div>";
						$shift_text = apply_filters( 'wpaesm_single_shift_cell', $shift_text, 10, $shift['id'], $employee );
						$mschedule .= $shift_text;
					}
				}
				if( empty( $shift_text ) ){
					$mschedule .= "<span class='wpaesm-noshift'>" . __( 'No shifts', 'wpaesm' ) . "</span>";
				}
				
				$mschedule .= "</td>";
			}
			$mschedule .= "</tr>";
		}

		$mschedule .= "</tbody></table>";
		if( 'on' == $nav ) {
			$mschedule .= "<nav class='wpaesm-schedule'><ul><li class='previous'><a href='" . get_the_permalink() . "?week=" . $lastweek . "'>" . __( 'Previous Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='this'><a href='" . get_the_permalink() . "'>" . __( 'This Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='next'><a href='" . get_the_permalink() . "?week=" . $nextweek . "'>" . __( 'Next Week', 'wpaesm' ) . "</a></li></ul></nav>";
		}
		
	} else {
		$mschedule = "<p>" . __( 'You must be logged in to view this page.', 'wpaesm' ) . "</p>";
		$args = array(
	        'echo' => false,
		); 
		$mschedule .= wp_login_form($args);
	}

	$mschedule = apply_filters( 'wpaesm_filter_master_schedule', $mschedule );
	return $mschedule;
}
add_shortcode( 'master_schedule', 'wpaesm_master_schedule_shortcode' );


/**
 * Your Schedule Shortcode.
 *
 * [your_schedule] displays a weekly work schedule for the currently logged-in user.
 *
 * @since 1.0
 *
 * @param array $atts {
 *     Shortcode attributes: begin date, end date, employee ID
 *
 * }
 * @return string  HTML for your schedule.
 */
function wpaesm_your_schedule_shortcode( $atts ) {

	// Attributes
	extract( shortcode_atts(
		array(
			'employee' => '',
			'begin' => '',
			'end' => '',
			'type' => '',
			'status' => '',
			'location' => '',
		), $atts )
	);

	// Enqueue style to make everything look right
	wp_enqueue_style( 'employee-scheduler', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );

	// must be logged in to view this
	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {
		// see if we have shortcode attributes
		if( '' !== $employee ) {
			$nav = 'off';
			$schedulebegin = $begin;
			$scheduleend = $end;
			$i = 0;
			$thisday = strtotime( $begin );
			$lastday = strtotime( $end );
			while( $thisday <= $lastday ) {
				$thisday = strtotime( '+ ' . $i . 'days', $thisday );
				$week[date( "Y-m-d", $thisday )] = array();
				$i++;
			}

		} else {
			$nav = 'on';
			$employee = get_current_user_id();

			// get the appropriate date
			if(isset($_GET['week'])) {
				$thisweek = $_GET['week'];
				$nextweek = strtotime("+1 week", $thisweek);
				$lastweek = strtotime("-1 week", $thisweek);
			} else {
				$thisweek = current_time("timestamp");
				$nextweek = strtotime("+1 week");
				$lastweek = strtotime("-1 week");
			}
			
			$options = get_option('wpaesm_options');

			// get the range of dates for this week

			// find out what day of the week today is
			$today = date("l", $thisweek);

			if($today == $options['week_starts_on']) { // today is first day of the week
				$weekstart = $thisweek;
			} else { // find the most recent first day of the week
				$sunday = 'last ' . $options['week_starts_on'];
				$weekstart = strtotime($sunday, $thisweek);
			}

			// from the first day of the week, add one day 7 times to get all the days of the week
			$i = 0;
			while($i < 7) {
				$week[date("Y-m-d", strtotime('+ ' . $i . 'days', $weekstart))] = array();
				if($i == 0) {
					$schedulebegin = date('F j, Y', strtotime('+ ' . $i . 'days', $weekstart));
				} elseif ($i == 6) {
					$scheduleend = date('F j, Y', strtotime('+ ' . $i . 'days', $weekstart));
				}
				$i++;
			}
		}

		$mschedule = "<h3>" . sprintf( __( 'Your Schedule for %s through %s', 'wpaesm' ), $schedulebegin, $scheduleend ) . "</h3>";
		if( 'on' == $nav ) {
			$mschedule .= "<nav class='wpaesm-schedule'><ul><li class='previous'><a href='" . get_the_permalink() . "?week=" . $lastweek . "'>" . __( 'Previous Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='this'><a href='" . get_the_permalink() . "'>" . __( 'This Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='next'><a href='" . get_the_permalink() . "?week=" . $nextweek . "'>" . __( 'Next Week', 'wpaesm' ) . "</a></li></ul></nav>";
		}

		// collect all the shifts
		foreach($week as $day => $shifts) {
			$args = array( 
				'post_type' => 'shift',
				'meta_query' => array(
					array(
						'key'     => '_wpaesm_date',
						'value'   => $day,
					),
				),
				'connected_type' => 'shifts_to_employees',
				'connected_items' => get_current_user_id(),
				'posts_per_page' => -1,
				'meta_key' => '_wpaesm_starttime',
				'orderby' => 'meta_value_num',
				'order' => 'ASC',
			);

			if( '' !== $type || '' !== $status || '' !== $location ) {
				$args['tax_query'] = array(
						'relation' => 'AND',
				);
				if( '' !== $type ) {
					$args['tax_query'][] =
							array(
									'taxonomy' => 'shift_type',
									'field'    => 'slug',
									'terms'    => $type,
							);
				}
				if( '' !== $status ) {
					$args['tax_query'][] =
							array(
									'taxonomy' => 'shift_status',
									'field'    => 'slug',
									'terms'    => $status,
							);
				}
				if( '' !== $location ) {
					$args['tax_query'][] =
							array(
									'taxonomy' => 'location',
									'field'    => 'slug',
									'terms'    => $location,
							);
				}
			}
			
			$msquery = new WP_Query( $args );
			$i = 0;
			if ( $msquery->have_posts() ) :
				while ( $msquery->have_posts() ) : $msquery->the_post();
					global $shift_metabox;
					$meta = $shift_metabox->the_meta();
					$id = get_the_id();
					$week[$day][$i]['id'] = $id;
					$week[$day][$i]['permalink'] = get_the_permalink();
					$week[$day][$i]['starttime'] = $meta['starttime'];
					$week[$day][$i]['endtime'] = $meta['endtime'];
					$week[$day][$i]['date'] = $meta['date'];
					$statuses = get_the_terms($id, 'shift_status');
					if(is_array($statuses)) {
						foreach($statuses as $shift_status) {
							$week[$day][$i]['status'] = $shift_status->slug;
							$color = get_tax_meta($shift_status->term_id, 'status_color');
							$week[$day][$i]['color'] = $color;
						}
					}
					$jobs = get_posts( array(
					  'connected_type' => 'shifts_to_jobs',
					  'connected_items' => $id,
					  'nopaging' => true,
					  'suppress_filters' => false
					) );
					if( empty( $jobs ) ) {
						$week[$day][$i]['job'] = __( 'No job assigned', 'wpaesm' );
						$week[$day][$i]['joblink'] = '#';
					} else {
						foreach($jobs as $job) {
							$week[$day][$i]['job'] = $job->post_title;
							$week[$day][$i]['joblink'] = site_url() . "/job/" . $job->post_name;
						}
					}
					$i++;
				endwhile;
			endif;
			wp_reset_postdata();

		}

		// collect all the jobs
		$job_array = array();
		foreach($week as $day => $shifts) {
			foreach($shifts as $shift) {
				if(isset($shift['job'])) {
					$job_array[] = $shift['job'];
				}
			}
		}
		// take out all the duplicates
		$job_array = array_unique($job_array);

		// display table
		if( 'off' == $nav ) {
			$class = 'class="wp-list-table widefat fixed posts striped"';
		} else {
			$class = '';
		}
		$mschedule .= "<table id='your-schedule'" . $class . "><thead><tr>";
		$mschedule .= "<th>" . __( 'Job', 'wpaesm' ) . "</th>";
		foreach( $week as $day => $shifts ) {
			$mschedule .= "<th>" . date("D M j", strtotime($day)) . "</th>";
		}
		$mschedule .= "</tr>";
		foreach( $job_array as $job) {
			$mschedule .= "<tr><th scope='row'>" . $job . "</th>";
			foreach($week as $day => $shifts) {
				$mschedule .= "<td>";
				$shift_text='';
				foreach($shifts as $shift) {
					$shift_text .= "<div";
					if( isset( $shift['status'] ) ) {
						$shift_text .= " class='wpaesm-" . $shift['status'] . "'";
					}
					if( isset( $shift['color'] ) ) {
						$shift_text .= " style='background: " . $shift['color'] . "'";
					}
					$shift_text .= ">";
					if(isset($shift['job']) && $job == $shift['job']) {
						$shift_text .= "<div><span class='wpaesm-employee'>" . date("g:i", strtotime($shift['starttime'])) . " - " . date("g:i", strtotime($shift['endtime'])) . "</span>";
						$shift_text .= "<br /><span><a class='wpaesm-details' href='" . $shift['permalink'] . "'>" . __( 'View Shift Details', 'wpaesm' ) . "</a></div>";
					}
					$shift_text .= "</div>";
				}
				if( empty($shift_text) ){
					$mschedule .= __( 'No shifts', 'wpaesm' );
				}
				$mschedule .= $shift_text;
				do_action( 'wpaesm_your_schedule_table_cell' );
				$mschedule .= "</td>";
			}
			$mschedule .= "</tr>";
		}

		$mschedule .= "</table>";
		if( 'on' == $nav ) {
			$mschedule .= "<nav class='wpaesm-schedule'><ul><li class='previous'><a href='" . get_the_permalink() . "?week=" . $lastweek . "'>" . __( 'Previous Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='this'><a href='" . get_the_permalink() . "'>" . __( 'This Week', 'wpaesm' ) . "</a></li>";
			$mschedule .= "<li class='next'><a href='" . get_the_permalink() . "?week=" . $nextweek . "'>" . __( 'Next Week', 'wpaesm' ) . "</a></li></ul></nav>";
		}
		
	} else {
		$mschedule = "<p>" . __( 'You must be logged in to view this page.', 'wpaesm' ) . "</p>";
		$args = array(
	        'echo' => false,
		); 
		$mschedule .= wp_login_form($args);
	}

	$mschedule = apply_filters( 'wpaesm_filter_your_schedule', $mschedule );
	return $mschedule;
}
add_shortcode( 'your_schedule', 'wpaesm_your_schedule_shortcode' );


/**
 * Output buffer.
 *
 * Add output buffer so that when an employee saves their profile, we can redirect to show them their updated profile.
 *
 * @since 1.3
 *
 * @see wpaesm_employee_profile_shortcode()
 */
function wpaesm_output_buffer() {
    ob_start();
}
add_action('init', 'wpaesm_output_buffer');


/**
 * Employee Profile Shortcode.
 *
 * [employee_profile] lets employees edit some of their profile information.
 *
 * @see http://wordpress.stackexchange.com/questions/9775/how-to-edit-a-user-profile-on-the-front-end
 *
 * @since 1.0
 * @return string HTML to display profile form.
 */
function wpaesm_employee_profile_shortcode() {

	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {

		// Enqueue style to make everything look right
		wp_enqueue_style( 'employee-scheduler', plugin_dir_url( __FILE__ ) . 'css/employee-scheduler.css' );
		wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
		wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );

		global $current_user, $wp_roles;

		$error = array();
		/* If profile was saved, update profile. */
		if ( 'POST' == $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['action'] ) && $_POST['action'] == 'update-user' ) {

			/* Update user password. */
			if ( ! empty( $_POST['pass1'] ) && ! empty( $_POST['pass2'] ) ) {
				if ( $_POST['pass1'] == $_POST['pass2'] ) {
					wp_update_user( array( 'ID' => $current_user->ID, 'user_pass' => esc_attr( $_POST['pass1'] ) ) );
				} else {
					$error[] = __( 'The passwords you entered do not match.  Your password was not updated.', 'profile' );
				}
			}

			/* Update user information. */
			if ( ! empty( $_POST['url'] ) ) {
				update_user_meta( $current_user->ID, 'user_url', esc_url( $_POST['url'] ) );
			}
			if ( ! empty( $_POST['email'] ) ) {
				if ( ! is_email( esc_attr( $_POST['email'] ) ) ) {
					$error[] = __( 'The Email you entered is not valid.  please try again.', 'profile' );
				} elseif ( email_exists( esc_attr( $_POST['email'] ) ) != $current_user->id ) {
					$error[] = __( 'This email is already used by another user.  try a different one.', 'profile' );
				} else {
					wp_update_user( array( 'ID' => $current_user->ID, 'user_email' => esc_attr( $_POST['email'] ) ) );
				}
			}

			if ( ! empty( $_POST['first-name'] ) ) {
				update_user_meta( $current_user->ID, 'first_name', esc_attr( $_POST['first-name'] ) );
			}
			if ( ! empty( $_POST['last-name'] ) ) {
				update_user_meta( $current_user->ID, 'last_name', esc_attr( $_POST['last-name'] ) );
			}
			if ( ! empty( $_POST['description'] ) ) {
				update_user_meta( $current_user->ID, 'description', esc_attr( $_POST['description'] ) );
			}
			if ( ! empty( $_POST['address'] ) ) {
				update_user_meta( $current_user->ID, 'address', esc_attr( $_POST['address'] ) );
			}
			if ( ! empty( $_POST['city'] ) ) {
				update_user_meta( $current_user->ID, 'city', esc_attr( $_POST['city'] ) );
			}
			if ( ! empty( $_POST['state'] ) ) {
				update_user_meta( $current_user->ID, 'state', esc_attr( $_POST['state'] ) );
			}
			if ( ! empty( $_POST['zip'] ) ) {
				update_user_meta( $current_user->ID, 'zip', esc_attr( $_POST['zip'] ) );
			}
			if ( ! empty( $_POST['phone'] ) ) {
				update_user_meta( $current_user->ID, 'phone', esc_attr( $_POST['phone'] ) );
			}

			do_action( 'wpaesm_save_additional_user_profile_fields', $current_user->ID );

			/* Redirect so the page will show updated info.*/
			if ( count( $error ) == 0 ) {
				//action hook for plugins and extra fields saving
				do_action( 'edit_user_profile_update', $current_user->ID );
				wp_redirect( get_permalink() );
				exit;
			}
		}

		ob_start();
		include 'profile-form.php';

		return ob_get_clean();

		return $profile;

	} else {
		$msg = __( 'You must be logged in to view this page', 'wpaesm' );
		$args = array(
				'echo' => false,
		);
		$msg .= wp_login_form($args);
		return $msg;
	}
}
add_shortcode( 'employee_profile', 'wpaesm_employee_profile_shortcode' );


/**
 * Today shortcode.
 *
 * [today] shows the currently logged-in employee the shift(s) they are scheduled to work today.
 *
 * @since 1.0
 *
 * @return string HTML to display today's shifts.
 */
function wpaesm_today_shortcode() {

	// Enqueue style to make everything look right
	wp_enqueue_style( 'employee-scheduler', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );

	// must be logged in to view this
	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {
		$now = date("Y-m-d", current_time("timestamp"));
		$viewer = wp_get_current_user();
		$today = '';
		
		$args = array( 
		    'post_type' => 'shift',
		    'posts_per_page' => -1,
		    'order' => 'ASC', 
		    'meta_key' => '_wpaesm_starttime',        
		    'orderby' => 'meta-value', 
		    'meta_query' => array(
				array(
					'key'     => '_wpaesm_date',
					'value'   => $now,
				),
			),
		    'connected_type' => 'shifts_to_employees',
		    'connected_items' => $viewer->ID,

		);
		
		$todayquery = new WP_Query( $args );
		
		// The Loop
		if ( $todayquery->have_posts() ) {
			$jobs = get_posts( array(  
				'connected_type' => 'shifts_to_jobs',
				'connected_items' => get_the_id(),
				'nopaging' => true,
				'suppress_filters' => false
			) );
			foreach($jobs as $job) {
				$job_name = $job->post_title;
			}
			$today .= "<p>" . sprintf( __( 'You have %i shift(s) scheduled today.  Which would you like to view?', 'wpaesm' ), $todayquery->found_posts ) . "</p>";
			$today .= "<ul>";
				while ( $todayquery->have_posts() ) : $todayquery->the_post();
					global $shift_metabox;
					$meta = $shift_metabox->the_meta();
					$today .= "<li><a href='" . get_the_permalink() . "'>" . date("g:i", strtotime($meta['starttime'])) . " - " . date("g:i", strtotime($meta['endtime']));
					if(isset($job_name)) {
						$today .=  ", " . __( 'Job: ', 'wpaesm' ) . $job_name . "</a></li>";
					}
					$today .= "</a></li>";
				endwhile;
			$today .= "</ul>";
		} else {
			$today .= "<p>" . __( 'You do not have any shifts scheduled today.', 'wpaesm' ) . "</p>";
		}
		
		// Reset Post Data
		wp_reset_postdata();
		
	} else {
		$today = "<p>" . __( 'You must be logged in to view this page.', 'wpaesm' ) . "</p>";
		$args = array(
	        'echo' => false,
		); 
		$today .= wp_login_form($args);
	}

	$today = apply_filters( 'wpaesm_filter_today', $today );

	return $today;

}
add_shortcode( 'today', 'wpaesm_today_shortcode' );

/**
 * Extra Work shortcode.
 *
 * [extra_work] shortcode displays a form where employees can record work they did that was not a scheduled shift.
 *
 * @since 1.0
 *
 * @return string HTML to display extra work form.
 */
function wpaesm_extra_work_shortcode() {

	// enqueue scripts to make date and time pickers work
	wp_enqueue_style( 'employee-scheduler', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );
    wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
    wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );

	// must be logged in to view this
	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {
		$morework = '';
		// Process the form if we need to
	    // get current user's name
	    $viewer = wp_get_current_user();
	    $viewername = $viewer->display_name;
	    if( isset( $_POST['form_name'] ) && "extra_work" == ( $_POST['form_name'] ) ) {
	    	$morework = wpaesm_add_extra_work_shift( $viewer );
	    }

	    // display the form
		$morework .=
			'<p>' . __( 'Use this form to record work you do outside of your scheduled shifts.', 'wpaesm' ). '</p>
			<form method="post" action="' . get_the_permalink() . '" id="extra-work">
				<p>
					<label>' . __('Date', 'wpaesm') . '</label>
					<input type="text" name="thisdate" id="thisdate" required>
				</p>
				<p>
					<label>' . __('Start Time', 'wpaesm') . '</label>
					<input type="text" name="starttime" id="starttime" required>
				</p>
				<p>
					<label>' . __('End Time', 'wpaesm') . '</label>
					<input type="text" name="endtime" id="endtime" required>
				</p>';
				// get the extra term
				$extratype = get_term_by( 'slug', 'extra', 'shift_type' );
				// if extra has children, show dropdown of children
				$extra_children = get_term_children( $extratype->term_id, 'shift_type' );
				if( !empty( $extra_children ) ) {
					$morework .=
						'<p>
							<label>' . __('Type of Work', 'wpaesm') . '</label>
							<select name="shifttype" id="extra-work-shift-type">
								<option value=""> </option>';
								foreach( $extra_children as $child ) {
									$childterm = get_term_by( 'id', $child, 'shift_type' );
									$morework .= "<option value='" . $childterm->slug . "'>" . $childterm->name . "</option>";
								}
					$morework .= "</select>";
				}
		$morework .= "<p id='wpaesm-job'><label>" . __('Job', 'wpaesm') . "</label>";
		$morework .= "<select name='this_job' id='this_job'><option value=''> </option>";
		$args = array('post_type' => 'job', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'title' );
		$jobs = get_posts( $args );
		foreach ($jobs as $job) {
			$morework .= "<option value='" . $job->ID . "'>" . get_the_title($job->ID) . "</option>";
		}
		$morework .= "</select>";
		$morework .= "<p><label>" . __('Description', 'wpaesm') . "</label>";
		$morework .= "<textarea name='description' id='description'></textarea></p>";
		$morework .= "<input type='hidden' name='form_name' value='extra_work'>";
		$morework .= "<input name='wpaesm_extra_work_nonce' id='wpaesm_extra_work_nonce' type='hidden' value='" . wp_create_nonce( 'wpaesm_extra_work_nonce' ) . "'>";
		$morework .= "<input type='submit' value='" . __('Record work', 'wpaesm') . "'>";
		$morework .= "</form>";

	} else {
		$morework = "<p>" . __( 'You must be logged in to view this page.', 'wpaesm' ) . "</p>";
		$args = array(
	        'echo' => false,
		); 
		$morework .= wp_login_form($args);
	}

	$morework = apply_filters( 'wpaesm_filter_extra_work', $morework );

	return $morework;
}
add_shortcode( 'extra_work', 'wpaesm_extra_work_shortcode' );

/**
 * Save extra work shift.
 *
 * When an employee fills out the extra work form, create a shift.
 *
 * @since 1.0
 *
 * @see wpaesm_extra_work_shortcode()
 *
 * @param int  $viewer  ID of the employee who filled in the form.
 * @return string  Success or failure message.
 */
function wpaesm_add_extra_work_shift( $viewer ) {
	if ( !wp_verify_nonce( $_POST['wpaesm_extra_work_nonce'], "wpaesm_extra_work_nonce")) {
        exit( "Permission error." );
    }

    $viewername = $viewer->display_name;
	$extrawork = array(
			'post_type'     => 'shift',
			'post_title'    => 'Extra shift by ' . $viewername,
			'post_status'   => 'publish',
			'post_content'	=> sanitize_text_field( $_POST['description'] ),
		);
	$extrashift = wp_insert_post( $extrawork );

	// check whether admins need to approve extra shifts
	$options = get_option( 'wpaesm_options' );
	if( '1' == $options['extra_shift_approval'] ) {
		// mark the shift as pending approval
		wp_set_object_terms( $extrashift, 'pending-approval', 'shift_status' );

		// email notification to admin
		$from = $options['notification_from_name'] . " <" . $options['notification_from_email'] . ">";
		if(isset($options['admin_notification_email'])) {
			$to = $options['admin_notification_email'];
		} else {
			$to = get_bloginfo('admin_email');
		}
		$subject = sprintf( __( 'Extra shift by %s is pending your approval', 'wpaesm' ), $viewername );
		$message = '
			<p>' . __( 'There is a new extra shift awaiting your approval', 'wpaesm' ) . '</p>
			<p><strong>' . __( 'Shift details' ) . '</strong>
				<ul>
					<li><strong>' . __( 'Employee:', 'wpaesm' ) . '</strong> ' . $viewername . '</li>
					<li><strong>' . __( 'Date:', 'wpaesm' ) . '</strong> ' . $_POST['thisdate'] . '</li>
					<li><strong>' . __( 'Time:', 'wpaesm' ) . '</strong> ' . $_POST['starttime'] . '&nbsp;-&nbsp' . $_POST['endtime'] . '</li>
					<li><strong>' . __( 'Duration:', 'wpaesm' ) . '</strong> ' . wpaesm_calculate_duration( $_POST['starttime'], $_POST['endtime'] ) . '</li>';
					if( isset( $_POST['description'] ) && '' !== $_POST['description'] ) {
						$message .= '
						<li><strong>' . __( 'Description:', 'wpaesm' ) . '</strong> ' . sanitize_text_field( $_POST['description'] ) . '</li>
						';
					}
				$message .=
				'</ul>
			</p>
			<p><a href="' . get_the_permalink( $extrashift ) . '">' . __( 'View this shift', 'wpaesm' ) . '</a></p>
			<p><a href="' . get_edit_post_link( $extrashift ) . '">' . __( 'Edit this shift', 'wpaesm' ) . '</a></p>
			<p>' . __( 'To approve this shift, edit it and change the shift status to "worked."  If you do not approve this shift, edit it and change the shift status to "not approved."') . '</p>
			<p><a href="' . admin_url( 'edit.php?shift_status=pending-approval&post_type=shift' ) . '">' . __( 'View all extra shifts awaiting approval', 'wpaesm' ) . '</a></p>';
		wpaesm_send_email( $from, $to, '', $subject, $message, '' );
	} else {
		// we don't need admin approval, so mark the shift as worked
		wp_set_object_terms( $extrashift, 'worked', 'shift_status' );
	}

	wp_set_object_terms( $extrashift, 'extra', 'shift_type' );
	// also add subcategory, if they selected one from the drop-down
	if( isset( $_POST['shifttype'] ) ) {
		wp_set_object_terms( $extrashift, $_POST['shifttype'], 'shift_type' );
	}
	wp_set_object_terms( $extrashift, 'worked', 'shift_status' );
	// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
	$fields = array('_wpaesm_employeenote', '_wpaesm_date', '_wpaesm_starttime', '_wpaesm_endtime', '_wpaesm_notify', '_wpaesm_clockin', '_wpaesm_clockout', '_wpaesm_location_in', '_wpaesm_location_out', '_wpaesm_location_in', '_wpaesm_location_out');
	$str = $fields;
	update_post_meta( $extrashift, 'shift_meta_fields', $str );

	add_post_meta( $extrashift, '_wpaesm_date', $_POST['thisdate'] );
	add_post_meta( $extrashift, '_wpaesm_clockin', $_POST['starttime'] );
	add_post_meta( $extrashift, '_wpaesm_clockout', $_POST['endtime'] );
	add_post_meta( $extrashift, '_wpaesm_starttime', $_POST['starttime'] );
	add_post_meta( $extrashift, '_wpaesm_endtime', $_POST['endtime'] );
	// connect shift to employee
	p2p_type( 'shifts_to_employees' )->connect( $extrashift, $viewer->ID, array(
	    'date' => current_time('mysql')
	) );
	// connect shift to job
	if(isset($_POST['this_job']) && $_POST['this_job'] !== ' ') {
		p2p_type( 'shifts_to_jobs' )->connect( $extrashift, $_POST['this_job'], array(
		    'date' => current_time('mysql')
		) );
	}

	if($extrashift) {
		$message = "<p class='wpaesm-success'>" . __('Your extra work has been recorded.  ', 'wpaesm') . "<a href='" . get_the_permalink( $extrashift ) . "'>" . __('View extra work shift', 'wpaesm') . "</a></p>";
	} else {
		$message = "<p class='wpaesm-failure'>" . __('Sorry, there was an error recording your work.', 'wpaesm') . "</p>";
	}

	do_action( 'wpaesm_add_extra_work_action' );

	return $message;
}

/**
 * Record Expense shortcode.
 *
 * [record_expense] displays a form where employees can record mileage and expenses.
 *
 * @since 1.0
 *
 * @return string HTML to display form.
 */
function wpaesm_record_expense_shortcode() {

	// Enqueue style to make everything look right
	wp_enqueue_style( 'employee-scheduler', plugin_dir_url(__FILE__) . 'css/employee-scheduler.css' );

	// enqueue scripts to make date and time pickers work
    wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
    wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );

	// must be logged in to view this
	if( is_user_logged_in() && ( wpaesm_check_user_role('employee') || wpaesm_check_user_role('administrator') ) ) {
		$expense = '';
		// Process the form if we need to
	    // get current user's name
	    $viewer = wp_get_current_user();
	    $viewername = $viewer->display_name;
	    if( isset( $_POST['form_name'] ) && "expense" == ( $_POST['form_name'] ) ) {
	    	$expense .= wpaesm_add_expense( $viewer );
	    }

	    // display the form
		$expense .=
		'<p>' . __('Use this form to record your expenses and mileage.', 'wpaesm'). '</p>
		<form method="post" action="' . get_the_permalink() . '" id="expense">
			<input type="hidden" name="form_name" value="expense">
			<p>
				<label>' . __('Date', 'wpaesm') . '</label>
				<input type="text" name="thisdate" id="thisdate2" required>
			</p>
			<p>
				<label>' . __('Expense Type', 'wpaesm') . '</label>
				<select name="type" id="create_expense_type" required>
					<option value=""> </option>';
					$expense .= wpaesm_expense_category_dropdown();
					$expense .=
				'</select>
			<p>
				<label>' . __('Amount (currency or number of miles)', 'wpaesm') . '</label>
				<input type="text" name="amount" id="amount" required>
			</p>
			<p id="jobfield">
				<label>' . __('Job', 'wpaesm') . '</label>
				<select name="this_job" id="this_job">
					<option value=""> </option>';
					$args = array('post_type' => 'job', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'title' );
					$jobs = get_posts( $args );
					foreach ($jobs as $job) {
						$expense .= "<option value='" . $job->ID . "'>" . get_the_title($job->ID) . "</option>";
					}
					$expense .=
				'</select>
			</p>
			<p>
				<label>' . __('Description', 'wpaesm') . '</label>
				<textarea name="description" id="description"></textarea>
			</p>
			<p>
				<input type="submit" value="' . __('Record Expense', 'wpaesm') . '">
			</p>
		</form>';

	} else {
		$expense = "<p>" . __( 'You must be logged in to view this page.', 'wpaesm' ) . "</p>";
		$args = array(
	        'echo' => false,
		); 
		$expense .= wp_login_form($args);
	}

	$expense = apply_filters( 'wpaesm_filter_expense_report', $expense );

	return $expense;
}
add_shortcode( 'record_expense', 'wpaesm_record_expense_shortcode' );

/**
 * Expense category dropdown.
 *
 * Expense category is a hierarchical taxonomy: this displays the top-level expense categories.
 *
 * @since 1.0
 *
 * @see wpaesm_record_expense_shortcode()
 *
 * @return string HTML for dropdown.
 */
function wpaesm_expense_category_dropdown() {
	$dropdown = '';

    // Get all taxonomy terms 
    $terms = get_terms('expense_category', array(
            "hide_empty" => false,
            "parent" => 0
        )
    );

    if( isset( $terms ) ) {
    	foreach( $terms as $term ) {
    		$dropdown .= '<option value="' . $term->slug . '">' . $term->name . '</option>';
    		$dropdown .= wpaesm_get_term_children( $term->term_id, 1 );
    	}
    }

   	return $dropdown;
}

/**
 * Expense category dropdown: child terms.
 *
 * Display the children and grandchildren in the expense category dropdown.
 *
 * @since 1.0
 *
 * @see wpaesm_expense_category_dropdown()
 *
 * @param $termid  int  ID of taxonomy term
 * @param $depth  int  how deep in the hierarchy we are
 *
 * @return string HTML for dropdown.
 */
function wpaesm_get_term_children( $termid, $depth ) {

	$children = '';
	$childterms = get_terms('expense_category', array(
            "hide_empty" => false,
            "parent" => $termid
        )
    );

	if( isset( $childterms ) ) {
		$depth++;
	    foreach( $childterms as $childterm ) {
	    	

	    	$children .= '<option value="' . $childterm->slug . '"> ';
	    	for ($i=0; $i < $depth; $i++) { 
	    		$children .= '--';
	    	}
	    	$children .= ' ' . $childterm->name . '</option>';
	    	$children .= wpaesm_get_term_children( $childterm->term_id, $depth );
	    }
	}

	return $children;
}

/**
 * Record expense.
 *
 * When employee fills out the "record expense" form, save the expense.
 *
 * @since 1.0
 *
 * @see wpaesm_record_expense_shortcode()
 *
 * @param int  $viewer  ID of employee who filled in the form.
 * @return string  Success or failure message.
 */
function wpaesm_add_expense( $viewer ) {
	$viewername = $viewer->display_name;
	$thisexpense = array(
			'post_type'     => 'expense',
			'post_title'    => 'Expense reported by ' . $viewername,
			'post_status'   => 'publish',
			'post_content'	=> sanitize_text_field( $_POST['description'] ),
		);
	$newexpense = wp_insert_post($thisexpense);

	// add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
	$data = array('_wpaesm_date','_wpaesm_amount','_wpaesm_mileage');
	$str = $data;
	update_post_meta( $newexpense, 'expense_meta_fields', $str );

	add_post_meta( $newexpense, '_wpaesm_date', $_POST['thisdate'] );
	if( isset( $_POST['type'] ) ) {
		add_post_meta( $newexpense, '_wpaesm_amount', $_POST['amount'] );
		wp_set_object_terms( $newexpense, $_POST['type'], 'expense_category' );
	}

	// connect shift to employee
	p2p_type( 'expenses_to_employees' )->connect( $newexpense, $viewer->ID, array(
	    'date' => current_time('mysql')
	) );
	// connect shift to job
	if(isset($_POST['this_job']) && $_POST['this_job'] !== ' ') {
		p2p_type( 'expenses_to_jobs' )->connect( $newexpense, $_POST['this_job'], array(
		    'date' => current_time('mysql')
		) );
	}

	if($newexpense) {
		$message = "<p class='wpaesm-success'>" . __('Your expense has been recorded.', 'wpaesm') . "</p>";
	} else {
		$message = "<p class='wpaesm-failure'>" . __('Sorry, there was an error recording your expense.', 'wpaesm') . "</p>";
	}

	do_action( 'wpaesm_add_expense_action' );

	return $message;
}


?>