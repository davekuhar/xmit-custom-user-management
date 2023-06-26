<?php
/*
Plugin Name:  Xmit Custom User Management
Plugin URI:   http://transmitstudio.com
Description:  A package of user management customizations for EA Online Portal
Version:      20180605
Author:       Dave Kuhar
Author URI:   http://davekuhar.com
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  xbk5
Domain Path:  /languages
*/

/*
	via https://isabelcastillo.com/editor-role-manage-users-wordpress
*/

// Let Editors manage users, and run this only once.
function xmit_editor_manage_users() {

	if ( get_option( 'xmit_add_cap_editor_once' ) != 'done' ) {

		// let editor manage users

		$edit_editor = get_role('editor'); // Get the user role
		$edit_editor->add_cap('edit_users');
		$edit_editor->add_cap('list_users');
		$edit_editor->add_cap('promote_users');
		$edit_editor->add_cap('create_users');
		$edit_editor->add_cap('add_users');
		$edit_editor->add_cap('delete_users');

		update_option( 'xmit_add_cap_editor_once', 'done' );
	}

}
add_action( 'init', 'xmit_editor_manage_users' );


// Prevent Editors from deleting, editing, or creating an administrator
// Only needed because Editors were given right to edit users above

class xmit_User_Caps {

	// Add our filters
	function __construct() {
		add_filter( 'editable_roles', array(&$this, 'editable_roles'));
		add_filter( 'map_meta_cap', array(&$this, 'map_meta_cap'),10,4);
	}
	// Remove 'Administrator' from the list of roles if the current user is not an admin
	function editable_roles( $roles ){
		if( isset( $roles['administrator'] ) && !current_user_can('administrator') ){
			unset( $roles['administrator']);
		}
		return $roles;
	}
	// If someone is trying to edit or delete an
	// admin and that user isn't an admin, don't allow it
	function map_meta_cap( $caps, $cap, $user_id, $args ){
		switch( $cap ){
		case 'edit_user':
		case 'remove_user':
		case 'promote_user':
			if( isset($args[0]) && $args[0] == $user_id )
				break;
			elseif( !isset($args[0]) )
				$caps[] = 'do_not_allow';
				$other = new WP_User( absint($args[0]) );
			if( $other->has_cap( 'administrator' ) ){
				if(!current_user_can('administrator')){
					$caps[] = 'do_not_allow';
				}
			}
			break;
		case 'delete_user':
		case 'delete_users':
			if( !isset($args[0]) )
				break;
			$other = new WP_User( absint($args[0]) );
			if( $other->has_cap( 'administrator' ) ){
				if(!current_user_can('administrator')){
					$caps[] = 'do_not_allow';
				}
			}
			break;
		default:
			break;
		}
		return $caps;
	}

}

$xmit_user_caps = new xmit_User_Caps();

/*
	via http://pluginsreviews.com/remove-personal-options-section/
*/

// Remove fields from Admin profile page
function wordpress_remove_user_personal_options($personal_options) {
	
	// Remove the "Personal Options" title
	$personal_options = preg_replace('#<h2>' . __("Personal Options") . '</h2>#s', '', $personal_options, 1); 
	
	// Remove the "Name" title
	$personal_options = preg_replace('#<h2>' . __("Name") . '</h2>#s', '', $personal_options, 1);
	
	// Remove the "Contact Info" title
	$personal_options = preg_replace('#<h2>' . __("Contact Info") . '</h2>#s', '', $personal_options, 1);
	
	// Remove the "About Yourself" title
	$personal_options = preg_replace('#<h2>' . __("About Yourself") . '</h2>#s', '', $personal_options, 1);
	
	 // Remove the "Visual Editor" field
	$personal_options = preg_replace('#<tr class="user-rich-editing-wrap(.*?)</tr>#s', '', $personal_options, 1);
	
	// Remove the "Keyboard Shortcuts"
	$personal_options = preg_replace('#<tr class="user-comment-shortcuts-wrap(.*?)</tr>#s', '', $personal_options, 1); 

	// Remove the "Display name publicly as" field
	$personal_options = preg_replace('#<tr class="user-display-name-wrap(.*?)</tr>#s', '', $personal_options, 1);
	
	// Remove the "Website" field
	//$personal_options = preg_replace('#<tr class="user-url-wrap(.*?)</tr>#s', '', $personal_options, 1);
	
	// Remove the "Profile Picture" field
	$personal_options = preg_replace('#<tr class="user-profile-picture(.*?)</tr>#s', '', $personal_options, 1);
	
	// Remove the "Biographical Info" field
	$personal_options = preg_replace('#<tr class="user-description-wrap(.*?)</tr>#s', '', $personal_options, 1); 
	return $personal_options;
}
function wordpress_profile_subject_start() {
	ob_start('wordpress_remove_user_personal_options');
}

function wordpress_profile_subject_end() {
	ob_end_flush();
}

// // Hooks.
add_action('admin_head', 'wordpress_profile_subject_start');
add_action('admin_footer', 'wordpress_profile_subject_end');


/*
	via https://nazmulahsan.me/rename-user-roles-wordpress/	
*/

function change_role_name() {
    global $wp_roles;
    if ( ! isset( $wp_roles ) )
        $wp_roles = new WP_Roles();
    //You can use any of the roles "administrator" "editor", "author", "contributor" or "subscriber"...
    $wp_roles->roles['editor']['name'] = 'Project Manager';
    $wp_roles->role_names['editor'] = 'Project Manager';
    $wp_roles->roles['contributor']['name'] = 'Guide';
    $wp_roles->role_names['contributor'] = 'Guide';  
    $wp_roles->roles['subscriber']['name'] = 'Alum';
    $wp_roles->role_names['subscriber'] = 'Alum';  
}
add_action('init', 'change_role_name');



add_action ('admin_head', 'xmit_hide_web_field');

function xmit_hide_web_field() {
	echo '<style>body.user-new-php table.form-table tr:nth-child(5) {display: none;}</style>';
}
