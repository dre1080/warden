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
 * Model_UserToken
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_UserToken extends \Orm\Model
{
    protected static $_belongs_to = array(
        'user' => array(
            'key_from' => 'user_id',
            'model_to' => 'Model_User',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    protected static $_properties = array(
        'id',
        'user_id',
        'token',
        'user_agent',
        'expires',
        'created_at',
    );

    protected static $_observers = array(
        'Orm\\Observer_Self'
    );

    /**
     * Handles garbage collection and deleting of expired objects.
     */
    public function __construct(array $data = array(), $new = true)
    {
        parent::__construct($data, $new);

        if (mt_rand(1, 100) === 1) {
            // Do garbage collection
            $this->delete_expired();
        }

        if ($this->expires < time()) {
            // This object has expired
            $this->delete();
        }
    }

    /**
     * Event to set the created time and to create a new token
     * when the object is saved.
     */
    public function _event_before_save()
    {
        if ($this->is_new()) {
            $this->user_agent = sha1(\Input::server('HTTP_USER_AGENT'));
        }

        // Create a new token each time the token is saved
        $this->token = $this->generate_token();
    }

    /**
     * Deletes all expired tokens.
     */
    public function delete_expired()
    {
        static::find()->where('expires', '<', time())->delete();
    }

    /**
     * Generate a token by looping and ensuring does not already exist.
     *
     * @return string
     */
    public function generate_token()
    {
        while(true) {
            $token = $this->generate_friendly_token();
            if (static::count(array('where' => array('token' => $token))) == 0) {
                return $token;
            }
        }
    }

    /**
     * Generate a friendly string randomically to be used as token.
     *
     * @return string A random friendly string of length 32
     */
    protected function generate_friendly_token()
    {
        $token = \Str::random('alnum', 22);
        return str_replace(array('+', '/', '='), array('x', 'y', 'z'), base64_encode($token));
    }
}