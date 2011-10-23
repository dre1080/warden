<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.8.6
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade warden without losing your custom config.
 */

/**
 * Warden library configuration.
 *
 * @package    Warden
 * @subpackage Warden
 */
return array(
    /**
     * Set the remember-me cookie lifetime, in seconds. The default
     * lifetime is two weeks.
     *
     * (integer)
     */
    'lifetime' => 1209600,

    /**
     * Set the default role to assign a newly created user, it must already exist,
     * Set to `null` to disable. This role is also used by Warden's driver as
     * a default when checking if a user is logged in.
     *
     * (string|null)
     */
    'default_role' => null,

    /**
     * Adds user profile support
     *
     * Requires at least, a `profiles` table with an `id` and `user_id` column
     */
    'profilable' => false,

    /**
     * Set to track information about user sign ins.
     * It tracks and requires the following columns:
     *
     *   - `sign_in_count`      - Increased every time a sign in is made (by form, openid, oauth)
     *   - `current_sign_in_at` - A timestamp updated when the user signs in
     *   - `last_sign_in_at`    - Holds the timestamp of the previous sign in
     *   - `current_sign_in_ip` - The remote ip updated when the user sign in
     *   - `last_sign_in_at`    - Holds the remote ip of the previous sign in
     *
     * (bool)
     */
    'trackable' => false,

    /**
     * Recoverable takes care of resetting the user password.
     *
     * It requires the following columns:
     *
     *   - `reset_password_token`
     *   - `reset_password_sent_at`
     */
    'recoverable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => false,

        /**
         * The limit time within which the reset password token is valid.
         * Must always be a valid php date/time value.
         * Default is '+1 week'.
         *
         * @see http://www.php.net/manual/en/datetime.formats.php
         *
         * (string)
         */
        'reset_password_within' => '+1 week'
    ),

    /**
     * Confirmable is responsible to verify if an account is already confirmed to
     * sign in
     */
    'confirmable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => false,

        /**
         * The limit time within which the confirmation token is valid.
         * Must always be a valid php date/time value.
         * Default is '+1 week'.
         *
         * @see http://www.php.net/manual/en/datetime.formats.php
         *
         * (string)
         */
        'confirm_within' => '+1 week'
    ),

    /**
     * Lockable handles blocking a user access after a certain number of attempts.
     * It accepts two different strategies to unlock a user after it's
     * blocked: email and time. The former will send an email to the user when
     * the lock happens, containing a link to unlock it's account. The second
     * will unlock the user automatically after some configured time (eg. +2 hours).
     * It's also possible to setup lockable to use both email and time strategies.
     */
    'lockable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => false,

        /**
         * How many attempts should be accepted before blocking the user.
         *
         * (integer)
         */
        'maximum_attempts' => 10,

        /**
         * Lock the user account by (eg. failed_attempts, `trackable's` sign_in_count)
         * or null for none. This can be any integer column name in the users table.
         *
         * (string)
         */
        'lock_strategy' => 'sign_in_count',

        /**
         * Unlock the user account by time, email, both or none.
         *
         * (string)
         */
        'unlock_strategy' => 'both',

        /**
         * The time you want to lock the user after to lock happens.
         * Only available when unlock_strategy is time or both.
         *
         * @see http://www.php.net/manual/en/datetime.formats.php
         *
         * (string)
         */
        'unlock_in' => '+1 week',
    ),

    /**
     * Http authenticatable provides basic and digest authentication
     * based on the HTTP protocol.
     */
    'http_authenticatable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => false,

        /**
         * The type of Http method to use for authentication.
         * Default is digest.
         * To use Basic authentication, set to basic.
         *
         * (string)
         */
        'method' => 'digest',

        /**
         * Default is 'Protected by Warden'
         */
        'realm'  => 'Protected by Warden',

        /**
         * The users to permit.
         *
         * (array) key => value pair of username => password
         */
        'users' => array(
            //'warden' => 'warden'
        ),

        /**
         * The message to display on failure
         *
         * (string)
         */
        'failure_text' => '<h1>401 Unauthorized</h1>'
    ),

    /**
     * Adds OAuth support
     */
    'omniauthable' => array(
        /**
         * Set to false, to disable
         *
         * (bool)
         */
        'in_use'   => true,

        /**
         * The urls for omniauth
         *
         * (array)
         */
        'urls' => array(
            'registration' => 'auth/register',
            'login'        => 'auth/login',
            'callback'     => 'auth/callback',
            'registered'   => 'auth/account',
            'logged_in'    => 'auth/account',
        ),

        /**
         * The providers that are available.
         *
         * Providers such as Facebook, Twitter, etc all use different Strategies such as oAuth, oAuth2, etc.
         * oAuth takes a key and a secret, oAuth2 takes a (client) id and a secret, optionally a scope.
         *
         * (array)
         */
        'providers' => array(
            'facebook' => array(
                'id' => '',
                'secret' => '',
                'scope' => 'email, offline_access',
            ),

            'twitter' => array(
                'key' => 'VDX6hqBq0Dstgi2hooCeg',
                'secret' => 'oRUvV7qY0GCUQMnWH41OJPDYKTSioab8z0Qt0FJg',
                'client_options' => array(
                    'ssl' => array(
                        'verify' => false
                    )
                )
            ),

            'dropbox' => array(
                'key' => '',
                'secret' => '',
            ),

            'linkedin' => array(
                'key' => '',
                'secret' => '',
            ),

            'flickr' => array(
                'key' => '',
                'secret' => '',
            ),

            'youtube' => array(
                'key' => '',
                'scope' => 'http://gdata.youtube.com',
            ),
        ),

        /**
         * Whether multiple providers can be attached to one user account
         *
         * (bool)
         */
        'link_multiple' => true,
    )
);