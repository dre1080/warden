![Warden](https://github.com/dre1080/warden/raw/gh-pages/assets/img/warden-logo-text.jpg)

# Warden

Latest release: 1.1 ([view changelog](https://github.com/dre1080/warden/blob/master/HISTORY.md))

For docs, see [http://dre1080.github.com/warden](http://dre1080.github.com/warden)

For oAuth feature use the Warden adapter in [Fuel NinjAuth](https://github.com/happyninjas/fuel-ninjauth).


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

    if (($user = Model_User::find(1))) {
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

Checking the current user has permission for a resource:

    try {
        // Can the user create an article?
        Warden::authorize('create', 'Article');
    } catch (Warden_AccessDenied $ex) {
        // Nope, get out
        die($ex->getMessage());
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
    if ($user) {
        try {
            $user->send_reset_password_instructions();
        } catch (Exception $ex) {
            echo sprintf('Oops, something went wrong: %s', $ex->getMessage());
        }
    }

    // Resetting the password
    try {
        $user = Model_User::reset_password_by_token(\Input::get('reset_password_token'), 'new_password');

        if ($user) {
            echo 'Success!';
        } else {
            echo 'Not a valid user';
        }
    } catch (Exception $ex) {
        // something went wrong
        echo sprintf('Oops, something went wrong: %s', $ex->getMessage());
    }


More examples are in the doc comments for each method.

## Contributors

Creator and lead developer: Andrew Wayne.

Special thanks to Jesse O'Brien, Andreo Vieira, Ray Clanan and Drazen Tenzera for contributing code, ideas and testing early versions.

Thanks also to the FuelPHP dev team + many who have contributed code, ideas and issues.
