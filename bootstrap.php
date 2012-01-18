<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    1.0
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */

require_once __DIR__.'/vendor/CryptLib/bootstrap.php';

Autoloader::add_core_namespace('Warden');

Autoloader::add_classes(array(
    // Base Warden classes
    'Warden\\Warden'              => __DIR__.'/classes/warden.php',
    'Warden\\Warden_Driver'       => __DIR__.'/classes/warden/driver.php',
    'Warden\\Warden_Mailer'       => __DIR__.'/classes/warden/mailer.php',
    'Warden\\Warden_Failure'      => __DIR__.'/classes/warden/exceptions.php',
    'Warden\\Warden_AccessDenied' => __DIR__.'/classes/warden/exceptions.php',

    // Models
    'Warden\\Model_User'          => __DIR__.'/classes/warden/model/user.php',
    'Warden\\Model_Profile'       => __DIR__.'/classes/warden/model/profile.php',
    'Warden\\Model_Role'          => __DIR__.'/classes/warden/model/role.php',
    'Warden\\Model_Permission'    => __DIR__.'/classes/warden/model/permission.php',
    'Warden\\Model_Service'       => __DIR__.'/classes/warden/model/service.php',
    'Warden\\Observer_Timestamps' => __DIR__.'/classes/warden/observer/timestamps.php',

    // Controllers
    'Warden\\Controller_OmniAuth' => __DIR__.'/classes/warden/controller/omniauth.php',

    // OmniAuth Support
    'Warden\\OmniAuth_Exception'       => __DIR__.'/classes/warden/omniauth/exception.php',
    'Warden\\OmniAuth_Strategy'  				  => __DIR__.'/classes/warden/omniauth/strategy.php',
    'Warden\\OmniAuth_Strategy_OAuth'  => __DIR__.'/classes/warden/omniauth/strategy/oauth.php',
    'Warden\\OmniAuth_Strategy_OAuth2' => __DIR__.'/classes/warden/omniauth/strategy/oauth2.php',
));

Config::load('warden', true);
Lang::load('warden', true);
