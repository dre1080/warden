# Warden

A user database authorization & authentication package for FuelPHP.

Features:

+ Secure BCrypt password hashing
+ User login
+ User logout
+ User ACL
+ Remember-me functionality
+ Reset-password functionality
+ User confirmation functionality
+ Http authentication
+ oAuth support
and many more to come

## Why use BCrypt?

[How To Safely Store A Password] (http://codahale.com/how-to-safely-store-a-password)

[Use BCrypt Fool!] (http://yorickpeterse.com/articles/use-bcrypt-fool)

## Requirements

Packages:

+ [Orm] (https://github.com/fuel/orm)

If you're planning to use the `omniauthable` feature (OAuth), then the additional
packages are needed:

+ [Fuel OAuth]  (https://github.com/fuel-packages/fuel-oauth)
+ [Fuel OAuth2] (https://github.com/fuel-packages/fuel-oauth2)

The `omniauthable` feature uses [Fuel NinjAuth] (https://github.com/philsturgeon/fuel-ninjauth) internally, so you won't need to
include it.

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
+ (bool) `profilable`: Set to add support for user profiles. (default: false)
+ (bool) `trackable`: Set to track information about user sign ins. (default: false)
+ `recoverable`: Takes care of resetting the user password.
    + (bool) `in_use`: Set to true, to enable (default: false)
    + (string) `reset_password_within`: The limit time within which the reset password token is valid. (default: '+1 week')
+ `confirmable`: verify if an account is already confirmed to sign in.
    + (bool) `in_use`: Set to true, to enable (default: false)
    + (string) `confirm_within`: The limit time within which the confirmation token is valid. (default: '+1 week')
+ `http_authenticatable`: provides basic and digest authentication.
    + (bool) `in_use`: Set to true, to enable (default: false)
    + (string) `method`: The type of Http method to use for authentication. (default: 'digest')
    + (string) `realm`: (default: 'Protected by Warden')
    + (array) `users`: The users to permit.
    + (string) `failure_text`: The message to display on failure.
+ `omniauthable`: provides OAuth support.
    + (bool) `in_use`: Set to true, to enable (default: false)
    + (array) `urls`: The urls to use for omniauth authentication.
    + (array) `providers`: The providers that are available.
    + (bool) `link_multiple`: Whether multiple providers can be attached to one user account.

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

Explicitly setting the current user:

    if (($user = Model_User:find(1))) {
        Warden::set_user($user);
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
        if (Warden::authenticate(Input::post('username_or_email'), Input::post('password'))) {
            Session::set_flash('success', 'Logged in successfully');
        } else {
            Session::set_flash('error', 'Username or password invalid');
        }
        Response::redirect();
    }

Log in a user using a http based authentication method:

    if (($user_array = Warden::http_authenticate())) {
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
    } catch (Warden_Failure $ex) {
        // reset password token has expired
        echo $ex->getMessage();
    }


More examples are in the doc comments for each method.

## Callbacks

There are 3 callbacks at various points in the authentication cycle available. Namely:

+ `after_set_user`
+ `after_authentication`
+ `before_logout`

For each callback Warden will send the current user object as an argument.

### after_set_user

This is called every time the user is set. The user is set:

+ when the user is initially authenticated.
+ when the user is set via `Warden::set_user()`.

```php
Warden::after_set_user(function($user) {
    if (!$user->is_confirmed()) {
        Warden::logout();
    }
});
```

### after_authentication

This is called every time the user is authenticated.

    Warden::after_authentication(function($user) {
        $user->last_login = time();
    });

### before_logout

This is called before each user is logged out.

    Warden::before_logout(function($user) {
        logger(\Fuel::L_INFO, 'User '.$user->id.' logging out', 'Warden::before_logout');
    });