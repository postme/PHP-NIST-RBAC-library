<?php
/**
* NIST Core RBAC
* @package NIST RBAC test framework
* @author M.E. Post <meint@meint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* START CONFIGURATION SECTION
*
* The configuration items below may be changed to suit your specific
* environment needs
*
*/

/**
* Define the url path for the application
*/
defined('PATH') or define('PATH', '/rbac/apps/test');

/**
* Define the url path for the resources
*/
defined('INCLUDE_PATH') or define('INCLUDE_PATH', '/rbac/include');

/**
* Define the language using language code based on BCP 47 + RFC 4644, 
* http://www.rfc-editor.org/rfc/bcp/bcp47.txt
*
* The language files can be found in directory 'lang'
*/
defined('LANGUAGE') or define('LANGUAGE', 'en-us');

/**
* Define the environment in which the script is running
*
* The status should either be DEVELOPMENT or PRODUCTION. Based on the status
* the level of error reporting is determined
*/
defined('STATUS') or define('STATUS', 'DEVELOPMENT');

/**
* The path to the error log file
*/
defined('ERROR_LOG_PATH') or define('ERROR_LOG_PATH', '/var/tmp/php-errors.log');

/**
* Set explicit timezone to facilitate date/time functions
* 
* Timezone may be changed to fit your local situation. An overview of date/time
* zones can be found here: http://us2.php.net/manual/en/timezones.php
*/
defined('TIMEZONE') or define('TIMEZONE','Europe/Amsterdam');

/**
* Define the title for the application
*/
defined('TITLE') or define('TITLE', 'NIST RBAC Test Framework');

/**
* Define the title for the application
*/
defined('SUBTITLE') or define('SUBTITLE', 'An implementation in PHP of the NIST Core RBAC Standard');


/**
* END CONFIGURATION SECTION
*/

?>
