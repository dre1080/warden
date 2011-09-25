# Warden

A user database authorization package for FuelPHP.
Handles user login and logout, as well as secure password hashing.

This package requires the FuelPHP ORM package.
It uses the Blowfish algorithm for password storage.

# Installation

It relies on the following table structures:

    CREATE TABLE `users` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique user ID',
     `username` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'User username',
     `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'User email',
     `encrypted_password` varbinary(60) NOT NULL COMMENT 'Blowfish encryption of the user password',
     `sign_in_count` int(11) unsigned NOT NULL COMMENT 'Increased every time a sign in is made',
     `current_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'A timestamp updated when the user signs in',
     `last_sign_in_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Holds the timestamp of the previous sign in',
     `current_sign_in_ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'The remote IP updated when the user sign in',
     `last_sign_in_ip` int(11) unsigned NOT NULL DEFAULT '0' COMMENT 'Holds the remote IP of the previous sign in',
     `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'When the user account was created',
     `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When the user account was last updated',
     PRIMARY KEY (`id`),
     UNIQUE KEY `index_users_on_email` (`email`),
     UNIQUE KEY `index_users_on_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT 'User account details';

The following fields: `sign_in_count`, `current_sign_in_at`, `last_sign_in_at`, `current_sign_in_ip`, `last_sign_in_ip` are optional, view config file in `config/warden.php` for more details on these columns.


    CREATE TABLE `user_tokens` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique token ID',
     `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user ID the token belongs to',
     `token` varchar(32) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Unique token value',
     `user_agent` varbinary(40) NOT NULL COMMENT 'Hash of the user agent where the token was created',
     `expires` int(10) unsigned NOT NULL COMMENT 'Expiration time of the token',
     `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When the token was created',
     PRIMARY KEY (`id`),
     UNIQUE KEY `index_user_tokens_on_token` (`token`),
     KEY `index_user_tokens_on_user_id` (`user_id`),
     CONSTRAINT `fk_index_user_tokens_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT 'User authentication tokens';

    --

    CREATE TABLE `roles` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique role ID',
     `name` varchar(255) NOT NULL COMMENT 'The name of the role',
     PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT 'Allowed user roles';

    --

    CREATE TABLE `roles_users` (
     `role_id` int(11) unsigned NOT NULL COMMENT 'Unique role ID',
     `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user ID',
     PRIMARY KEY (`role_id`,`user_id`),
     KEY `index_roles_users_on_user_id` (`user_id`),
     CONSTRAINT `fk_index_roles_users_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
     CONSTRAINT `fk_index_roles_users_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT 'User to role mapping';


To change the table names all you have to do is extend any of the Warden models and add
your custom table name and/or properties. For more info see the FuelPHP ORM docs.

For now, check the `classes/warden.php` file for usage examples. This is the main
file that holds the class that does authentication. All examples are in the doc
comments for each method.

### ROADMAP
+ Bundle install task/method for migrations
+ Caching support (APC, Memcached)
+ User and Session Controllers + Views
+ User account confirmation
+ User password resetting
+ Http auth support