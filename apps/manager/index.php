<?php
/**
* NIST Core RBAC
* @package NIST RBAC manager app
* @author M.E. Post <meintmeint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* Include the configuration file
*/
include dirname(__FILE__) . '/configuration.php';

/**
* Include the view functions
*/
include dirname(__FILE__) . '/view.php';

/**
* Include the model functions
*/
include dirname(__FILE__) . '/model.php';

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

/** 
*
* Front End Controller code
*
* This is the Front End Controller that handles all requests and directs
* traffic to the correct function. Every function request is checked on tampering 
* (only alfabetic characters allowed), verified whether it exists and lastly 
* checked for authorisation. If all aspects are ok the user is forwarded to the 
* requested function.
* 
* The controller loop invokes user authentication by requesting Basic 
* Authentication credentials. If none are present the user is challenged
* with a 401 not authorized header and has to enter credentials. If the
* credentials are ok the Basic Authentication information is part of any
* subsequent request and can be checked with every call. 
* 
* In a production situation please use an SSL encrypted session to make sure 
* your username/password isn't intercepted at the network transport level. 
*/

/* If no action has been set the Homepage will be called */
$url_action = (empty($_REQUEST['action'])) ? 'HomePage' : $_REQUEST['action'];

/* Filter the GET/POST action parameter to allow only alphabetic characters
because this is a main entry point to the program logic and therefore an 
interesting target for URL manipulation */
if (!ctype_alpha($url_action)) {
	  criticalError(localize('Action string has been tampered with, request terminated'));
}

/* Check whether the action is set (it should be because it is filled 
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
