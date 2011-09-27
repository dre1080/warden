# Warden

A user database authorization package for FuelPHP.
Handles user login and logout, as well as secure password hashing.

This package requires the FuelPHP ORM package.

## Installation

It relies on the following table structures:

    CREATE TABLE `users` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique user ID',
     `username` varchar(32) NOT NULL COMMENT 'User username',
     `email` varchar(255) NOT NULL COMMENT 'User email',
     `encrypted_password` varbinary(60) NOT NULL COMMENT 'Encryption of the user password',
     `authentication_token` varbinary(60) DEFAULT NULL COMMENT 'Session authentication token',
     `remember_token` varbinary(60) DEFAULT NULL COMMENT 'Cookie remember token',
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
    ) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='User account details';

The following fields: `sign_in_count`, `current_sign_in_at`, `last_sign_in_at`, `current_sign_in_ip`, `last_sign_in_ip` are optional, view config file in `config/warden.php` for more details on these columns.


    CREATE TABLE `roles` (
     `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique role ID',
     `name` varchar(40) NOT NULL COMMENT 'The name of the role',
     `description` varchar(255) NOT NULL COMMENT 'The description of the role',
     PRIMARY KEY (`id`),
     UNIQUE KEY `index_roles_on_name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'Allowed user roles';

    --

    CREATE TABLE `roles_users` (
     `role_id` int(11) unsigned NOT NULL COMMENT 'Unique role ID',
     `user_id` int(11) unsigned NOT NULL COMMENT 'Unique user ID',
     PRIMARY KEY (`role_id`,`user_id`),
     KEY `index_roles_users_on_user_id` (`user_id`),
     CONSTRAINT `fk_index_roles_users_on_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
     CONSTRAINT `fk_index_roles_users_on_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT 'User to role mapping';


To change the table names all you have to do is extend any of the Warden models and add
your custom table name and/or properties. All you have to do is create your roles in the roles table and assign roles to users.
For more info see the FuelPHP ORM docs.

## Configuration
For now, only config options are:

+ (int)  lifetime : The remember-me cookie lifetime, in seconds
+ (bool) trackable: Set to track information about user sign ins

## Usage

Check for validated login:

    if (Warden::check()) {
        echo "I'm logged in :D";
    } else {
        echo "Failed, I'm NOT logged in :(";
    }

Getting the currently logged in user:

    if (Warden::check()) {
        $current_user = Warden::current_user();
        echo $current_user->username;
    }

Checking for a specific role:

    if (Warden::logged_in('admin')) {
        echo "Current user logged in as an admin";
    }

    $user = Model_User::find(2);
    if (Warden::has_access(array('editor', 'moderator'), $user)) {
        echo "Hey, editor - moderator";
    } else {
        echo "Fail!";
    }

Log in a user by using a username or email and plain-text password:

    if (Input::method() === 'POST') {
        if (Warden::authenticate_user(Input::post('username_or_email'), Input::post('password'))) {
            Session::set_flash('success', 'Logged in successfully');
        } else {
            Session::set_flash('error', 'Username or password invalid');
        }
        Response::redirect();
    }

Log out a user by removing the related session variables:

    if (Warden::logout()) {
         echo "I'm logged out";
    }

More examples are in the doc comments for each method.

### ROADMAP
+ Bundle install task/method for migrations
+ Caching support (APC, Memcached)
+ User and Session Controllers + Views
+ User account confirmation
+ User password resetting
+ Http auth support