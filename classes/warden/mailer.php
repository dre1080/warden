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
 * Warden\Mailer
 *
 * @package    Warden
 * @subpackage Warden
 */
class Mailer
{
  public static function __callStatic($method, $arguments)
  {
    $name = str_replace(array('send_', '_instructions'), '', $method);
    return static::send_instructions($name, $arguments[0]);
  }

  /**
   * Sends the instructions to a user's email address.
   *
   * @return bool
   */
  protected static function send_instructions($name, Model_User $user)
  {
    $config_key = null;

    switch ($name) {
      case 'confirmation':
        $config_key = 'confirmable';
        break;
      case 'reset_password':
        $config_key = 'recoverable';
        break;
      case 'unlock':
        $config_key = 'lockable';
        break;
      default:
        throw new \InvalidArgumentException("Invalid instruction: $name");
    }

    $mail = \Email::forge();
    $mail->from(\Config::get('email.defaults.from.email'), \Config::get('email.defaults.from.name'));
    $mail->to($user->email);
    $mail->subject(__("warden.mailer.subject.$name"));

    $token_name = "{$name}_token";
    $mail->html_body(\View::forge("warden/mailer/{$name}_instructions", array(
      'username' => $user->username,
      'uri'      => \Uri::create(':url/:token', array(
        'url'   => rtrim(\Config::get("warden.{$config_key}.url"), '/'),
        'token' => $user->{$token_name}
      ))
    )));

    $mail->priority(\Email::P_HIGH);

    try {
      return $mail->send();
    } catch (\EmailSendingFailedException $ex) {
      logger(\Fuel::L_ERROR, "Warden\Mailer failed to send {$name} instructions.");
      return false;
    }
  }
}
