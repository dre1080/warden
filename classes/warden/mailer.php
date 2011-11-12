<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.9.2
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * Warden_Mailer
 *
 * @package    Warden
 * @subpackage Warden
 */
class Warden_Mailer
{
    public static function __callStatic($method, $arguments)
    {
        $name = str_replace(array('send_', '_instructions'), '', $method);
        return static::_send_instructions($name, $arguments[0]);
    }

    /**
     * Sends the instructions to a user's email address.
     *
     * @return bool
     */
    private static function _send_instructions($name, Model_User $user)
    {
        $mail = \Email::forge();
        $mail->from('no-reply@'.\Input::server('http_host'));
        $mail->to($user->email);
        $mail->subject(__("warden.mailer.subject.$name"));

        $token_name = "{$name}_token";
        $mail->html_body(\View::forge("warden/mailer/{$name}_instructions", array(
            'username'  => $user->username,
            $token_name => $user->{$token_name}
        )));

        $mail->priority(\Email::P_HIGH);

        try {
            return $mail->send();
        } catch (\EmailSendingFailedException $ex) {
            logger(\Fuel::L_ERROR, "Warden_Mailer failed to send {$name} instructions.");
            return false;
        }
    }
}