-- phpMyAdmin SQL Dump
-- version 3.2.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 22, 2010 at 08:32 PM
-- Server version: 5.1.46
-- PHP Version: 5.3.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


--
-- Database: `rbac`
--

-- --------------------------------------------------------

--
-- Table structure for table `object`
--

CREATE TABLE IF NOT EXISTS `object` (
  `object_id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`object_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=51 ;

--
-- Dumping data for table `object`
--

INSERT INTO `object` (`object_id`, `name`, `locked`) VALUES
(1, 'AddUser', 1),
(2, 'AddUserView', 1),
(3, 'DeleteUser', 1),
(4, 'DeleteUserView', 1),
(5, 'AddRole', 1),
(6, 'AddRoleView', 1),
(7, 'DeleteRole', 1),
(8, 'DeleteRoleView', 1),
(9, 'AssignUser', 1),
(10, 'AssignUserView', 1),
(11, 'DeassignUser', 1),
(12, 'DeassignUserView', 1),
(13, 'GrantPermission', 1),
(14, 'GrantPermissionView', 1),
(15, 'RevokePermission', 1),
(16, 'RevokePermissionView', 1),
(17, 'AddActiveRole', 1),
(18, 'AddActiveRoleView', 1),
(19, 'DropActiveRole', 1),
(20, 'DropActiveRoleView', 1),
(21, 'AssignedUsers', 1),
(22, 'AssignedUsersView', 1),
(23, 'AssignedRoles', 1),
(24, 'AssignedRolesView', 1),
(25, 'RolePermissions', 1),
(26, 'RolePermissionsView', 1),
(27, 'UserPermissions', 1),
(28, 'UserPermissionsView', 1),
(29, 'SessionRoles', 1),
(30, 'SessionRolesView', 1),
(31, 'SessionPermissions', 1),
(32, 'SessionPermissionsView', 1),
(33, 'AddPermission', 1),
(34, 'AddPermissionView', 1),
(35, 'DeletePermission', 1),
(36, 'DeletePermissionView', 1),
(37, 'AddObject', 1),
(38, 'AddObjectView', 1),
(39, 'DeleteObject', 1),
(40, 'DeleteObjectView', 1),
(41, 'CreateSession', 1),
(42, 'CreateSessionView', 1),
(43, 'DeleteSession', 1),
(44, 'DeleteSessionView', 1),
(45, 'CheckAccessView', 1),
(46, 'CheckAccess', 1),
(47, 'AddOperationView', 1),
(48, 'AddOperation', 1),
(49, 'DeleteOperation', 1),
(50, 'DeleteOperationView', 1);

-- --------------------------------------------------------

--
-- Table structure for table `operation`
--

CREATE TABLE IF NOT EXISTS `operation` (
  `operation_id` int(2) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `_create` tinyint(1) DEFAULT NULL,
  `_read` tinyint(1) DEFAULT NULL,
  `_update` tinyint(1) DEFAULT NULL,
  `_delete` tinyint(1) DEFAULT NULL,
  `locked` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`operation_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=17 ;

--
-- Dumping data for table `operation`
--

INSERT INTO `operation` (`operation_id`, `name`, `_create`, `_read`, `_update`, `_delete`, `locked`) VALUES
(1, 'none', 0, 0, 0, 0, 1),
(2, 'create', 1, 0, 0, 0, 1),
(3, 'read', 0, 1, 0, 0, 1),
(4, 'update', 0, 0, 1, 0, 1),
(5, 'delete', 0, 0, 0, 1, 1),
(6, 'create_read', 1, 1, 0, 0, 1),
(7, 'create_read_update', 1, 1, 1, 0, 1),
(8, 'create_read_update_delete', 1, 1, 1, 1, 1),
(9, 'create_update', 1, 0, 1, 0, 1),
(10, 'create_update_delete', 1, 0, 1, 1, 1),
(11, 'read_update', 0, 1, 1, 0, 1),
(12, 'update_delete', 0, 0, 1, 1, 1),
(13, 'read_update_delete', 0, 1, 1, 1, 1),
(14, 'create_read_delete', 1, 1, 0, 1, 1),
(15, 'create_delete', 1, 0, 0, 1, 1),
(16, 'read_delete', 0, 1, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `permission`
--

CREATE TABLE IF NOT EXISTS `permission` (
  `permission_id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `object_id` int(8) NOT NULL,
  `operation_id` int(2) NOT NULL,
  PRIMARY KEY (`permission_id`,`object_id`,`operation_id`,`name`),
  KEY `IDX_permission_1` (`object_id`),
  KEY `IDX_permission_2` (`operation_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10097 ;

--
-- Dumping data for table `permission`
--

INSERT INTO `permission` (`permission_id`, `name`, `object_id`, `operation_id`) VALUES
(1, 'AddUser', 1, 6),
(2, 'AddUserView', 2, 3),
(3, 'DeleteUser', 3, 16),
(4, 'DeleteUserView', 4, 3),
(5, 'AddRole', 5, 6),
(6, 'AddRoleView', 6, 3),
(7, 'DeleteRole', 7, 16),
(8, 'DeleteRoleView', 8, 3),
(9, 'AssignUser', 9, 6),
(10, 'AssignUserView', 10, 3),
(11, 'DeassignUser', 11, 16),
(12, 'DeassignUserView', 12, 3),
(13, 'GrantPermission', 13, 6),
(14, 'GrantPermissionView', 14, 3),
(15, 'RevokePermission', 15, 16),
(16, 'RevokePermissionView', 16, 3),
(17, 'AddActiveRole', 17, 6),
(18, 'AddActiveRoleView', 18, 3),
(19, 'DropActiveRole', 19, 16),
(20, 'DropActiveRoleView', 20, 3),
(21, 'AssignedUsers', 21, 3),
(22, 'AssignedUsersView', 22, 3),
(23, 'AssignedRoles', 23, 3),
(24, 'AssignedRolesView', 24, 3),
(25, 'RolePermissions', 25, 3),
(26, 'RolePermissionsView', 26, 3),
(27, 'UserPermissions', 27, 3),
(28, 'UserPermissionsView', 28, 3),
(29, 'SessionRoles', 29, 3),
(30, 'SessionRolesView', 30, 3),
(31, 'SessionPermissions', 31, 3),
(32, 'SessionPermissionsView', 32, 3),
(33, 'AddPermission', 33, 6),
(34, 'AddPermissionView', 34, 3),
(35, 'DeletePermission', 35, 16),
(36, 'DeletePermissionView', 36, 3),
(37, 'AddObject', 37, 6),
(38, 'AddObjectView', 38, 3),
(39, 'DeleteObject', 39, 16),
(40, 'DeleteObjectView', 40, 3),
(41, 'CreateSession', 41, 6),
(42, 'CreateSessionView', 42, 3),
(43, 'DeleteSession', 43, 16),
(44, 'DeleteSessionView', 44, 3),
(45, 'CheckAccessView', 45, 3),
(46, 'CheckAccess', 46, 3),
(47, 'AddOperationView', 47, 3),
(48, 'AddOperation', 48, 6),
(49, 'DeleteOperation', 49, 3),
(50, 'DeleteOperationView', 50, 16);

-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `role_id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`role_id`, `name`) VALUES
(1, 'Administrator');

-- --------------------------------------------------------

--
-- Table structure for table `role_permission`
--

CREATE TABLE IF NOT EXISTS `role_permission` (
  `role_id` int(8) NOT NULL,
  `permission_id` int(8) NOT NULL,
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `IDX_role_permission_1` (`role_id`),
  KEY `IDX_role_permission_2` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `role_permission`
--

INSERT INTO `role_permission` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(1, 21),
(1, 22),
(1, 23),
(1, 24),
(1, 25),
(1, 26),
(1, 27),
(1, 28),
(1, 29),
(1, 30),
(1, 31),
(1, 32),
(1, 33),
(1, 34),
(1, 35),
(1, 36),
(1, 37),
(1, 38),
(1, 39),
(1, 40),
(1, 41),
(1, 42),
(1, 43),
(1, 44),
(1, 45),
(1, 46),
(1, 47),
(1, 48),
(1, 49),
(1, 50);

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE IF NOT EXISTS `session` (
  `session_id` int(8) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`session_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Dumping data for table `session`
--


-- --------------------------------------------------------

--
-- Table structure for table `session_role`
--

CREATE TABLE IF NOT EXISTS `session_role` (
  `role_id` int(8) NOT NULL,
  `session_id` int(8) NOT NULL,
  PRIMARY KEY (`role_id`,`session_id`),
  KEY `IDX_session_role_1` (`role_id`),
  KEY `IDX_session_role_2` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `session_role`
--


-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `user_id` int(8) NOT NULL AUTO_INCREMENT,
  `username` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `nonce` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `family_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `nonce`, `first_name`, `family_name`, `email`) VALUES
(1, 'admin', '374601f1201ac714e79747eb74f56a9538b96271a7ffbdbff9810ccaf65259f7', '2010-05-22 20:40:24', 'admin', 'admin', 'info@admin.net');

-- --------------------------------------------------------

--
-- Table structure for table `user_role`
--

CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(8) NOT NULL,
  `role_id` int(8) NOT NULL,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `IDX_user_role_1` (`user_id`),
  KEY `IDX_user_role_2` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_role`
--

INSERT INTO `user_role` (`user_id`, `role_id`) VALUES
(1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_session`
--

CREATE TABLE IF NOT EXISTS `user_session` (
  `user_id` int(8) NOT NULL,
  `session_id` int(8) NOT NULL,
  PRIMARY KEY (`user_id`,`session_id`),
  KEY `IDX_user_session_18` (`session_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Dumping data for table `user_session`
--


--
-- Constraints for dumped tables
--

--
-- Constraints for table `permission`
--
ALTER TABLE `permission`
  ADD CONSTRAINT `object_permission` FOREIGN KEY (`object_id`) REFERENCES `object` (`object_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `operation_permission` FOREIGN KEY (`operation_id`) REFERENCES `operation` (`operation_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `role_permission`
--
ALTER TABLE `role_permission`
  ADD CONSTRAINT `permission_role_permission` FOREIGN KEY (`permission_id`) REFERENCES `permission` (`permission_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_role_permission` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `session_role`
--
ALTER TABLE `session_role`
  ADD CONSTRAINT `role_session_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `session_session_role` FOREIGN KEY (`session_id`) REFERENCES `session` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_role`
--
ALTER TABLE `user_role`
  ADD CONSTRAINT `role_user_role` FOREIGN KEY (`role_id`) REFERENCES `role` (`role_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_user_role` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_session`
--
ALTER TABLE `user_session`
  ADD CONSTRAINT `session_user_session` FOREIGN KEY (`session_id`) REFERENCES `session` (`session_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_user_session` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
