--
-- Warden 1.2
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique user ID',
  `username` varchar(32) NOT NULL COMMENT 'User username',
  `email` varchar(255) NOT NULL COMMENT 'User email',
  `encrypted_password` varbinary(60) NOT NULL COMMENT 'Encryption of the user password',
  `authentication_token` varbinary(60) DEFAULT NULL COMMENT 'Session authentication token',
  `remember_token` varbinary(60) DEFAULT NULL COMMENT 'Cookie remember token',

  -- IF USING recoverable uncomment the following lines
  -- `reset_password_token` varbinary(60) DEFAULT NULL COMMENT 'Reset password token',
  -- `reset_password_sent_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the reset password token was sent',

  -- IF USING confirmable uncomment the following lines
  -- `is_confirmed` tinyint(1) unsigned DEFAULT '0' COMMENT 'Flag whether the user account has been confirmed',
  -- `confirmation_token` varbinary(60) DEFAULT NULL COMMENT 'Confirmation token',
  -- `confirmation_sent_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the confirmation token was sent',

  -- IF USING trackable uncomment the following lines
  -- `sign_in_count` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Increased every time a sign in is made',
  -- `current_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'A timestamp updated when the user signs in',
  -- `last_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Holds the timestamp of the previous sign in',
  -- `current_sign_in_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The remote IP updated when the user sign in',
  -- `last_sign_in_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Holds the remote IP of the previous sign in',

  -- IF USING lockable uncomment the following lines, depending on ur lock_strategy
  -- `failed_attempts` int(11) unsigned DEFAULT '0' COMMENT 'The number of current failed sign in attempts',
  -- `unlock_token` varbinary(60) DEFAULT NULL COMMENT 'Unlock token',
  -- `locked_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the account was locked',

  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the user account was created',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the user account was last updated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_users_on_email` (`email`),
  UNIQUE KEY `index_users_on_username` (`username`),
  KEY `index_users_on_authentication_token` (`authentication_token`),
  KEY `index_users_on_remember_token` (`remember_token`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User account details';

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique role ID',
  `name` varchar(20) NOT NULL COMMENT 'The name of the role',
  `description` varchar(100) NOT NULL COMMENT 'The description of the role',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_roles_on_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Allowed user roles';

-- --------------------------------------------------------

--
-- Table structure for table `roles_users`
--

CREATE TABLE IF NOT EXISTS `roles_users` (
  `role_id` int(11) unsigned NOT NULL COMMENT 'Unique role ID',
  `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user ID',
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `index_roles_users_on_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User to role mapping';

--
-- Constraints for table `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `fk_index_roles_users_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_index_roles_users_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE IF NOT EXISTS `permissions` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique permission ID',
 `name` varchar(20) NOT NULL COMMENT 'The name of the permission',
 `resource` varchar(30) NOT NULL COMMENT 'The resource the permission corresponds to (eg. ''admin'')',
 `action` varchar(30) NOT NULL COMMENT 'The action the permission corresponds to (eg. ''read'')',
 `description` varchar(100) NOT NULL COMMENT 'The description of the permission',
 PRIMARY KEY (`id`),
 UNIQUE KEY `index_permissions_on_name` (`name`),
 UNIQUE KEY `index_permissions_on_resource_and_action` (`resource`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User permissions';

-- --------------------------------------------------------

--
-- Table structure for table `roles_permissions`
--

CREATE TABLE IF NOT EXISTS `roles_permissions` (
  `role_id` int(11) unsigned NOT NULL COMMENT 'Unique role ID',
  `permission_id` int(11) unsigned NOT NULL COMMENT 'Unique permission ID',
  PRIMARY KEY (`role_id`,`permission_id`),
  KEY `index_roles_permissions_on_permission_id` (`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Role to permission mapping';

--
-- Constraints for table `roles_permissions`
--
ALTER TABLE `roles_permissions`
  ADD CONSTRAINT `fk_index_roles_permissions_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_index_roles_permissions_on_permission_id` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

-- --------------------------------------------------------

-- IF USING profilable the following table is required
--
-- Minimum table structure for table `profiles`
--

CREATE TABLE IF NOT EXISTS `profiles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique profile id',
  `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user id',
  PRIMARY KEY (`id`),
  KEY `index_profiles_on_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='User profiles';

--
-- Constraints for table `services`
--
ALTER TABLE `profiles`
  ADD CONSTRAINT `fk_index_profiles_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;