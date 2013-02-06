<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    2.0.1
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 - 2013 Andrew Wayne
 */

/*
 * Make sure the dependency packages are loaded.
 */
Package::load(array('orm', 'email'));

Autoloader::add_core_namespace('Warden');

Autoloader::add_classes(array(
  // PHPass
  'PasswordHash' => __DIR__.'/vendor/phpass/PasswordHash.php',

  // Base Warden classes
  'Warden\\Warden'              => __DIR__.'/classes/warden.php',
  'Warden\\Driver'       => __DIR__.'/classes/warden/driver.php',
  'Warden\\Mailer'       => __DIR__.'/classes/warden/mailer.php',
  'Warden\\Failure'      => __DIR__.'/classes/warden/exceptions.php',
  'Warden\\AccessDenied' => __DIR__.'/classes/warden/exceptions.php',

  // Models
  'Warden\\Model_User'          => __DIR__.'/classes/warden/model/user.php',
  'Warden\\Model_Profile'       => __DIR__.'/classes/warden/model/profile.php',
  'Warden\\Model_Role'          => __DIR__.'/classes/warden/model/role.php',
  'Warden\\Model_Permission'    => __DIR__.'/classes/warden/model/permission.php',
  'Warden\\Observer_Timestamps' => __DIR__.'/classes/warden/observer/timestamps.php',
));

Config::load('warden', true);
Lang::load('warden', true);
