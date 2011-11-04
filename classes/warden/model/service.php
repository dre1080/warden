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
namespace Warden;

/**
 * Model_Service
 *
 * @package    Warden
 * @subpackage OmniAuth
 */
class Model_Service extends \Orm\Model
{
    /**
     * Belongs to; relationship properties
     *
     * @var array
     */
    protected static $_belongs_to = array(
        'user' => array(
            'key_from'       => 'user_id',
            'model_to'       => 'Model_User',
            'key_to'         => 'id',
            'cascade_save'   => true,
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
        'user_id',
        'uid',
        'provider',
        'access_token',
        'access_secret',
        'created_at',
        'updated_at',
    );

    /**
     * Observer classes to use
     *
     * @var array
     */
    protected static $_observers = array(
        'Warden\\Observer_Timestamps'
    );
}