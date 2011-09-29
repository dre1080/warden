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
 * Model_Profile
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_Profile extends \Orm\Model
{
    /**
     * Validation regular expression for a name
     */
//    const REGEX_NAME = '\^[^;]+$';

    /**
     * Belongs to; relationship properties
     *
     * @var array
     */
    protected static $_belongs_to = array(
        'user' => array(
            'key_from'       => 'user_id',
            'model_to'       => '\Warden\Model_User',
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
        // Example fields
//        'first_name' => array(
//            'validation' => array(
//                'null' => true,
//                'match_pattern' => array(self::REGEX_NAME),
//            ),
//        ),
//        'last_name' => array(
//            'validation' => array(
//                'null' => true,
//                'match_pattern' => array(self::REGEX_NAME),
//            ),
//        ),
//        'gender',
//        'bio',
//        'location',
//        'searchable',
//        'created_at',
//        'updated_at',
    );

    /**
     * Observer classes to use
     *
     * @var array
     */
//    protected static $_observers = array(
//        'Warden\\Observer_Timestamps'
//    );
}