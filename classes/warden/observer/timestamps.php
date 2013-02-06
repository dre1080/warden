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
 * Observer_Timestamps
 *
 * @package    Warden
 * @subpackage Warden
 */
class Observer_Timestamps extends \Orm\Observer
{
  public function before_save(\Orm\Model $obj)
  {
    $timestamp = \DB::expr('CURRENT_TIMESTAMP');

    if ($obj->is_new()) {
      $obj->created_at = $timestamp;
    }

    $obj->updated_at = $timestamp;
  }
}
