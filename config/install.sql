-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique role ID',
  `name` varchar(40) NOT NULL COMMENT 'The name of the role',
  `description` varchar(255) NOT NULL COMMENT 'The description of the role',
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
  -- `reset_password_sent_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the reset password token was sent',

  -- IF USING trackable uncomment the following lines
  -- `sign_in_count` int(11) unsigned NOT NULL COMMENT 'Increased every time a sign in is made',
  -- `current_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'A timestamp updated when the user signs in',
  -- `last_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Holds the timestamp of the previous sign in',
  -- `current_sign_in_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'The remote IP updated when the user sign in',
  -- `last_sign_in_ip` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'Holds the remote IP of the previous sign in',

  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the user account was created',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the user account was last updated',
  PRIMARY KEY (`id`),
  UNIQUE KEY `index_users_on_email` (`email`),
  UNIQUE KEY `index_users_on_username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='User account details';

-- --------------------------------------------------------

-- IF USING omniauthable the following table is required
--
-- Table structure for table `services`
--

CREATE TABLE IF NOT EXISTS `services` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique service id',
  `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user id',
  `uid` varchar(255) DEFAULT NULL COMMENT 'An identifier unique to the given provider, such as a Twitter user ID. Should be stored as a string.',
  `provider` varchar(50) DEFAULT NULL COMMENT 'The provider with which the user authenticated (e.g. ''twitter'' or ''facebook'')',
  `access_token` varchar(255) DEFAULT NULL COMMENT 'The access token, supplied by OAuth and OAuth 2.0 providers',
  `access_secret` varchar(255) DEFAULT NULL COMMENT 'The access token secret, supplied by OAuth providers',
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the service was created',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the service was updated',
  PRIMARY KEY (`id`),
  KEY `index_services_on_access_token` (`access_token`),
  KEY `index_services_on_user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


--
-- Constraints for table `roles_users`
--
ALTER TABLE `roles_users`
  ADD CONSTRAINT `fk_index_roles_users_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_index_roles_users_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;