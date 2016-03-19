<?php
/*
Plugin Name: Employee Scheduler
Plugin URI: https://employee-scheduler.co
Description: Manage your employees' schedules, let employees view their schedule online, generate timesheets and payroll reports
Version: 1.8.5
Author: Morgan Kay
Author URI: http://wpalchemists.com
Text Domain: wpaesm
*/

/*  Copyright 2015 Morgan Kay (email : morgan@wpalchemists.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Require minimum version of WordPress.
 *
 * Checks if site is running at least 3.8, refuses to activate plugin otherwise.
 *
 * @since 1.0
 *
 */
add_action( 'admin_init', 'wpaesm_requires_wordpress_version' );

function wpaesm_requires_wordpress_version() {
	global $wp_version;
	$plugin = plugin_basename( __FILE__ );
	$plugin_data = get_plugin_data( __FILE__, false );

	if ( version_compare($wp_version, "3.8", "<" ) ) {
		if( is_plugin_active($plugin) ) {
			deactivate_plugins( $plugin );
			$msg = __( 'Employee Scheduler cannot be activated, because it requires WordPress version 3.8 or higher!  Please update WordPress and try again.', 'wpaesm' );
			wp_die( $msg );
		}
	}
}

/**
 * Add default option values.
 *
 * Creates default option values when plugin is activated.  Function defined in options.php.
 * 
 * @since 1.0
 *
 */
register_activation_hook( __FILE__, 'wpaesm_add_defaults' );

/**
 * Delete options.
 *
 * Deletes the plugin options when plugin is deleted.  Function defined in options.php.
 *
 * @since 1.0
 *
 */
register_uninstall_hook( __FILE__, 'wpaesm_delete_plugin_options' );

/**
 * Create options.
 *
 * Use Settings API to set up options page.  Function defined in options.php.
 *
 * @since 1.0
 *
 */
add_action('admin_init', 'wpaesm_options_init' );

/**
 * Add pages to menus.
 *
 * Add Employee Scheduler options page, instructions page, and schedules page to admin menu.  Function definted in options.php.
 *
 * @since 1.0
 *
 */
add_action('admin_menu', 'wpaesm_add_options_page');

/**
 * Require other necessary plugin files.
 *
 * @since 1.0
 *
 */
// Require options stuff
require_once( plugin_dir_path( __FILE__ ) . 'options.php' );
// Require views
require_once( plugin_dir_path( __FILE__ ) . 'views.php' );
// Require dashboard views
require_once( plugin_dir_path( __FILE__ ) . 'dashboard-views.php' );
// Require email
require_once( plugin_dir_path( __FILE__ ) . 'email.php' );
// Require instructions
require_once( plugin_dir_path( __FILE__ ) . 'instructions.php' );


/**
 * Initialize language.
 *
 * Initialize language so plugin can be translated.
 *
 * @since 1.0
 *
 */
add_action('init', 'wpaesm_language_init');

function wpaesm_language_init() {
  load_plugin_textdomain( 'wpaesm', false, 'employee-scheduler/languages' );
}



/**
 * Register custom taxonomy: shift type.
 *
 * Register the custom taxonomy "shift type" which is associated with shifts, and create default shift types.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_shift_type', 0 );

function wpaesm_register_tax_shift_type() {

	$labels = array(
		'name'                       => _x( 'Shift Types', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Shift Type', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Shift Types', 'wpaesm' ),
		'all_items'                  => __( 'All Shift Types', 'wpaesm' ),
		'parent_item'                => __( 'Parent Shift Type', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Shift Type:', 'wpaesm' ),
		'new_item_name'              => __( 'New  Shift Type', 'wpaesm' ),
		'add_new_item'               => __( 'Add New Shift Type', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Shift Type', 'wpaesm' ),
		'update_item'                => __( 'Update Shift Type', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Shift Types with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Shift Types', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Shift Types', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Shift Types', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_shift_type_labels', $labels );

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'shift_type', array( 'shift' ), $args );

	// Create default statuses
	wp_insert_term(
		'Extra', // the term 
		'shift_type', // the taxonomy
		array(
			'description'=> 'Work done outside of a scheduled shift',
			'slug' => 'extra',
		)
	);

	wp_insert_term(
		'Paid Time Off', // the term 
		'shift_type', // the taxonomy
		array(
			'description'=> 'Paid time that is not a work shift',
			'slug' => 'pto',
		)
	);

}

/**
 * Register custom taxonomy: shift status.
 *
 * Register the custom taxonomy "shift status" which is associated with shifts, and create default shift statuses.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_shift_status', 0 );

function wpaesm_register_tax_shift_status() {

	$labels = array(
		'name'                       => _x( 'Shift Statuses', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Shift Status', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Shift Statuses', 'wpaesm' ),
		'all_items'                  => __( 'All Shift Statuses', 'wpaesm' ),
		'parent_item'                => __( 'Parent Shift Status', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Shift Status:', 'wpaesm' ),
		'new_item_name'              => __( 'New Shift Status', 'wpaesm' ),
		'add_new_item'               => __( 'Add New Shift Status', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Shift Status', 'wpaesm' ),
		'update_item'                => __( 'Update Shift Status', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Shift Statuses with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Shift Statuses', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Shift Statuses', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Shift Statuses', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_shift_status_labels', $labels );

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'shift_status', array( 'shift' ), $args );

	wp_insert_term(
		'Unassigned', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'No one has been assigned to work this shift',
			'slug' => 'unassigned',
		)
	);

	wp_insert_term(
		'Assigned', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'Default status for a shift, indicates that this shift has been assigned to an employee',
			'slug' => 'assigned',
		)
	);

	wp_insert_term(
		'Worked', // the term 
		'shift_status', // the taxonomy
		array(
			'description'=> 'Employee has worked this shift',
			'slug' => 'worked',
		)
	);

	// if admin must approve extra shifts, then we need a "pending approval" status and a "not approved" status
	$options = get_option( 'wpaesm_options' );
	if( isset( $options['extra_shift_approval'] ) && '1' == $options['extra_shift_approval'] ) {
		wp_insert_term(
			'Pending Approval', // the term 
			'shift_status', // the taxonomy
			array(
				'description'=> 'Employee has worked the shift, but it is pending admin approval',
				'slug' => 'pending-approval',
			)
		);

		wp_insert_term(
			'Not Approved', // the term 
			'shift_status', // the taxonomy
			array(
				'description'=> 'Employee reported an extra shift, but admin did not approve it',
				'slug' => 'not-approved',
			)
		);
	}

}

/**
 * Default shift status.
 *
 * When a shift is saved, if no other shift status has been selected, it will default to "assigned.""
 *
 * @since 1.0
 *
 * @link http://wordpress.mfields.org/2010/set-default-terms-for-your-custom-taxonomies-in-wordpress-3-0/
 *
 * @param int  $post_id ID of the post being saved
 * @param int  $post ID of post object
 */
add_action( 'save_post', 'wpaesm_default_shift_status', 100, 2 );

function wpaesm_default_shift_status( $post_id, $post ) {
    if ( 'publish' === $post->post_status ) {
        $defaults = array(
            'shift_status' => array( 'assigned' ),
            );
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( (array) $taxonomies as $taxonomy ) {
            $terms = wp_get_post_terms( $post_id, $taxonomy );
            if ( empty( $terms ) && array_key_exists( $taxonomy, $defaults ) ) {
                wp_set_object_terms( $post_id, $defaults[$taxonomy], $taxonomy );
            }
        }
    }
}

/**
 * Register custom taxonomy: location.
 *
 * Register the custom taxonomy "location" which is associated with shift post type.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_location', 0 );

function wpaesm_register_tax_location() {

	$labels = array(
		'name'                       => _x( 'Locations', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Location', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Locations', 'wpaesm' ),
		'all_items'                  => __( 'All Locations', 'wpaesm' ),
		'parent_item'                => __( 'Parent Location', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Location:', 'wpaesm' ),
		'new_item_name'              => __( 'New Item Location', 'wpaesm' ),
		'add_new_item'               => __( 'Add New Location', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Location', 'wpaesm' ),
		'update_item'                => __( 'Update Location', 'wpaesm' ),
		'view_item'                  => __( 'View Location', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Locations with commas', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Locations', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used', 'wpaesm' ),
		'popular_items'              => __( 'Popular Locations', 'wpaesm' ),
		'search_items'               => __( 'Search Locations', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_location_labels', $labels );

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'location', array( 'shift' ), $args );

}



/**
 * Register custom taxonomy: job category.
 *
 * Register the custom taxonomy "job category" which is associated with job post type.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_job_category', 0 );

function wpaesm_register_tax_job_category() {

    $labels = array(
        'name'                       => _x( 'Job Category', 'Taxonomy General Name', 'wpaesm' ),
        'singular_name'              => _x( 'Job Category', 'Taxonomy Singular Name', 'wpaesm' ),
        'menu_name'                  => __( 'Job Categories', 'wpaesm' ),
        'all_items'                  => __( 'All Job Categories', 'wpaesm' ),
        'parent_item'                => __( 'Parent Job Category', 'wpaesm' ),
        'parent_item_colon'          => __( 'Parent Job Category:', 'wpaesm' ),
        'new_item_name'              => __( 'New Job Category', 'wpaesm' ),
        'add_new_item'               => __( 'Add New Job Category', 'wpaesm' ),
        'edit_item'                  => __( 'Edit Job Category', 'wpaesm' ),
        'update_item'                => __( 'Update Job Category', 'wpaesm' ),
        'separate_items_with_commas' => __( 'Separate Job Categories with commas', 'wpaesm' ),
        'search_items'               => __( 'Search Job Categories', 'wpaesm' ),
        'add_or_remove_items'        => __( 'Add or remove Job Categories', 'wpaesm' ),
        'choose_from_most_used'      => __( 'Choose from the most used Job Categories', 'wpaesm' ),
        'not_found'                  => __( 'Not Found', 'wpaesm' ),
    );

	$labels = apply_filters( 'wpaesm_filter_job_category_labels', $labels );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'show_ui'                    => true,
        'show_admin_column'          => true,
        'show_in_nav_menus'          => true,
        'show_tagcloud'              => true,
    );
    register_taxonomy( 'job_category', array( 'job' ), $args );

}


/**
 * Register custom post type: shift.
 *
 * Register the custom post type, shift, which is the basis for the schedule.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_cpt_shift', 0 );

function wpaesm_register_cpt_shift() {

	$labels = array(
		'name'                => _x( 'Shifts', 'Post Type General Name', 'wpaesm' ),
		'singular_name'       => _x( 'Shift', 'Post Type Singular Name', 'wpaesm' ),
		'menu_name'           => __( 'Shifts', 'wpaesm' ),
		'parent_item_colon'   => __( 'Parent Shift:', 'wpaesm' ),
		'all_items'           => __( 'All Shifts', 'wpaesm' ),
		'view_item'           => __( 'View Shift', 'wpaesm' ),
		'add_new_item'        => __( 'Add New Shift', 'wpaesm' ),
		'add_new'             => __( 'Add New', 'wpaesm' ),
		'edit_item'           => __( 'Edit Shift', 'wpaesm' ),
		'update_item'         => __( 'Update Shift', 'wpaesm' ),
		'search_items'        => __( 'Search Shifts', 'wpaesm' ),
		'not_found'           => __( 'Not found', 'wpaesm' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_shift_labels', $labels );

	$args = array(
		'label'               => __( 'shift', 'wpaesm' ),
		'description'         => __( 'Shifts you can assign to employees', 'wpaesm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', ),
		'taxonomies'          => array( 'shift_type' ),
		'hierarchical'        => true,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-calendar',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);
	register_post_type( 'shift', $args );

}

/**
 * Register custom post type: job.
 *
 * Register the custom post type for jobs, which will be associated with shifts.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_cpt_job', 0 );

function wpaesm_register_cpt_job() {

    $labels = array(
        'name'                => _x( 'Jobs', 'Post Type General Name', 'wpaesm' ),
        'singular_name'       => _x( 'Job', 'Post Type Singular Name', 'wpaesm' ),
        'menu_name'           => __( 'Jobs', 'wpaesm' ),
        'parent_item_colon'   => __( 'Parent Job:', 'wpaesm' ),
        'all_items'           => __( 'All Jobs', 'wpaesm' ),
        'view_item'           => __( 'View Job', 'wpaesm' ),
        'add_new_item'        => __( 'Add New Job', 'wpaesm' ),
        'add_new'             => __( 'Add New', 'wpaesm' ),
        'edit_item'           => __( 'Edit Job', 'wpaesm' ),
        'update_item'         => __( 'Update Job', 'wpaesm' ),
        'search_items'        => __( 'Search Jobs', 'wpaesm' ),
        'not_found'           => __( 'Not found', 'wpaesm' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
    );

	$labels = apply_filters( 'wpaesm_filter_job_labels', $labels );

    $args = array(
        'label'               => __( 'job', 'wpaesm' ),
        'description'         => __( 'jobs', 'wpaesm' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', ),
        'taxonomies'          => array( 'job_category' ),
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 70,
        'menu_icon'           => 'dashicons-hammer',
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => true,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',
    );
    register_post_type( 'job', $args );

}

/**
 * Register custom taxonomy: expense category.
 *
 * Register expense category, which is associated with expense custom post type, and create default categories.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_expense_category', 0 );

function wpaesm_register_tax_expense_category() {

	$labels = array(
		'name'                       => _x( 'Expense Categories', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Expense Category', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Expense Categories', 'wpaesm' ),
		'all_items'                  => __( 'All Expense Categories', 'wpaesm' ),
		'parent_item'                => __( 'Parent Expense Category', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Expense Category:', 'wpaesm' ),
		'new_item_name'              => __( 'New Expense Category', 'wpaesm' ),
		'add_new_item'               => __( 'Add Expense Category', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Expense Category', 'wpaesm' ),
		'update_item'                => __( 'Update Expense Category', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Expense Categories with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Expense Categories', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Expense Categories', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Expense Categories', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_expense_category_labels', $labels );

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'expense_category', array( 'expense' ), $args );

	wp_insert_term(
		'Mileage', // the term 
		'expense_category', // the taxonomy
		array(
			'description'=> 'Mileage to be reimbursed',
			'slug' => 'mileage',
		)
	);

	wp_insert_term(
		'Receipt', // the term 
		'expense_category', // the taxonomy
		array(
			'description'=> 'Receipts to be reimbursed',
			'slug' => 'receipt',
		)
	);

}

/**
 * Register taxonomy: expense status.
 *
 * Register custom taxonomy for expense status, which is associated with expenses, and create default status.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_tax_expense_status', 0 );

function wpaesm_register_tax_expense_status() {

	$labels = array(
		'name'                       => _x( 'Expense Statuses', 'Taxonomy General Name', 'wpaesm' ),
		'singular_name'              => _x( 'Expense Status', 'Taxonomy Singular Name', 'wpaesm' ),
		'menu_name'                  => __( 'Expense Statuses', 'wpaesm' ),
		'all_items'                  => __( 'All Expense Statuses', 'wpaesm' ),
		'parent_item'                => __( 'Parent Expense Status', 'wpaesm' ),
		'parent_item_colon'          => __( 'Parent Expense Status:', 'wpaesm' ),
		'new_item_name'              => __( 'New Expense Status', 'wpaesm' ),
		'add_new_item'               => __( 'Add Expense Status', 'wpaesm' ),
		'edit_item'                  => __( 'Edit Expense Status', 'wpaesm' ),
		'update_item'                => __( 'Update Expense Status', 'wpaesm' ),
		'separate_items_with_commas' => __( 'Separate Expense Statuses with commas', 'wpaesm' ),
		'search_items'               => __( 'Search Expense Statuses', 'wpaesm' ),
		'add_or_remove_items'        => __( 'Add or remove Expense Statuses', 'wpaesm' ),
		'choose_from_most_used'      => __( 'Choose from the most used Expense Statuses', 'wpaesm' ),
		'not_found'                  => __( 'Not Found', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_expense_status_labels', $labels );

	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => false,
		'show_tagcloud'              => false,
	);
	register_taxonomy( 'expense_status', array( 'expense' ), $args );

	wp_insert_term(
		'Reimbursed', // the term 
		'expense_status', // the taxonomy
		array(
			'description'=> 'Expenses for which employee has been reimbursed',
			'slug' => 'reimbursed',
		)
	);

}


/**
 * Register custom post type: expense.
 *
 * Register custom post type for expense.
 *
 * @since 1.0
 *
 */
add_action( 'init', 'wpaesm_register_cpt_expense', 0 );

function wpaesm_register_cpt_expense() {

	$labels = array(
		'name'                => _x( 'Expenses', 'Post Type General Name', 'wpaesm' ),
		'singular_name'       => _x( 'Expense', 'Post Type Singular Name', 'wpaesm' ),
		'menu_name'           => __( 'Expenses', 'wpaesm' ),
		'parent_item_colon'   => __( 'Parent Expense:', 'wpaesm' ),
		'all_items'           => __( 'All Expenses', 'wpaesm' ),
		'view_item'           => __( 'View Expense', 'wpaesm' ),
		'add_new_item'        => __( 'Add New Expense', 'wpaesm' ),
		'add_new'             => __( 'Add New', 'wpaesm' ),
		'edit_item'           => __( 'Edit Expense', 'wpaesm' ),
		'update_item'         => __( 'Update Expense', 'wpaesm' ),
		'search_items'        => __( 'Search Expenses', 'wpaesm' ),
		'not_found'           => __( 'Not found', 'wpaesm' ),
		'not_found_in_trash'  => __( 'Not found in Trash', 'wpaesm' ),
	);

	$labels = apply_filters( 'wpaesm_filter_expense_labels', $labels );

	$args = array(
		'label'               => __( 'expense', 'wpaesm' ),
		'description'         => __( 'Expenses submitted by employees', 'wpaesm' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', ),
		'taxonomies'          => array( 'expense_category' ),
		'hierarchical'        => false,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_nav_menus'   => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => 70,
		'menu_icon'           => 'dashicons-chart-area',
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
		'capability_type'     => 'page',
	);
	register_post_type( 'expense', $args );

}


/**
 * Create employee user role.
 *
 * Employee user role has same privileges as subscriber, but can also view plugin shortcodes and will be included in employee lists.
 *
 * @since 1.0
 *
 */
register_activation_hook( __FILE__, 'wpaesm_create_employee_user_role' );

function wpaesm_create_employee_user_role() {
    add_role( 'employee', 'Employee', array( 'read' => true, 'edit_posts' => false, 'publish_posts' => false ) );
	add_role( 'former-employee', 'Former Employee', array( 'read' => false, 'edit_posts' => false, 'publish_posts' => false ) );
}


/**
 * Get a user's role.
 *
 * Check if a user has a particular role.
 *
 * @since 1.0
 *
 * @link http://docs.appthemes.com/tutorials/wordpress-check-user-role-function/
 *
 * @param string  Name of user role.
 * @param int  ID of user
 * @return bool True if user has role, false if not.
 */

function wpaesm_check_user_role( $role, $user_id = null ) {
 
    if ( is_numeric( $user_id ) )
	$user = get_userdata( $user_id );
    else
        $user = wp_get_current_user();
 
    if ( empty( $user ) )
	return false;
 
    return in_array( $role, (array) $user->roles );
}

/**
 * Create additional user profile fields.
 *
 * Create user profile fields for employee contact information.
 *
 * @since 1.0
 *
 */
add_action( 'show_user_profile', 'wpaesm_employee_profile_fields' );
add_action( 'edit_user_profile', 'wpaesm_employee_profile_fields' );

function wpaesm_employee_profile_fields( $user ) { ?>
	<h3><?php _e("Employee Contact Information", "wpaesm"); ?></h3>

	<table class="form-table">
		<tr>
			<th><label for="address"><?php _e( 'Street Address', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="address" id="address" value="<?php echo esc_attr( get_the_author_meta( 'address', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="city"><?php _e( 'City', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="state"><?php _e( 'State/Province', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="state" id="state" value="<?php echo esc_attr( get_the_author_meta( 'state', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="zip"><?php _e( 'Zip/Postal Code', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="zip" id="zip" value="<?php echo esc_attr( get_the_author_meta( 'zip', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<tr>
			<th><label for="phone"><?php _e( 'Phone Number', 'wpaesm' ); ?></label></th>
			<td>
				<input type="text" name="phone" id="phone" value="<?php echo esc_attr( get_the_author_meta( 'phone', $user->ID ) ); ?>" class="regular-text" /><br />
			</td>
		</tr>
		<?php do_action( 'wpaesm_additional_user_profile_fields', $user ); ?>
	</table>
<?php }

/**
 * Save additional user profile fields.
 *
 * Save user profile fields for employee contact information.
 *
 * @since 1.0
 *
 */
add_action( 'personal_options_update', 'wpaesm_save_employee_profile_fields' );
add_action( 'edit_user_profile_update', 'wpaesm_save_employee_profile_fields' );

function wpaesm_save_employee_profile_fields( $user_id ) {

	if ( !current_user_can( 'edit_user', $user_id ) ) { return false; }

	if( isset( $_POST['address'] ) ) {
		update_user_meta( $user_id, 'address', $_POST['address'] );
	}
	if( isset( $_POST['city'] ) ) {
		update_user_meta( $user_id, 'city', $_POST['city'] );
	}
	if( isset( $_POST['state'] ) ) {
		update_user_meta( $user_id, 'state', $_POST['state'] );
	}
	if( isset( $_POST['zip'] ) ) {
		update_user_meta( $user_id, 'zip', $_POST['zip'] );
	}
	if( isset( $_POST['phone'] ) ) {
		update_user_meta( $user_id, 'phone', $_POST['phone'] );
	}

	do_action( 'wpaesm_save_additional_user_profile_fields', $user_id );
}


/**
 * Set up WP Alchemy.
 *
 * Include WP Alchemy files.
 *
 * @since 1.0
 *
 * @see WPAlchemy_Metabox
 * @link http://www.farinspace.com/wpalchemy-metabox/
 */
if(!class_exists('WPAlchemy_MetaBox')) {
	include_once 'wpalchemy/MetaBox.php';
}

/**
 * Define plugin path for WP Alchemy.
 *
 * @since 1.0
 */
define( 'WPAESM_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Enqueue styles and scripts.
 *
 * Enqueue admin styles and scripts.
 *
 * @since 1.0
 *
 * @global $post  Current post type.
 *
 * @param string  $hook  The page's hook tells us whether this page needs the style/script.
 */
add_action( 'admin_enqueue_scripts', 'wpaesm_add_admin_styles_and_scripts' );

function wpaesm_add_admin_styles_and_scripts( $hook ) {
	wp_enqueue_style( 'wpalchemy-metabox', plugins_url() . '/employee-scheduler/css/meta.css' );
	global $post;  

	if( $hook == 'post-new.php' || $hook == 'post.php' || $hook == 'edit.php' ) {
		if( is_object( $post ) ) {
			if ( 'shift' === $post->post_type || 'expense' === $post->post_type ) { 
			    wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
			    wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );
			    wp_enqueue_script( 'shift-actions', plugin_dir_url(__FILE__) . 'js/shift.js', array( 'jquery' ) );
            	wp_localize_script( 'shift-actions', 'shiftajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) ); 
			}
		}
	}
	if ( 'employee-scheduler_page_payroll-report' == $hook || 'shift_page_add-repeating-shifts' == $hook || 'shift_page_filter-shifts' == $hook || 'expense_page_filter-expenses' == $hook || 'employee-scheduler_page_scheduled-worked' == $hook || 'shift_page_view-schedules' == $hook ) {
		wp_enqueue_script( 'date-time-picker', plugins_url() . '/employee-scheduler/js/jquery.datetimepicker.js', 'jQuery' );
		wp_enqueue_script( 'wpaesm_scripts', plugins_url() . '/employee-scheduler/js/wpaesmscripts.js', 'jQuery' );
	}
	if( 'shift_page_filter-shifts' == $hook || 'employee-scheduler_page_scheduled-worked' == $hook ) {
		wp_enqueue_script( 'stupid-table', plugins_url() . '/employee-scheduler/js/stupidtable.min.js', array( 'jquery' ) );
	}
}

/**
 * Define WP Alchemy metaboxes.
 *
 * @since 1.0
 */
// Create metabox for shifts
$shift_metabox = new WPAlchemy_MetaBox( array
	(
	    'id' => 'shift_meta',
	    'title' => 'Shift Details',
	    'types' => array('shift'),
	    'template' => WPAESM_PATH . '/wpalchemy/shiftinfo.php',
	    'mode' => WPALCHEMY_MODE_EXTRACT,
	    'prefix' => '_wpaesm_'
	)
);

// Create metabox for expenses
$expense_metabox = new WPAlchemy_MetaBox( array
	(
	    'id' => 'expense_meta',
	    'title' => 'Expense Details',
	    'types' => array('expense'),
	    'template' => WPAESM_PATH . '/wpalchemy/expenseinfo.php',
	    'mode' => WPALCHEMY_MODE_EXTRACT,
	    'prefix' => '_wpaesm_'
	)
);

if( function_exists( 'wpaesp_require_employee_scheduler' ) ) {
	$shift_publish_metabox = new WPAlchemy_MetaBox( array
		(
		    'id' => 'shift_publish_meta',
		    'title' => 'Related Shifts',
		    'types' => array('shift'),
		    'template' => WPAESP_PATH . '/shiftpublish.php',
		    'mode' => WPALCHEMY_MODE_EXTRACT,
		    'prefix' => '_wpaesm_',
		    'context' => 'side',
		    'priority' => 'high'
		)
	);
}

/**
 * Add custom fields to taxonomies.
 *
 * Add custom field to shift status so that schedule can display different colors for different statuses.
 *
 * @since 1.0
 *
 * @see Tax_Meta_Class
 * @link http://en.bainternet.info/wordpress-taxonomies-extra-fields-the-easy-way/
 *
 */

//include the main class file
require_once( "Tax-meta-class/Tax-meta-class.php" );

/*
* configure taxonomy custom fields
*/
$status_config = array(
   'id' => 'status_meta_box',
   'title' => 'Shift Status Details',
   'pages' => array( 'shift_status' ),
   'context' => 'normal',
   'fields' => array(),
   'local_images' => false,
   'use_with_theme' => false
);

$status_meta = new Tax_Meta_Class( $status_config );

$status_meta->addColor( 'status_color',array( 'name'=> 'Shift Status Color ' ) );

$status_meta->Finish();

$loc_config = array(
   'id' => 'location_meta_box',
   'title' => 'Location Details',
   'pages' => array( 'location' ),
   'context' => 'normal',
   'fields' => array(),
   'local_images' => false,
   'use_with_theme' => false
);

$loc_meta = new Tax_Meta_Class( $loc_config );

$loc_meta->addTextarea( 'location_address', array( 'name'=> 'Address' ) );

$loc_meta->Finish();



/**
 * Add columns to shift overview page.
 *
 * Change default columns on shift overview page to add columns for date and time.
 *
 * @since 1.0
 *
 * @param array $defaults  Default columns.
 * @return array column list.
 */
function wpaesm_shift_overview_columns_headers( $defaults ) {

    $defaults['shiftdate'] = __( 'Scheduled Date', 'wpaesm' );
    $defaults['shifttime'] = __( 'Scheduled Time', 'wpaesm' );
    return $defaults;

}

/**
 * Populate shift overview columns.
 *
 * Add date and time to shift overview columns.
 *
 * @since 1.0
 *
 * @global object  $shift_metabox.
 *
 * @param string  Column name.
 * @param int  Post ID.
 */
add_filter('manage_shift_posts_columns', 'wpaesm_shift_overview_columns_headers', 10);
add_action('manage_shift_posts_custom_column', 'wpaesm_shift_overview_columns', 10, 2);

function wpaesm_shift_overview_columns( $column_name, $post_ID ) {
	global $shift_metabox;

	$meta = $shift_metabox->the_meta();
    if ($column_name == 'shiftdate' && isset($meta['date'])) {
        echo $meta['date'];
    }    
    if ($column_name == 'shifttime' && isset($meta['starttime']) && isset($meta['endtime'])) {
        echo $meta['starttime'] . "-" . $meta['endtime'];
    }    
}

/**
 * Check for WPP2P.
 *
 * Check whether the WP Posts 2 Posts functionality is already running on the site, and if not, load WPP2P and define constants.
 *
 * @since 1.0
 *
 */
add_action( 'admin_init', 'wpaesm_p2p_check' );

function wpaesm_p2p_check() {
	if ( !is_plugin_active( 'posts-to-posts/posts-to-posts.php' ) ) {
		if ( !class_exists( 'P2P_Autoload' ) ) {
			require_once dirname( __FILE__ ) . '/wpp2p/autoload.php';
		}
		if( !defined( 'P2P_PLUGIN_VERSION') ) {
			define( 'P2P_PLUGIN_VERSION', '1.6.3' );
		}
		if( !defined( 'P2P_TEXTDOMAIN') ) {
			define( 'P2P_TEXTDOMAIN', 'wpaesm' );
		}
	}
}


/**
 * Load P2P.
 *
 * Load and initialize the classes for WP Posts to Posts.
 *
 * @since 1.0
 *
 * @see P2P_Autoload
 * @link https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
 *
 */
function wpaesm_p2p_load() {
	if ( !class_exists( 'P2P_Autoload' ) ) {
		//load_plugin_textdomain( P2P_TEXTDOMAIN, '', basename( dirname( __FILE__ ) ) . '/languages' );
		if ( !function_exists( 'p2p_register_connection_type' ) ) {
			require_once dirname( __FILE__ ) . '/wpp2p/autoload.php';
		}
		P2P_Storage::init();
		P2P_Query_Post::init();
		P2P_Query_User::init();
		P2P_URL_Query::init();
		P2P_Widget::init();
		P2P_Shortcodes::init();
		register_uninstall_hook( __FILE__, array( 'P2P_Storage', 'uninstall' ) );
		if ( is_admin() )
			wpaesm_load_admin();
	}
}

/**
 * Load WPP2P Admin.
 *
 * Load and initialize the classes for WP Posts to Posts.
 *
 * @since 1.0
 *
 * @see P2P_Autoload
 * @link https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
 *
 */
function wpaesm_load_admin() {
	P2P_Autoload::register( 'P2P_', dirname( __FILE__ ) . '/wpp2p/admin' );

	new P2P_Box_Factory;
	new P2P_Column_Factory;
	new P2P_Dropdown_Factory;

	new P2P_Tools_Page;
}

/**
 * Initialize WPP2P.
 *
 * Load and initialize WP Posts to Posts.
 *
 * @since 1.0
 *
 * @see P2P_Autoload
 * @link https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
 *
 */
function wpaesm_p2p_init() {
	// Safe hook for calling p2p_register_connection_type()
	do_action( 'p2p_init' );
}

require dirname( __FILE__ ) . '/wpp2p/scb/load.php';
scb_init( 'wpaesm_p2p_load' );
add_action( 'wp_loaded', 'wpaesm_p2p_init' );

/**
 * Create connections.
 *
 * Use WPP2P to create connections between shifts, jobs, expenses, and employees.
 *
 * @since 1.0
 *
 * @see WPP2P
 * @link https://github.com/scribu/wp-posts-to-posts/blob/master/posts-to-posts.php
 *
 */
add_action( 'p2p_init', 'wpaesm_create_connections' );

function wpaesm_create_connections() {
    // create the connection between shifts and employees (users)
    p2p_register_connection_type( array(
        'name' => 'shifts_to_employees',
        'from' => 'shift',
        'to' => 'user',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
			'singular_name' => __( 'Employee', 'wpaesm' ),
			'search_items' => __( 'Search employees', 'wpaesm' ),
			'not_found' => __( 'No employees found.', 'wpaesm' ),
			'create' => __( 'Add Employee', 'wpaesm' ),
		),
    ) );
    // create the connection between expenses and employees (users)
    p2p_register_connection_type( array(
        'name' => 'expenses_to_employees',
        'from' => 'expense',
        'to' => 'user',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
			'singular_name' => __( 'Employee', 'wpaesm' ),
			'search_items' => __( 'Search employees', 'wpaesm' ),
			'not_found' => __( 'No employees found.', 'wpaesm' ),
			'create' => __( 'Add Employee', 'wpaesm' ),
		),
    ) );
    // create the connection between shifts and jobs
    p2p_register_connection_type( array(
        'name' => 'shifts_to_jobs',
        'from' => 'shift',
        'to' => 'job',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
            'singular_name' => __( 'Job', 'wpaesm' ),
            'search_items' => __( 'Search jobs', 'wpaesm' ),
            'not_found' => __( 'No jobs found.', 'wpaesm' ),
            'create' => __( 'Add Job', 'wpaesm' ),
        ),
    ) );

    // create the connection between expenses and jobs
    p2p_register_connection_type( array(
        'name' => 'expenses_to_jobs',
        'from' => 'expense',
        'to' => 'job',
        'cardinality' => 'many-to-one',
        'admin_column' => 'from',
        'to_labels' => array(
            'singular_name' => __( 'Job', 'wpaesm' ),
            'search_items' => __( 'Search jobs', 'wpaesm' ),
            'not_found' => __( 'No jobs found.', 'wpaesm' ),
            'create' => __( 'Add Job', 'wpaesm' ),
        ),
    ) );
}


/**
 * Find the last priority.
 *
 * The wpaesm_notify_employee function needs to run very last on save, so we need to find what priority will make it run last.
 *
 * @since 1.0
 *
 * @see wpaesm_notify_employee()
 * @link http://wordpress.stackexchange.com/questions/116221/how-to-force-function-to-run-as-the-last-one-when-saving-the-post
 *
 * @param string @filter  
 * @return int Priority that will run last.
 */
add_action( 'save_post', 'wpaesm_run_that_action_last', 0 ); 

function wpaesm_get_latest_priority( $filter ) { 

    if ( empty ( $GLOBALS['wp_filter'][ $filter ] ) )
        return PHP_INT_MAX;

    $priorities = array_keys( $GLOBALS['wp_filter'][ $filter ] );
    $last       = end( $priorities );

    if ( is_numeric( $last ) )
        return PHP_INT_MAX;

    return "$last-z";
}

/**
 * Notify employee that shift has been created/updated.
 *
 * Admin can choose to have an email sent to the employee assigned to a shift when the shift is created or edited.
 *
 * @since 1.0
 *
 * @see wpaesm_send_notification_email()
 * @global shift_metabox WP Alchemy metabox containing shift metadata
 *
 * @param int  $post_id  The ID of the post the employee needs to be notified about.
 */
function wpaesm_notify_employee( $post_id ) {
	if( is_admin() && 'trash' !== get_post_status( $post_id ) ) { // we only need to run this function if we're in the dashboard
		$options = get_option('wpaesm_options'); // get options
		global $shift_metabox; // get metabox data
		$meta = $shift_metabox->the_meta( $post_id ); 
		if( isset( $meta['notify'] ) && "1" == $meta['notify'] ) {  // only send the email if the "notify employee" option is checked 
			// get the employee id
			$users = get_users( array(
				'connected_type' => 'shifts_to_employees',
				'connected_items' => $post_id,
			) );
			foreach($users as $user) {
				$employeeid = $user->ID;
			}
			// get the job 
			$jobs = new WP_Query( array(
				'connected_type' => 'shifts_to_jobs',
				'connected_items' => $post_id,
			) );
			if ( $jobs->have_posts() ) {
				while ( $jobs->have_posts() ) : $jobs->the_post();
					$jobname = get_the_title();
				endwhile;
			} else {
				$jobname = '';
			}
			wp_reset_postdata();
			// get the other meta data
			if( isset( $meta['date'] ) ) {
				$date = $meta['date'];
			} else {
				$date = '';
			}
			if( isset( $meta['starttime'] ) ) {
				$starttime = $meta['starttime'];
			} else {
				$starttime = '';
			}
			if( isset( $meta['endtime'] ) ) {
				$endtime = $meta['endtime'];
			} else {
				$endtime = '';
			}

			// send the email
			if( !isset( $employeeid ) ) {
				$error = __( 'We could not send a notification, because you did not select an employee.  Click your back button and select an employee for this shift, or uncheck the employee notification option.', 'wpaesm' );
				wp_die( $error );
			}
			if( isset( $employeeid ) && isset( $jobname ) ) {
				wpaesm_send_notification_email( $employeeid, $jobname, $date, $starttime, $endtime, '', '', $post_id );
			}
		}
	}
}

/**
 * Send notification after shift is saved.
 *
 * Add the notification action now, with lowest priority so it runs after meta data has been saved.
 *
 * @since 1.0
 *
 * @see wpaesm_get_latest_priority()
 */
function wpaesm_run_that_action_last() {  
    add_action( 
        'save_post', 
        'wpaesm_notify_employee',
        wpaesm_get_latest_priority( current_filter() ),
        2 
    ); 

}

/**
 * Send employee notification.
 *
 * Send the email to the employee when the shift is saved.
 *
 * @since 1.0
 *
 * @see wpaesm_send_email()
 *
 * @param int $employeeid ID of the employee who will receive notification.
 * @param string $clientname Name of the job associated with the shift.
 * @param string $date The date of the shift (Y-m-d).
 * @param string $starttime The time the shift starts.
 * @param string $endtime The time the shift ends.
 * @param string $repeatdays The days of the week when the shift repeats.
 * @param string $repeatuntil The date the shift stops repeating.
 * @param int $postid The ID of the shift.
 */
function wpaesm_send_notification_email( $employeeid, $clientname, $date, $starttime, $endtime, $repeatdays, $repeatuntil, $postid ) {
	$employeeinfo = get_user_by( 'id', $employeeid );
	$employeeemail = $employeeinfo->user_email;	
	$options = get_option('wpaesm_options');

	$to = $employeeemail;

	$subject = $options['notification_subject'];

	$message = '<p>' . __( 'You have been scheduled to work the following shift: ', 'wpaesm' ) . '</p>';
	if( isset( $date ) && '' !== $date ) {
		$message .= '<p><strong>' . __( 'Date: ', 'wpaesm' ) . '</strong>' . $date . '</p>';
	}
	if( isset( $starttime ) && '' !== $starttime && isset( $endtime ) && '' !== $endtime ) {
		$message .= '<p><strong>' . __( 'Time: ', 'wpaesm' ) . '</strong>' . $starttime . " - " . $endtime . '</p>';
	}
	if( isset( $clientname ) && '' !== $clientname ) {
		$message .= '<p><strong>' . __( 'With the client: ', 'wpaesm' ) . '</strong>' . $clientname . '</p>';
	}
	if( isset( $repeatdays ) && '' !== $repeatdays ) {
		$message .= '<p>' . __( 'This shift repeats every ', 'wpaesm' );
		$message .= implode(', ', $repeatdays);
		$message .= __( ' until ', 'wpaesm' );
		$message .= $repeatuntil . '</p>';
	}
	$content = get_post_field( 'post_content', $postid );
	if( isset( $content ) && !empty( $content ) ) {
		$message .= '<strong>' . __( 'Shift Details: ', 'wpaesm' ) . '</strong><br />' . $content;
	}

	$message .= '<p><strong>' . __( 'View this shift online:', 'wpaesm' ) . '&nbsp;<a href="' . get_the_permalink( $postid ) . '">' . get_the_permalink( $postid ) . '</a>';

	$from = $options['notification_from_name'] . "<" . $options['notification_from_email'] . ">";

	wpaesm_send_email( $from, $to, '', $subject, $message );
}

/**
 * Calculate duration between two times.
 *
 * @since 1.7.0
 *
 * @param $start  Time in HH:MM format.
 * @param $end  Time in HH:MM format.
 * @return string  Duration between the two times.
 */
function wpaesm_calculate_duration( $start, $end ) {
	$a = new DateTime( $start );
	$b = new DateTime( $end );
	$sched_duration = $a->diff( $b );
	return $sched_duration->format( "%H:%I" );
}

?>