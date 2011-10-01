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
     * We need these to avoid an error in the \Warden\Model_User::_init() method:
     * "Access to undeclared static property"
     *
     * @var array
     */
    protected static $_has_one = array();
    protected static $_has_many = array();

    /**
     * Many Many; relationship properties
     *
     * @var array
     */
    protected static $_many_many = array(
        'roles' => array(
            'key_from'         => 'id',
            'key_through_from' => 'user_id',
            'key_through_to'   => 'role_id',
            'table_through'    => 'roles_users',
            'model_to'         => '\Warden\Model_Role',
            'key_to'           => 'id',
            'cascade_delete'   => true,
        )
    );

    /**
     * Object properties
     *
     * @var array
     */
    protected static $_properties = array(
        'id',
        'encrypted_password',
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

        'authentication_token' => array('default' => null),
        'remember_token'       => array('default' => null),

        'created_at'  => array('default' => '0000-00-00 00:00:00'),
        'updated_at'  => array('default' => '0000-00-00 00:00:00'),
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
     * Loads configuration options and sets up class properties.
     */
    public static function _init()
    {
        static::_init_trackable();
        static::_init_recoverable();
        static::_init_profilable();
        static::_init_omniauthable();
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
        }
    }

    /**
     * {@inheritdoc}
     */
    public function & __get($property)
    {
        if ($property == 'current_sign_in_ip' || $property == 'last_sign_in_ip') {
            $value = $this->get_sign_in_ip($property);
            return $value;
        }

        return parent::__get($property);
    }

    /**
     * {@inheritdoc}
     */
    public function __set($property, $value)
    {
        if ($property == 'current_sign_in_ip' || $property == 'last_sign_in_ip') {
            $value = $this->get_ip_as_int($value);
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

        $username_or_email = \DB::escape(\Str::lower($username_or_email));

        $table = static::table();
        $properties = implode('`, `', array_keys(static::properties()));

        // Indices lose their speed advantage when using them in OR-situations
        // so this is much faster than
        // SELECT ... FROM ... WHERE email = 'foo' OR username = 'bar';
        $sql = <<<SQL
   (SELECT `$properties` FROM `$table`
       WHERE `email` = $username_or_email)
   UNION
      (SELECT `$properties` FROM `$table`
          WHERE `username` = $username_or_email)
   LIMIT 1
SQL;

        $record = \DB::query($sql, \DB::SELECT)
                  ->as_object('\Warden\Model_User')
                  ->execute()
                  ->current();

        return $record ? $record : null;
    }

    /**
     * Creates an anonymous user. An anonymous user is basically an auto-generated
     * {@link \Warden\Model_User} account that is created behind the scenes and its
     * completely transparent.
     *
     * All "guests" must have a {@link \Warden\Model_User} so this is necessary
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
        if (!\Config::get('warden.trackable') === true) {
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

        return $this->save(false);
    }

    /**
     * Update password saving the record and clearing token.
     *
     * @param string $new_password The new plaintext password to set
     *
     * @return bool
     */
    public function reset_password($new_password)
    {
        $this->password = $new_password;
        $this->clear_reset_password_token();

        return $this->save(false);
    }

    /**
     * Attempt to find a user by it's reset_password_token to reset its
     * password and automatically try saving the record.
     *
     * @param string $reset_password_token
     * @param string $new_password
     *
     * @return \Warden\Model_User|null Returns a user if is found and token is still valid,
     *                                 or null if no user is found.
     *
     * @throws \Orm\ValidationFailed If the token has expired
     */
    public static function reset_password_by_token($reset_password_token, $new_password)
    {
        $recoverable = static::find('first', array(
            'where' => array(
               'reset_password_token' => $reset_password_token
            )
        ));

        if (!is_null($recoverable)) {
            if ($recoverable->is_reset_password_period_valid()) {
                $recoverable->reset_password($new_password);
            } else {
                throw new \Orm\ValidationFailed('Reset password token has expired, please request a new one.');
            }
        }

        return $recoverable;
    }

    /**
     * Generates a new random token for reset password and save the record
     *
     * @return bool
     */
    public function generate_reset_password_token()
    {
        if (!is_null($this->reset_password_token) && $this->is_reset_password_period_valid()) {
            return true;
        }

        $this->reset_password_token = Warden::instance()->generate_token();
        $this->reset_password_sent_at = \DB::expr('CURRENT_TIMESTAMP');

        return $this->save(false);
    }

    /**
     * Checks if the reset password token sent is within the limit time.
     *
     * <code>
     * \Config::set('warden.reset_password_within', '+1 day');
     * $user->reset_password_sent_at = \Date::time()->format('mysql');
     * $user->is_reset_password_period_valid(); // returns true
     * </code>
     *
     * @return bool Returns true if the user is not responding to reset_password_sent_at at all.
     */
    public function is_reset_password_period_valid()
    {
        if (!isset(static::$_properties['reset_password_sent_at'])) {
            return true;
        }

        if ($this->reset_password_sent_at == static::$_properties['reset_password_sent_at']['default']) {
            return false;
        }

        $lifetime = \Config::get('warden.recoverable.reset_password_within');
        $expires  = strtotime($lifetime, strtotime($this->reset_password_sent_at));

        return (bool)($expires >= time());
    }

    /**
     * Event that does the following:
     *
     * - tests if a username or email exists in the database.
     * - downcases and trims username and email.
     * - validates and ensures an encrypted password exists if the password has changed.
     * - checks for unique fields.
     * - adds a default role, if one was specified in the config file
     */
    public function _event_before_save()
    {
        $this->_strip_and_downcase_username_and_email();
        $this->_ensure_and_validate_password();
        $this->_username_or_email_exists();
        $this->_add_default_role();
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
     * Removes reset password token
     */
    protected function clear_reset_password_token()
    {
        $this->reset_password_token = null;
        $this->reset_password_sent_at = static::$_properties['reset_password_sent_at']['default'];
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
     * @return void
     *
     * @see \Warden\Model_User::_event_before_save()
     *
     * @throws \Orm\ValidationFailed If the user is found in the database
     */
    private function _username_or_email_exists()
    {
        // Let's not do unnecessary database queries
        if (!$this->is_changed('username') && !$this->is_changed('email')) {
            return;
        }

        $email    = \DB::escape(\Str::lower($this->email));
        $username = \DB::escape(\Str::lower($this->username));

        $table = static::table();

        $sql = <<<SQL
   (SELECT `email` FROM `$table`
       WHERE `email` = $email)
   UNION
      (SELECT `email` FROM `$table`
          WHERE `username` = $username)
   LIMIT 1
SQL;

        $user = \DB::query($sql, \DB::SELECT)
                ->as_assoc()
                ->execute()
                ->current();

        if ($user != false) {
            if ($user['email'] === $this->email) {
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

    /**
     * Validates a user password & ensures an encrypted password is set
     *
     * @see \Warden\Model_User::_event_before_save()
     */
    private function _ensure_and_validate_password()
    {
        if (!empty($this->password)) {
            if (\Str::length($this->password) < 6) {
                throw new \Orm\ValidationFailed('Password is too short (minimum is 6 characters)');
            }

            $this->encrypted_password = Warden::instance()->encrypt_password($this->password);
        }

        if (empty($this->encrypted_password)) {
            throw new \Orm\ValidationFailed('Password is required');
        }
    }

    /**
     * Adds default role to a new user if enabled in config
     *
     * @see \Warden\Model_User::_event_before_save()
     */
    private function _add_default_role()
    {
        // Make sure no roles exist already
        if (empty($this->roles) || !static::query()->related('roles')->get_one()) {
            // Check for default role
            if (($default_role = \Config::get('warden.default_role'))) {
                $role = Model_Role::find('first', array(
                    'where' => array(
                        'name' => $default_role
                    )
                ));

                if (!is_null($role)) {
                    $this->roles[] = $role;
                }
            }
        }
    }

    /**
     * Checks that the trackable feature is enabled and adds its required
     * fields to the properties
     *
     * @see \Warden\Model_User::_init()
     */
    private static function _init_trackable()
    {
        if (\Config::get('warden.trackable') === true) {
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
     * Checks that the recoverable feature is enabled and adds its required
     * fields to the properties
     *
     * @see \Warden\Model_User::_init()
     */
    private static function _init_recoverable()
    {
        if (\Config::get('warden.recoverable.in_use') === true) {
            static::$_properties = array_merge(static::$_properties, array(
                'reset_password_token'   => array('default' => null),
                'reset_password_sent_at' => array('default' => '0000-00-00 00:00:00')
            ));
        }
    }

    /**
     * Checks that the profilable feature is enabled and sets up its relation.
     *
     * @see \Warden\Model_User::_init()
     */
    private static function _init_profilable()
    {
        if (\Config::get('warden.profilable') === true) {
            static::$_has_one = array_merge(static::$_has_one, array(
                'profile' => array(
                    'key_from'       => 'id',
                    'model_to'       => '\Warden\Model_Profile',
                    'key_to'         => 'user_id',
                    'cascade_save'   => true,
                    'cascade_delete' => true,
                )
            ));
        }
    }

    /**
     * Checks that the omniauthable feature is enabled and sets up its relation.
     *
     * @see \Warden\Model_User::_init()
     */
    private static function _init_omniauthable()
    {
        if (\Config::get('warden.omniauthable.in_use') === true) {
            $relation = (\Config::get('warden.omniauthable.link_multiple', false)
                      ? array('_has_many', 'services')
                      : array('_has_one', 'service'));

            static::${$relation[0]} = array_merge(static::${$relation[0]}, array(
                $relation[1] => array(
                    'key_from'       => 'id',
                    'model_to'       => '\Warden\Model_Service',
                    'key_to'         => 'user_id',
                    'cascade_save'   => true,
                    'cascade_delete' => true,
                )
            ));
        }
    }
}