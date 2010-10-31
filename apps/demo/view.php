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
* Demo: AddMove 
*/
function AddMove() {

    /* Filter the external variables */
    $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);

    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddMove', 'create_read')) {
        $content = '<h2>Add Move:</h2><p>This is the Add Move area. It is only 
        accessible to users with a role that has the AddMove permission.</p>';
        print demoScreen($user, $content);
    } else {
    	notAuthorized();
    }
}


/**
* Demo: UpdateMove 
*/
function UpdateMove() {

    /* Filter the external variables */
    $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);

    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'UpdateMove', 'read_update')) {
        $content = '<h2>Update Move:</h2><p>This is the Update Move area. It is 
        only accessible to users with a role that has the UpdateMove permission.</p>';
        print demoScreen($user, $content);
    } else {
    	notAuthorized();
    }
}


/**
* Demo: DeleteMove 
*/
function DeleteMove() {

    /* Filter the external variables */
    $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);

    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteMove', 'read_delete')) {
        $content = '<h2>Delete Move:</h2><p>This is the Delete Move area. It is 
        only accessible to users with a role that has the DeleteMove permission.</p>';
        print demoScreen($user, $content);
    } else {
    	notAuthorized();
    }
}


/**
* Demo: ShowMoves
*/
function ShowMoves() {

    /* Filter the external variables */
    $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);

    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'ShowMoves', 'read')) {
        $content = '<h2>Show Moves:</h2><p>This is the Show Moves area. It is 
        only accessible to users with a role that has the ShowMoves permission.</p>';
        print demoScreen($user, $content);
    } else {
    	notAuthorized();
    }
}


/**
* Demo: ShowMove 
*/
function ShowMove() {

    /* Filter the external variables */
    $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);

    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'ShowMove', 'read')) {
        $content = '<h2>Show Move:</h2><p>This is the Show Move area. It is 
        only accessible to users with a role that has the ShowMove permission.</p>';
        print demoScreen($user, $content);
    } else {
    	notAuthorized();
    }
}


/**
* Show homepage with menu options
*
*/
function HomePage() {
    $content = '<h1><a href="?action=logIn" class="non-standard">Log In</a></h1>';
    print mergeContentWithTemplate($content);   
}


/**
* Show personalised menu
*
*/
function Menu() {
    if (logIn("Menu")) {
        $user = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        if (!empty($user) && !empty($session)) {
            $content = '<h2>Moving Services:</h2>';
            $user_permissions = SessionPermissions($session);
            if (!empty($user_permissions)) {
                $permissions_array = personalisedMenuItems($user_permissions);
                $menu_array = array('AddMove' => 'Add a Move', 'UpdateMove' => 'Update a Move', 'DeleteMove' => 'Delete a Move', 'ShowMove' => 'Show individual Move', 'ShowMoves' => 'Show All Moves');
        		    $content .= '<ul>' . personalisedMenu($menu_array, $permissions_array) . '</ul>';
        		} else {
        		    $content .= '<p>No permissions available</p>';
        		}
            print demoScreen($user, $content);
        } 
  } else {
      notAuthorized();
  }
}

?>
