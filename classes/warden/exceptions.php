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
 * Warden_Failure
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden_Failure extends \FuelException
{
    public function __construct($lang_key, array $params = array())
    {
        parent::__construct(__("warden.failure.{$lang_key}", $params));
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
class Warden_AccessDenied extends \FuelException
{
    public $action;
    public $resource;

    public function __construct($message = null, $action = null, $resource = null)
    {
        $this->action = $action;
        $this->resource = $resource;

        $message || $message = __('warden.unauthorized.default');

        if (empty($message)) {
            $message = 'You are not authorized to access this page.';
        }

        parent::__construct($message);
    }
}