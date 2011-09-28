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
 * OmniAuth_Exception
 *
 * Ported from NinjAuth
 *
 * @package    Warden
 * @subpackage OmniAuth
 *
 * @author Phil Sturgeon, Modified by Andrew Wayne
 */
class OmniAuth_Exception extends \Fuel_Exception {}