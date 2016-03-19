<?php
/**
 * Instructions
 *
 * Display instructions for using the plugin.
 *
 * @package WordPress
 * @subpackage Employee Scheduler
 * @since 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Display instructions.
 *
 * Displays the instructions for using the plugin.
 *
 * @since 1.0
 */
function wpaesm_instructions() { ?>
	<div class="wrap instructions">
		
		<!-- Display Plugin Icon, Header, and Description -->
		<div class="icon32" id="icon-options-general"><br></div>
		<h1><?php _e('Instructions for using the Employee Scheduler', 'wpaesm'); ?></h1>

		<aside style="width: 24%; float: right; background: #fff; padding: 5px 15px; border: 1px solid;">
			<?php do_action( 'wpaesm_options_sidebar' ); ?>
		</aside>

		<h3><?php _e('Initial Set Up', 'wpaesm'); ?></h3>

		<h4><?php _e('Plugin Settings', 'wpaesm'); ?></h4>
		<p><?php _e('The plugin has a few settings that you might want to adjust.  In the WordPress dashboard menu, click on <a href="' . admin_url( 'page=employee-scheduler/options.php' ) . '">Employee Scheduler.</a>', 'wpaesm'); ?></p>
		<p><?php _e('The first several settings relate to the email notifications sent to employees. You can change the sender name, sender email, and message subject.', 'wpaesm'); ?></p>
		<p><?php _e('The "Admin Notifications" settings let you decide whether or not to receive a notification when an employee leaves a note on a shift, or when a shift changes status.  You can change the email address that receives these notifications.', 'wpaesm'); ?></p>
		<p><?php _e('You can select what day of the week your work-week starts on, whether or not you want to record employees\' location when they clock in and out, and other settings related to overtime hours and pay.  You can also turn on and off the email sent to employees to ask them to verify their payroll report.', 'wpaesm'); ?></p>
		<p><?php _e('You can choose whether the payroll report will be generated based on scheduled hours, or on actual hours worked.  If you choose "scheduled hours," then the payroll report will be based on the scheduled start and end times for the shift.  The following shift statuses will be included in the report: assigned, tentative, worked.  If you choose to base the payroll on actual hours worked, then the clock-in and clock-out times will be used to calculate the payroll, and only shifts with the "worked" shift status will be added into the report.', 'wpaesm'); ?></p>

		<h4><?php _e('Set up shift types', 'wpaesm'); ?></h4>
		<p><?php _e('Shifts can be organized into shift types if you want.  You might want to categorize shifts based on where the are worked or the type of work involved.', 'wpaesm'); ?></p> 
		<p><?php _e('To create your shift types, go to <a href="' . admin_url( 'edit-tags.php?taxonomy=shift_type&post_type=shift') . '">Shifts --> Shift Types</a>.  Two shift types have already been created for you:', 'wpaesm'); ?>
			<ul>
				<li>
					<?php _e('Extra: automatically assigned to shifts that employees create themselves, if they do work outside of their scheduled shifts.', 'wpaesm'); ?>
				</li>
				<li>	
					<?php _e('Paid Time Off: If you want an employee to get paid time off, you can create a shift in this category, with a duration of the number of hours you want them to be paid for.', 'wpaesm'); ?>
				</li>
			</ul>
		</p>
		<p><?php _e('To create more shift types, fill in the "Add New Shift Type" form on the left side of the page.  You only need to fill in the name - you can ignore the "slug" field, and the "description" field is optional.', 'wpaesm'); ?></p>
		<p><?php _e('If you want to keep track of different kinds of "Extra" work, you can make shift types with "Extra" as their parent.  If you do this, the "Record Extra Work" form will let employees select what kind of extra work they are recording.', 'wpaesm'); ?></p> 

		<h4><?php _e('Set up shift statuses', 'wpaesm'); ?></h4>
		<p><?php _e('Shifts are also organized into statuses, such as "tentative," "assigned," and "worked."', 'wpaesm'); ?></p> 
		<p><?php _e('Several shift statuses have already been created for you:', 'wpaesm'); ?>
			<ul>
				<li>
					<?php _e('Assigned: Default status for a shift, indicates that this shift has been assigned to an employee', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Tentative: Shift has been assigned, but not cast in stone', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Unassigned: No one has been assigned to work this shift', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Worked: Employee has worked this shift', 'wpaesm'); ?>
				</li>
			</ul>
		</p>
		<p><?php _e('Only shifts with the "Worked" shift status will appear in payroll reports and timesheets.', 'wpaesm'); ?>
		<p><?php _e('If you need additional shift statuses, you can create them by going to go to <a href="' . admin_url( 'edit-tags.php?taxonomy=shift_status&post_type=shift') . '">Shifts --> Shift Statuses</a> and using the form on the left side of the page.', 'wpaesm'); ?></p>
		<p><?php _e('Shift statuses have an extra field for color.  If you assign a color to a shift status, then that color will be used as the background color for shifts with that status on the schedule.  So you might want to give the "Tentative" status a light grey color, so that employees can easily see that the shift isn\'t cast in stone.  You will probably also want to assign a color to shifts that have been worked, so that you can easily see if someone missed a shift.  These colors are entirely optional.', 'wpaesm'); ?></p>
		<p><?php _e('To assign a color to an existing shift, click on the status name in the list of shift statuses.  Click in the "color" field, and a color wheel will appear.  When you like the color, click "Update."', 'wpaesm'); ?></p>

		<h4><?php _e('Create employees', 'wpaesm'); ?></h4>
		<p><?php _e('Next you need to set up your employees.  To do this, you will create User accounts for them.', 'wpaesm'); ?></p>
		<p><?php _e('Go to <a href="' . admin_url( 'user-new.php') . '">Users --> Add New.</a>', 'wpaesm'); ?></p>
		<p><?php _e('Enter the information about an employee: username, name, email, password (the employee will be able to change the password).  In the dropdown menu labeled "Role," make sure you select "Employee."', 'wpaesm'); ?></p>
		<p><?php _e('Click "Add New User."', 'wpaesm'); ?></p>
		<p><?php _e('Once you have created a user, there are some more fields you can fill in.  Click on <a href="' . admin_url( 'users.php') . '">Users --> All Users</a>.  Click on a user to edit them.  Scroll down to the section labeled "Employee Information."  There you can enter their address and phone number.', 'wpaesm'); ?></p>

		<h4><?php _e('Create jobs', 'wpaesm'); ?></h4>
		<p><?php _e('Next you need to set up your jobs.', 'wpaesm'); ?></p>
		<p><?php _e('You can create categories for your jobs if you want - it is just like create shift types or shift statuses.', 'wpaesm'); ?></p>
		<p><?php _e('To create a job, go to <a href="' . admin_url( 'post-new.php?post_type=job') . '">Jobs --> Add New</a>.  For the title, enter the job\'s name.  You can enter a description of the job if you want.  ', 'wpaesm'); ?></p>
		<p><?php _e('In the right-hand sidebar, you will see several boxes.  Most of these boxes will be auto-populated as you create shifts and expenses.  However, you might want to fill in some of these boxes.  Here are descriptions of these boxes:', 'wpaesm'); ?>
			<ul>
				<li>
					<?php _e('Job Category: If you are grouping your jobs into categories, here is where you select what category or categories this job belongs to.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Connected Shifts: This box will auto-populate, so you do not need to enter anything into it.  This box will show you all of the shifts associated with a job.  This box is likely to get very long, so you might want to click the little triangle next to the box title to collapse the box.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Connected Expenses: This box will auto-populate, so you do not need to enter anything into it.  When employees enter expenses, they can enter the client associated with that expense: those expenses will show up here.', 'wpaesm'); ?>
				</li>
			</ul>

		</p>

		<h4><?php _e('Shortcodes', 'wpaesm'); ?></h4>
		<p><?php _e('Here are the shortcodes to display your schedule and other information on the site:', 'wpaesm'); ?>
			<ul>
				<li>
					<?php _e('Master Schedule.  Displays the full work schedule for all employees: <br /><code>[master_schedule]</code>', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Your Schedule.  Displays the schedule for the employee who is viewing the page (so if John Smith is viewing the site, this page will show him his schedule): <br /><code>[your_schedule]</code>', 'wpaesm'); ?>
				</li>
				<li>	
					<?php _e('Employee Profile.  Displays the employee\'s user profile let them edit their password and contact information:<br /><code>[employee_profile]</code>', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Today. Displays today\'s shifts to the employee who is viewing the page:<br /><code>[today]</code>', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Extra Work.  Displays a form where employees can enter the date, start time, end time, and description of extra work they do that is not a part of a scheduled shift: <br /><code>[extra_work]</code>', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Record Expense. Displays a form where employees can enter mileage and other expenses:<br /><code>[record_expense]</code>', 'wpaesm'); ?>
				</li>
			</ul>
		</p>

		<h3><?php _e('Creating the Schedule', 'wpaesm'); ?></h3>
		<p><?php _e('To create a single shift:', 'wpaesm'); ?>
			<ul>
				<li>
					<?php _e('Go to <a href="' . admin_url( 'post-new.php?post_type=shift') . '">Shifts --> Add Single Shift</a>.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Enter a title (this won\'t display anywhere on the website, but it\'s a good idea to make an informative title so site admins can recognize the shift)', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('Enter a description if you want - employees will see this description when they view the shift detail.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('In the shift details box, enter the date and times of the shift.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('If you check the box next to "notify employee", the employee will receive an email telling them this shift has been created.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('In the right sidebar, look for the box labeled "Connected Jobs."  Click "Add Job" to choose a job.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('In the right sidebar, look for the box labeled "Connected Users."  Click "Add Employee" to choose the employee who will work this shift.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('You can choose a Shift Status in the right sidebar.  If you do not choose a status, the shift will default to "Tentative."', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('You can also choose a Shift Type in the right sidebar.', 'wpaesm'); ?>
				</li>
				<li>
					<?php _e('When you are happy with all of these details, click "Publish."', 'wpaesm'); ?>
				</li>
			</ul>
		</p>

		<?php do_action( 'wpaesm_pro_instructions' ) ;?>
		
	</div>
<?php }