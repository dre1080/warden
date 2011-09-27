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

require_once __DIR__.'/vendor/cryptlib/bootstrap.php';

Autoloader::add_core_namespace('Warden');

Autoloader::add_classes(array(
    'Warden\\Warden'              => __DIR__.'/classes/warden.php',
    'Warden\\Warden_Driver'       => __DIR__.'/classes/driver.php',
    'Warden\\Model_User'          => __DIR__.'/classes/warden/model/user.php',
    'Warden\\Model_UserToken'     => __DIR__.'/classes/warden/model/usertoken.php',
    'Warden\\Model_Role'          => __DIR__.'/classes/warden/model/role.php',
    'Warden\\Observer_Timestamps' => __DIR__.'/classes/warden/observer/timestamps.php',
));

Config::load('warden', true);