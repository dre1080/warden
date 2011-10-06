<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
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
 * Warden_Failure
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden_Failure extends \Fuel_Exception
{
    public function __construct($lang_key)
    {
        \Lang::load('warden', true);
        parent::__construct(__("warden.{$lang_key}"));
    }
}

/**
 * Warden_AccessDenied
 *
 * Thrown when a user isn't allowed to access a given controller action.
 * This usually happens within a call to Warden::authorize() but can be
 * thrown manually.
 *
 * <code>
 * throw new Warden\Warden_AccessDenied('Not authorized!', 'read', 'Article');
 * </code>
 */
class Warden_AccessDenied extends \Fuel_Exception
{
    public $action;
    public $resource;

    public function __construct($message = null, $action = null, $resource = null)
    {
        \Lang::load('warden', true);

        $this->action = $action;
        $this->resource = $resource;

        if (empty($message)) {
            $message = 'You are not authorized to access this page.';
        }

        parent::__construct($message);
    }
}