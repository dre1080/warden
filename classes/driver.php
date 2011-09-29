<?php
/**
 * The Warden: User authorization library for fuelphp.
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
 * Warden_Driver
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden_Driver
{
    /**
     * Configuration
     *
     * @var array
     */
    protected $config;

    /**
     * Current logged in user
     *
     * @var \Warden\Model_User
     */
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
     * @param  string $role The role name (optional)
     *
     * @return bool
     */
    public function logged_in($role = null)
    {
        $auth_token = \Session::get('authenticity_token');

        if (!empty($auth_token) &&
            (is_null($this->user) || $this->user->authentication_token != $auth_token))
        {
            $this->user = null;

            $user = Model_User::find('first', array(
                'where' => array('authentication_token' => $auth_token)
            ));

            if (!is_null($user)) {
                $this->set_user($user);
            }
        }

        return $this->has_access($role ? $role : $this->config['default_role'], $this->user);
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
    public function has_access($role, Model_User $user = null)
    {
        $status   = !!$user;

        if (!empty($role) && $status) {
            $role = (is_array($role) ? $role : array($role));

            $diff = array_udiff($role, $user->roles, function ($r, $ur) {
                // check for a role object
                $r = (is_object($r) ? $r->name : $r);
                // compare each given role against the user's roles
                return $r != $ur->name;
            });

            // empty = true
            $status = empty($diff);
        }

        return $status;
    }

    /**
     * Logs a user in.
     *
     * @param string $username_or_email
     * @param string $password
     * @param bool   $remember
     *
     * @return bool
     */
    public function authenticate_user($username_or_email, $password, $remember)
    {
        if (($user = Model_User::authenticate($username_or_email)) &&
            Warden::instance()->has_password($user, $password))
        {
            if ($remember === true) {
                // Set token data
                $user->remember_token = Warden::generate_token();

                // Set the remember-me cookie
                \Cookie::set('remember_token',
                             $user->remember_token,
                             $this->config['lifetime'],
                             null, null, null, true);
            }

            $this->complete_login($user);

            return true;
        }

        // Login failed
        return false;
    }

    /**
     * Logs a user in using Http based authentication
     *
     * @see \Warden\Warden_Driver::_http_basic()
     * @see \Warden\Warden_Driver::_http_digest()
     *
     * @return array
     */
    public function http_authenticate_user()
    {
        if (!$this->config['http_authenticatable']['in_use']) {
            return false;
        }

        $method = "_http_{$this->config['http_authenticatable']['method']}";

        $body = <<<BODY
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
     "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
    <html>
        <head>
            <title>Error</title>
            <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
        </head>
        <body>
            {$this->config['http_authenticatable']['failure_text']}
        </body>
    </html>
BODY;
        return $this->{$method}(new \Response($body, 401));
    }

    /**
     * Sets the currently logged in user.
     *
     * @param \Warden\Model_User The user to set
     */
    public function set_user(Model_User $user)
    {
        $this->user = $user;
        $this->_run_event('after_set_user');
    }

    /**
     * Gets the currently logged in user from the session.
     *
     * @return mixed Returns FALSE if no user is currently logged in, otherwise
     *               returns a \Warden\Model_User object.
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
     * @param string $username_or_email
     *
     * @return bool
     */
    public function force_login($username_or_email)
    {
        $user = Model_User::authenticate($username_or_email);
        return $user && $this->complete_login($user);
    }

    /**
     * Logs a user in, based on stored credentials, typically cookies.
     *
     * @param string $role The role name (optional)
     *
     * @return bool
     */
    public function auto_login($role = null)
    {
        if (($token = \Cookie::get('remember_token'))) {
            $user = Model_User::find('first', array(
                'where' => array('remember_token' => $token)
            ));

            if (!is_null($user)) {
                if ($this->has_access($role, $user)) {
                    // Complete the login with the found data
                    $this->complete_login($user);

                    // Automatic login was successful
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Log a user out.
     *
     * @param bool $destroy Whether to completely destroy the session
     *
     * @return bool
     */
    public function logout($destroy)
    {
        $this->_run_event('before_logout');

        $this->user = null;

        // Delete the session identifier for the user
        \Session::delete('authenticity_token');

        // Delete http server variables
        if ($this->config['http_authenticatable']['in_use']) {
            unset($_SERVER['PHP_AUTH_USER'],
                  $_SERVER['PHP_AUTH_PW'],
                  $_SERVER['PHP_AUTH_DIGEST']);
        }

        if (\Cookie::get('remember_token')) {
            // Delete the remember-me cookie to prevent re-login
            \Cookie::delete('remember_token');
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
        // Create and set new authentication token
        $user->authentication_token = Warden::generate_token();

        if ($this->config['trackable'] === true) {
            $user->update_tracked_fields();
        } else {
            $user->save(false);
        }

        \Session::set('authenticity_token', $user->authentication_token);
        \Session::instance()->rotate();

        $this->set_user($user);

        $this->_run_event('after_authentication');

        return true;
    }

    /**
     * Handler for HTTP Basic Authentication
     *
     * @return array A key/value array of the username => value and password => value
     */
    private function _http_basic(\Response $response)
    {
        $users = $this->config['http_authenticatable']['users'];
        $username = \Input::server('PHP_AUTH_USER');
        $password = \Input::server('PHP_AUTH_PW');

        if (!isset($users[$username]) || $users[$username] !== $password) {
            $response->set_header('WWW-Authenticate', "Basic realm=\"{$this->config['http_authenticatable']['realm']}\"");
            $response->send(true);
            exit;
        }

        $this->_run_event('after_authentication');

        return array('username' => $username, 'password' => $password);
    }

    /**
     * Handler for HTTP Digest Authentication
     *
     * @return array A key/value array of the username => value and password => value
     */
    private function _http_digest(\Response $response)
    {
        $realm = $this->config['http_authenticatable']['realm'];

        $data = array(
            'username' => null, 'nonce' => null, 'nc' => null,
            'cnonce'   => null, 'qop'  => null, 'uri' => null,
            'response' => null
        );

        foreach (explode(',', \Input::server('PHP_AUTH_DIGEST')) as $string) {
            $parts = explode('=', trim($string), 2) + array('', '');
            $data[$parts[0]] = trim($parts[1], '"');
        }

        $users = $this->config['http_authenticatable']['users'];
        $password = !empty($users[$data['username']]) ? $users[$data['username']] : null;

        $user = md5("{$data['username']}:{$realm}:{$password}");
        $nonce = "{$data['nonce']}:{$data['nc']}:{$data['cnonce']}:{$data['qop']}";
        $req = md5(\Input::server('REQUEST_METHOD').':'.$data['uri']);
        $hash = md5("{$user}:{$nonce}:{$req}");

        if (!$data['username'] || $hash !== $data['response']) {
            $nonce = uniqid();
            $opaque = md5($realm);
            $header_value = "Digest realm=\"{$realm}\",qop=\"auth\", nonce=\"{$nonce}\",opaque=\"{$opaque}\"";

            $response->set_header('WWW-Authenticate', $header_value);
            $response->send(true);
            exit;
        }

        $this->_run_event('after_authentication');

        return array('username' => $data['username'], 'password' => $password);
    }

    /**
     * Runs a Warden callback event if its been registered
     *
     * @param string $name The event to run
     */
    private function _run_event($name)
    {
        $event = "warden_{$name}";

        if (\Event::has_events($event)) {
            \Event::trigger($event, $this->user);
        }
    }
}