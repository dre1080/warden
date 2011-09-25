<?php
/**
 * The Warden: User authorization library for FuelPHP.
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
 * Model_User
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_User extends \Orm\Model
{
    /**
     * Validation regular expression for username
     */
    const REGEX_USERNAME = '/^[a-zA-Z0-9_]+$/';
    /**
     * Validation regular expression for email
     */
    const REGEX_EMAIL = '/^([\w\.%\+\-]+)@([\w\-]+\.)+([\w]{2,})$/i';

    /**
     * User's plaintext password, used for validation purposes
     *
     * @see \Warden\Model_User::_event_before_save()
     * @see \Warden\Model_User::_event_after_save()
     *
     * @var string
     */
    public $password;

    /**
     * Has Many; relationship properties
     *
     * @var array
     */
    protected static $_has_many = array(
        'user_tokens' => array(
            'key_from' => 'id',
            'model_to' => 'Model_UserToken',
            'key_to'   => 'user_id',
            'cascade_delete' => true,
        )
    );

    /**
     * Many Many; relationship properties
     *
     * @var array
     */
    protected static $_many_many = array(
        'roles' => array(
            'key_from' => 'id',
            'key_through_from' => 'user_id',
            'key_through_to'   => 'role_id',
            'model_to' => 'Model_Role',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    /**
     * Object properties
     *
     * @var array
     */
    protected static $_properties = array(
        'id',
        'encrypted_password' => array(
            'validation' => array(
                'required',
                'exact_length' => array(60),
            ),
        ),
        'username' => array(
            'validation' => array(
                'required',
                'min_length'    => array(3),
                'max_length'    => array(32),
                'match_pattern' => array(self::REGEX_USERNAME),
            ),
        ),
        'email' => array(
            'validation' => array(
                'required',
                'match_pattern' => array(self::REGEX_EMAIL),
            ),
        ),

        'created_at',
        'updated_at',
    );

    /**
     * Observer classes to use
     *
     * @var array
     */
    protected static $_observers = array(
        'Warden\\Observer_Timestamps',
        'Orm\\Observer_Validation',
        'Orm\\Observer_Self'
    );

    /**
     * Loads configuration options.
     */
    public static function _init()
    {
        if (\Config::get('warden.trackable', false)) {
            static::$_properties = array_merge(static::$_properties, array(
                'sign_in_count'      => array('default' => 0),
                'current_sign_in_at' => array('default' => '0000-00-00 00:00:00'),
                'last_sign_in_at'    => array('default' => '0000-00-00 00:00:00'),
                'current_sign_in_ip' => array('default' => 0),
                'last_sign_in_ip'    => array('default' => 0),
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function __construct(array $data = array(), $new = true)
    {
        parent::__construct($data, $new);

        // This is needed for validation and encryption later
        if (isset($data['password'])) {
            $this->password = $data['password'];
            $this->encrypted_password = null;
        }

        $val = \Validation::instance(get_class($this));
        $val->add_field('password', 'Password', 'required|min_length[6]|max_length[128]');
    }

    /**
     * {@inheritdoc}
     */
    public function & __get($property)
    {
        $value = null;
        switch($property) {
            case 'current_sign_in_ip':
            case 'last_sign_in_ip'   :
                $value = $this->get_sign_in_ip($property);
                break;
            default:
                $value = parent::__get($property);
                break;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function __set($property, $value)
    {
        switch($property) {
            case 'current_sign_in_ip':
            case 'last_sign_in_ip'   :
                $value = $this->get_ip_as_int($value);
                break;
        }

        parent::__set($property, $value);
    }

    /**
     * Authenticates and allows a user to enter either their email address or
     * their username into the username field.
     *
     * @param string $username_or_email
     *
     * @return \Warden\Model_User|null The user that matches the tokens or
     *                                 null if no user matches that condition.
     */
    public static function authenticate($username_or_email)
    {
        if (empty($username_or_email)) {
            return null;
        }

        $username_or_email = \Str::lower($username_or_email);

        $user = static::find('first', array(
            'where' => array(
                'email' => $username_or_email,
                array('username', '=', $username_or_email),
            ),
        ));

        return $user;
    }

    /**
     * Creates an anonymous user. An anonymous user is basically an auto-generated
     * {@link Model_User} account that is created behind the scenes and its
     * completely transparent.
     *
     * All "guests" must have a {@link Model_User} so this is necessary
     * (eg. when adding to the "cart" and before the customer has a chance to
     * provide an email or to register).
     *
     * @return \Warden\Model_User
     */
    public static function anonymous()
    {
        static $user = null;

        $user || $user = \Session::get('warden.anonymous_user');

        if ($user instanceof static) {
            return $user;
        }

        // Create a new token
        $persistence_token = \Str::random('unique');

        $user = new static();
        $user->username = $persistence_token;
        $user->email    = "{$persistence_token}@warden_anonymous.net";
        $user->password = $persistence_token;

        \Session::set('warden.anonymous_user', $user);
        \Session::instance()->rotate();

        return $user;
    }

    /**
     * Returns whether a user is an anonymous user (guest)
     *
     * @return bool
     */
    public function is_anonymous()
    {
        return (bool)(preg_match('/@warden_anonymous.net$/', $this->email) === 1);
    }

    /**
     * Fetch the login form for a user
     *
     * @return string
     */
    public static function login_form()
    {
        static $user = null;

        $user || $user = new static();

        $fieldset = Fieldset::forge('user');

        // Add the fields
        $fieldset->add('session[username]', 'Username', array('type' => 'text'));
        $fieldset->add('session[password]', 'Password', array('type' => 'password'));
        $fieldset->add('commit', '', array('value' => 'Sign up', 'type' => 'submit'));

        // Populate with your $user instance and second param is true to use POST
        // to overwrite those values when available
        $fieldset->populate($user, true);

        return $fieldset;
    }

    /**
     * Fetch the signup form for a user
     *
     * @return string
     */
    public static function signup_form()
    {
        static $user = null;

        $user || $user = new static();

        $fieldset = Fieldset::forge('user');

        // Add the fields
        $fieldset->add('user[username]', 'Username', array('type' => 'text'));
        $fieldset->add('user[email]', 'Email', array('type' => 'text'));
        $fieldset->add('user[password]', 'Password', array('type' => 'password'));
        $fieldset->add('commit', '', array('value' => 'Sign up', 'type' => 'submit'));

        // Populate with your $user instance and second param is true to use POST
        // to overwrite those values when available
        $fieldset->populate($user, true);

        return $fieldset;
    }

    /**
     * Track information about user sign ins. It tracks the following columns:
     *
     * - sign_in_count      - Increased every time a sign in is made (by form, openid, oauth)
     * - current_sign_in_at - A timestamp updated when the user signs in
     * - last_sign_in_at    - Holds the timestamp of the previous sign in
     * - current_sign_in_ip - The remote ip updated when the user sign in
     * - last_sign_in_at    - Holds the remote ip of the previous sign in
     *
     * @return bool
     */
    public function update_tracked_fields()
    {
        if (!\Config::get('warden.trackable', false)) {
            return true;
        }

        $old_current = $this->current_sign_in_at;
        $new_current = \DB::expr('CURRENT_TIMESTAMP');

        $this->last_sign_in_at = ($old_current != static::$_properties['last_sign_in_at']['default'])
                               ? $old_current
                               : $new_current;

        $this->current_sign_in_at = $new_current;

        $old_current = $this->current_sign_in_ip;
        $this->current_sign_in_ip = null;

        $new_current = \Input::real_ip();

        $this->last_sign_in_ip = ($old_current != static::$_properties['last_sign_in_ip']['default'])
                               ? $old_current
                               : $new_current;

        $this->current_sign_in_ip = $new_current;

        $this->sign_in_count += 1;

        return $this->save();
    }

    /**
     * Event that tests if a username or email exists in the database.
     * Also downcases and trims username and email.
     *
     * @return void
     */
    public function _event_before_save()
    {
        if (!empty($this->password)) {
            $this->encrypted_password = Warden::instance()->encrypt_password($this->password);
        }

        // Let's not do unnecessary database queries
        // Validation will take care of required fields
        if (!$this->is_changed('username') && !$this->is_changed('email')) {
            return;
        }

        $this->_strip_and_downcase_username_and_email();

        try {
            $this->_username_or_email_exists();
        } catch(\Orm\ValidationFailed $ex) {
            throw $ex;
        }
    }

    /**
     * Event that makes sure password is unset once the model is saved.
     *
     * @return void
     */
    public function _event_after_save()
    {
        unset($this->password);
    }

    /**
     * Gets a user's sign in ip address, handling ip to int conversion
     *
     * @return int $ip
     */
    protected function get_ip_as_int($ip)
    {
        return sprintf('%u', ip2long($ip));
    }

    /**
     * Gets a user's sign in ip address, handling int to ip conversion
     *
     * @param  string $column
     *
     * @return string|int
     */
    protected function get_sign_in_ip($column)
    {
        $ip = $this->get($column);
        $value = ($ip != 0) ? long2ip($ip) : 0;
        return $value;
    }

    /**
     * Tests if a username or email exists in the database.
     *
     * @see \Warden\Model_User::_event_before_save()
     *
     * @throws \Orm\ValidationFailed If the user is found in the database
     */
    private function _username_or_email_exists()
    {
        $user = static::find('first', array(
            'where' => array(
                'email' => $this->email,
                array('username', '=', $this->username),
            )
        ));

        if (!is_null($user)) {
            if ($user->email === $this->email) {
                throw new \Orm\ValidationFailed('Email address already exists');
            } else {
                throw new \Orm\ValidationFailed('Username already exists');
            }
        }
    }

    /**
     * Removes trailing whitespace from username and email and
     * converts them to lower case
     *
     * @see \Warden\Model_User::_event_before_save()
     */
    private function _strip_and_downcase_username_and_email()
    {
        if (!empty($this->username)) {
            $this->username = \Str::lower(trim($this->username));
        }

        if (!empty($this->email)) {
            $this->email = \Str::lower(trim($this->email));
        }
    }
}