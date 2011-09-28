# Warden

A user database authorization package for FuelPHP.

Features:

+ Secure BCrypt password hashing
+ User login
+ User logout
+ User ACL
+ Remember-me functionality
+ Reset-password functionality
+ Http authentication
and many more to come

## Why use BCrypt?

http://codahale.com/how-to-safely-store-a-password
http://yorickpeterse.com/articles/use-bcrypt-fool

## Requirements

Packages:

+ Orm (https://github.com/fuel/orm)

## Installation

This package follows standard installation rules, which can be found within the [FuelPHP Documentation for Packages] (http://fuelphp.com/docs/general/packages.html)

You have two options you can autoload the package in your `app/config.php`:

    'always_load'	=> array(
        'packages'	=> array(
            array('warden')
        ),
    ) //...

or load it manually like so

    Fuel::add_package('warden');

View `config/install.sql` for table structures.
Create your roles in the `roles` table to assign roles to users.

## Configuration
For now, only config options are:

+ (int) `lifetime`: The remember-me cookie lifetime, in seconds. (default: 1209600)
+ (string) `default_role`: The default role to assign a newly created user, it must already exist. (default: null)
+ (bool) `trackable`: Set to track information about user sign ins. (default: true)
+ `recoverable`: Takes care of resetting the user password.
    + (bool) `in_use`: Set to false, to disable (default: true)
    + (string) `reset_password_within`: The limit time within which the reset password token is valid. (default: '+1 week')
+ `http_authenticatable`: provides basic and digest authentication.
    + (bool) `in_use`: Set to false, to disable (default: true)
    + (string) `method`: The type of Http method to use for authentication. (default: 'digest')
    + (string) `realm`: (default: 'Protected by Warden')
    + (array) `users`: The users to permit.
    + (string) `failure_text`: The message to display on failure.

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

Log in a user using a http based authentication method:

    if (($user_array = Warden::http_authenticate_user())) {
        echo "Welcome {$user_array['username']}";
    }

Log out a user by removing the related session variables:

    if (Warden::logout()) {
         echo "I'm logged out";
    }

Resetting a user's password

    // Sending the password token
    $user = Model_User::find('first', array('where' => array('email' => 'myemail@warden.net')));
    if (!is_null($user)) {
        if ($user->generate_reset_password_token()) {
            $token = $user->reset_password_token;
            // mail it to the user with a link
            // ...
        }
    }

    // Resetting the password
    try {
        $user = Model_User::reset_password_by_token(\Input::get('reset_password_token'), 'new_password');

        if (!is_null($user)) {
            echo 'Success!';
        } else {
            echo 'Not a valid user';
        }
    } catch (\Orm\ValidationFailed $ex) {
        // reset password token has expired
        echo $ex->getMessage();
    }


More examples are in the doc comments for each method.