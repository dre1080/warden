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
            'model_to' => 'Model_User',
            'key_to' => 'id',
            'cascade_delete' => true,
        )
    );

    protected static $_properties = array(
        'id' => array(
            'type' => 'int',
            'min'  => 0
        ),

        'name' => array(
            'type'  => 'varchar',
            'validation' => array(
                'required',
                'min_length' => array(4),
                'max_length' => array(32),
            ),
        ),

        'description' => array(
            'type'  => 'varchar',
            'validation' => array(
                'max_length' => array(255),
            ),
        ),
    );
}