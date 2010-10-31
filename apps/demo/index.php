<?php
/**
* NIST Core RBAC
* @package NIST RBAC demo app
* @author M.E. Post <meint@meint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/


/**
* Include configuration file
*/
include dirname(__FILE__) . '/configuration.php';

/**
* Include the view functions
*/
include dirname(__FILE__) . '/view.php';

/**
* Include the helper functions
*/
include dirname(__FILE__) . '/../../include/php/include.php';

/**
* Include the NIST Core RBAC API library
*/
include dirname(__FILE__) . '/../../lib/rbac_api.php';

/**
* Set up some start values like session management, date/time and error 
* reporting
*/
initializeSettings();

/* If no action has been set the Homepage will be called */
$url_action = (empty($_REQUEST['action'])) ? 'HomePage' : $_REQUEST['action'];

/* Filter the GET/POST action parameter to allow only alphabetic characters
because this is a main entry point to the program logic and therefore an 
interesting target for URL manipulation. */
if (!ctype_alpha($url_action)) {
	  criticalError(localize('Action string has been tampered with, request terminated'));
}

/* Simple Front End Controller pattern based on URL actions 
First check whether the action is set (it should be because it is filled 
with a default value anyway) */
if (isset($url_action)) {	
	  /* Check whether the function exists in the program code */
    if (is_callable($url_action)) {
    	  /* If the function requested in the action exists call the 
    	  function by the name supplied in the action request */
        call_user_func($url_action);
    } else {
        /* Abort program execution and terminate with an error message */
        criticalError(localize('Function does not exist, request terminated'));
    }
} else {
    /* Abort program execution and terminate with an error message */
    criticalError(localize('Function does not exist, request terminated'));
}

/* End of the Front End Controller code */

?>
