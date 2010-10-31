<?php
/**
* NIST Core RBAC
* @package NIST RBAC test framework
* @author M.E. Post <meintmeint.net>
* @version 0.66
* @copyright  M.E. Post
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/**
* NIST RBAC PHP API Test Framework
*/

/**
* Include configuration file
*/
include dirname(__FILE__) . '/configuration.php';

/**
* Include the helper functions
*/
include dirname(__FILE__) . '/../../include/php/include.php';

/**
* Include the NIST Core RBAC API library
*/
include dirname(__FILE__) . '/../../lib/rbac_api.php';


/* initialize variables */
$random = getRandomString(5);
$result = '';

/* Switch on output buffering, no output to screen but dump in string */
ob_start();


print '<h1>Test Framework</h1>';

/**
* Test 1: AddUser
*
* This test adds a user to the RBAC user table
*/
$user = 'TestUser_' . $random;
$password = 'test';
$first_name = 'Test';
$family_name = 'User';
$email = 'test@user.org';
$result = AddUser($user, $password, $first_name, $family_name, $email);
print '<div class="marginspace"><h2>Test 1 - AddUser:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> added succesfully</p>' : '<p class="error">Failed adding user <strong>' . $user . '</strong></p></div>';
print '</div>';


/**
* Test 2: AddObject
*
* This test adds an object to the RBAC object table
*/
$object = 'TestObject_' . $random;
$locked = 0;
$result = AddObject($object, $locked);
print '<div class="marginspace"><h2>Test 2 - AddObject:</h2>';
print $result ? '<p class="no-error">Object <strong>' . $object . '</strong> added succesfully</p>' : '<p class="error">Failed adding object <strong>' . $object . '</strong></p></div>';
print '</div>';


/**
* Test 3: AddOperation
*
* This test adds an operation to the RBAC operation table
*/
$operation = 'TestOperation_' . $random;
$mask = '0110';
$result = AddOperation($operation, $mask, $locked);
print '<div class="marginspace"><h2>Test 3 - AddOperation:</h2>';
print $result ? '<p class="no-error">Operation <strong>' . $operation . '</strong> added succesfully</p>' : '<p class="error">Failed adding operation <strong>' . $operation . '</strong></p></div>';
print '</div>';


/**
* Test 4: AddPermission
*
* This test adds a permission to the RBAC permission table
*/
$permission = 'TestPermission_' . $random;
$result = AddPermission($permission, $object, $operation);
print '<div class="marginspace"><h2>Test 4 - AddPermission:</h2>';
print $result ? '<p class="no-error">Permission <strong>' . $permission . '</strong> added succesfully</p>' : '<p class="error">Failed adding permission <strong>' . $permission . '</strong></p>';
print '</div>';


/**
* Test 5: AddRole
*
* This test adds a role to the RBAC role table
*/
$role = 'TestRole_' . $random;
$result = AddRole($role);
print '<div class="marginspace"><h2>Test 5 - AddRole:</h2>';
print $result ? '<p class="no-error">Role <strong>' . $role . '</strong> added succesfully</p>' : '<p class="error">Failed adding role <strong>' . $role . '</strong></p>';
print '</div>';


/**
* Test 6: GrantPermission
*
* This test associates a permission with a role
*/
$permission_set = array(array($object, $operation));
$result = GrantPermission($permission_set, $role);
print '<div class="marginspace"><h2>Test 6 - GrantPermission:</h2>';
print $result ? '<p class="no-error">Permission <strong>' . $permission . '</strong> added succesfully to Role <strong>' . $role . '</strong></p>' : '<p class="error">Failed adding permission <strong>' . $permission . '</strong> to Role <strong>' . $role . '/<strong></p>';
print '</div>';


/**
* Test 7: AssignUser
*
* This test associates a user with a role
*/
$result = AssignUser($user, array($role));
print '<div class="marginspace"><h2>Test 7 - AssignUser:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> associated succesfully with Role <strong>' . $role . '</strong></p>' : '<p class="error">Failed associating user <strong>' . $user . '</strong> with Role <strong>' . $role . '</strong></p>';
print '</div>';


/**
* Test 8: CreateSession
*
* This test creates a session for the user
*/
ini_set('session.hash_function', '1');
session_start();
session_regenerate_id();
$session = session_id();
$result = CreateSession($user, $session);
print '<div class="marginspace"><h2>Test 8 - CreateSession:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> associated succesfully with Session <strong>' . $session . '</strong></p>' : '<p class="error">Failed associating user <strong>' . $user . '</strong> with Session <strong>' . $session . '</strong></p>';
print '</div>';


/**
* Test 9: AddActiveRole
*
* This test temporary associates a user with a role during the session
*/
$role2 = 'TestRole2_' . $random;
AddRole($role2);
$result = AddActiveRole($user, $session, array($role2));
print '<div class="marginspace"><h2>Test 9 - AddActiveRole:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> associated succesfully with Role <strong>' . $role2 . '</strong> for the duration of Session <strong>' . $session . '</strong></p>' : '<p class="error">Failed associating user <strong>' . $user . '</strong> with Role <strong>' . $role2 . '</strong> for the duration of Session <strong>' . $session . '</strong></p>';
print '</div>';


/**
* Test 10: AssignedUsers
*
* This test shows all users that have been assigned to a role
*/
print '<div class="marginspace"><h2>Test 10 - AssignedUsers:</h2>';
print showTableTest(AssignedUsers($role));
print '<br/>';
print '</div>';


/**
* Test 11: AssignedRoles
*
* This test shows all roles that have been assigned to a user
*/
print '<div class="marginspace"><h2>Test 11 - AssignedRoles:</h2>';
print showTableTest(AssignedRoles($user));
print '<br/>';
print '</div>';


/**
* Test 12: RolePermissions
*
* This test shows all permissions associated with a specific role
*/
print '<div class="marginspace"><h2>Test 12 - RolePermissions:</h2>';
print showTableTest(RolePermissions($role));
print '<br/>';
print '</div>';


/**
* Test 13: UserPermissions
*
* This test shows all permissions associated with a specific user
*/
print '<div class="marginspace"><h2>Test 13 - UserPermissions:</h2>';
print showTableTest(UserPermissions($user));
print '<br/>';
print '</div>';


/**
* Test 14: SessionRoles
*
* This test shows all roles associated with a specific session
*/
print '<div class="marginspace"><h2>Test 14 - SessionRoles:</h2>';
print showTableTest(SessionRoles($session));
print '<br/>';


/**
* Test 15: SessionPermissions
*
* This test shows all permissions associated with a specific session
*/
print '<div class="marginspace"><h2>Test 15 - SessionPermissions:</h2>';
print showTableTest(SessionPermissions($session));
print '<br/>';


/**
* Test 16: DropActiveRole
*
* This test removes temporary associations between a user and role(s) during the session
*/
$result = DropActiveRole($user, $session, array($role2));
print '<div class="marginspace"><h2>Test 16 - DropActiveRole:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> with Role <strong>' . $role2 . '</strong> removed from Session <strong>' . $session . '</strong></p>' : '<p class="error">Failed removing association between user <strong>' . $user . '</strong> and Role <strong>' . $role2 . '</strong> for Session <strong>' . $session . '</strong></p>';
print '</div>';


/**
* Test 17: SessionRoles
*
* This test shows all roles associated with a specific session
*/
print '<div class="marginspace"><h2>Test 17 - SessionRoles:</h2>';
print showTableTest(SessionRoles($session));
print '<br/>';


/**
* Test 18: DeleteSession
*
* This test deletes a session for the user
*/
$result = DeleteSession(array($session));
print '<div class="marginspace"><h2>Test 18 - DeleteSession:</h2>';
print $result ? '<p class="no-error">Session <strong>' . $session . '</strong> deleted</p>' : '<p class="error">Failed deleting Session <strong>' . $session . '</strong></p>';
print '</div>';


/**
* Test 19: DeassignUser
*
* This test dissociates a user from a role
*/
$result = DeassignUser($user, array($role));
print '<div class="marginspace"><h2>Test 19 - DeassignUser:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> dissociated succesfully from Role <strong>' . $role . '</strong></p>' : '<p class="error">Failed dissociating user <strong>' . $user . '</strong> from Role <strong>' . $role . '</strong></p>';
print '</div>';


/**
* Test 20: RevokePermission
*
* This test revokes a permission from a role
*/
$permission_set = array(array($object, $operation));
$result = RevokePermission($permission_set, $role);
print '<div class="marginspace"><h2>Test 20 - RevokePermission:</h2>';
print $result ? '<p class="no-error">Permission <strong>' . $permission . '</strong> revoked from Role <strong>' . $role . '</strong></p>' : '<p class="error">Failed revoking permission <strong>' . $permission . '</strong> from Role <strong>' . $role . '/<strong></p>';
print '</div>';


/**
* Test 21: DeleteRole
*
* This test deletes a role from the RBAC role table
*/
$result = DeleteRole(array($role, $role2));
print '<div class="marginspace"><h2>Test 21 - DeleteRole:</h2>';
print $result ? '<p class="no-error">Role <strong>' . $role . '</strong> deleted succesfully</p>' : '<p class="error">Failed deleting role <strong>' . $role . '</strong></p>';
print '</div>';


/**
* Test 22: DeletePermission
*
* This test deletes a permission from the RBAC permission table
*/
$result = DeletePermission(array($permission));
print '<div class="marginspace"><h2>Test 22 - DeletePermission:</h2>';
print $result ? '<p class="no-error">Permission <strong>' . $permission . '</strong> deleted succesfully</p>' : '<p class="error">Failed deleting permission <strong>' . $permission . '</strong></p>';
print '</div>';


/**
* Test 23: DeleteOperation
*
* This test adds an operation to the RBAC operation table
*/
$result = DeleteOperation(array($operation));
print '<div class="marginspace"><h2>Test 23 - DeleteOperation:</h2>';
print $result ? '<p class="no-error">Operation <strong>' . $operation . '</strong> deleted succesfully</p>' : '<p class="error">Failed deleting operation <strong>' . $operation . '</strong></p></div>';
print '</div>';


/**
* Test 24: DeleteObject
*
* This test adds an object to the RBAC object table
*/
$result = DeleteObject(array($object));
print '<div class="marginspace"><h2>Test 24 - DeleteObject:</h2>';
print $result ? '<p class="no-error">Object <strong>' . $object . '</strong> deleted succesfully</p>' : '<p class="error">Failed deleting object <strong>' . $object . '</strong></p></div>';
print '</div>';



/**
* Test 25: DeleteUser
*
* This test adds a user to the RBAC user table
*/
$result = DeleteUser(array($user));
print '<div class="marginspace"><h2>Test 25 - DeleteUser:</h2>';
print $result ? '<p class="no-error">User <strong>' . $user . '</strong> deleted succesfully</p>' : '<p class="error">Failed deleting user <strong>' . $user . '</strong></p></div>';
print '</div>';

print '<h1>All tests have run succesfully</h1>';

$page = ob_get_contents();
ob_end_clean();
print mergeContentWithTemplate($page);

?>
