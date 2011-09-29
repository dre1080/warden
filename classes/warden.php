<?php
/**
 * The Warden: User authorization library for FuelPHP.
 * Handles user login and logout, as well as secure password hashing.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.6
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * Warden
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden
{
    /**
     * Warden driver
     *
     * @var \Warden\Warden_Driver
     */
    public $driver;

    /**
     * Prevent instantiation
     */
    final private function __construct() {}

    /**
     * Loads configuration options.
     */
    public static function _init()
    {
        static::authenticated();
    }

    /**
     * Create an instance of Warden. An alias for Warden::instance()
     *
     * @see Warden::instance()
     *
     * @return Warden
     */
    public static function forge($config = array())
    {
        return static::instance($config);
    }

    /**
     * Return a static instance of Warden.
     *
     * @return Warden
     */
    public static function instance($config = array())
    {
        static $instance = null;

        // Load the Warden instance
        if (!$instance) {
            $config = array_merge(\Config::get('warden', array()), $config);
            $instance = new static;
            $instance->driver = new Warden_Driver($config);
        }

        return $instance;
    }

    /**
     * Checks for validated login. This checks for current session as well as
     * a remember me cookie.
     * Whereas {@link Warden::logged_in()} only checks the current session login.
     *
     * <code>
     * if (Warden::authenticated()) {
     *     echo "I'm logged in :D";
     * } else {
     *     echo "Failed, I'm NOT logged in :(";
     * }
     * </code>
     *
     * @return bool Returns true on success or false on failure
     */
    public static function authenticated()
    {
        if (static::logged_in()) {
            return true;
        }

        return static::auto_login();
    }

    /**
     * Check if there is an active session. Optionally allows checking for a
     * specific role.
     *
     * <code>
     * if (Warden::logged_in()) {
     *     echo "I'm logged in";
     * }
     *
     * if (Warden::logged_in('admin')) {
     *     echo "I'm logged in as an admin";
     * }
     * </code>
     *
     * @param string $role The role name (optional)
     *
     * @return bool Returns true on success or false on failure
     */
    public static function logged_in($role = null)
    {
        return static::instance()->driver->logged_in($role);
    }

    /**
     * Verify Acl access
     *
     * <code>
     * if (Warden::has_access('admin')) {
     *     echo "Hey, admin";
     * } else {
     *     echo "Halt! You are not an admin!";
     * }
     *
     * // OR
     *
     * $user = Model_User::find(2);
     * if (Warden::has_access(array('editor', 'moderator'), $user)) {
     *     echo "Hey, editor - moderator";
     * } else {
     *     echo "Fail!";
     * }
     * </code>
     *
     * @param mixed              $role The role name(s) to check
     * @param \Warden\Model_User $user The user to check against, if no user is given (null)
     *                                 it will check against the currently logged in user
     *
     * @return bool Returns true on success or false on failure
     */
    public static function has_access($role, Model_User $user = null)
    {
        return static::instance()->driver->has_access($role, $user);
    }

    /**
     * Explicitly set the current user.
     *
     * <code>
     * if (($user = Model_User:find(1))) {
     *      Warden::set_user($user);
     * }
     * </code>
     *
     * @param \Warden\Model_User The user to set
     */
    public static function set_user(Model_User $user)
    {
        static::instance()->driver->set_user($user);
    }

    /**
     * Returns the currently logged in user, or null.
     *
     * <code>
     * if (Warden::authenticated()) {
     *     $current_user = Warden::current_user();
     *     $current_user->username;
     * }
     * </code>
     *
     * @return \Warden\Model_User|null Returns a \Warden\Model_User object on success or null on failure
     */
    public static function current_user()
    {
        return static::instance()->driver->current_user();
    }

    /**
     * Attempt to log in a user by using a username or email and plain-text password.
     *
     * <code>
     * if (Input::method() === 'POST') {
     *     if (Warden::authenticate(Input::post('username_or_email'), Input::post('password'))) {
     *         Session::set_flash('success', 'Logged in successfully');
     *     } else {
     *         Session::set_flash('error', 'Username or password invalid');
     *     }
     *
     *     Response::redirect();
     * }
     * </code>
     *
     * @param string $username_or_email The username or email to log in
     * @param string $password          The password to check against
     * @param bool   $remember          Whether to set remember-me cookie
     *
     * @return bool Returns true on success or false on failure
     */
    public static function authenticate($username_or_email, $password, $remember = false)
    {
        if (empty($username_or_email) || empty($password)) {
            return false;
        }

        return static::instance()->driver->authenticate_user($username_or_email, $password, $remember);
    }

    /**
     * Attempt to log in a user by using an http based authentication method.
     *
     * <code>
     * if (($user = Warden::http_authenticate())) {
     *      Session::set_flash('success', "Logged in as {$user['username']}");
     * }
     * </code>
     *
     * @see \Warden\Warden_Driver::http_authenticate_user()
     *
     * @return array A key/value array of the username => value and password => value
     */
    public static function http_authenticate()
    {
        return static::instance()->driver->http_authenticate_user();
    }

    /**
     * Attempt to automatically log a user in.
     *
     * <code>
     * if (Warden::auto_login()) {
     *     $remembered_user = Warden::current_user();
     *     echo $remembered_user->username.' was retrieved from a remember-me cookie';
     * }
     * </code>
     *
     * @return bool
     */
    public static function auto_login()
    {
        return static::instance()->driver->auto_login();
    }

    /**
     * Force a login for a specific username.
     *
     * <code>
     * if (Warden::force_login('username')) {
     *     $forced_user = Warden::current_user();
     *     echo $forced_user->username.' was forced to login only with a username';
     * }
     * </code>
     *
     * @param mixed $username The user's username
     *
     * @return bool
     */
    public static function force_login($username)
    {
        return static::instance()->driver->force_login($username);
    }

    /**
     * Log out a user by removing the related session variables.
     *
     * <code>
     * if (Warden::logout()) {
     *     echo "I'm logged out";
     * }
     * </code>
     *
     * @param bool $destroy completely destroy the session
     *
     * @return bool
     */
    public static function logout($destroy = false)
    {
        return static::instance()->driver->logout($destroy);
    }

    /**
     * This is called every time the user is set.
     * The user is set:
     *
     *      - when the user is initially authenticated
     *      - when the user is set via Warden::set_user()
     *
     * <code>
     * Warden::after_set_user(function($user) {
     *      if (!$user->is_active) {
     *          Warden::logout();
     *      }
     * });
     *
     * // OR
     *
     * Warden::after_set_user('Myclass::method');
     * </code>
     *
     * @param mixed $callback The callable function to execute
     *
     * @uses \Fuel\Core\Event::register()
     */
    public static function after_set_user($callback)
    {
        \Event::register('warden_after_set_user', $callback);
    }

    /**
     * Executed every time the user is authenticated.
     *
     * <code>
     * Warden::after_authentication(function($user) {
     *      $user->last_login = time();
     * });
     *
     * // OR
     *
     * Warden::after_authentication('Myclass::method');
     * </code>
     *
     * @param mixed $callback The callable function to execute
     *
     * @uses \Fuel\Core\Event::register()
     */
    public static function after_authentication($callback)
    {
        \Event::register('warden_after_authentication', $callback);
    }

    /**
     * Executed before each user is logged out.
     *
     * <code>
     * Warden::before_logout(function($user) {
     *      logger(\Fuel::L_INFO, 'User '.$user->id.' logging out', 'Warden::before_logout');
     * });
     *
     * // OR
     *
     * Warden::before_logout('Myclass::method');
     * </code>
     *
     * @param mixed $callback The callable function to execute
     *
     * @uses \Fuel\Core\Event::register()
     */
    public static function before_logout($callback)
    {
        \Event::register('warden_before_logout', $callback);
    }

    /**
     * Encrypts a user password using the Blowfish algo
     *
     * @param string $password The plaintext password
     *
     * @return string The hashed password string
     */
    public function encrypt_password($password)
    {
        static $hasher = null;
        $hasher || $hasher = new \CryptLib\Password\Implementation\Blowfish();
        return $hasher->create($password);
    }

    /**
     * Checks that a submitted password matches the users password
     *
     * @param \Warden\Model_User $user
     * @param string             $submitted_password
     *
     * @return bool
     */
    public function has_password(Model_User $user, $submitted_password)
    {
        if (empty($user->encrypted_password) || empty($submitted_password)) {
            return false;
        }

        $hasher = \CryptLib\Password\Implementation\Blowfish::loadFromHash($user->encrypted_password);
        return $hasher->verify($submitted_password, $user->encrypted_password);
    }

    /**
     * Generate a unique friendly string to be used as a token.
     *
     * @return string
     */
    public static function generate_token()
    {
        $token = \Str::random('unique').'_'.time();
        return str_replace(array('+', '/', '='), array('x', 'y', 'z'), base64_encode($token));
    }
}