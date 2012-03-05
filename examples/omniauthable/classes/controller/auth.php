<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    1.1
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 - 2012 Andrew Wayne
 */

/**
 * An example that shows how to use the omniauthable feature.
 *
 * Its as simple as just extending \Warden\Controller_OmniAuth
 * in your APPPATH/classes/controller
 */
class Controller_Auth extends \Warden\Controller_OmniAuth {}