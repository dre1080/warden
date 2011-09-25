<?php
/**
 * The Warden: User authorization library for fuelphp.
 * Handles user login and logout, as well as secure password hashing.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.1
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * Warden_Driver
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden_Driver
{
    // Configuration
    protected $config;

    protected $user;

    /**
     * Creates a new driver instance, loading the session and storing config.
     *
     * @param  array $config configuration
     *
     * @return void
     */
    public function __construct(array $config)
    {
        // Store config
        $this->config = $config;
    }

    /**
     * Checks if a session is active.
     *
     * @param  string  $role role name
     *
     * @return boolean
     */
    public function logged_in($role = null)
    {
        $auth_token = \Session::get('warden.authenticity_token');

        $token = $username_hash = '';
        @list($token, $username_hash) = explode(':', $auth_token);

        if (!empty($token) &&
            !empty($username_hash) &&
            (is_null($this->user) || md5($this->user->username) != $username_hash))
        {
            $this->user = null;

            $user_token = Model_UserToken::find('first', array(
                'where' => array('token' => $token)
            ));

            if (!is_null($user_token) &&
                isset($user_token->user) &&
                md5($user_token->user->username) == $username_hash)
            {
                $this->user = $user_token->user;
            }
        }

        return $this->has_access($role, $this->user);
    }

    /**
     * Verify Acl access
     *
     * @param mixed              $role The role name to check
     * @param \Warden\Model_User $user The user to check against, if no user is given (null)
     *                                 it will check against the currently logged in user
     *
     * @return bool
     */
    public function has_access($role, Model_User $user)
    {
        $acl_user = $user;
        $status   = !!$acl_user;

        if (!empty($role) && $status) {
            // If role is an array
            if (is_array($role)) {
                // Check each role
                foreach ($role as $r) {
                    if (!is_object($r)) {
                        $r = Model_Role::find('first', array(
                            'where' => array('name' => $r)
                        ));
                    }

                    // If the user doesn't have the role
                    if (!in_array($r, $acl_user->roles)) {
                        // Set the status false and get outta here
                        $status = false;
                        break;
                    }
                }
            } else {
                // Else just check the one supplied roles
                if (!is_object($role)) {
                    // Load the role
                    $role = Model_Role::find('first', array(
                        'where' => array('name' => $role)
                    ));
                }

                // Check that the user has the given role
                $status = in_array($role, $acl_user->roles);
            }
        }

        return $status;
    }

    /**
     * Logs a user in.
     *
     * @param   string  $user     username
     * @param   string  $password password
     * @param   boolean $remember enable auto-login
     *
     * @return  boolean
     */
    public function authenticate_user($username_or_email, $password, $remember)
    {
        if (($user = Model_User::authenticate($username_or_email)) &&
            Warden::instance()->has_password($user, $password))
        {
            if ($remember === true) {
                // Create a new autologin token
                $token = new \Model_UserToken;

                // Set token data
                $token->user_id = $user->id;
                $token->expires = time() + $this->config['lifetime'];
                $token->save();

                // Set the autologin cookie
                \Cookie::set('warden.remember_user_token',
                             $token->token,
                             $this->config['lifetime']);
            }

            $this->complete_login($user);

            return true;
        }

        // Login failed
        return false;
    }

    /**
     * Gets the currently logged in user from the session.
     * Returns FALSE if no user is currently logged in.
     *
     * @return  mixed
     */
    public function current_user()
    {
        if ($this->logged_in(null)) {
            return $this->user;
        }

        return false;
    }

    /**
     * Forces a user to be logged in, without specifying a password.
     *
     * @param   mixed    username
     * @return  bool
     */
    public function force_login($username_or_email)
    {
        $user = Model_User::authenticate($username_or_email);

        // Mark the session as forced, to prevent users from changing account information
        \Session::set('warden.auth_forced', true);

        // Run the standard completion
        return $user && $this->complete_login($user);
    }

    /**
     * Logs a user in, based on stored credentials, typically cookies.
     * Not supported by default.
     *
     * @return  boolean
     */
    public function auto_login()
    {
        if (($token = \Cookie::get('warden.remember_user_token'))) {
            // Load the token and user
            $token = Model_UserToken::find('first', array(
                'where' => array('token' => $token)
            ));

            if ($token && $token->user) {
                if ($token->user_agent === sha1(\Input::server('HTTP_USER_AGENT'))) {
                    // Save the token to create a new unique token
                    $token->save();

                    // Set the new token
                    \Cookie::set('warden.remember_user_token',
                                 $token->token,
                                 $token->expires - time());

                    // Complete the login with the found data
                    $this->complete_login($token->user);

                    // Automatic login was successful
                    return true;
                }

                // Token is invalid
                $token->delete();
            }
        }

        return false;
    }

    /**
     * Log a user out.
     *
     * @param   boolean  completely destroy the session
     *
     * @return  boolean
     */
    public function logout($destroy)
    {
        $this->user = null;
        \Session::delete('warden.authenticity_token');

        if (\Cookie::get('warden.remember_user_token')) {
            // Delete the autologin cookie to prevent re-login
            \Cookie::delete('warden.remember_user_token');
        }

        if ($destroy === true) {
            // Destroy the session completely
           \Session::destroy();
        } else {
            // Regenerate session_id
            \Session::instance()->rotate();
        }

        // Double check
        return !$this->logged_in(null);
    }

    /**
     * Completes a login by assigning the user to the session key.
     *
     * @param \Warden\Model_User $user
     *
     * @return bool
     */
    protected function complete_login(Model_User $user)
    {
        // Create a new authentication token
        $authtoken = new Model_UserToken;

        // Set token data
        $authtoken->user_id = $user->id;
        $authtoken->expires = time() + \Config::get('session.expiration_time', 86400);
        $authtoken->save();

        $user->update_tracked_fields();

        \Session::set('warden.authenticity_token', $authtoken->token.':'.md5($user->username));
        \Session::instance()->rotate();

        $this->user = $user;

        return true;
    }
}