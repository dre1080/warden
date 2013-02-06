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
namespace Warden;

/**
 * Warden\Failure
 *
 * @package    Warden
 * @subpackage Warden
 */
class Failure extends \FuelException
{
  public function __construct($lang_key, array $params = array())
  {
    parent::__construct(__("warden.failure.{$lang_key}", $params));
  }
}

/**
 * Warden\AccessDenied
 *
 * Thrown when a user isn't allowed to access a given controller action.
 * This usually happens within a call to Warden::authorize() but can be
 * thrown manually.
 *
 * <code>
 * throw new Warden\AccessDenied('Not authorized!', 'read', 'Article');
 * </code>
 */
class AccessDenied extends \FuelException
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
