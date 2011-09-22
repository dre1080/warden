# The Warden

A user authorization package for FuelPHP.
Handles user login and logout, as well as secure password hashing.

This package uses the requires the FuelPHP ORM package.
It uses the Blowfish algorithm for password storage.

# Installation

It relies on the following tables:

CREATE TABLE `users` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
 `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 `encrypted_password` varbinary(60) NOT NULL,
 `sign_in_count` int(11) unsigned NOT NULL,
 `current_sign_in_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `last_sign_in_at` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
 `current_sign_in_ip` int(11) unsigned NOT NULL DEFAULT '0',
 `last_sign_in_ip` int(11) unsigned NOT NULL DEFAULT '0',
 `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `index_users_on_email` (`email`),
 UNIQUE KEY `index_users_on_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci

The following fields:
+`sign_in_count`,
+`current_sign_in_at`,
+`last_sign_in_at`,
+`current_sign_in_ip`,
+`last_sign_in_ip`
are optional, view config file in `config/warden.php` for more details.


CREATE TABLE `user_tokens` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `user_id` int(11) unsigned NOT NULL,
 `token` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
 `user_agent` varbinary(40) NOT NULL,
 `expires` int(10) unsigned NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 UNIQUE KEY `index_user_tokens_on_token` (`token`),
 KEY `index_user_tokens_on_user_id` (`user_id`),
 CONSTRAINT `fk_index_user_tokens_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci


CREATE TABLE `roles` (
 `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
 `name` varchar(255) NOT NULL DEFAULT '',
 PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci


CREATE TABLE `roles_users` (
 `role_id` int(11) unsigned NOT NULL,
 `user_id` int(11) unsigned NOT NULL,
 PRIMARY KEY (`role_id`,`user_id`),
 KEY `index_roles_users_on_user_id` (`user_id`),
 CONSTRAINT `fk_index_roles_users_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
 CONSTRAINT `fk_index_roles_users_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci


To change the table names all you have to do is extend the Warden models and add
your custom table name and/or properties.

For now, check the`classes/warden.php` file for usage examples.

## TODO
+ Bundle install task/method for migrations