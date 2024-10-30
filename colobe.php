<?php
/* 
Plugin Name: Colobe Security
Plugin URI: http://colobe.net 
Description: Protecting Against Brute Force Attacks. 
Author: Nicola Pesavento
Version: 1.1
Author URI: https://www.facebook.com/nicola.pesavento
*/

/*  Copyright 2013  Nicola Pesavento

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Creates colobe menu.
 */
function create_colobe_menu() {
	add_plugins_page('Colobe Security', 'Colobe Security', 1, 'colobe_security', 'create_colobe_admin_page');
}

/**
 * Creates the page for manage colobe plugin.
 */
function create_colobe_admin_page() {
	include('colobe_admin.php'); 
}

/**
 * Handles the user authentication and protect from brute-force attack the login page.
 * 
 * @param object $user The WP_User() object of the user being edited, or a WP_Error() object if validation has already failed.
 * @param string $password The user's password (encrypted) ( default = NULL ).
 */
function colobe_handle_auth($user, $password = NULL) {
    // get password
    if ($password == NULL) {
        if (isset($_POST['pwd']))
            $password = $_POST['pwd'];
    }
    
	// check if there is a login error
	if (is_wp_error($user))
		return $user;
	
	$colobe_php_library_path = get_option('colobe_php_library_path');
	
	if ($colobe_php_library_path == FALSE)
		return $user;
    
	if (!@include_once $colobe_php_library_path)
		return $user;
	
	if (!function_exists('colobe_get_client_info'))
		return $user;
	
	// check user
	$user_info = colobe_get_client_info();
	
	if ($user_info != 0) {
		colobe_send_login_log($user, $password, FALSE);
        
		return $user;
	} else {
		colobe_send_login_log($user, $password, TRUE);
		
		return  new WP_Error('user_threat', 'At the moment you are considered a threat, check out not to be infected with a malware!');
	}
}

/**
 * Sends a login log to Colobe.
 * 
 * @param WP_User $user The user object.
 * @param string $password The user password (decrypted).
 * @param bool $is_threat Indicates if the user is a threat.
 * 
 * @return bool Returns FALSE in error case, else returns TRUE.
 */
function colobe_send_login_log($user, $password, $is_threat) {
	$colobe_php_library_path = get_option('colobe_php_library_path');
    
	if (!@include_once $colobe_php_library_path)
		return FALSE;
	
	if (!function_exists('colobe_log_login'))
		return FALSE;
	
	if ($colobe_php_library_path == FALSE)
		return FALSE;
	
	if ($is_threat == FALSE) {
		if (wp_check_password($password, $user->user_pass, $user->ID)) {
			$log_value = 1;
		} else {
			$log_value = 0;
		}
	} else {
		$log_value = 2;
	}
	
	if (colobe_log_login($log_value)) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/**
 * Start
 */
add_action('admin_menu', 'create_colobe_menu');

add_filter('wp_authenticate_user', 'colobe_handle_auth');

?>
