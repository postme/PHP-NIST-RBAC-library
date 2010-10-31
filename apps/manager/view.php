<?php
/**
* NIST Core RBAC
* @package NIST RBAC manager app
* @author M.E. Post <meint@meint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/


/**
* View for AddUser. 
*/
function AddUserView() {
	
    /* initialize variables */
    $submitted = $first_name = $family_name = $email = $username = $password = 
    $session = $error_array = $error_value = $error_message = $status_message = 
    $process_form_array = $sql = $table_content = $create_page_array = 
    $page_content = $create_form_array = '';
              
    /* Filter the external variables */
    $submitted   = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $first_name  = filter_input(INPUT_POST, 'first_name', FILTER_SANITIZE_STRING); 
    $family_name = filter_input(INPUT_POST, 'family_name', FILTER_SANITIZE_STRING); 
    $email       = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL); 
    $username    = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password    = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_URL);
    $session     = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Check the submitted data and call the AddUser function */
    if ($submitted) {
        if (empty($first_name))  { $error_array[] = localize('Please enter a valid first name'); } 
        if (empty($family_name)) { $error_array[] = localize('Please enter a valid family name'); } 
        if (empty($email))       { $error_array[] = localize('Please enter a valid email address'); } 
        if (empty($username))    { $error_array[] = localize('Please enter a valid username'); } 
    	  if (empty($password))    { $error_array[] = localize('Please enter a valid password'); } 
        /* Run through the error array and format the error messages */
    	  if (is_array($error_array)){
            foreach ($error_array as $error_value) {
                $error_message .= $error_value;
            }
            /* Alert the user with the error message */
            $status_message = statusMessage(FALSE, $error_message);
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddUser', 'create_read')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AddUser',
                    'function_parameters' => array($username, $password, $first_name, $family_name, $email),
                    'success_message' => localize('Success, the following user was added: ...'),
                    'error_message' => localize('Duplicate ID found, user ... has not been committed to RBAC users'),
                    'replace' => $username
                ); 
                /* Access allowed, process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddUserView', 'read')) {
        /* Generate an overview of all current users */
        $table_content = AddUserModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add User'),
            'table_explanation' => localize('This command creates a new RBAC user'),
            'table_content' => $table_content,
            'dropdown_hide' => '1',
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddUserView',
            'form_content' => array(
                'first_name' => array('type' => 'text', 'label' => localize('First name')),
                'family_name' => array('type' => 'text', 'label' => localize('Family name')),
                'email' => array('type' => 'text', 'label' => localize('Email')),
                'username' => array('type' => 'text', 'label' => localize('Username')),
                'password' => array('type' => 'password', 'label' => localize('Password'))
            ),
            'form_buttons' => TRUE,
            'modal_button' => localize('Add new RBAC User')
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeleteUser. 
*/
function DeleteUserView() {

    /* initialize variables */
    $submitted = $users = $session = $process_form_array = $sql = 
    $table_content = $create_page_array = $page_content = 
    $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $users     = filter_input(INPUT_POST, 'checkbox', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Delete the user(s) by calling the DeleteUsers function with $users array */  
    if ($submitted) {
        if (empty($users))  {
            $status_message = statusMessage(FALSE, localize('Please select a user to delete'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeleteUser', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeleteUser',
                    'function_parameters' => array($users),
                    'success_message' => localize('User(s) deleted succesfully'),
                    'error_message' => localize('Error occurred whilst deleting user(s)')
                );
                /* Access allowed, process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }
        }
    } 
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteUserView', 'read')) {
        /* Generate the DeleteUser table */
        $table_content = DeleteUserModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete User'),
            'table_explanation' => localize('This command deletes an existing user from the RBAC database'),
            'table_content' => $table_content,
            'dropdown_hide' => '1',
            'checkbox' => 'checkbox',
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeleteUserView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AddRole
*/
function AddRoleView() {
	
    /* initialize variables */
    $submitted = $rolename = $session = $process_form_array = $sql = 
    $table_content = $create_page_array = $page_content = 
    $create_form_array = $status_message = '';
    
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $rolename  = filter_input(INPUT_POST, 'rolename', FILTER_SANITIZE_STRING); 
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
	  /* Add the role(s) by calling the AddRole function with $rolename variable */
    if ($submitted) {
        if (empty($rolename))  { 
            $status_message = statusMessage(FALSE, localize('Please provide a rolename'));
        } else { 
        	  /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddRole', 'create_read')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AddRole',
                    'function_parameters' => array($rolename),
                    'success_message' => localize('Role ... committed to role collection'),
                    'error_message' => localize('Role ... already exists'),
                    'replace' => $rolename
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddRoleView', 'read')) {
        /* Populate the Roles table */
        $table_content = AddRoleModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add Role'),
            'table_explanation' => localize('This command creates a new role'),
            'table_content' => $table_content,
            'dropdown_hide' => '1',
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddRoleView',
            'form_content' => array(
                'rolename' => array('type' => 'text', 'label' => localize('Role name'))
            ),
            'form_buttons' => TRUE,
            'modal_button' => localize('Add new RBAC Role')
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeleteRole
*/
function DeleteRoleView() {
	
    /* initialize variables */
    $submitted = $roles = $session = $sql = $table_content =  
    $process_form_array = $create_page_array = $page_content = 
    $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $roles     = filter_input(INPUT_POST, 'role', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
	  /* Delete the Role */ 
    if ($submitted) {
        if (empty($roles))  {
            $status_message = statusMessage(FALSE, localize('Please select a role to delete'));
        } else { 
        	  /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeleteRole', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeleteRole',
                    'function_parameters' => array($roles),
                    'success_message' => localize('Role(s) deleted succesfully'),
                    'error_message' => localize('Error occurred whilst deleting Role(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    } 
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteRoleView', 'read')) {
        /* Populate the Roles table and edit form */
        $table_content = DeleteRoleModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete Role'),
            'table_explanation' => localize('This command deletes an existing role from the RBAC database'),
            'table_content' => $table_content,
            'dropdown_hide' => '1',
            'checkbox' => 'role',
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeleteRoleView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AssignUser
*
*/
function AssignUserView() {

    /* initialize variables */
    $submitted = $ajax = $user = $roles = $session = $sql = 
    $process_form_array = $dropdown_element = $create_page_array = 
    $page_content = $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax      = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user      = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $roles     = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Assign the role(s) by calling the AssignUser function with $user_id and $roles */
    if ($submitted && empty($ajax)) {
        if (empty($roles) && empty($user))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role and a user to assign the role'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AssignUser', 'create_read')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AssignUser',
                    'function_parameters' => array($user, $roles),
                    'success_message' => localize('User assigned succesfully to Role(s)'),
                    'error_message' => localize('Error occurred whilst assigning Role(s) to user')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    } 
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AssignUserView', 'read')) {
        /* Retrieve list of all usernames and full names */
        $dropdown_element = UserOverviewModel();
        /* If no user_id has been set select the lowest one 
        Could also be done with a subselect but I need the user_id in the table */
        if (empty($user)) {
            $user = LowestUserIdModel();
        }
        /* Get all the roles belonging to user and add the non-selected roles */
        $table_content = AssignUserModel($user);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Assign User'),
            'table_explanation' => localize('This command assigns a user to a role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'checkbox' => 'role_name',
            'label' => 'User',
            'option' => 'username',
            'selected' => $user,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AssignUserView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeassignUser
*/
function DeassignUserView() {
    
    /* initialize variables */
    $submitted = $ajax = $user = $roles = $session = $sql = 
    $process_form_array = $dropdown_element = $create_page_array = 
    $page_content = $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax      = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user      = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $roles     = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Remove the role(s) by calling the DeassignUser function with $user_id and $roles variables */
    if ($submitted && empty($ajax)) {
        if (empty($roles) && empty($user))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role and a user to deassign the role'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeassignUser', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeassignUser',
                    'function_parameters' => array($user, $roles),
                    'success_message' => localize('User deassigned succesfully from Role(s)'),
                    'error_message' => localize('Error occurred whilst deassigning Role(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeassignUserView', 'read')) {
        /* Retrieve list of all usernames and full names */
        $dropdown_element = UserOverviewModel();
        /* If no user has been set select the lowest one 
        Could also be done with a subselect but I need the user in the table */
        if (empty($user)) {
            $user = LowestUserIdModel();
        }
        /* Retrieve list of roles associated with the user */
        $table_content = DeassignUserModel($user);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Deassign User'),
            'table_explanation' => localize('This command deassigns a user to a role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'checkbox' => 'role_name',
            'label' => 'User',
            'option' => 'username',
            'selected' => $user,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeassignUserView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for GrantPermission
* 
*/
function GrantPermissionView() {

    /* initialize variables */
    $submitted = $ajax = $role = $permissions = $session = $sql = 
    $dropdown_element = $process_form_array = $create_page_array = 
    $page_content = $create_form_array = $status_message = '';
    
    /* Filter the external variables */
    $submitted   = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax        = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $role        = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING);
    $permissions = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session     = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Grant permissions to roles via permissions array and role_id */
    if ($submitted && empty($ajax)) {
        if (empty($permissions) && empty($role))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role and permission(s) to grant permission(s)'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'GrantPermission', 'create_read')) {
                while (list($key, $val) = each($permissions)) {
            	      list($object, $operation) = explode("+", $val);
            	      $permission[] = array($object, $operation);
                }
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'GrantPermission',
                    'function_parameters' => array($permission, $role),
                    'success_message' => localize('The permissions where granted succesfully'),
                    'error_message' => localize('An error occurred whilst granting permission(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'GrantPermissionView', 'read')) {
        /* List all roles */
        $dropdown_element = RoleOverviewModel();
        /* If no role has been provided select the first role in the 
        collection */
        if (empty($role)) {
            $role = LowestRoleIdModel();
        }
        /* List permissions including locked state based on role id */
        $table_content = GrantPermissionModel($role);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Grant Permission'),
            'table_explanation' => localize('This command grants a role the permission to perform an operation on an object to a role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'checkbox' => 'permission',
            'label' => localize('Role'),
            'option' => 'role_name',
            'selected' => $role,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'GrantPermissionView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for RevokePermission 
*/
function RevokePermissionView() {
	
    /* initialize variables */
    $submitted = $ajax = $role = $permissions = $session = $table_content = 
    $process_form_array = $object = $operation = $permission = 
    $dropdown_element = $create_page_array = $page_content = 
    $form_builder_set = $status_message = '';
    
    /* Filter the external variables */
    $submitted   = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax        = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $role        = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING);
    $permissions = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session     = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($permissions) && empty($role))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role and permission(s) to revoke permission(s)'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'RevokePermission', 'read_delete')) {
                while (list($key, $val) = each($permissions)) {
            	      list($object, $operation) = explode("+", $val);
            	      $permission[] = array($object, $operation);
                }
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'RevokePermission',
                    'function_parameters' => array($permission, $role),
                    'success_message' => localize('The permissions where revoked succesfully'),
                    'error_message' => localize('An error occurred whilst revoking permission(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'RevokePermissionView', 'read')) {
        /* List all roles */
        $dropdown_element = RoleOverviewModel();
        /* If no role has been provided select the first role in the 
        collection */
        if (empty($role)) {
            $role = LowestRoleIdModel();
        }
        /* List all permissions */
        $table_content = RevokePermissionModel($role);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Revoke Permission'),
            'table_explanation' => localize('This command revokes the permission to perform an operation on an object from the set of permissions assigned to a role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '2',
            'checkbox' => 'permission',
            'label' => localize('Role'),
            'option' => 'role_name',
            'selected' => $role,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $form_builder_set = array(
            'form_action' => 'RevokePermissionView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($form_builder_set, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for CreateSession
*/
function CreateSessionView() {
	
    /* initialize variables */
    $session = $sql = $table_content = $create_page_array = '';

    /* Filter the external variables */
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'CreateSessionView', 'read')) {
        $table_content = CreateSessionModel();
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Create Session'),
            'table_explanation' => localize('This function creates a new session with a given user as owner and an active role set. In the View it only shows the active sessions'),
            'table_content' => $table_content,
            'table_sort' => '[[0,0],[1,0]]'
        );
        createPage($create_page_array, TRUE);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeleteSession
*/
function DeleteSessionView() {

    /* initialize variables */
    $submitted = $ajax = $sessions = $session = $process_form_array = 
    $table_content = $dropdown_element = $create_page_array = 
    $page_content = $create_form_array = $status_message = '';
    
    /* Filter the external variables */
    $submitted   = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax        = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $sessions    = filter_input(INPUT_POST, 'checkbox', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session     = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($sessions))  { 
            $status_message = statusMessage(FALSE, localize('Please select a session to delete'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeleteSession', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeleteSession',
                    'function_parameters' => array($sessions),
                    'success_message' => localize('The sessions were deleted succesfully'),
                    'error_message' => localize('An error occurred whilst deleting session(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }  
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteSessionView', 'read')) {
        $table_content = DeleteSessionModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete Session'),
            'table_explanation' => localize('This function deletes a given session with a given owner user'),
            'table_content' => $table_content,
            'checkbox' => 'checkbox',
            'table_sort' => '[[2,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeleteSessionView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AddActiveRole
*/
function AddActiveRoleView() {

    /* initialize variables */
    $submitted = $ajax = $user = $roles = $session = $sql = 
    $process_form_array = $dropdown_element = $create_page_array = 
    $usersession = $user_session = $page_content = 
    $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted    = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax         = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user_session = filter_input(INPUT_POST, 'user_session', FILTER_SANITIZE_STRING);
    $roles        = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session      = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
           
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($roles))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role to add the role to the current session'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddActiveRole', 'create_read')) {
                /* Split the concatenated string into the username and
                session necessary for the API call */
                list($user, $usersession) = explode("+", $user_session);
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AddActiveRole',
                    'function_parameters' => array($user, $usersession, $roles),
                    'success_message' => localize('The role(s) are succesfully added to the current active session'),
                    'error_message' => localize('An error occurred whilst adding the role(s) to the active session')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }            
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddActiveRoleView', 'read')) {
        /* List all user sessions */
        $dropdown_element = UserSessionOverviewModel();
        /* If $user_session is not filled select the lowest value */
        if (empty($user_session)) {
            $user_session = LowestUserSessionIdModel();
        }
        /* Retrieve the username from the concatenated string to use in the next
        query */
        list($username, $temp) = explode("+", $user_session);
        /* Create a set of all roles that belong to the user and all roles 
        that aren't assigned to the user but still available to add */
        $table_content = AddActiveRoleModel($username);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add Active Role'),
            'table_explanation' => localize('This function adds a role as an active role of a session whose owner is a given user'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'checkbox' => 'role_name',
            'label' => localize('User'),
            'option' => 'user_session',
            'selected' => $user_session,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddActiveRoleView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DropActiveRole
*/
function DropActiveRoleView() {

    /* initialize variables */
    $submitted = $ajax = $user = $roles = $session = $sql = 
    $process_form_array = $dropdown_element = $create_page_array = 
    $usersession = $user_session = $page_content = 
    $create_form_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted    = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax         = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user_session = filter_input(INPUT_POST, 'user_session', FILTER_SANITIZE_STRING);
    $roles        = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session      = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
	  
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($roles))  { 
            $status_message = statusMessage(FALSE, localize('Please select a role to remove the role from the current session'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DropActiveRole', 'read_delete')) {
                /* Split the concatenated string into the username and
                session necessary for the API call */
                list($user, $usersession) = explode("+", $user_session);
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DropActiveRole',
                    'function_parameters' => array($user, $usersession, $roles),
                    'success_message' => localize('The role(s) were succesfully removed from the current active session'),
                    'error_message' => localize('An error occurred whilst removing the role(s) from the active session')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }     
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DropActiveRoleView', 'read')) {
        /* List all active user sessions */
        $dropdown_element = UserSessionOverviewModel();
        /* If $user_session is not filled select the lowest value */
        if (empty($user_session)) {
            $user_session = LowestUserSessionIdModel();
        }
        /* Retrieve the username from the concatenated string to use in the next
        query */
        list($username, $temp) = explode("+", $user_session);
    	  /* List all roles that are part of the current user session */
        $table_content = DropActiveRoleModel($username);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Drop Active Role'),
            'table_explanation' => localize('This function deletes a role from the active role set of a session owned by a given user'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '2',
            'checkbox' => 'role_name',
            'label' => localize('User'),
            'option' => 'user_session',
            'selected' => $user_session,
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DropActiveRoleView',
            'form_buttons' => TRUE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AssignedUsers
*/
function AssignedUsersView() {

    /* initialize variables */
    $ajax = $role = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax    = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $role    = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'AssignedUsersView', 'read') && CheckAccess($session, 'AssignedUsers', 'read')) {
        /* Retrieve list of all usernames and full names */
        $dropdown_element = RoleLocalizedOverviewModel();
        /* If no user_id has been set select the lowest one 
        Could also be done with a subselect but I need the user_id in the table */
        if (empty($role)) {
            $role = LowestRoleIdModel();
        }
        $table_content = AssignedUsers($role);
        if (empty($table_content)) {
            $table_content = array('0' => array('User' => localize('No user')));
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Assigned Users'),
            'table_explanation' => localize('This function returns the set of users assigned to a given role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'Role',
            'option' => 'role_name',
            'selected' => $role,
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AssignedUsersView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AssignedRoles
*/
function AssignedRolesView() {

    /* initialize variables */
    $ajax = $user = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax    = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user    = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'AssignedRolesView', 'read') && CheckAccess($session, 'AssignedRoles', 'read')) {
        /* Retrieve list of all usernames and full names */
        $dropdown_element = UserOverviewModel();
        /* If no user has been set select the lowest one */
        if (empty($user)) {
            $user = LowestUserIdModel();
        }
        /* Call the API and retrieve the list of asssociated users */
        $table_content = AssignedRoles($user);
        /* Notify when no roles are set */
        if (empty($table_content)) {
            $table_content = array('0' => array('Role' => localize('No role')));
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Assigned Roles'),
            'table_explanation' => localize('This function returns the set of roles assigned to a given user'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'User',
            'option' => 'username',
            'selected' => $user,
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AssignedRolesView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for RolePermissions
*/
function RolePermissionsView() {

    /* initialize variables */
    $ajax = $user = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax    = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $role    = filter_input(INPUT_POST, 'role_name', FILTER_SANITIZE_STRING);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'RolePermissionsView', 'read') && CheckAccess($session, 'RolePermissions', 'read')) {
        /* Retrieve list of all roles */
        $dropdown_element = RoleLocalizedOverviewModel();
        /* If no user_id has been set select the lowest one 
        Could also be done with a subselect but I need the user_id in the table */
        if (empty($role)) {
            $role = LowestRoleIdModel();
        }
        /* Call the API and retrieve the list of permissions */
        $table_content = RolePermissions($role);
        /* Notify when no permissions are set */
        if (empty($table_content)) {
            $table_content = array('0' => 
                array(
                    'Permission' => localize('No permission'), 
                    'Object' => localize('No object'), 
                    'Operation' => localize('No operation')
                )
            );
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Role Permissions'),
            'table_explanation' => localize('This function returns the set of permissions granted to a given role'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'Role',
            'option' => 'role_name',
            'selected' => $role,
            'table_sort' => '[[0,0],[1,0],[2,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'RolePermissionsView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for UserPermission
*/
function UserPermissionsView() {

    /* initialize variables */
    $ajax = $user = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax    = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user    = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'UserPermissionsView', 'read') && CheckAccess($session, 'UserPermissions', 'read')) {
        /* Retrieve list of all usernames and full names */
        $dropdown_element = UserOverviewModel();
        /* If no user has been set select the lowest one */
        if (empty($user)) {
            $user = LowestUserIdModel();
        }
        /* Call the API and retrieve the list of asssociated roles/permissions */
        $table_content = UserPermissions($user);
        /* Notify when no roles are set */
        if (empty($table_content)) {
            $table_content = array('0' => array('Role' => localize('No role'), 'Permission' => localize('No permission')));
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('User Permissions'),
            'table_explanation' => localize('This function returns the permissions a given user gets through his/her assigned roles'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'User',
            'option' => 'username',
            'selected' => $user,
            'table_sort' => '[[0,0],[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'UserPermissionsView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for SessionRoles
*/
function SessionRolesView() {
    
    /* initialize variables */
    $ajax = $user_session = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax         = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user_session = filter_input(INPUT_POST, 'user_session', FILTER_SANITIZE_STRING);
    $session      = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'SessionRolesView', 'read') && CheckAccess($session, 'SessionRoles', 'read')) {
        /* Retrieve list of all active session */
        $dropdown_element = SessionRolesModel();
        /* If no user has been set select the lowest one */
        if (empty($user_session)) {
            $user_session = LowestSessionModel();
        }
        /* Call the API and retrieve the list of asssociated roles/permissions */
        $table_content = SessionRoles($user_session);
        /* Notify when no roles are set */
        if (empty($table_content)) {
            $table_content = array('0' => array('Role' => localize('No role')));
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Session Roles'),
            'table_explanation' => localize('This function returns the active roles associated with a session'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'User',
            'option' => 'user_session',
            'selected' => $user_session,
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'SessionRolesView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for SessionPermissions
*/
function SessionPermissionsView() {
    
    /* initialize variables */
    $ajax = $user_session = $session = $table_content = $dropdown_element = 
    $create_page_array = $page_content = $create_form_array = $results = '';
    
    /* Filter the external variables */
    $ajax         = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $user_session = filter_input(INPUT_POST, 'user_session', FILTER_SANITIZE_STRING);
    $session      = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        
    /* Check whether the user is authorised to access the view and the API call */
    if (CheckAccess($session, 'SessionPermissionsView', 'read') && CheckAccess($session, 'SessionPermissions', 'read')) {
        /* Retrieve list of all active session */
        $dropdown_element = $dropdown_element = SessionRolesModel();;
        /* If no user has been set select the lowest one */
        if (empty($user_session)) {
            $user_session = LowestSessionModel();
        }
        /* Call the API and retrieve the list of asssociated roles/permissions */
        $table_content = SessionPermissions($user_session);
        /* Notify when no roles are set */
        if (empty($table_content)) {
            $table_content = array('0' => 
                array(
                    'Permission' => localize('No permission'), 
                    'Object' => localize('No object'), 
                    'Operation' => localize('No operation')
                )
            );
        }
        /* Create table */
        $create_page_array = array(
            'table_caption' => localize('Session Permissions'),
            'table_explanation' => localize('This function returns the permissions of the session, i.e., the permissions assigned to its active roles'),
            'table_content' => $table_content,
            'dropdown' => $dropdown_element,
            'dropdown_hide' => '1',
            'label' => 'User',
            'option' => 'user_session',
            'selected' => $user_session,
            'table_sort' => '[[0,0],[1,0],[2,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'SessionPermissionsView',
            'form_buttons' => FALSE,
            'form_ajax' => $ajax
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
    
    
}

/**
* View for AddPermission
*/
function AddPermissionView() {
	
    /* initialize variables */
    $submitted = $ajax = $permission = $object = $sql = $operation =
    $dropdown_object = $dropdown_operation = $create_page_array = 
    $page_content = $create_form_array = $error_array = $error_value = 
    $session = $operation = $status_message = '';
    
    /* Filter the external variables */
    $submitted  = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax       = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $permission = filter_input(INPUT_POST, 'permission', FILTER_SANITIZE_STRING); 
    $object     = filter_input(INPUT_POST, 'object', FILTER_SANITIZE_STRING); 
    $operation  = filter_input(INPUT_POST, 'operation', FILTER_SANITIZE_STRING); 
    $session    = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($permission)) { $error_array[] = localize('Please enter a permission name'); } 
        if (empty($object))     { $error_array[] = localize('Please select a permission object'); } 
        if (empty($operation))  { $error_array[] = localize('Please select a permission operation'); } 
        /* Run through the error array and display any error messages */
    	  if (is_array($error_array)){
            foreach ($error_array as $error_value) {
                $status_message .= $error_value;
            }
            $status_message = statusMessage(FALSE, $status_message);
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddPermission', 'create_read')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AddPermission',
                    'function_parameters' => array($permission, $object, $operation),
                    'success_message' => localize('Permission committed to RBAC permissions'),
                    'error_message' => localize('An error occurred whilst adding a permission')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
        
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddPermissionView', 'read')) {
        /* Query for filling the table with existing users */
        $table_content = AddPermissionModel();
        /* Query for filling the dropdown list */
        $dropdown_object = ObjectOverviewModel();
        /* Query for filling the dropdown list */
        $dropdown_operation = OperationOverviewModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add Permission'),
            'table_explanation' => localize('This command creates a new RBAC permission'),
            'table_content' => $table_content,
            'table_sort' => '[[0,0],[1,0],[2,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddPermissionView',
            'submitted' => '1',
            'form_content' => array(
                'permission' => array('type' => 'text', 'label' => localize('Permission name')),
                'object' => array('type' => 'select', 'label' => localize('Object'), 'data' => $dropdown_object, 'option' => 'object_name'),
                'operation' => array('type' => 'select', 'label' => localize('Operation'), 'data' => $dropdown_operation, 'option' => 'operation_name'),
            ),
            'form_buttons' => TRUE,
            'modal_button' => localize('Add Permission')
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeletePermission
*/
function DeletePermissionView() {

    /* initialize variables */
    $submitted = $ajax = $permissions = $session = $status_message = $sql = 
    $dropdown_element = $create_page_array = $page_content = 
    $create_form_array = $process_form_array = '';
    
    /* Filter the external variables */
    $submitted   = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax        = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $permissions = filter_input(INPUT_POST, 'permission_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session     = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($permissions)) {
            $status_message = statusMessage(FALSE, localize('Please select a permission to delete'));
        } else { 
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeletePermission', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeletePermission',
                    'function_parameters' => array($permissions),
                    'success_message' => localize('Permission(s) deleted succesfully'),
                    'error_message' => localize('An error occurred whilst deleting permission(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeletePermissionView', 'read')) {
        /* Query for filling the table with existing users */
        $table_content = DeletePermissionModel();
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete Permission'),
            'table_explanation' => localize('This command deletes a RBAC permission'),
            'table_content' => $table_content,
            'checkbox' => 'permission_name',
            'table_sort' => '[[1,0],[2,0],[3,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeletePermissionView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AddObject
*/
function AddObjectView() {

    /* initialize variables */
    $submitted = $ajax = $object = $session = $process_form_array = $object_set =
    $table_content = $create_page_array = $page_content = $create_form_array = 
    $status_message = $locked = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax      = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $object    = filter_input(INPUT_POST, 'object_name', FILTER_SANITIZE_STRING);
    $locked    = filter_input(INPUT_POST, 'object_locked', FILTER_SANITIZE_NUMBER_INT);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($object)) {
            $status_message = statusMessage(FALSE, localize('Please provide an object name to add'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddObject', 'create_read')) {
                /* Create form processing array */
                isset($locked) ? $locked = TRUE : $locked = FALSE; 
                $process_form_array = array(
                    'function_name' => 'AddObject',
                    'function_parameters' => array($object, $locked),
                    'success_message' => localize('Object ... added to object collection'),
                    'error_message' => localize('Object ... already exists'),
                    'replace' => $object
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddObjectView', 'read')) {
        /* List all authorisation objects */
        $object_set = AddObjectModel();
        $table_content = showLock($object_set);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add Object'),
            'table_explanation' => localize('This command creates a new authorisation object'),
            'table_content' => $table_content,
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddObjectView',
            'form_content' => array(
                'object_name' => array('type' => 'text', 'label' => localize('Object name')),
                'object_locked' => array('type' => 'checkbox', 'label' => localize('Locked'))
            ),
            'form_buttons' => TRUE,
            'modal_button' => localize('Add new object')
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeleteObject
*/
function DeleteObjectView() {

    /* initialize variables */
    $submitted = $ajax = $objects = $session = $process_form_array = $sql = 
    $lock_set = $create_page_array = $page_content = $create_form_array = 
    $status_message = '';
	  
    /* Filter the external variables */
    $submitted = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax      = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $objects   = filter_input(INPUT_POST, 'object_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session   = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($objects)) {
            $status_message = statusMessage(FALSE, localize('Please select an object to delete'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeleteObject', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeleteObject',
                    'function_parameters' => array($objects),
                    'success_message' => localize('Object(s) deleted succesfully'),
                    'error_message' => localize('An error occurred whilst deleting Object(s)')
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }   
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteObjectView', 'read')) {
        /* List all authorisation objects */
        $lock_set = DeletObjectModel();
        $table_content = showLock($lock_set);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete Object'),
            'table_explanation' => localize('This command deletes a RBAC object'),
            'table_content' => $table_content,
            'checkbox' => 'object_name',
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeleteObjectView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for AddOperation
*/
function AddOperationView() {
	
    /* initialize variables */
    $submitted = $ajax = $operation = $session = $process_form_array = $flag = 
    $table_content = $create_page_array = $page_content = $create_form_array = 
    $error_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted  = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax       = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $operation  = filter_input(INPUT_POST, 'operation_name', FILTER_SANITIZE_STRING);
    $locked     = filter_input(INPUT_POST, 'operation_locked', FILTER_SANITIZE_NUMBER_INT);
    $new_create = filter_input(INPUT_POST, 'new_create', FILTER_SANITIZE_NUMBER_INT);
    $new_read   = filter_input(INPUT_POST, 'new_read', FILTER_SANITIZE_NUMBER_INT);
    $new_update = filter_input(INPUT_POST, 'new_update', FILTER_SANITIZE_NUMBER_INT);
    $new_delete = filter_input(INPUT_POST, 'new_delete', FILTER_SANITIZE_NUMBER_INT);
    $session    = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        $flag = FALSE;
        if (isset($new_create)) { $flag = TRUE; }
        if (isset($new_read))   { $flag = TRUE; }
        if (isset($new_update)) { $flag = TRUE; }
        if (isset($new_delete)) { $flag = TRUE; }
        if (!$flag) {
            $error_array[] = localize('Please select at least one operation category'); 
        } else {
            isset($new_create) ? $mask = '1'  : $mask = '0';
            isset($new_read)   ? $mask .= '1' : $mask .= '0'; 
            isset($new_update) ? $mask .= '1' : $mask .= '0'; 
            isset($new_delete) ? $mask .= '1' : $mask .= '0'; 
        }
        isset($locked) ? $locked = '1' : $locked = '0'; 
        if (empty($operation)) { 
            $error_array[] = localize('Please provide an operation name to add'); 
        } 
        /* Run through the error array and display any error messages */
    	  if (is_array($error_array)){
            foreach ($error_array as $error_value) {
                $status_message .= $error_value;
            }
    	      $status_message = statusMessage(FALSE, $status_message);
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'AddOperation', 'create_read')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'AddOperation',
                    'function_parameters' => array($operation, $mask, $locked),
                    'success_message' => localize('Operation ... added to operation collection'),
                    'error_message' => localize('Operation ... already exists'),
                    'replace' => $operation
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'AddOperationView', 'read')) {
        /* List all authorisation objects */
        $object_set = AddOperationModel();
        $table_content = showLock($object_set);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Add Operation'),
            'table_explanation' => localize('This command creates a new operation'),
            'table_content' => $table_content,
            'table_sort' => '[[0,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'AddOperationView',
            'form_content' => array(
                'operation_name' => array('type' => 'text', 'label' => localize('Operation name')),
                'operation_locked' => array('type' => 'checkbox', 'label' => localize('Locked')),
                'new_create' => array('type' => 'checkbox', 'label' => localize('Create')),
                'new_read' => array('type' => 'checkbox', 'label' => localize('Read')),
                'new_update' => array('type' => 'checkbox', 'label' => localize('Update')),
                'new_delete' => array('type' => 'checkbox', 'label' => localize('Delete'))
            ),
            'form_buttons' => TRUE,
            'modal_button' => localize('Add new operation')
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* View for DeleteOperation
*/
function DeleteOperationView() {
    
    /* initialize variables */
    $submitted = $ajax = $operation = $session = $process_form_array = $flag = 
    $table_content = $create_page_array = $page_content = $create_form_array = 
    $error_array = $status_message = '';
	  
    /* Filter the external variables */
    $submitted  = filter_input(INPUT_POST, 'submitted', FILTER_VALIDATE_BOOLEAN);
    $ajax       = filter_input(INPUT_POST, 'ajax', FILTER_VALIDATE_BOOLEAN);
    $operations = filter_input(INPUT_POST, 'operation_name', FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $session    = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
    /* Process submitted form */
    if ($submitted && empty($ajax)) {
        if (empty($operations)) {
            $status_message = statusMessage(FALSE, localize('Please select an operation to delete'));
        } else {
            /* Verify whether the user is authorised to call the function */
            if (CheckAccess($session, 'DeleteOperation', 'read_delete')) {
                /* Create form processing array */
                $process_form_array = array(
                    'function_name' => 'DeleteOperation',
                    'function_parameters' => array($operations),
                    'success_message' => localize('Operation ... deleted from operation collection'),
                    'error_message' => localize('An error occurred whilst deleting Operation(s)'),
                    'replace' => $operation
                );
                /* Process the form */
                $status_message = processForm($process_form_array);
            } else {
                notAuthorized();
            }    
        }
    }
    
    /* Check whether the user is authorised to access the view */
    if (CheckAccess($session, 'DeleteOperationView', 'read')) {
        /* List all authorisation objects */
        $object_set = DeleteOperationModel();
        $table_content = showLock($object_set);
        /* Create table */
        $create_page_array = array(
            'status_message' => $status_message,
            'table_caption' => localize('Delete Operation'),
            'table_explanation' => localize('This command deletes an operation'),
            'table_content' => $table_content,
            'checkbox' => 'operation_name',
            'table_sort' => '[[1,0]]'
        );
        $page_content = createPage($create_page_array, FALSE);
        /* Generate the edit form */
        $create_form_array = array(
            'form_action' => 'DeleteOperationView',
            'form_buttons' => TRUE
        );
        createForm($create_form_array, $page_content);
    } else {
    	notAuthorized();
    }
}

/**
* Show homepage with menu options
*
*/
function HomePage() {
    print mergeContentWithTemplate('<h1><a href="?action=logIn" class="non-standard">' . localize('Log In') . '</a></h1>');
}

/**
* Show the personalised menu
*/
function Menu() {
    if (!empty($_SERVER['PHP_AUTH_USER'])) {
        /* Filter external variables */
        $user    = filter_var($_SERVER['PHP_AUTH_USER'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
        $session = filter_var($_SESSION['session_id'], FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    
        /* If the url action is Menu show the personalised menu */
        if (logIn("Menu")) {
            /* Switch on output buffering, no output to screen but dump in string 
            at the end of the function */
            ob_start();
            /* Retrieve the role(s) that have been assigned to the user and show
            this as logIn information */
            $role = getCleanedRoles(SessionRoles($session));
            $role_description = !empty($role) ? $role : localize('No role');
            print_nl('<h3>' . localize('Logged in as') . ': ' . $role_description . '</h3>');
            print_nl('<p><a href="?action=logOut" class="non-standard">' . localize('Log Out') . '</a></p>');
            /* Retrieve the permissions that are associated with the user to
            use them for a personalised menu */
            $permissions_array = personalisedMenuItems(SessionPermissions($session));
            print_nl('<div class="boxes">');
            /* First Box */
            print_nl('<div id="box1">');
            print_nl('<h2>' . localize('Administrative Commands') . '</h2>');
            print_nl('<ul>');
            /* Define the menu items */
            $menu_array = array('AddUserView' => 'Add User', 'DeleteUserView' => 'Delete User', 'AddRoleView' => 'Add Role', 'DeleteRoleView' => 'Delete Role', 
            'AssignUserView' => 'Assign User', 'DeassignUserView' => 'Deassign User', 'GrantPermissionView' => 'Grant Permission', 'RevokePermissionView' => 'Revoke Permission');
            /* Compare the user permissions with all available menu-items and
            show only the items that the user is entitled to */
            print personalisedMenu($menu_array, $permissions_array);
            print_nl('</ul>');
            print_nl('</div>');
            /* Second Box */
            print_nl('<div id="box2">');
            print_nl('<h2>' . localize('System Functions') . '</h2>');
            print_nl('<ul>');
            /* Define the menu items */
            $menu_array = array('CreateSessionView' => 'Create Session', 'DeleteSessionView' => 'Delete Session', 'AddActiveRoleView' => 'Add Active Role', 'DropActiveRoleView' => 'Drop Active Role');
            print personalisedMenu($menu_array, $permissions_array);
            print_nl('</ul>');
            print_nl('</div>');
            /* Third Box */
            print_nl('<div id="box3">');
            print_nl('<h2>' . localize('Review Functions') . '</h2>');
            print_nl('<ul>');
            /* Define the menu items */
            $menu_array = array('AssignedUsersView' => 'Assigned Users', 'AssignedRolesView' => 'Assigned Roles', 'RolePermissionsView' => 'Role Permissions', 
            'UserPermissionsView' => 'User Permissions', 'SessionRolesView' => 'Session Roles', 'SessionPermissionsView' => 'Session Permissions');
            print personalisedMenu($menu_array, $permissions_array);
            print_nl('</ul>');
            print_nl('</div>');
            /* Fourth Box */
            print_nl('<div id="box4">');
            print_nl('<h2>' . localize('Nonstandard Functions') . '</h2>');
            print_nl('<ul>');
            /* Define the menu items */
            $menu_array = array('AddPermissionView' => 'Add Permission', 'DeletePermissionView' => 'Delete Permission', 'AddObjectView' => 'Add Object', 
            'DeleteObjectView' => 'Delete Object', 'AddOperationView' => 'Add Operation', 'DeleteOperationView' => 'Delete Operation');
            print personalisedMenu($menu_array, $permissions_array);
            print_nl('</ul>');
            print_nl('</div>');
            print_nl('</div>');
            /* Dump output in string and clean output buffer */
            $page = ob_get_contents();
            ob_end_clean();
            /* Merge the content with the template */
            print mergeContentWithTemplate($page);
        } else {
            notAuthorized();
        }
    } else {
        criticalError(localize('No valid user context, request terminated'));
    }
}

?>
