<?php
/**
 * The Warden: User authorization library for FuelPHP.
 * Handles user login and logout, as well as secure password hashing.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.4.1-beta
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * Model_Role
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_Role extends \Orm\Model
{
    protected static $_many_many = array(
        'users' => array(
            'key_from' => 'id',
            'key_through_from' => 'role_id',
            'key_through_to'   => 'user_id',
            'table_through' => 'roles_users',
            'model_to' => '\Warden\Model_User',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    protected static $_properties = array(
        'id',

        'name' => array(
            'validation' => array(
                'required',
                'min_length' => array(4),
                'max_length' => array(40)
            )
        ),

        'description' => array(
            'validation' => array(
                'required'
            )
        ),
    );

    protected static $_observers = array(
        'Orm\\Observer_Validation'
    );
}