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
* Provide the set of all users
*
* @return array
*/
function AddUserModel() {
    $sql = 'SELECT user.username AS ' . localize('Username') . ', 
    CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('Person') . ', 
    user.email AS ' . localize('Email') . '
    FROM user';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all users including delete information
* in the form of the checkbox column containing usernames
* as identifiers
*
* @return array
*/
function DeleteUserModel() {
    $sql = 'SELECT user.username AS checkbox, 
    user.username AS ' . localize('Username') . ', 
    CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('Person') . ', 
    user.email AS ' . localize('Email') . ' 
    FROM user';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all roles
*
* @return array
*/
function AddRoleModel() {
    $sql = 'SELECT name AS ' . localize('Role') . ' FROM role';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all roles including delete information
* in the form of the role column containing roles
* as identifiers
*
* @return array
*/
function DeleteRoleModel() {
    $sql = 'SELECT name AS role, 
    name AS ' . localize('Role') . ' FROM role';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the first (=lowest ordered) username
*
* @return string
*/
function LowestUserIdModel() {
    $user = '';
    $sql = 'SELECT MIN(username) AS username FROM user';
    $results = QueryEngine($sql, '', '', 0);
    if (!empty($results)) {
        $user = $results[0]['username'];
    }
    return $user;
}

/**
* Provide the set of all users but only partly localized
*
* @return array
*/
function UserOverviewModel() {
    $sql = 'SELECT user.username, CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('User') . '
    FROM user';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of matched and non-matched roles as a union. If the role
* is already assigned to the user a virtual column "Matched=1" is added to the
* set, otherwise "Matched=0" is added to the set. The program logic uses this
* differentiator to distinguish between roles that are part of the current set
* versus unmatched roles
*
* @param string $user username
* @return array
*/
function AssignUserModel($user) {
    $sql = 'SELECT name AS role_name, name AS ' . localize('Role') . ', "1" AS Matched 
    FROM role
    INNER JOIN user_role USING (role_id)
    INNER JOIN user USING (user_id)
    WHERE username = ?
    UNION
    SELECT name AS role_name, name AS ' . localize('Role') . ', "0" AS Matched
    FROM role
    WHERE role_id NOT IN (
        SELECT role_id
        FROM role
        INNER JOIN user_role USING (role_id)
        INNER JOIN user USING (user_id)
        WHERE username = ?
    )';
    return QueryEngine($sql, array(&$user, &$user), 'ss', 0);
}

/**
* Provide the set of roles that are part of the current active role set
*
* @param string $user username
* @return array
*/
function DeassignUserModel($user) {
    $sql = 'SELECT name AS role_name, name AS ' . localize('Role') . ' 
    FROM role
    INNER JOIN user_role USING (role_id)
    INNER JOIN user USING (user_id)
    WHERE username = ?';
    return QueryEngine($sql, array(&$user), 's', 0);
}

/**
* Provide the first (=lowest ordered) role
*
* @return string
*/
function LowestRoleIdModel() {
    $role = '';
    $sql = 'SELECT MIN(name) AS role_name FROM role';
    $results = QueryEngine($sql, '', '', 0);
    if (!empty($results)) {
        $role = $results[0]['role_name'];
    }
    return $role;
}

/**
* Provide the set of all roles (non-localized)
*
* @return array
*/
function RoleOverviewModel() {
    $sql = 'SELECT name AS role_name, name FROM role';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of matched and non-matched permissions as a union. If the 
* permission is already assigned to the role a virtual column "Matched=1" is 
* added to the set, otherwise "Matched=0" is added to the set. The program 
* logic uses this differentiator to distinguish between permissions that are 
* part of the current set versus unmatched permissions
*
* @param string $role rolename
* @return array
*/
function GrantPermissionModel($role) {
    $sql = 'SELECT CONCAT(object.name, "+", operation.name) AS permission,
    permission.name AS  ' . localize('Permission') . ', 
    object.name AS  ' . localize('Object') . ', 
    operation.name AS ' . localize('Operation') . ', 
    "1" AS Matched 
    FROM role_permission
    INNER JOIN permission USING (permission_id)
    INNER JOIN object USING (object_id)
    INNER JOIN operation USING (operation_id)
    INNER JOIN role USING (role_id)
    WHERE role_id = (SELECT role_id FROM role WHERE name = ?)
    UNION
    SELECT DISTINCT CONCAT(object.name, "+", operation.name) AS permission,
    permission.name AS  ' . localize('Permission') . ', 
    object.name AS  ' . localize('Object') . ', 
    operation.name AS ' . localize('Operation') . ', 
    "0" AS Matched
    FROM permission
    INNER JOIN object USING (object_id)
    INNER JOIN operation USING (operation_id)
    WHERE object_id NOT IN (
      SELECT object_id FROM role_permission 
      INNER JOIN permission USING (permission_id)
      WHERE role_id = (SELECT role_id FROM role WHERE name = ?)
    )';
    return QueryEngine($sql, array(&$role, &$role), 'ss', 0);
}

/**
* Provide the set of permissions that are part of the current active role set
*
* @param string $role rolename
* @return array
*/
function RevokePermissionModel($role) {
    $sql = 'SELECT CONCAT(object.name, "+", operation.name) AS permission,
    object.name AS  ' . localize('Object') . ', 
    operation.name AS ' . localize('Operation') . ' 
    FROM role_permission
    INNER JOIN permission USING (permission_id)
    INNER JOIN object USING (object_id)
    INNER JOIN operation USING (operation_id)
    INNER JOIN role USING (role_id)
    WHERE role_id = (SELECT role_id FROM role WHERE name = ?)';
    return QueryEngine($sql, array(&$role), 's', 0);
}

/**
* Provide set of sessions and users
*
* @return array
*/
function CreateSessionModel() {
    $sql = 'SELECT DISTINCT session.session_id AS ' . localize('Session') . ', 
    CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('User') . '
    FROM user
    INNER JOIN session USING (user_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of sessions and users including delete information
* in the form of the checkbox column containing the session name
* as identifier
*
* @return array
*/
function DeleteSessionModel() {
    $sql = 'SELECT DISTINCT session.name AS checkbox, 
    session.session_id AS ' . localize('Session') . ', 
    CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('User') . '
    FROM user
    INNER JOIN session USING (user_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the first (=lowest ordered) user+session combination
*
* @return string
*/
function LowestUserSessionIdModel() {
    $user_session = '';
    $sql = 'SELECT MIN(CONCAT(user.username, "+", session.name)) AS user_session 
    FROM user 
    INNER JOIN session USING (user_id)';
    $results = QueryEngine($sql, '', '', 0);
    if (!empty($results)) {
        $user_session = $results[0]['user_session'];
    }
    return $user_session;
}

/**
* Provide the set of matched and non-matched roles as a union. If the 
* role is already assigned to the user in the current session a virtual column 
* "Matched=1" is added to the set, otherwise "Matched=0" is added to the set. 
* The program logic uses this differentiator to distinguish between roles 
* that are part of the current session versus unmatched roles
*
* @param string $user username
* @return array
*/
function AddActiveRoleModel($user) {
    $sql = 'SELECT role.name AS role_name, role.name AS ' . localize('Role') . ', 
    "1" AS Matched 
    FROM role
    INNER JOIN session_role USING (role_id)
    INNER JOIN session USING (session_id)
    WHERE session.user_id = (SELECT user_id FROM user WHERE username = ?)
    UNION
    SELECT role.name AS role_name, role.name AS ' . localize('Role') . ', 
    "0" AS Matched
    FROM role
    WHERE role_id NOT
    IN (
        SELECT role_id
        FROM session_role
        INNER JOIN session USING ( session_id )
        WHERE session.user_id = (SELECT user_id FROM user WHERE username = ?)
    )';
    return QueryEngine($sql, array(&$user, &$user), 'ss', 0);
}

/**
* Provide the set of all users and sessions (concatenated)
*
* @return array
*/
function UserSessionOverviewModel() {
    $sql = 'SELECT CONCAT(user.username, "+", session.name) AS user_session, CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('User') . '
    FROM user
    INNER JOIN session USING (user_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all roles that are part of a specific users session
*
* @param string $user username
* @return array
*/
function DropActiveRoleModel($user) {
    $sql = 'SELECT DISTINCT role.name AS role_name, role.name AS ' . localize('Role') . '
    FROM role
    INNER JOIN session_role USING (role_id)
    INNER JOIN session USING (session_id)
    WHERE session.user_id = (SELECT user_id FROM user WHERE username = ?)';
    return QueryEngine($sql, array(&$user), 's', 0);
}

/**
* Provide the set all roles (Localized)
*
* @return array
*/
function RoleLocalizedOverviewModel() {
    $sql = 'SELECT name AS role_name, name AS ' . localize('Role') . '
    FROM role';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all users and sessions (non-concatenated)
*
* @return array
*/
function SessionRolesModel() {
    $sql = 'SELECT session.name AS user_session, CONCAT(user.family_name, ", ", user.first_name) AS ' . localize('User') . '
    FROM user
    INNER JOIN session USING (user_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the first (=lowest ordered) session
*
* @return string
*/
function LowestSessionModel() {
    $session = '';
    $sql = 'SELECT MIN(name) AS session FROM session';
    $results = QueryEngine($sql, '', '', 0);
    if (!empty($results)) {
        $session = $results[0]['session'];
    }
    return $session;
}

/**
* Provide the set of all permissions and associated objects and operations
*
* @return array
*/
function AddPermissionModel() {
    $sql = 'SELECT permission.name AS ' . localize('Permission') . ',
    object.name AS ' . localize('Object') . ', 
    operation.name AS ' . localize('Operation') . '
    FROM permission
    LEFT JOIN operation USING (operation_id)
    LEFT JOIN object USING (object_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all objects
*
* @return array
*/
function ObjectOverviewModel() {
    $sql = 'SELECT name AS object_name, name 
    FROM object';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all operations
*
* @return array
*/      
function OperationOverviewModel() {
    $sql = 'SELECT name AS operation_name, name 
    FROM operation';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all permissions and associated objects and operations
* including a column containing delete information in the form of
* permission_name as identifier
*
* @return array
*/
function DeletePermissionModel() {
    $sql = 'SELECT permission.name AS permission_name, permission.name AS ' . localize('Permission') . ',
    object.name AS ' . localize('Object') . ', 
    operation.name AS ' . localize('Operation') . '
    FROM permission
    LEFT JOIN operation USING (operation_id)
    LEFT JOIN object USING (object_id)';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all objects (localized)
*
* @return array
*/
function AddObjectModel() {
    $sql = 'SELECT object.name AS ' . localize('Object') . ', 
    object.locked AS ' . localize('Locked') . '
    FROM object';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all objects including a column containing delete
* information in the form of object_name as identifier
*
* @return array
*/
function DeletObjectModel() {
    $sql = 'SELECT object.name AS object_name, object.name AS ' . localize('Object') . ', 
    object.locked AS ' . localize('Locked') . '
    FROM object';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all operations and fill the row with an X if there is
* a true value otherwise provide a no-break space as value 
*
* @return array
*/
function AddOperationModel() {
    $sql = 'SELECT operation.name AS ' . localize('Operation') . ', 
    IF(operation._create,"X","&nbsp;") AS ' . localize('_Create'). ', 
    IF(operation._read,"X","&nbsp;") AS ' . localize('_Read') . ', 
    IF(operation._update,"X","&nbsp;") AS ' . localize('_Update') . ', 
    IF(operation._delete,"X","&nbsp;") AS ' . localize('_Delete') . ',
    operation.locked AS ' . localize('Locked') . '
    FROM operation';
    return QueryEngine($sql, '', '', 0);
}

/**
* Provide the set of all operations and fill the row with an X if there is
* a true value otherwise provide a no-break space as value. Also provide
* a column containing delete information in the form iof operation_name
* as identifier
*
* @return array
*/
function DeleteOperationModel() {
    $sql = 'SELECT operation.name AS operation_name, operation.name AS ' . localize('Operation') . ', 
    IF(operation._create,"X","&nbsp;") AS ' . localize('_Create'). ', 
    IF(operation._read,"X","&nbsp;") AS ' . localize('_Read') . ', 
    IF(operation._update,"X","&nbsp;") AS ' . localize('_Update') . ', 
    IF(operation._delete,"X","&nbsp;") AS ' . localize('_Delete') . ',
    operation.locked AS ' . localize('Locked') . '
    FROM operation';
    return QueryEngine($sql, '', '', 0);
}

?>
