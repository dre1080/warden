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
            'model_to' => 'Model_User',
            'key_to' => 'id',
            'cascade_delete' => true,
        ),

        'permissions' => array(
            'key_from' => 'id',
            'key_through_from' => 'role_id',
            'key_through_to'   => 'permission_id',
            'table_through' => 'roles_permissions',
            'model_to' => 'Model_Permission',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    protected static $_properties = array(
        'id',

        'name' => array(
            'validation' => array(
                'required',
                'max_length' => array(20)
            )
        ),

        'description' => array(
            'validation' => array(
                'required',
                'max_length' => array(100)
            )
        ),
    );

    protected static $_observers = array(
        'Orm\\Observer_Validation'
    );
}