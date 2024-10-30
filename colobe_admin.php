<?php
if (is_admin()) { ?>
<div class="wrap">
<?php
    $curl_installed             = function_exists('curl_version');
    $curl_ssl_enabled           = cb_check_curl_ssl_support();
    $colobe_library_installed   = cb_check_colobe_library();
    
    
    if (!$curl_installed)
        cb_echo_error_message('cURL (PHP) is not installed, install cURL (PHP) before use Colobe Security plugin.');
    
    if (!$curl_ssl_enabled)
        cb_echo_error_message('cURL does not support SSL, enable SSL support for cURL before use Colobe Security plugin.');
    
    $colobe_php_library_path = get_option('colobe_php_library_path');
    if ($colobe_php_library_path == FALSE) {
        $colobe_php_library_path = '';
    }
	
	if (isset($_FILES['colobe_php_library'])) {
		if (!function_exists('wp_handle_upload'))
			require_once(ABSPATH.'wp-admin/includes/file.php');
		
		$colobe_library = $_FILES['colobe_php_library'];
        
        // check if the file has been uploaded
        if ($colobe_library['error'] != 4) {
            // check file type
            if (strlen($colobe_library['name']) >= 4 && substr($colobe_library['name'], -4, 4) == '.php') {
                add_filter('upload_mimes', 'cb_add_php_mime_upload');

                $upload_overrides = array('test_form' => false);
                $moved_colobe_library = wp_handle_upload($colobe_library, $upload_overrides);

                if (!isset($moved_colobe_library['file'])) {
                    cb_echo_error_message($moved_colobe_library['error']);
                } else {
                    update_option('colobe_php_library_path', $moved_colobe_library['file']);
                }
            } else {
                cb_echo_error_message('The uploaded file is incorrect.');
            }
        }
	}
?> 
    <h1>Colobe Security</h1>
    <h2>First Use</h2>
    <p>
        Go to <a target="_blank" href="https://colobe.net">colobe.net</a> and create a new free account, then add your site to your account and
        download the Colobe Library file and upload it with this control panel.
        <br>
        For more information view <a target="_blank" href="https://colobe.net/?wh=documentation">documentation</a>.
    </p>
    <h2>Settings</h2>
    <form name="colobe_form" method="post" enctype="multipart/form-data" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
		<p>Colobe Library (PHP): <input type="file" name="colobe_php_library">
            <?php
            if (empty($colobe_php_library_path)) {
                cb_echo_label_message('Upload the PHP Colobe Library file', 'error');
            } else {
                cb_echo_label_message('Done', 'success');
            }
            ?>
        </p>
        <br/>
        <p class="submit">  
			<input class="button button-primary" type="submit" name="submit" value="Save Changes" />
        </p>
    </form>
    <h2>Colobe Account</h2>
    <p>
        Click here for view your Colobe Account: <a class="button" target="_blank" href="https://colobe.net">Colobe Account</a>
        <br>
        ( you must be logged in )
    </p>
    <h2>Status</h2>
    <style media="screen" type="text/css">
        table.widefat {
            width: 300px !important;
            margin-top: 10px !important;
        }
        .widefat td {
            padding: 10px 20px;
        }
        .widefat .colobe-status div {
            text-align: center;
        }
    </style>
    <table class="widefat">
           <tr>
               <td>cURL (PHP)</td>
               <td>
                   <?php
                   if ($curl_installed) {
                       echo '<div style="color: #468847;">Installed</div>';
                   } else {
                       echo '<div style="color: #b94a48;">Not Installed</div>';
                   }
                   ?>
               </td>
           </tr>
           <tr>
               <td>cURL SSL support</td>
               <td>
                   <?php
                   if ($curl_ssl_enabled) {
                       echo '<div style="color: #468847;">Enabled</div>';
                   } else {
                       echo '<div style="color: #b94a48;">Not Enabled</div>';
                   }
                   ?>
               </td>
           </tr>
           <tr>
               <td>Colobe Library (PHP)</td>
               <td>
                   <?php
                   if ($colobe_library_installed) {
                       echo '<div style="color: #468847;">Installed</div>';
                   } else {
                       echo '<div style="color: #b94a48;">Not Installed</div>';
                   }
                   ?>
               </td>
           </tr>
           <tr>
               <?php
               if ($curl_installed && $curl_ssl_enabled && $colobe_library_installed) {
                   $style = 'background-color: rgb(223, 240, 216);';
                   $status_mess = '<div style="color: #468847;">Colobe Security is working!</div>';
               } else {
                   $style = 'background-color: rgb(242, 222, 222);';
                   $status_mess = '<div style="color: #b94a48;">Colobe Security is not working!</div>';
               }
               ?>
               <td class="colobe-status" colspan="2" style="<?php echo $style; ?>">
                   <?php echo $status_mess; ?>
               </td>
           </tr>
    </table>
</div>
<?php
}

/** 
 * Prints out a error message.
 * 
 * @param string $message The error message to print out.
 */
function cb_echo_error_message($message) {
	?>
	<div class="colobe-error-message" style="
		border: 1px solid rgba(0, 0, 0,0.2);
		border-radius: 3px;
		padding: 25px;
		padding-left: 40px;
		padding-right: 40px;
		font-size: 15px;
		color: white;
		background-color: rgb(218, 79, 73);
		background-image: linear-gradient(to bottom, rgb(238, 95, 91), rgb(189, 54, 47));
	">
		<div class="message">
			<?php echo $message; ?>
		</div>
	</div>
	<?php
}

/**
 * Prints out a label with a message.
 * 
 * @param string $message The label message..
 * @param string $type The type of label..
 */
function cb_echo_label_message($message, $type) {
    ?>
    <span class="colobe_label" style="
          <?php
          switch ($type) {
              case 'error':
                  echo 'background-color: rgb(185, 74, 72);';
                  break;
              case 'info':
                  echo 'background-color: rgb(248, 148, 6);';
                  break;
              case 'success':
                  echo 'background-color: rgb(70, 136, 71);';
                  break;
              default:
                  echo 'background-color: rgb(153, 153, 153);';
                  break;
          }
          ?>
          border-bottom-left-radius: 3px;
          border-bottom-right-radius: 3px;
          border-collapse: separate;
          border-top-left-radius: 3px;
          border-top-right-radius: 3px;
          color: rgb(255, 255, 255);
          display: inline-block;
          font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
          font-size: 12px;
          font-weight: bold;
          height: 14px;
          line-height: 14px;
          padding-bottom: 2px;
          padding-left: 4px;
          padding-right: 4px;
          padding-top: 2px;
          text-align: left;
          text-shadow: rgba(0, 0, 0, 0.247059) 0px -1px 0px;
          vertical-align: baseline;
          white-space: nowrap;
          width: auto;
    ">
        <?php 
            echo $message;
        ?>
    </span>
    <?php
}

/**
 * Adds php mime type to permitted mime types list for upload.
 * 
 * @param array $mimes The list of permitted mime types (DEFAUTL = empty array).
 * 
 * @return array The new list of permitted mime types.
 */
function cb_add_php_mime_upload($mimes = array()) {
	$mimes['php'] = 'text/php';
	
	return $mimes;
}

/**
 * Checks if cURL supports SSL.
 * 
 * @return bool Returns TRUE if cURL supports SSL else returns FALSE.
 */
function cb_check_curl_ssl_support() {
    if (!function_exists('curl_version'))
        return FALSE;
        
    $curl_version = curl_version();
    if ($curl_version['features'] & constant('CURL_VERSION_SSL')) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Checks if Colobe Library is installed.
 * 
 * @return bool Returns TRUE if the library is installed else returns FALSE.
 */
function cb_check_colobe_library() {
    $colobe_php_library_path = get_option('colobe_php_library_path');
    
    if (!@include_once $colobe_php_library_path)
		return FALSE;
    
    if (!function_exists('colobe_get_client_info'))
        return FALSE;
    
    if (!function_exists('colobe_log_login'))
        return FALSE;
    
    return TRUE;
}
?>