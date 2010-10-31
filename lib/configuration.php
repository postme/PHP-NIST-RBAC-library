<?php
/**
* NIST Core RBAC library
* @package NIST RBAC
* @author M.E. Post <meint@meint.net>
* @version 0.66
* @copyright M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/


/**
*
* START CONFIGURATION SECTION
*
* The configuration items below may be changed to suit your specific
* environment needs. These constants are used in the QueryEngine function
* of rbac_api.php
*/


/**
* Database Server IP address
*/
defined('DATABASE_SERVER') or define('DATABASE_SERVER', '127.0.0.1');

/**
* Database username
*/
defined('DATABASE_USER') or define('DATABASE_USER', 'rbac');

/**
* Database password
*/
defined('DATABASE_PASSWORD') or define('DATABASE_PASSWORD', 'rbac');

/**
* Database name
*/
defined('DATABASE_NAME') or define('DATABASE_NAME', 'rbac');

/**
* Database port
*/
defined('DATABASE_PORT') or define('DATABASE_PORT', '3306');

/**
* Timeout value for inactive session in seconds
* INACTIVE_SESSION_TIMEOUT number of seconds
*/
defined('INACTIVE_SESSION_TIMEOUT') or define('INACTIVE_SESSION_TIMEOUT', '300');

/**
* Timeout value for total duration of session in seconds
* TOTAL_SESSION_TIMEOUT number of seconds
*/
defined('TOTAL_SESSION_TIMEOUT') or define('TOTAL_SESSION_TIMEOUT', '14400');

/**
*
* END CONFIGURATION SECTION
*
*/
?>
