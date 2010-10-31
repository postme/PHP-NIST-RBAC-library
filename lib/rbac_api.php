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
* A note from the author 
*
* If you use my code in your projects please drop me a line, I would love to 
* hear for what purposes you use the software and if you have improvement
* suggestions
*
* Best regards, Meint Post
* meint@meint.net
*/

/**
* The configuration file contains the settings for database connectivity
*/
include dirname(__FILE__) . '/configuration.php';

/**
*
* Administrative Commands for Core RBAC
*
*/

/**
* This command creates a new RBAC user. 
*
* The command is valid only if the new user is not already a member of the
* USERS data set. The USER data set is updated. The new user does not own 
* any session at the time of its creation. 
*
* @param string $user username
* @param string $password password
* @param string $first_name first name
* @param string $family_name family name
* @param string $email email address
* @return boolean
* 
* Example:
* <code>
* <?php
* AddUser('username', 'password', 'first_name', 'family_name', 'email');
* ?>
* </code>
*/
function AddUser($user='', $password='', $first_name='', $family_name='', $email='') {
    /* Filter external variables */
    $user        = filter_var($user, FILTER_SANITIZE_STRING);
    $password    = filter_var($password, FILTER_SANITIZE_URL);
    $first_name  = filter_var($first_name, FILTER_SANITIZE_STRING); 
    $family_name = filter_var($family_name, FILTER_SANITIZE_STRING); 
    $email       = filter_var($email, FILTER_VALIDATE_EMAIL); 
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = $hashed_password = 
    $timestamp = '';
    /* If a username has been supplied check whether the username already exists */
    if (!empty($user) && !empty($password)) {
        $sql = 'SELECT user_id 
        FROM user 
        WHERE username = ?';
        $results = QueryEngine($sql, array(&$user), 's', 0);
        if (!empty($results)) {
            /* User exists, abort the function and return false */
            return FALSE;
        } else {
            /* If the user doesn't exist insert it into the database.
            Create a hashed password based on sha256, the supplied password 
            and the current timestamp (as salt) */
            $timestamp = date('Y-m-d H:i:s');
            $hashed_password = hash('sha256', $password . $timestamp);
            /* This flag determines whether the transaction is committed or 
            rolled back */
    	      $query_success = TRUE; 
            /* Start transaction */
            QueryEngine('', '', '', 1);
            /* Insert into user table the username and hashed password */
            $sql = 'INSERT INTO user (username, password, nonce, first_name, family_name, email) 
            VALUES (?, ?, ?, ?, ?, ?)';
            $results = QueryEngine($sql, array(&$user, &$hashed_password, &$timestamp, &$first_name, &$family_name, &$email), 'ssssss', 0);
            if (!empty($results)) {
                /* Database error, transaction will fail */
                $query_success = FALSE;
            }
            /* Commit or rollback transaction based on the value of $query_success */
            if ($query_success) {
                /* Commit transaction, return true */
                QueryEngine('', '', '', 2);
                return TRUE;
            } else {
                /* Rollback transaction, return false */
                QueryEngine('', '', '', 3); 
                return FALSE;
            }
        }
    }
    return FALSE;
}

/**
* This command deletes an existing user from the RBAC database. 

* The command is valid if and only if the user to be deleted is a member of the USERS data 
* set. The USERS and UA data sets and the assigned_users function are updated.
* The session associated with the deleted user is removed as well.
* This function calls the {@link DeleteSession()} function to remove any open sessions.
*
* @param array $users an array of usernames
* @return boolean
*
* Example:
* <code>
* <?php
* DeleteUser(array('username','username', '...'))
* ?>
* </code>
*/
function DeleteUser($users=array()) {
    /* Filter external variables */
    $users = filter_var($users, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = $user = '';
    /* Loop through the $users array, retrieve all usernames and delete all 
    associated users */
    if (!empty($users)) {
    	/* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Delete all roles that are part of the $roles array */
        while (list ($key, $val) = each($users)) {
            $results = '';
            $user = (string) $val;
        	/* Delete the session that is associated with the user */
        	$sql = 'SELECT session.name FROM session 
            INNER JOIN user USING (user_id)
            WHERE user.username = ?';
            $results = QueryEngine($sql, array(&$user), 's', 0);
            if (!empty($results)) {
        	      $session = (string) $results[0]['name'];
        	      if (!empty($session)) {
        	          DeleteSession(array($session));
        	      }
        	  }
        	  /* Delete the user */
            $sql = 'DELETE FROM user WHERE username = ?';
            $results = QueryEngine($sql, array(&$user), 's', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This command creates a new role. 
* The command is valid if and only if the new 
* role is not already a member of the ROLES data set. The ROLES data set and 
* the functions assigned_users and assigned_permissions are updated. Initially,
* no user or permission is assigned to the new role. 
*
* @param string $role role name
* @return boolean
*
* Example:
* <code>
* <?php
* AddRole('role');
* ?>
* </code>
*/
function AddRole($role='') {
    /* Filter external variables */
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $role_id = '';
    /* Check whether rolename exists */
    if (!empty($role)) {
        $query_success = TRUE;
        /* Select role id based on form supplied role name */
        $sql = 'SELECT role_id 
        FROM role 
        WHERE name = ?';
        $results = QueryEngine($sql, array(&$role), 's', 0);
        if (empty($results)) {
            /* If the rolename doesn't exist insert the role in the database */
            $query_success = TRUE; 
            /* Start transaction */
            QueryEngine('', '', '', 1);
            $sql = 'INSERT INTO role (name) VALUES (?)';
            $results = QueryEngine($sql, array(&$role), 's', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        } else {
            $query_success = FALSE;
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This command deletes an existing role from the RBAC database. 
*
* The command is valid if and only if the role to be deleted is a member of the ROLES data set.
* The session associated with the deleted role is removed as well.
* This function calls the {@link DeleteSession()} function to remove any open sessions.
*
* @param array $roles an array of role names
* @return boolean
*
* Example:
* <code>
* <?php
* DeleteRole(array('role','role','...'));
* ?>
* </code>
*/
function DeleteRole($roles=array()) {
    /* Filter external variables */
    $roles = filter_var($roles, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $role_name = '';
    /* If a role id has been supplied delete the associated role */
    if (!empty($roles)) {
    	/* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Delete all roles that are part of the $roles array */
        while (list ($key, $val) = each($roles)) {
            $results = '';
            $role_name = (string) $val;
            /* Delete the session that is associated with the role */
            $sql = 'SELECT session.name FROM session 
            INNER JOIN session_role USING (session_id)
            INNER JOIN role USING (role_id)
            WHERE role.name = ?';
            $results = QueryEngine($sql, array(&$role_name), 's', 0);
            if (!empty($results)) {
        	      $session  = (string) $results[0]['name'];
        	      if (!empty($session)) {
        	          DeleteSession(array($session));
        	      }
        	  }
        	  /* Delete the role */
            $sql = 'DELETE FROM role WHERE name = ?';
            $results = QueryEngine($sql, array(&$role_name), 's', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }    
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This command assigns a user to a role. 
*
* The command is valid if and only if 
* the user is a member of the USERS data set, the role is a member of the 
* ROLES data set, and the user is not already assigned to the role. The data 
* set UA and the function assigned_users are updated to reflect the assignment. 
*
* @param string $user username
* @param array $roles an array of role names
* @return boolean
*
* Example:
* <code>
* <?php
* AssignUser('username', array('role','role','...'));
* ?>
* </code>
*/
function AssignUser($user='', $roles=array()) {
    /* Filter external variables */
    $user  = filter_var($user, FILTER_SANITIZE_STRING);
    $roles = filter_var($roles, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = $role_name = '';
    /* If user id and role id are filled insert them into user_role table */
    if (!empty($user) && !empty($roles)) {
        /* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through the roles array and insert the role id into the 
        user_role database together with the associated user id */
        while (list($key, $val) = each($roles)) {
            $role_name = (string) $val;
            /* If the combination user id + role id is empty insert it 
            into table user_role else abort */
            $sql = 'SELECT name FROM role 
            WHERE name IN (
              SELECT role.name
              FROM user_role
              INNER JOIN user USING (user_id)
              INNER JOIN role USING (role_id)
              WHERE user.username = ? AND role.name = ?
            )';
            $results = QueryEngine($sql, array(&$user, &$role_name), 'ss', 0);
            if (empty($results)) {
                /* Associate the role with the user */
                $sql = 'INSERT INTO user_role (user_id, role_id) 
                VALUES ((SELECT user_id FROM user WHERE username = ?), (SELECT role_id FROM role WHERE name = ?))';
                $results = QueryEngine($sql, array(&$user, &$role_name), 'ss', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
                /* Insert the new role also into the current user session */
                $sql = 'INSERT INTO session_role (session_id, role_id) 
                VALUES ((SELECT session_id FROM session WHERE user_id = (SELECT user_id FROM user WHERE username = ?)), 
                (SELECT role_id FROM role WHERE name = ?))';
                $results = QueryEngine($sql, array(&$user, &$role_name), 'ss', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            }
        } 
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This command deletes the assignment of the user to the role. 
*
* The command is valid if and only if the user is a member of the USERS data set, the role
* is a member of the ROLES data set, and the user is assigned to the role.
* This function calls the {@link DeleteSession()} function to remove any open sessions.
*
* @param string $user username
* @param array $roles and array of role names
* @return boolean
*
* Example:
* <code>
* <?php
* DeassignUser('username', array('role','role','...'));
* ?>
* </code>
*/
function DeassignUser($user='', $roles=array()) {
    /* Filter external variables */
    $user  = filter_var($user, FILTER_SANITIZE_STRING);
    $roles = filter_var($roles, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $role_name = '';
    /* Iterate through roles array and delete all associated users in user_role */
    if (!empty($user) && !empty($roles)) {
        /* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Delete all roles that are part of the $roles array */
        while (list ($key, $val) = each($roles)) {
            $role_name = (string) $val;
            /* Check whether there is a valid user/role assignment and if so
            delete it */
            $sql = 'DELETE FROM user_role 
            WHERE user_id = (SELECT user_id FROM user WHERE username = ?) 
            AND role_id = (SELECT role_id FROM role WHERE name = ?)';
            $results = QueryEngine($sql, array(&$user, &$role_name), 'ss', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            } else {
                $sql = 'SELECT session.name FROM session 
                INNER JOIN user USING (user_id)
                WHERE user.username = ?';
                $results = QueryEngine($sql, array(&$user), 's', 0);
                if (!empty($results)) {
            	      $session  = (string) $results[0]['name'];
            	      if (!empty($session)) {
            	          DeleteSession(array($session));
            	      }
            	  }
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }    
    return FALSE;
}

/**
* This command grants a role the permission to perform an operation on an
* object to a role. 
*
* The command may be implemented as granting permissions
* to a group corresponding to that role, i.e., setting the access control
* list of the object involved.
* The command is valid if and only if the pair (operation, object) represents 
* a permission, and the role is a member of the ROLES data set. 
*
* @param array $permission an array consisting of a combination of object and operation
* @param string $role role name
* @return boolean
*
* Example:
* <code>
* <?php
* GrantPermission(array(array('object', 'operation')), role);
* ?>
* </code>
*/
function GrantPermission($permission=array(), $role='') {
    /* Filter external variables */
    $permission  = filter_var($permission, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $role        = filter_var($role, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = 
    $object = $operation = '';
    /* If role id and permission id are filled check whether the combination 
    role id + permission id is already registered */
    if (!empty($role) && !empty($permission)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Iterate through all permissions and select the correct 
        permission id */
        while (list($key, $val) = each($permission)) {
            $object = (string) $val[0];
            $operation = (string) $val[1];
            /* Does the permission exist in the role_permission table? */
            $sql = 'SELECT permission_id, role_id 
            FROM role_permission 
            INNER JOIN permission USING (permission_id)
            WHERE role_permission.role_id = (SELECT role_id FROM role WHERE name = ?)
            AND permission_id = (SELECT permission_id FROM permission WHERE 
            object_id = (SELECT object_id FROM object WHERE name = ?) 
            AND operation_id = (SELECT operation_id FROM operation WHERE name = ?))';
            $results = QueryEngine($sql, array(&$role, &$object, &$operation), 'sss', 0);
            if (!empty($results)) {
                /* Combination role + permission already exists */
                $query_success = FALSE;
            } else {
                /* Does the permission exist based on the object and operation
            	combination? */
                $sql = 'SELECT permission_id 
                FROM permission 
                WHERE object_id = (SELECT object_id FROM object WHERE name = ?) 
                AND operation_id = (SELECT operation_id FROM operation WHERE name = ?)';
                $results = QueryEngine($sql, array(&$object, &$operation), 'ss', 0);
                if (!empty($results)) {
                    $permission_id  = (int) $results[0]['permission_id'];
                    /* Insert the role + permission combination into the 
                    role_permission table */
                    $sql = 'INSERT INTO role_permission (role_id, permission_id) 
                    VALUES ((SELECT role_id FROM role WHERE name = ?), ?)';
                    $results = QueryEngine($sql, array(&$role, &$permission_id), 'si', 0);
                    if (!empty($results)) {
                        $query_success = FALSE;
                    }
                } else {
                    $query_success = FALSE;
                }
            }
        } 
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This command revokes the permission to perform an operation on an object from 
* the set of permissions assigned to a role. 
*
* The command is valid if and only if the pair (operation, object) represents 
* a permission, the role is a member of the ROLES data set, and the permission 
* is assigned to that role. 
*
* @param array $permission an array consisting of a combination of object and operation
* @param string $role role name
* @return boolean
*
* Example:
* <code>
* <?php
* RevokePermission(array(array('object', 'operation')), role);
* ?>
* </code>
*/
function RevokePermission($permission=array(), $role='') {
    /* Filter external variables */
    $permission = filter_var($permission, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    $role       = filter_var($role, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $key = $val = $query_success = $permission_id = $role_id = '';
    /* If role id is set delete from role_permission table */
    if (!empty($role) && !empty($permission)) {
        /* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through all submitted permissions */
        while (list($key, $val) = each($permission)) {
            $object = (string) $val[0];
            $operation = (string) $val[1];
            $results = '';
            /* Do a SELECT to check whether the permission exists */
            $sql = 'SELECT role_permission.permission_id, role_permission.role_id
            FROM role_permission
            INNER JOIN permission USING (permission_id)
            WHERE permission.object_id = (SELECT object_id FROM object WHERE name = ?) 
            AND permission.operation_id = (SELECT operation_id FROM operation WHERE name = ?)
            AND role_permission.role_id = (SELECT role_id FROM role WHERE name = ?)';
            $results = QueryEngine($sql, array(&$object, &$operation, &$role), 'sss', 0);
            if (!empty($results)) {
            	  $permission_id = (int) $results[0]['permission_id'];
            	  $role_id = (int) $results[0]['role_id'];
            	  /* If the permission does exist delete it */
                $sql = 'DELETE FROM role_permission WHERE role_id = ? AND permission_id = ?';
                $results = QueryEngine($sql, array(&$role_id, &$permission_id), 'ii', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                } 
            } else {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This function creates a new session with a given user as owner and an active 
* role set.
*
* This function creates a new session with a given user as owner and an active 
* role set. The function is valid if and only if:
* - The user is a member of the USERS data set, and
* - The active role set is a subset of the roles assigned to that user. In a 
*   RBAC implementation, the session's active roles might actually be the groups 
*   that represent those roles.
* This function calls the {@link DeleteSession()} function to remove any expired sessions.
*
* @param string $user username
* @param string $session session identifier
* @return boolean
*
* Example:
* <code>
* <?php
* CreateSession('user', 'session');
* ?>
* </code>
*/
function CreateSession($user='', $session='') {
    /* Filter external variables */
    $user = filter_var($user, FILTER_SANITIZE_STRING);
    $session  = filter_var($session, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $identifier = 
    $current_timestamp = $user_agent =$remote_address = 
    $total_session_time_difference = $inactive_session_time_difference = 
    $user_id = $session_id = $role_id = $session_timestamp = 
    $role_id_collection = '';
    /* Clean up old sessions whose timestamp exceeds the TOTAL_SESSION_TIMEOUT 
    value. This should/could be implemented as a timed job, trigger or stored
    procedure. */
    $current_timestamp = strtotime(date('Y-m-d H:i:s'));
    $total_session_time_difference = $current_timestamp - TOTAL_SESSION_TIMEOUT;
    $sql = 'DELETE FROM session 
    WHERE UNIX_TIMESTAMP(created) <= ? ';
    QueryEngine($sql, array(&$total_session_time_difference), 's', 0);
    /* Check whether an active session exists based on the unique identifier. */
    $sql = 'SELECT session.session_id, session.user_id, session_role.role_id, UNIX_TIMESTAMP(session.created) AS unix_timestamp 
    FROM session 
    INNER JOIN session_role USING (session_id)
    WHERE session.name = ?';
    $results = QueryEngine($sql, array(&$session), 's', 0);
    if (!empty($results)) {
        $session_id = (int) $results[0]['session_id'];
        $user_id = (int) $results[0]['user_id'];
        $role_id = (int) $results[0]['role_id'];
        $session_timestamp = (int) $results[0]['unix_timestamp'];
    } 
    /* If there is no active session get the user id and role ids for the active 
    user */
    if (empty($session_id) && empty($user_id) && empty($role_id)) {
        /* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Get user id and role ids from the user table*/
        $sql = 'SELECT user.user_id, role.role_id 
        FROM user
        INNER JOIN user_role USING (user_id)
        INNER JOIN role USING (role_id)
        WHERE user.username = ?';
        $results = QueryEngine($sql, array(&$user), 's', 0);
        if (is_array($results)) {
            foreach ($results as $key => $val) {
                /* The user_id stays the same so may be overwritten */
                $user_id = (int) $val['user_id'];
                /* Build up an array of all associated role ids */
                $role_id_collection[] = (int) $val['role_id'];  
            }
        } 
        if ($query_success) {
            /* Insert the session based on the supplied session identifier */
            $sql = 'INSERT INTO session (user_id, name, created) VALUES (?, ?, NOW())';
            $results = QueryEngine($sql, array(&$user_id, &$session), 'is', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
            /* Assign the users' roles to the session */
            if (!empty($role_id_collection)) {
                $sql = 'INSERT INTO session_role (role_id, session_id) VALUES (?, LAST_INSERT_ID())';
                foreach($role_id_collection as $role_id) {
                    $results = QueryEngine($sql, array(&$role_id), 'i', 0);
                    if (!empty($results)) {
                        $query_success = FALSE;
                    }
                } 
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    
    /* If there is an active session check whether its still valid. If its valid
    update the timestamp. If its invalid then delete the session and recreate 
    it. */
    } else {
        $query_success = TRUE;
        /* If there is an active session check whether the session is still 
        valid. If so update the timestamp else delete the session and recreate */
        $current_timestamp = strtotime(date('Y-m-d H:i:s'));
        $inactive_session_time_difference = $current_timestamp - $session_timestamp;
        if ($inactive_session_time_difference >= INACTIVE_SESSION_TIMEOUT) {
            DeleteSession(array($session));
            /* Create a new session which gives the opportunity to recheck the 
            users current role and permission set */
            CreateSession($user, $session);
        } else {
            /* Update the sessions timestamp to the current time. */
            $sql = 'UPDATE session SET created = NOW() WHERE name = ?';
            $results = QueryEngine($sql, array(&$session), 's', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        }
        return $query_success;
    }
}

/**
* This function deletes a given session with a given owner user. 
*
* The function is valid if and only if the session identifier is a member 
* of the SESSIONS data set, the user is a member of the USERS data set, 
* and the session is owned by the given user. 
*
* @param array $sessions an array of session identifiers
* @return boolean
*
* Example:
* <code>
* <?php
* DeleteSession(array('session','...'));
* ?>
* </code>
*/
function DeleteSession($sessions) {
    /* Filter external variables */
    $sessions = filter_var($sessions, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $query_success = '';
    /* Delete the session using the supplied session id. All dependant 
    tables are updated automatically through foreign key relationships */
    if (!empty($sessions)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through $sessions array */
        while (list($key, $val) = each($sessions)) {
            $session = (string) $val;
            $query_success = TRUE;
            $sql = 'DELETE FROM session WHERE name = ?';
            $results = QueryEngine($sql, array(&$session), 's', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This function adds a role as an active role of a session whose owner is a 
* given user
*
* This function adds a role as an active role of a session whose owner is a 
* given user. The function is valid if and only if:
* - The user is a member of the USERS data set, and
* - The role is a member of the ROLES data set, and
* - The session identifier is a member of the SESSIONS data set, and
* - The role is assigned to the user, and
* - The session is owned by that user.
*
* @param string $user username
* @param string $session session identifier
* @param array $roles an array of role names
* @return boolean
*
* Example:
* <code>
* <?php
* AddActiveRole('username', 'session', array('role','...'));
* ?>
* </code>
*/
function AddActiveRole($user='', $session='', $roles=array()) {
    /* Filter external variables */
    $user    = filter_var($user, FILTER_SANITIZE_STRING);
    $session = filter_var($session, FILTER_SANITIZE_STRING);
    $roles   = filter_var($roles, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $role_id = $temp_role_id = 
    $role_name = '';
    /* If the form has been submitted both $roles and $session_id are filled */
    if (!empty($user) && !empty($session) && !empty($roles)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Iterate through the $roles array and insert the role together with 
        the session id into the session_role table */
        while (list($key, $val) = each($roles)) {
            $role = (string) $val;
            /* Select the role id based on the submitted role */
            $sql = 'SELECT role_id FROM role WHERE name = ?';
            $results = QueryEngine($sql, array(&$role), 's', 0);
            if (!empty($results)) {
                $role_id = (int) $results[0]['role_id'];
            }
            /* Select the role based on the submitted session and role to 
            verify whether it exists */
            $sql = 'SELECT session_role.role_id
            FROM session_role
            INNER JOIN role USING (role_id)
            INNER JOIN session USING (session_id)
            WHERE session.user_id = (SELECT user_id FROM user WHERE username = ?)
            AND session.session_id = (SELECT session_id FROM session WHERE name = ?)
            AND session_role.role_id = (SELECT role_id FROM role WHERE name = ?)';
            $results = QueryEngine($sql, array(&$user, &$session, &$role), 'sss', 0);
            if (!empty($results)) {
                $temp_role_id = (int) $results[0]['role_id'];
            }
            /* Get the correct session id based on the username */
            $sql = 'SELECT session_id FROM session WHERE user_id = (SELECT user_id FROM user WHERE username = ?)';
            $results = QueryEngine($sql, array(&$user), 's', 0);
            if (!empty($results)) {
                $session_id = (int) $results[0]['session_id'];
            }
            /* If the selected role id and the submitted role id don't match it 
            is a new relationship and it will be inserted in the database */
            if ($temp_role_id <> $role_id) {
                $sql = 'INSERT INTO session_role (role_id, session_id) VALUES (?, ?)';
                $results = QueryEngine($sql, array(&$role_id, &$session_id), 'ii', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            }
        } 
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This function deletes a role from the active role set of a session owned by a 
* given user. 
*
* The function is valid if and only if the user is a member of the 
* USERS data set, the session identifier is a member of the SESSIONS data set, 
* the session is owned by the user, and the role is an active role of that 
* session. 
*
* @param string $user username
* @param string $session session identifier
* @param array $roles an array of role names
* @return boolean
*
* Example:
* <code>
* <?php
* DropActiveRole('username', 'session', array('role','...'));
* ?>
* </code>
*/
function DropActiveRole($user='', $session='', $roles=array()) {
    /* Filter external variables */
    $user    = filter_var($user, FILTER_SANITIZE_STRING);
    $session = filter_var($session, FILTER_SANITIZE_STRING);
    $roles   = filter_var($roles, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $result = $results = 
    $role_id = '';
    /* Iterate through the roles array and delete all entries with a 
    matching session id */
    if (!empty($user) && !empty($session) && !empty($roles)) {
        /* Start transaction */
    	$query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Delete all roles that are part of the $roles array */
        while (list ($key, $val) = each($roles)) {
            $role = (string) $val;
            $sql = 'SELECT session_role.session_id, session_role.role_id
            FROM session_role 
            INNER JOIN session USING (session_id)
            WHERE session.user_id = (SELECT user_id FROM user WHERE username = ?)
            AND session.session_id = (SELECT session_id FROM session WHERE name = ?)
            AND session_role.role_id = (SELECT role_id FROM role WHERE name = ?)';
            $result = QueryEngine($sql, array(&$user, &$session, &$role), 'sss', 0);
            if (!empty($result)) {
                $session_id = (int) $result[0]['session_id'];
                $role_id = (int) $result[0]['role_id'];
                $sql = 'DELETE FROM session_role WHERE session_id = ? AND role_id = ?';
                $results = QueryEngine($sql, array(&$session_id, &$role_id), 'ii', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            } else {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This function returns a Boolean value meaning whether the subject of a given 
* session is allowed or not to perform a given operation on a given object. 
*
* The function is valid if and only if the session identifier is a member of the 
* SESSIONS data set, the object is a member of the OBJS data set, and the 
* operation is a member of the OPS data set. The session’s subject has the 
* permission to perform the operation on that object if and only if that 
* permission is assigned to (at least) one of the session’s active roles. 
*
* @param string $session session identifier
* @param string $object object name
* @param string $operation operation name
* @return boolean
*
* Example:
* <code>
* <?php
* CheckAccess('session', 'object', 'operation');
* ?>
* </code>
*/
function CheckAccess($session='', $object='', $operation='') {
    /* Filter external variables */
    $session   = filter_var($session, FILTER_SANITIZE_STRING);
    $object    = filter_var($object, FILTER_SANITIZE_STRING);
    $operation = filter_var($operation, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = '';
    
    /* REMINDER: make permissions a static array because this is an expensive
    query /*

    /* Determine whether the user has access to the requested function by 
    selecting the permissions associated with the roles that are 
    associated with the users sessions */
    if (!empty($session) && !empty($object) && !empty($operation)) {
        $sql = 'SELECT DISTINCT permission.name 
        FROM permission
        INNER JOIN object USING (object_id)
        INNER JOIN operation USING (operation_id)
        INNER JOIN role_permission USING (permission_id)
        INNER JOIN session_role USING (role_id)
        INNER JOIN session USING (session_id)
        WHERE session.name = ? 
        AND object.name = ? 
        AND operation_id = (SELECT operation_id FROM operation WHERE name = ?)';
        $results = QueryEngine($sql, array(&$session, &$object, &$operation), 'sss', 0);
        if (!empty($results)) {
            foreach ($results as $key => $val) {
                if (array_search($object, $val)) {
                    return TRUE;
                }
            }
        } else {
            return FALSE;
        }
    }
    return FALSE;
}

/**
* This function returns the set of users assigned to a given role. The function
* is valid if and only if the role is a member of the ROLES data set. 
*
* @param string $role role name
* @return array
*
* Example:
* <code>
* <?php
* AssignedUsers('rolename');
* ?>
* </code>
*/
function AssignedUsers($role='') {
    /* Filter input */
    $role = filter_var($role, FILTER_SANITIZE_STRING);
	/* Select all users that are associated with the role */
    $sql = 'SELECT DISTINCT CONCAT(user.family_name, ", ", user.first_name) AS User
    FROM user
    INNER JOIN user_role USING (user_id)
    INNER JOIN role USING (role_id)
    WHERE role.name = ?';
    /* Execute the query and return the result set */
    return QueryEngine($sql, array(&$role), 's', 0);
}

/**
* This function returns the set of roles assigned to a given user. 
* The function is valid if and only if the user is a member of the USERS data set.  
*
* @param string $user username
* @return array
*
* Example:
* <code>
* <?php
* AssignedRoles('username');
* ?>
* </code>
*/
function AssignedRoles($user='') {
    /* Filter input */
	$user = filter_var($user, FILTER_SANITIZE_STRING);
	/* Select all roles that are associated with the user */
    $sql = 'SELECT DISTINCT role.name AS Role
    FROM user
    INNER JOIN user_role USING (user_id)
    INNER JOIN role USING (role_id)
    WHERE user.username = ?';
    /* Execute the query and return the result set */
    return QueryEngine($sql, array(&$user), 's', 0);
}

/**
* This function returns the set of permissions (op, obj) granted to a given 
* role. The function is valid if and only if the role is a member of the 
* ROLES data set.
*
* @param string $role role name
* @return array
*
* Example:
* <code>
* <?php
* RolePermissions('rolename');
* ?>
* </code>
*/ 
function RolePermissions($role='') {
    /* Filter input */
    $role = filter_var($role, FILTER_SANITIZE_STRING);
    /* Select all permissions that are associated with the role */
    $sql = 'SELECT DISTINCT permission.name AS Permission, object.name AS Object, operation.name AS Operation
    FROM permission
    INNER JOIN object USING (object_id)
    INNER JOIN operation USING (operation_id)
    INNER JOIN role_permission USING (permission_id)
    INNER JOIN role USING (role_id)
    WHERE role.name = ?';
    return QueryEngine($sql, array(&$role), 's', 0);
}

/**
* This function returns the permissions a given user gets through his/her 
* assigned roles. The function is valid if and only if the user is a member of 
* the USERS data set.
*
* @param string $user username
* @return array
*
* Example:
* <code>
* <?php
* UserPermissions('username');
* ?>
* </code>
*/
function UserPermissions($user='') {
    /* Filter input */
    $user = filter_var($user, FILTER_SANITIZE_STRING);
    /* Select all permissions that are associated with a given user */
    $sql = 'SELECT DISTINCT role.name AS Role, permission.name AS Permission
    FROM permission
    INNER JOIN role_permission USING (permission_id)
    INNER JOIN role USING (role_id)
    INNER JOIN user_role USING (role_id)
    INNER JOIN user USING (user_id)
    WHERE user.username = ?';
    return QueryEngine($sql, array(&$user), 's', 0);
}

/**
* This function returns the active roles associated with a session. The function
* is valid if and only if the session identifier is a member of the SESSIONS 
* data set. 
*
* @param string $session session identifier
* @return array
*
* Example:
* <code>
* <?php
* SessionRoles('session');
* ?>
* </code>
*/
function SessionRoles($session='') {
    /* Filter input */
    $session = filter_var($session, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    /* Select all roles that are associated with the active session */
    $sql = 'SELECT DISTINCT role.name AS Role
    FROM role
    INNER JOIN session_role USING (role_id)
    INNER JOIN session USING (session_id)
    WHERE session.name = ?';
    return QueryEngine($sql, array(&$session), 's', 0);
}

/**
* This function returns the permissions of the session, i.e., the permissions 
* assigned to its active roles. The function is valid if and only if the session
* identifier is a member of the SESSIONS data set. 
*
* @param string $session session identifier
* @return array
*
* Example:
* <code>
* <?php
* SessionPermissions('session');
* ?>
* </code>
*/
function SessionPermissions($session='') {
    /* Filter input */
    $session = filter_var($session, FILTER_SANITIZE_STRING, FILTER_FLAG_ENCODE_HIGH|FILTER_FLAG_ENCODE_LOW);
    /* Select all permissions that are associated with the active session */
    $sql = 'SELECT DISTINCT permission.name AS Permission, object.name AS Object, operation.name AS Operation
    FROM permission
    INNER JOIN object USING (object_id)
    INNER JOIN operation USING (operation_id)
    INNER JOIN role_permission USING (permission_id)
    INNER JOIN session_role USING (role_id)
    INNER JOIN session USING (session_id)
    WHERE session.name = ?';
    return QueryEngine($sql, array(&$session), 's', 0);
}

/**
*
* START OF NON STANDARD API CALLS
*
* These API calls are not part of the NIST RBAC standard as the standard states
* the following:
* "Creation and Maintenance of Element Sets: The basic element sets in Core 
* RBAC are USERS, ROLES, OPS and OBS. Of these element sets, OPS and OBS are 
* considered predefined by the underlying information system for which RBAC is 
* deployed. For example, a banking system may have predefined transactions 
* (OPS) for savings deposit and others, and predefined data sets (OBS) such as
*  savings files, address files, and other necessary data.  
* 
* For situations in which no predefined element sets for OPS and OBS are
* available I have added 6 additional, non-standard, functions:
* - AddPermission: this will add a permission with create, read, update and delete
*   aspects and the object to which the permission belongs
* - DeletePermission: this will remove a permission
* - AddObject: this will add an object as the basis for a permission
* - DeleteObject: this will delete an object (and it's associated permissions)
* - AddOperation: this will add an operation as the basis for a permission
* - DeleteOperation: this will delete an operation (and it's associated permissions)
*/

/**
* This command creates a new permission. 
*
* @param string $permission permission name
* @param string $object object name
* @param string $operation operation name
* @return boolean
*
* Example:
* <code>
* <?php
* AddPermission('permission', 'object', 'operation');
* ?>
* </code>
*/
function AddPermission($permission='', $object='', $operation='') {
    /* Filter external variables */
    $permission = filter_var($permission, FILTER_SANITIZE_STRING);
    $object     = filter_var($object, FILTER_SANITIZE_STRING);
    $operation  = filter_var($operation, FILTER_SANITIZE_STRING);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $temp_permission_id = '';
    /* If a permission name has been supplied check whether the permission already exists */
    if (!empty($permission) && !empty($object) && !empty($operation)) {
        $query_success = TRUE;
        /* Start transaction */
        QueryEngine('', '', '', 1);
        /* Select the permission id based on the supplied permission name */
        $sql = 'SELECT permission_id 
        FROM permission
        WHERE name = ?';
        $result = QueryEngine($sql, array(&$permission), 's', 0);
        if (!empty($result)) {
            /* If the permission exists break off the operation */
            return FALSE;
        } else {
            /* Retrieve the applicable object id */
            $sql = 'SELECT object_id 
            FROM object 
            WHERE name = ?';
            $result = QueryEngine($sql, array(&$object), 's', 0);
            if (!empty($result)) {
                $object_id = (int) $result[0]['object_id'];
            } else {
                $query_success = FALSE;
            }
            /* Retrieve the applicable operation id */
            $sql = 'SELECT operation_id 
            FROM operation 
            WHERE name = ?';
            $result = QueryEngine($sql, array(&$operation), 's', 0);
            if (!empty($result)) {
                $operation_id = (int) $result[0]['operation_id'];
            } else {
                $query_success = FALSE;
            }
            /* Insert into permission table */
            $sql = 'INSERT INTO permission (name, object_id, operation_id) VALUES (?, ?, ?)';
            $results = QueryEngine($sql, array(&$permission, &$object_id, &$operation_id), 'sii', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }   
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* Delete a permission
*
* @param array $permissions array of permission names
* @return boolean
*
* Example:
* <code>
* <?php
* DeletePermission(array('permission', '...'));
* ?>
* </code>
*/
function DeletePermission($permissions=array()) {
    /* Filter external variables */
    $permissions = filter_var($permissions, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $result = $results = 
    $permission = '';
    /* Loop through the $permissions array, retrieve all permission_id's and 
    delete all associated permissions */
    if (!empty($permissions)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through the permissions */
        while (list ($key, $val) = each ($permissions)) {
            $permission = (string) $val;
            /* Select the permission id */
            $sql = 'SELECT permission_id 
            FROM permission
            WHERE name = ?';
            $result = QueryEngine($sql, array(&$permission), 's', 0);
            if (!empty($result)) {
                $sql = 'DELETE FROM permission WHERE name = ?';
                $results = QueryEngine($sql, array(&$permission), 's', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            } else {
                return FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* Add an Object to the database that is the subject of a permission
*
* @param string $object object name
* @param boolean $locked is the object locked or not
* @return boolean
*
* Example:
* <code>
* <?php
* AddObject('object', '0 or 1');
* ?>
* </code>
*/
function AddObject($object='', $locked=0) {
    /* Filter external variables */
    $object = filter_var($object, FILTER_SANITIZE_STRING);
    $locked = filter_var($locked, FILTER_SANITIZE_NUMBER_INT);
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = '';
    /* Check whether object name exists */
    if (!empty($object)) {
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Select object id based on supplied object name */
        $sql = 'SELECT object_id 
        FROM object 
        WHERE name = ?';
        $results = QueryEngine($sql, array(&$object), 's', 0);
        if (!empty($results)) {
            /* If the object exists break off the operation */
            return FALSE;
        } else {
            /* If the object name doesn't exist insert the object in the 
            database */
            $sql = 'INSERT INTO object (name, locked) VALUES (?,?)';
            $results = QueryEngine($sql, array(&$object, &$locked), 'si', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* Delete an Object
*
* @param array $objects array of object names
* @return boolean
*
* Example:
* <code>
* <?php
* DeleteObject(array('object', '...'));
* ?>
* </code>
*/
function DeleteObject($objects=array()) {
    /* Filter external variables */
    $objects = filter_var($objects, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $args = $key = $val = $query_success = $results = $object = '';
    /* Loop through the $objects array, retrieve all object_ids and delete all 
    associated objects */
    if (!empty($objects)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through the $objects array and deliver individual id's 
        to the SQL DELETE instruction */
        while (list ($key, $val) = each ($objects)) {
            $object = (string) $val;
            $sql = 'SELECT name AS object_name FROM object WHERE name = ?';
            $results = QueryEngine($sql, array(&$object), 's', 0);
            if (!empty($results)) {
                $object_without_locked = (string) $results[0]['object_name'];
            }
            $sql = 'SELECT name AS object_name
            FROM object 
            WHERE name = ? AND locked = 0';
            $results = QueryEngine($sql, array(&$object), 's', 0);
            if (!empty($results)) {
                $object_locked = (string) $results[0]['object_name'];
            }
            /* If both SELECTS retrieve the same result the object is not
            locked and the DELETE can be executed */
            if (!empty($object_without_locked) && !empty($object_locked) && ($object_without_locked == $object_locked)) {
                $sql = 'DELETE FROM object WHERE name = ?';
                $results = QueryEngine($sql, array(&$object), 's', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            } else {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* Add an Operation to the database that is the subject of a permission
*
* @param string $operation operation name
* @param string $mask a bitmask to determine create, read, update and delete settings
* @param boolean $locked is the operation locked or not
* @return boolean
*
* Example:
* <code>
* <?php
* AddOperation('operation', '0000 - 1111', '0 or 1');
* ?>
* </code>
*/
function AddOperation($operation='', $mask='', $locked=0) {
    /* Filter external variables */
    $operation = filter_var($operation, FILTER_SANITIZE_STRING);
    $mask      = filter_var($mask, FILTER_SANITIZE_STRING);
    $locked    = filter_var($locked, FILTER_SANITIZE_NUMBER_INT);
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = $_create = $_read = 
    $_update = $_delete = '';
    /* Check whether object name exists */
    if (!empty($operation) && !empty($mask)) {
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Select operation id based on supplied object name */
        $sql = 'SELECT operation_id 
        FROM operation
        WHERE name = ?';
        $results = QueryEngine($sql, array(&$operation), 's', 0);
        if (!empty($results)) {
            /* If the operation exists break off the operation */
            return FALSE;
        } else {
            /* Unravel $mask */
            $_create = (int) substr($mask, 0, 1);
            $_read = (int) substr($mask, 1, 1);
            $_update = (int) substr($mask, 2, 1);
            $_delete = (int) substr($mask, 3, 1);
            /* If the operation name doesn't exist insert the object in the 
            database */
            $sql = 'INSERT INTO operation (name, _create, _read, _update, _delete, locked) VALUES (?,?,?,?,?,?)';
            $results = QueryEngine($sql, array(&$operation, &$_create, &$_read, &$_update, &$_delete, &$locked), 'siiiii', 0);
            if (!empty($results)) {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
* Delete an Operation
*
* @param array $operations array of operation names
* @return boolean
*
* Example:
* <code>
* <?php
* DeleteOperation(array('operation','...'));
* ?>
* </code>
*/
function DeleteOperation($operations=array()) {
    /* Filter external variables */
    $operations = filter_var($operations, FILTER_SANITIZE_STRING, FILTER_REQUIRE_ARRAY);
    /* initialize variables */
    $sql = $key = $val = $query_success = $results = $operation = 
    $operation_locked = $operation_without_locked = '';
    /* Loop through the $objects array, retrieve all object_ids and delete all 
    associated objects */
    if (!empty($operations)) {
        /* Start transaction */
        $query_success = TRUE;
        QueryEngine('', '', '', 1);
        /* Loop through the $operations array and deliver individual id's 
        to the SQL DELETE instruction */
        while (list ($key, $val) = each ($operations)) {
            $operation = (string) $val;
            $sql = 'SELECT name AS operation_name 
            FROM operation 
            WHERE name = ?';
            $results = QueryEngine($sql, array(&$operation), 's', 0);
            if (!empty($results)) {
                $operation_without_locked = (string) $results[0]['operation_name'];
            }
            $sql = 'SELECT name AS operation_name
            FROM operation 
            WHERE name = ? AND locked = 0';
            $results = QueryEngine($sql, array(&$operation), 's', 0);
            if (!empty($results)) {
                $operation_locked = (string) $results[0]['operation_name'];
            }
            /* If both SELECTS retrieve the same result the object is not
            locked and the DELETE can be executed */
            if (!empty($operation_without_locked) && !empty($operation_locked) && ($operation_without_locked == $operation_locked)) {
                $sql = 'DELETE FROM operation WHERE name = ?';
                $results = QueryEngine($sql, array(&$operation), 's', 0);
                if (!empty($results)) {
                    $query_success = FALSE;
                }
            } else {
                $query_success = FALSE;
            }
        }
        /* Commit or rollback transaction based on the value of $query_success */
        if ($query_success) {
            /* Commit transaction, return true */
            QueryEngine('', '', '', 2);
            return TRUE;
        } else {
            /* Rollback transaction, return false */
            QueryEngine('', '', '', 3); 
            return FALSE;
        }
    }
    return FALSE;
}

/**
*
* END OF NON STANDARD API CALLS
*
*/

/**
*
* HELPER FUNCTION
* This function is not part of the API but is necessary for establishing
* connections with the database and execute the various queries.
* It can be replaced with your own solution if you so desire but don't forget
* to either maintain the interface or change the model code to suit your own
* solution
*
*/

/**
* Generic query execution engine for the RBAC data functions. This is a support
* function that is not part of the standard API. This function uses the mysqli
* interface and makes use of mysqli bound parameters and bound results to 
* lower the risk of SQL injection attacks.
*
* The $sql paramater contains the SQL string
* The $param array contains all bound parameters
* The $type array contains the type indicator(s) for the bound parameter(s)
* The $transaction parameter indicates the following actions:
* 0 = do nothing, automatic commit of individual transactions
* 1 = start transaction
* 2 = commit transaction
* 3 = rollback transaction
*
* @param string $sql 
* @param array $param
* @param string $type
* @param integer $transaction_flag
* @return array
*/
function QueryEngine($sql='', $param=array(), $type='', $transaction_flag=0) {
    /* initialize variables */
    $field = $meta =  $params = $key = $val = $set = $results = '';
    /* The database connection is cast as a static variable to ensure that the 
    database connection will be initializeSettingsd only once during script execution.
    This saves on valuable time otherwise spent on setting up the database
    connection over and over again in the same script execution. */
    static $database_connection = NULL; 
    if (is_null($database_connection)) {
    	 /* CONSTANTS for database connection are defined in configuration.php */
        $database_connection = mysqli_init();
        if (!$database_connection) {
            die('mysqli_init failed');
        }
        /* mysqli_real_connect offers a number of additional functions like
        compression and SSL that aren't used (yet) in this function but 
        might be introduced at a later stage */
        if (!mysqli_real_connect($database_connection, DATABASE_SERVER, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME, DATABASE_PORT)) {
            trigger_error('Connect Error (' . mysqli_connect_errno() . '): ' . mysqli_connect_error(), E_USER_ERROR);
        }
        /* Force the UTF-8 character set */
        if (!mysqli_set_charset($database_connection, "utf8")) {
            trigger_error('Error loading character set utf8: ' . mysqli_error($database_connection), E_USER_ERROR);
        }
    } 
    
    /* Check whether the transaction flag has been set, the transaction needs 
    to be comitted or rolled back */
    switch ($transaction_flag) {
        case 0:
            break;
        case 1:
            /* Set autocommit to off */
            mysqli_autocommit($database_connection, FALSE);
            break;
        case 2:
            /* Commit transaction */
            mysqli_autocommit($database_connection, TRUE);
            break;
        case 3:
    	      /* Rollback transaction */
            mysqli_rollback($database_connection);
            break;
        default:
            break;
    }
    
    if (!empty($sql)) {
        /* Prepare SQL statement */
        $stmt = mysqli_prepare($database_connection, $sql);    	  
        /* Dynamically bind arguments passed in through the $param array */
        if ($stmt) {
            if (($param) && ($type)) {
                /* A custom function is constructed that calls 
                mysqli_stmt_bind_param because the regular function can't 
                handle arrays as an input source */
                call_user_func_array('mysqli_stmt_bind_param', array_merge(array($stmt, $type), $param));
            }
            mysqli_stmt_execute($stmt);
            /* Get the column names of the retrieved rows by querying the schema 
            meta-data. The column names are returned as the keys of the 
            multidimensional array and the rows are returned as the 
            values of the array. */
            $meta = mysqli_stmt_result_metadata($stmt);
            if (!empty($meta)) {
                while ($field = mysqli_fetch_field($meta)) {
                    $params[] = &$row[$field->name];
                }
                /* A custom function is constructed that uses the retrieved 
                column names as bound result parameters */
                call_user_func_array(array($stmt, 'bind_result'), $params);
                while (mysqli_stmt_fetch($stmt)) {
                    /* The results are put into an associative array */
                    foreach($row as $key => $val) {
                        $set[$key] = $val;
                    }
                    $results[] = $set;
                } 
            }  
        } else {
            trigger_error('query failed: ' . $sql . ' ' . mysqli_error($database_connection), E_USER_ERROR);
        }
        mysqli_stmt_close($stmt);
    }
    
    return $results;
}

/**
*
* END OF HELPER FUNCTION
*
*/

?>
