<?php
/**
* NIST Core RBAC
* @package NIST RBAC include functions
* @author M.E. Post <meint@meint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* Processes the various forms
*
* param array $form
*/
function processForm($form=array()) {
	
    /* initialize variables */
    $function_name       = empty($form['function_name']) ? '' : $form['function_name'];
    $success_message     = empty($form['success_message']) ? '' : $form['success_message'];
    $error_message       = empty($form['error_message']) ? '' : $form['error_message'];
    $function_parameters = empty($form['function_parameters']) ? '' : $form['function_parameters'];
    $replace             = empty($form['replace']) ? '' : $form['replace'];

    /* Call the RBAC API with the function name and pass over the
    function arguments */
    $result = call_user_func_array($function_name, $function_parameters);
    /* If it's a TRUE or FALSE then carry out the statusMessage function
    otherwise return the output to the calling function */
    if (is_bool($result)) {
        if ($result === TRUE) {
            return statusMessage(TRUE, str_replace('...', $replace, $success_message));
        } else {
            return statusMessage(FALSE, str_replace('...', $replace, $error_message));
        }
    } else {
        return $result;
    }
}

/**
* Creates the screens for the various functions
*
* param array $page
* param boolean $output_switch
* @return string
*/
function createPage($page=array(), $output_switch=FALSE) {
  
    /* initialize variables */
    $match             = '';
    $status_message    = empty($page['status_message']) ? '' : $page['status_message'];
    $table_caption     = empty($page['table_caption']) ? '' : $page['table_caption'];
    $table_explanation = empty($page['table_explanation']) ? '' : $page['table_explanation'];
    $option            = empty($page['option']) ? '' : $page['option'];
    $label             = empty($page['label']) ? '' : $page['label'];
    $dropdown          = empty($page['dropdown']) ? '' : $page['dropdown'];
    $dropdown_hide     = empty($page['dropdown_hide']) ? '' : $page['dropdown_hide'];
    $table_content     = empty($page['table_content']) ? '' : $page['table_content'];
    $table_sort        = empty($page['table_sort']) ? '' : $page['table_sort'];
    $checkbox          = empty($page['checkbox']) ? '' : $page['checkbox'];
    $radiobutton       = empty($page['radiobutton']) ? '' : $page['radiobutton'];
    $selected          = empty($page['selected']) ? '' : $page['selected'];
    
    /* Switch on output buffering, no output to screen but dump in string */
    ob_start();
    
    /* Print out the status message */
    if ($status_message) {
        print $status_message;
    }
        
    /* Print pretty header */
    if ($table_caption) {
        print_nl('<h1><a href="?action=Menu">Menu</a> &raquo; ' . $table_caption . '</h1>');
    }
    if ($table_explanation) {
        print_nl('<h2>' . $table_explanation . '</h2>');
    }
   
    /* Generate html output for dropdown lists */
    if (is_array($dropdown)) {
    	  $column_headers = array_keys($dropdown[0]);
        $number_of_columns = count($column_headers);
        
    	  print_nl('<label class="resized" for="' . $option . '">' . $label . '</label>');
    	  /* Selects which class=dropdown can get class=strikethrough 
    	  via JQuery manipulation */
    	  if ($dropdown_hide == 1) {
    	      print_nl('<select class="dropdown autosubmit" name="' . $option . '" id="' . $option . '">');	
    	  }
    	  if ($dropdown_hide == 2) {
            print_nl('<select class="dropdown_hide autosubmit" name="' . $option . '" id="' . $option . '">');
        }
        print_nl('<option value="0">' . localize('--- Please Select ---') . '</option>');
        /* The column headers are based on the keys of the array */
        
        foreach ($dropdown as $key => $val) {
            for ($counter = 0; $counter < $number_of_columns; $counter++) {
            	  /* The $option variable contains the name of the column 
            	  that contains the data for the dropdown list */
                if ($column_headers[$counter] == $option) {
                	  /* Make the currently selected dropdown value the 
                	  preselected value */
                    if ($val[$column_headers[$counter]] == $selected) {
                        print_nl('<option value="' . $val[$column_headers[$counter]] . '" selected="selected">');
                    } else {
                        print_nl('<option value="' . $val[$column_headers[$counter]] . '">');
                    }
                }
                $counter++;
                if ($counter < $number_of_columns) {
                    if (!empty($val[$column_headers[$counter]])) {
                        print $val[$column_headers[$counter]];
                    }
                } else {
                    if (!empty($val[$column_headers[$counter-1]])) {
                        print $val[$column_headers[$counter-1]];
                    }
                }
            }
            print_nl('</option>');
        }
        print_nl('</select>');
    } 
    
    /* Generate the output tables with custom javascript sorting */
    if (is_array($table_content)) {
    ?>
        
    	  <table class="stripeMe tablesorter {sortlist: <?php print $table_sort; ?>, cssHeader: 'header'}" <?php if ($table_explanation) print 'summary="' . $table_explanation . '"'; ?>>
          <?php if ($table_caption) print '<caption>' . $table_caption . '</caption>'; ?>
          <thead>
          	<tr>
            <?php
            	$column_headers = array_keys($table_content[0]);
            	$number_of_columns = count($column_headers);
      	      for ($counter = 0; $counter < $number_of_columns; $counter++) {
      	          if ($column_headers[$counter] == $checkbox) {
      	              print_nl('<th scope="col" class="{sorter: false} checkbox"><input type="checkbox" name="select_deselect" id="select_deselect" value="-1"/></th>');	
      	          } elseif ($column_headers[$counter] == $radiobutton) {
      	              print_nl('<th scope="col" class="{sorter: false}">&nbsp;</th>');	
      	          } elseif ($column_headers[$counter] == 'Matched') {
      	              print_nl('<th scope="col" class="hidden">' . str_replace("_", "", $column_headers[$counter]) . '</th>');
      	          } else {
      	              print_nl('<th scope="col">' . str_replace("_", "", $column_headers[$counter]) . '</th>');
      	          }
      	      }
            ?>
            </tr>
          </thead>
          <tbody>
      	  <?php
          foreach ($table_content as $key => $val) {
              print_nl('<tr>');
              for ($counter = 0; $counter < $number_of_columns; $counter++) {
                  if ($column_headers[$counter] == $checkbox) {
                      for ($counter2 = 0; $counter2 < $number_of_columns; $counter2++) {
                          if ($column_headers[$counter2] == 'Matched') {
                              $match = $val[$column_headers[$counter2]];
                          }
                      }
                      /* If the 'Matched' column is present and the current value of $match equals '1'
                      then disable the checkbox and give the table cell class='disabled' */
                      if (!$match) {
                          if (!empty($val[$column_headers[$counter]])) {
                              print_nl('<td class="check"><input type="checkbox" class="check_me" name="' . $checkbox . '[]" value="' . $val[$column_headers[$counter]] . '"/></td>');
                          }
                      } else {
                          print_nl('<td class="check disabled"><input type="checkbox" checked="checked" disabled="disabled" name="' . $checkbox . '[]" value=""/></td>');
                      }
                      $counter++;
                  } 
                  if ($column_headers[$counter] == $radiobutton) {
                      if (!empty($val[$column_headers[$counter]])) {
                          print_nl('<td><input type="radio" name="' . $radiobutton . '" value="' . $val[$column_headers[$counter]] . '"/></td>');
                      }
                      $counter++;
                  }
                  /* If the 'Matched' column is present give it class="hidden" */
                  if ($column_headers[$counter] == 'Matched') {
                      print_nl('<td class="hidden">' . $val[$column_headers[$counter]] . '</td>');
                  } elseif (!empty($val[$column_headers[$counter]])) {
          	          print_nl('<td>' . $val[$column_headers[$counter]] . '</td>');
          	      }
      	      }
          		print_nl('</tr>');
          }
          ?>
          </tbody>
        </table>
    <?php
    }
    /* Dump output in string and clean output buffer */
    $page = ob_get_contents();
    ob_end_clean();
    
    /* if $output_switch=TRUE print the page otherwise return as a variable */
    if ($output_switch) {
        print mergeContentWithTemplate($page);
    } else {
        return $page;
    }
}

/**
* Creates the forms for the various functions
*
* param array $form
* param string $page
* @return string
*/
function createForm($form=array(), $page='') {
	
    /* initialize variables */
    $key = $val = '';
    $form_action  = empty($form['form_action']) ? '' : $form['form_action'];
    $modal_button = empty($form['modal_button']) ? '' : $form['modal_button'];
    $form_content = empty($form['form_content']) ? '' : $form['form_content'];
    $form_buttons = empty($form['form_buttons']) ? FALSE : $form['form_buttons'];
    $form_ajax    = empty($form['form_ajax']) ? FALSE : $form['form_ajax'];
    
    /* Switch on output buffering, no output to screen but dump in string */
    ob_start();
    
    if (is_array($form)) {
        /* No modal window, just a plain form */    
        if (empty($modal_button)) {
            /* Generate the <form> and set the action */
            print_nl('<form enctype="multipart/form-data" id="rbac_form" method="post" action="index.php">');
            print_nl('<div>');
            print_nl('<input type="hidden" name="action" id="action" value="' . $form_action . '" />');
            print_nl('<input type="hidden" name="submitted" id="submitted" value="1" />');
            print_nl('<input type="hidden" name="ajax" id="ajax" value="" />');
            /* Print the tables generated by createPage and make it part of the form */
            print $page;
            /* Generate the <form> closing html code */
            if (empty($modal_button) && ($form_buttons === TRUE)) {
                print_nl('<p><input type="submit" name="submitbutton" id="submitbutton" value="Submit" />');
                print_nl('<input type="reset" name="resetbutton" id="resetbutton" value="Clear" /></p>');
            }
            print_nl('</div>');
            print_nl('</form>');
        }
        /* Generate the code for the modal window */
        if ($modal_button) {
        	  print_nl('<p><button class="modal">' . $modal_button . '</button></p>');
        	  print $page;
        	  print_nl('<div id="dialog" class="hidden">');
        	  /* Generate the <form> and set the action */
            print_nl('<form enctype="multipart/form-data" method="post" action="index.php">');
            print_nl('<div>');
            print_nl('<input type="hidden" name="submitted" id="submitted" value="1" />');
            print_nl('<input type="hidden" name="action" id="action" value="' . $form_action . '" />');
            /* Print the form content */
        	  if (is_array($form_content)) {
                foreach ($form_content as $key => $val) {
                    if ($val["type"] == "text") {
                        print_nl('<label for="' . $key . '">' . $val["label"] . '</label>');
                        print_nl('<input type="text" name="' . $key . '" id="' . $key . '" value="" />');
                    } elseif ($val["type"] == "password") {
                        print_nl('<label for="' . $key . '">' . $val["label"] . '</label>');
                        print_nl('<input type="password" id="' . $key . '" name="' . $key . '" />');
                    } elseif ($val["type"] == "checkbox") {
                        print_nl('<label for="' . $key . '">' . $val["label"] . '</label>');
                        print_nl('<input type="checkbox" id="' . $key . '" name="' . $key . '" />');
            	      } elseif ($val["type"] == "select" || $val["type"] == "multiselect") {
            	          $objects = $val["data"];
                        if (is_array($objects)) {
                            print_nl('<label for="' . $key . '">' . $val["label"] . '</label>');
                            if ($val["type"] == "select") {
                        	      print_nl('<select name="' . $key . '" id="' . $key . '">');
                        	      print_nl('<option value="">--- Please Select ---</option>');
                        	  } elseif ($val["type"] == "multiselect") {
                        	      print_nl('<select name="' . $key . '[]" id="' . $key . '" multiple="multiple" size="5">');
                        	      print_nl('<script type="text/javascript">');
                                print_nl('//<![CDATA[');
                                print_nl('$(document).ready( function() {');
                                print_nl('$("#' . $key . '").multiSelect();');
                                print_nl('});');
                                print_nl('//]]>');
        	                      print_nl('</script>');
                        	  }
                            $column_headers = array_keys($objects[0]);
                            $number_of_columns = count($column_headers);
                            foreach ($objects as $key2 => $val2) {
                                for ($counter = 0; $counter < $number_of_columns; $counter++) {
                                    if ($column_headers[$counter] == $val['option']) {
                                        print_nl('<option value="' . $val2[$column_headers[$counter]] . '">');
                                        $counter++;
                                    }
                                    print_nl($val2[$column_headers[$counter]]);
                                }
                                print_nl('</option>');
                            }
                            print_nl('</select>');
                        }
                    }
                }
            }
            print_nl('<p><input name="submitbutton" id="submitbutton" value="' . localize('Submit') . '" type="submit" />');
            print_nl('<input name="resetbutton" id="resetbutton" value="' . localize('Clear') . '" type="reset" /></p>');
            print_nl('</div>');
            print_nl('</form>');
            print_nl('</div>');
            print_nl('<p><button class="modal">' . $modal_button . '</button></p>');
        }
    }
    
    /* Dump output in string and clean output buffer */
    $page = ob_get_contents();
    ob_end_clean();
    if ($form_ajax) {
        print $page;
    } else {
        print mergeContentWithTemplate($page);
    }
}

/**
* Present the 401 authentication header and verify whether the user has valid
* credentials to logIn
*
* @return boolean
*/
function logIn($url='') {
	  $session_id = $_SESSION['session_id'];
    while (!isAuthenticated()) {
        header('WWW-Authenticate: Basic realm="' . localize("RBAC Web Application") . '-' . $session_id . '"'); 
        header('HTTP/1.1 401 Unauthorized');
        die('Authorization Required');
    } 
    if (!empty($url)) {
        return TRUE;
    } else {
        header("Location: " . PATH . '/' . session_id() . "/?action=Menu", TRUE, 301);	
    }
}	

/**
* Determines whether the user has a valid logIn by checking
* the server environment variables PHP_AUTH_USER and PHP_AUTH_PWD
* If these variables are not set or they don't match a registered user
* in the database the function will return false
*
* @return boolean
*/
function isAuthenticated() {
    /* initialize variables */
    $username = $password = $timestamp = $httpd_username = $httpd_password = 
    $db_password = $sql = $args = $results = '';
    if (isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $httpd_username = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        $httpd_password = filter_var($_SERVER['PHP_AUTH_PW'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        if (!empty($httpd_username) && !empty($httpd_password)) {
            $results = databaseAuthentication($httpd_username);
            if (!empty($results)) {
                list($db_password, $nonce) = explode('+', $results);
                if (verifyBasicAuthentication($db_password, $nonce, $httpd_password)) {
                	  /* A match is found, meaning the user is authenticated. A 
                	  session is created by calling CreateSession(). */
                	  $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
                    return CreateSession($httpd_username, $session);
                }
            }
        }
    }
    return FALSE;
}

/**
* Check the password by hashing the input password and the salt value
*
* @param string $hash
* @param string $salt
* @param string $password
* @return boolean
*/
function verifyBasicAuthentication($hash, $salt, $password) {
    if (hash('sha256', $password . $salt) == $hash) {  	  
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
* Verify the password with the database
*
* @param string $username
* @return string
*/
function databaseAuthentication($username) {
    /* initialize variables */
    $sql = $results = $password = $nonce = '';
    /* retrieve password and nonce for user $username */
    if (!empty($username)) {
        $sql = "SELECT password, nonce FROM user WHERE username = ?";
        $results = QueryEngine($sql, array(&$username), 's', 0);
        if (!empty($results)) {
            $password = $results[0]['password'];
            $nonce = $results[0]['nonce'];
            return $password . '+' . $nonce;
        } 
    }
    return FALSE;
}

/**
* Notify the user access is denied based on access rights deficiency
*
*/
function notAuthorized() {
    $create_page_array = array(
        'table_caption' => 'Unauthorized',
        'table_explanation' => '<span class="alert">You are not authorized to access this function</span>'
    );
    createPage($create_page_array, TRUE);
    exit;
}

/**
* Log out the user by destroying the session
*/
function logOut() {
	  $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    if (DeleteSession(array($session))) {
        session_destroy(); 
        session_unset($_SESSION['session_id']);
	      header("Location: " . PATH . '/', TRUE, 301);	
	  } else {
	      $create_page_array = array(
            'table_caption' => 'logOut failed',
            'table_explanation' => '<span class="alert">Your session could not be deleted</span>'
        );
        createPage($create_page_array, TRUE);
	  }
}

/**
* Log information to file
*
* Retrieve callback information which function called which other function
* so it's easier to pinpoint errors in the program execution. 
*
* @param integer $log_level
* @param string $log_description
*/
function logEvent($log_level=1, $log_description='') {
    /* initialize variables */
	  $call_path = $backtrace = $log_entry = $n = '';
	  /* Backtrace the function call and output the log description */
    $backtrace = debug_backtrace();
    $call_path = $backtrace[$n]['function'];
    $call_path .= ' [Line ' . $backtrace[$n]['line'];
    $call_path .= '; File: ' . $backtrace[$n]['file'] . ']';
    for ($n = 1; $n <= 20; $n++) {
        if (isset($backtrace[$n]['function'])) {
            $call_path .= '<-' . $backtrace[$n]['function'];
        }
    }
    $log_entry = date("Y.m.d H:i:s (l)") . ': ' . $call_path;
    $log_entry .= " " . $log_description . "\n";
    error_log($log_entry, 3, ERROR_LOG_PATH);
}

/**
* Given an error or no-error message based on GrowlMessage
*
* @param boolean $status
* @param string $message
*/
function statusMessage($status=FALSE, $message='') {
    
    /* Switch on output buffering, no output to screen but dump in string */
    ob_start();
    /* If $status=TRUE present the no-error message box, else the error box */
?>
    
    <script type="text/javascript">
    //<![CDATA[
		jQuery(document).ready(function($) {
        $('<div><\/div>').html('<?php print $message ?>')
        .activebar({
        	  'icon': path + '/css/images/activebar-information.png',
            'button': path + '/css/images/activebar-closebtn.png',
            'background': '<?php print $status ? "#0f0" : "#f00"; ?>'
        });
    });
    //]]>
	  </script>
    
<?php	
    $script = ob_get_contents();
    ob_end_clean();
    return $script;
}

/**
* Raise an error that stops script execution
*
* @param string $error
*/
function criticalError($error='') {
	  print_nl('<h1>' . $error . '</h1>');
	  trigger_error($error, E_USER_ERROR);
}

/**
* Pretty prints strings with a newline for HTML
*
* @param string $text
*/
function print_nl($text) {
    print $text . "\n";
}

/**
* Replaces boolean value with a lock icon
*
* param array $lock_array
* @return array
*/
function showLock($lock_array) {
    if (is_array($lock_array)) {
        foreach ($lock_array as $key => &$val) {
            if (multiArrayKeyExists('Locked', $lock_array)) {
                if ($val['Locked'] == 1) {
                    $val['Locked'] = '<div class="locked"><span>1</span></div>';
                } elseif ($val['Locked'] == 0) {
                    $val['Locked'] = '<div class="unlocked"><span>0</span></div>';
                }
            }
        }
    }
    return $lock_array;
}

/**
 * multiArrayKeyExists function
 *
 * @param mixed $needle The key you want to check for
 * @param mixed $haystack The array you want to search
 * @return bool
 */
function multiArrayKeyExists($needle, $haystack) {
    foreach ($haystack as $key => $value) {
        if ($needle == $key) {
            return true;
        }
        if (is_array($value)) {
            if (multiArrayKeyExists($needle, $value) == true ) {
                return true;
            } else {
                continue;
            }
        }
    }
    return false;
} 

/**
* Load the proper language file and return the translated phrase
*
* The language file is JSON encoded and returns an associative array. The
* language snippet itself is used as the key for the translated value.
* Language filename is determined by BCP 47 + RFC 4646
* http://www.rfc-editor.org/rfc/bcp/bcp47.txt
*
* @param string $phrase The phrase that needs to be translated
* @return string The translated phrase
*/
function localize($phrase) {
    /* Static keyword is used to ensure the file is loaded only once */
    static $translations = NULL; 
    /* If no instance of $translations has occured load the language file */
    if (is_null($translations)) { 
        $lang_file =	dirname(__FILE__) . '/../lang/' . LANGUAGE . '.txt';
        if (!file_exists($lang_file)) {
            $lang_file = dirname(__FILE__) . '../lang/' . 'en-us.txt';
        }
        $lang_file_content = file_get_contents($lang_file);
        /* Load the language file as a JSON object and transform it into an
        associative array */
        $translations = json_decode($lang_file_content, true);
    }
    return $translations[$phrase];
}

/**
* Merge the page template with the content
*
* @param string $content
* @return string
*/
function mergeContentWithTemplate($content) {
    /* Static keyword is used to ensure the file is loaded only once */
    static $template = NULL; 
    /* If no instance of $template has occured load the template file */
    if (is_null($template)) {
        $template_file =	dirname(__FILE__) . '/../html/template.html';
        $template_file_content = file_get_contents($template_file);
    }
    mb_regex_encoding('utf-8');
    $pattern = array('{path}', '{includepath}', '{language}', '{title}', '{subtitle}', '{replace_content}');
    $replacement = array(PATH, INCLUDE_PATH, LANGUAGE, TITLE, SUBTITLE, $content);
    $pattern_size = sizeof($pattern);
    for ($i = 0; $i < $pattern_size; $i++) {
        $template_file_content = mb_ereg_replace($pattern[$i], $replacement[$i], $template_file_content);
    } 
    return $template_file_content;
}

/**
* Convert the User Permissions into an array fit for showing as menu items
*
* @param array $permissions
* @return array
*/
function personalisedMenuItems($permissions) {
    /* initialize variables */
    $column_headers = $number_of_columns = $permissions_array = 
    $permission_key = $permission_val = $counter = '';
	  /* Determine depth of array */
    $column_headers = array_keys($permissions[0]);
    $number_of_columns = count($column_headers);
    $permissions_array = array();
    /* Iterate through multi-dimensional associative array */
    foreach ($permissions as $permission_key => $permission_val) {
        for ($counter = 0; $counter < $number_of_columns; $counter++) {
            /* Skip the column name itself */
            $counter++;            
      	    /* Strip the formatting and put the permission names into 
      	    an array */
      	    $permissions_array[] = $permission_val[$column_headers[$counter]];
      	    $counter++;
      	    $counter++;
        }
    }
    return $permissions_array;
}

/**
* Print menu items based on the values of $menu_array
*
* @param array $menu_array
* @param array $permissions_array
*/
function personalisedMenu($menu_array, $permissions_array) {
    /* initialize variables */
    $menu_key = $menu_val = $menu = '';
    ob_start();
    foreach ($menu_array as $menu_key => $menu_val) {
			  if (in_array($menu_key, $permissions_array)) {
		        print '<li><a href="?action=' . $menu_key . '">' . $menu_val . '</a></li>' . "\n";
		    }
	  }
	  $menu = ob_get_contents();
    ob_end_clean();
    return $menu;
}

/**
* Print the screens for the RBAC demo application
*
* @param string $user
* @param string $content
* @return string
*/
function demoScreen($user, $content) {
    $role = AssignedRoles($user);
    if (!empty($role)) {
        $role_description = getCleanedRoles($role);
    } else {
        $role_description = localize('No role');
    }
    /* Switch on output buffering, no output to screen but dump in string */
    ob_start();
    print_nl('<h3>' . localize('Logged in as') . ': ' . $role_description . '</h3>');
    print_nl('<p><a href="?action=logOut" class="non-standard">' . localize('Log Out') . '</a></p>');
    print_nl('<div class="boxes">');
    print_nl('<div id="box1">');
    print_nl($content);
    print_nl('<p><br/><br/><a href="?action=Menu">&raquo;Menu</a></p>');
    print_nl('</div>');
    print_nl('</div>');
    $page = ob_get_contents();
    ob_end_clean();
    return mergeContentWithTemplate($page);   
}

/**
* Loop through the array and retrieve the role(s) information
*
* @param array $roles
* @return string
*/
function getCleanedRoles($roles) {
	  $role = '';
    $column_headers = array_keys($roles[0]);
    $number_of_columns = count($column_headers);
    foreach ($roles as $key => $val) {
        for ($counter = 0; $counter < $number_of_columns; $counter++) {
            if (!empty($val[$column_headers[$counter]])) {
      	        $role .= $val[$column_headers[$counter]] . ', ';
      	    }
  	    }
    }
    return substr($role, 0, count($role) - 3);
}

/**
* Generate a random string sequence with configurable length
*
* @param integer $length
* @return string $random_string
*/
function getRandomString($length=5) {
    $dictionary = 'abcdefghjkmnpqrstwxyz';
    $max = strlen($dictionary) - 1;
    $random_string = '';
    mt_srand((double)microtime() * 1000000);
    while (strlen($random_string) < $length + 1) { 
        $random_string .= $dictionary{mt_rand(0, $max)}; 
    }
    return $random_string; 
}

/**
* Generate a data table
*
* @param string $table_content
* @return string $table
*/
function showTableTest($table_content) {
    ob_start();
    print '<table class="stripeMe tablesorter {sortlist: [[0,0]], cssHeader: \'header\'}">';
    print '<thead>';
    print '<tr>';
   	$column_headers = array_keys($table_content[0]);
    $number_of_columns = count($column_headers);
    for ($counter = 0; $counter < $number_of_columns; $counter++) {
        print '<th scope="col">' . str_replace("_", "", $column_headers[$counter]) . '</th>';
    }
    print '</thead>';
    print '</tr>';
    print '<tbody>';
    foreach ($table_content as $key => $val) {
        print '<tr>';
        for ($counter = 0; $counter < $number_of_columns; $counter++) {
            if (!empty($val[$column_headers[$counter]])) {
          	    print '<td>' . $val[$column_headers[$counter]] . '</td>';
          	}
      	}
        print '</tr>';
    }
    print '</tbody>';
    print '</table>';
    $table = ob_get_contents();
    ob_end_clean();
    return $table;
}

/**
* Setup date/time zone and error reporting
*/
function initializeSettings() {
    /* Error reporting */
    if (STATUS == 'DEVELOPMENT') {
        error_reporting(E_ALL | E_STRICT);
        ini_set('display_errors', 'On');
    } else {
        ini_set('display_errors', 'Off');
        ini_set('log_errors', 'On');
        ini_set('error_log', ERROR_LOG_PATH);
    }
        
    /* Set the date/timezone */
    if (function_exists("date_default_timezone_set")) {
        date_default_timezone_set(TIMEZONE);
    }
    
    /* Get the session stuff working for logIn/logOut */
    ini_set('session.hash_function', '1');
    session_start();
    if (empty($_SESSION['session_id'])) {
        session_regenerate_id();
        $_SESSION['session_id'] = session_id();
        header("Location: " . PATH . '/' . $_SESSION['session_id'] . "/", TRUE, 301);
    }
}

?>
