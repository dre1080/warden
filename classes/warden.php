<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    1.2
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 - 2012 Andrew Wayne
 */
namespace Warden;

use PasswordHash;

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
     * Return a static instance of Warden.
     *
     * @return Warden
     */
    public static function forge($config = array())
    {
        static $instance = null;

        // Load the Warden instance
        if ($instance === null) {
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
     * if (Warden::check()) {
     *     echo "I'm logged in :D";
     * } else {
     *     echo "Failed, I'm NOT logged in :(";
     * }
     * </code>
     *
     * Checking a role has permission:
     * <code>
     * if (Warden::check('admin', 'delete', 'User')) {
     *     echo "User is an admin and has permission to delete other users";
     * } else {
     *     throw new Warden_AccessDenied();
     * }
     * </code>
     *
     * If an action is given and no resource is given, it will assume that the
     * resource has the same name as the role.
     * <code>
     * if (Warden::check('admin', 'delete')) {
     *     echo "User is an admin and has permission to delete other "admin"";
     * } else {
     *     throw new Warden_AccessDenied();
     * }
     * </code>
     *
     * @param mixed $role     The role name (optional)
     * @param mixed $action   The action permission for the role (optional)
     * @param mixed $resource The resource permission for the role (optional)
     *
     * @return bool Returns true on success or false on failure
     */
    public static function check($role = null, $action = null, $resource = null)
    {
        $status = false;

        if (static::logged_in($role) || static::auto_login($role)) {
            $status = true;
        }

        if (!empty($action)) {
            $resource = (empty($resource) ? $role : $resource);
            return $status && static::can($action, $resource);
        }

        return $status;
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
     * @param mixed $role The role name (optional)
     *
     * @return bool Returns true on success or false on failure
     */
    public static function logged_in($role = null)
    {
        return static::driver()->logged_in($role);
    }

    /**
     * Verify user-role access
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
     *
     * // Checking a user has a role permission
     * if (Warden::has_access('admin', 'delete', $user)) {
     *     echo "User is an admin and has deleting rights";
     * } else {
     *     echo "Failed, you're not an admin with deleting rights";
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
        return static::driver()->has_access($role, $user);
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
        static::driver()->set_user($user);
    }

    /**
     * Returns the currently logged in user, or null.
     *
     * <code>
     * if (Warden::check()) {
     *     $current_user = Warden::current_user();
     *     $current_user->username;
     * }
     * </code>
     *
     * @return \Warden\Model_User|null Returns a \Warden\Model_User object on success or null on failure
     */
    public static function current_user()
    {
        return static::driver()->current_user();
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
     *
     * @throws \Warden\Warden_Failure If lockable enabled & attempts exceeded
     */
    public static function authenticate($username_or_email, $password, $remember = false)
    {
        if (empty($username_or_email) || empty($password)) {
            return false;
        }

        return static::driver()->authenticate_user($username_or_email, $password, $remember);
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
        return static::driver()->http_authenticate_user();
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
     * @param mixed $role The role name (optional)
     *
     * @return bool
     */
    public static function auto_login($role = null)
    {
        return static::driver()->auto_login($role);
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
     * @param mixed $username_or_email_or_id
     *
     * @return bool
     */
    public static function force_login($username_or_email_or_id)
    {
        return static::driver()->force_login($username_or_email_or_id);
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
        return static::driver()->logout($destroy);
    }

    /**
     * Check if the user has permission to perform a given action on a resource.
     *
     * <code>
     * if (Warden::can('destroy', $project)) {
     *      echo 'User can destroy';
     * }
     * </code>
     *
     * You can also pass the class instead of an instance (if you don't have one handy).
     * <code>
     * if (Warden::can('destroy', 'Project')) {
     *      echo 'User can destroy';
     * }
     * </code>
     *
     * Multiple actions/resources can be passed through an array. It will return
     * true if one of the supplied actions/resources are found.
     * <code>
     * if (Warden::can('destroy', array('Project', 'Task'))) {
     *      echo 'User can destroy a project/task';
     * }
     *
     * if (Warden::can(array('destroy', 'create'), array('Project', 'Task'))) {
     *      echo 'User can create/destroy a project/task';
     * }
     * </code>
     *
     * You can pass 'all' to match any resource and 'manage' to match any action.
     * <code>
     * if (Warden::can('manage', 'all')) {
     *      echo 'User can do something on one of the resources';
     * }
     *
     * if (Warden::can('manage', 'Project')) {
     *      echo 'User can do something on a Project';
     * }
     * </code>
     *
     * @param mixed $action   The action for the permission.
     * @param mixed $resource The resource for the permission.
     *
     * @return bool
     */
    public static function can($action, $resource)
    {
        return static::driver()->can_user($action, $resource);
    }

    /**
     * Convenience method which works the same as {@link Warden::can()}
     * but returns the opposite value.
     *
     * <code>
     * if (Warden::cannot('create', 'Project') {
     *      die('Unauthorized user');
     * }
     * </code>
     */
    public static function cannot($action, $resource)
    {
        return !static::can($action, $resource);
    }

    /**
     * An alias for {@link Warden::can()} except throws an exception on failure and allows
     * extra options.
     *
     * A 'message' option can be passed to specify a different message. By default it will look
     * for a lang line 'warden.unauthorized.[resource].[action]' first.
     * <code>
     * Warden::authorize('read', $article, array('message' => "Not authorized to read {$article->name}"));
     * </code>
     *
     * @param mixed $action   The action for the permission.
     * @param mixed $resource The resource for the permission.
     * @param array $options
     *
     * @see {@link \Warden\Warden_AccessDenied}
     *
     * @throws \Warden\Warden_AccessDenied If the current user cannot perform the given action
     */
    public static function authorize($action, $resource, array $options = array())
    {
        $message = null;
        if (isset($options['message'])) {
            $message = $options['message'];
        }

        if (static::cannot($action, $resource)) {
            $message || $message = __("warden.unauthorized.{$resource}.{$action}");
            throw new Warden_AccessDenied($message, $action, $resource);
        }
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
     * Executed every time a user is authorized.
     *
     * <code>
     * Warden::after_authorization(function($user) {
     *      logger(\Fuel::L_INFO, 'User '.$user->id.' was successfully authorized to access '.\Input::server('REQUEST_URI'));
     * });
     *
     * // OR
     *
     * Warden::after_authorization('Myclass::method');
     * </code>
     *
     * @param mixed $callback The callable function to execute
     *
     * @uses \Fuel\Core\Event::register()
     */
    public static function after_authorization($callback)
    {
        \Event::register('warden_after_authorization', $callback);
    }

    /**
     * Encrypts a user password using the Blowfish algo
     *
     * @param string $password The plaintext password
     *
     * @return string The hashed password string
     */
    public static function encrypt_password($password)
    {
        $hasher = new PasswordHash(8, false);
        return $hasher->HashPassword($password);
    }

    /**
     * Checks that a submitted password matches the users password
     *
     * @param \Warden\Model_User $user
     * @param string             $submitted_password
     *
     * @return bool
     */
    public static function has_password(Model_User $user, $submitted_password)
    {
        if (empty($user->encrypted_password) || empty($submitted_password)) {
            return false;
        }

        $hasher = new PasswordHash(8, false);
        return $hasher->CheckPassword($submitted_password, $user->encrypted_password);
    }

    /**
     * Generate a unique friendly string to be used as a token.
     *
     * @return string
     */
    public static function generate_token()
    {
        $token = join(':', array(\Str::random('alnum', 15), time()));
        return str_replace(
	        array('+', '/', '=', 'l', 'I', 'O', '0'), 
	        array('p', 'q', 'r', 's', 'x', 'y', 'z'), 
	        base64_encode($token)
		);
    }

    /**
     * Fetches the warden driver instance
     *
     * @return \Warden\Warden_Driver
     */
    protected static function driver()
    {
        return static::forge()->driver;
    }
}