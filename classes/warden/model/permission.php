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
 * Model_Permission
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_Permission extends \Orm\Model
{
    protected static $_many_many = array(
        'roles' => array(
            'key_from' => 'id',
            'key_through_from' => 'permission_id',
            'key_through_to'   => 'role_id',
            'table_through' => 'roles_permissions',
            'model_to' => 'Model_Role',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    protected static $_properties = array(
        'id',

        'resource' => array(
            'validation' => array(
                'required',
                'max_length' => array(30)
            )
        ),

        'action' => array(
            'validation' => array(
                'required',
                'max_length' => array(30)
            )
        ),

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
        )
    );

    protected static $_observers = array(
        'Orm\\Observer_Validation'
    );
}