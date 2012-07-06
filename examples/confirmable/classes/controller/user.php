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

/**
 * An example that shows how to use the confirmable feature.
 */
class Controller_User
{
    /**
     * The user registration form will be processed by this action.
     */
    public function action_create()
    {

        $data = \Input::post('user');

        /**
         * $data is an array of required user properties, eg.
         * - username
         * - password
         * - email
         */

        try {
            $user = new \Model_User($data);
            $user->save();
            $user->send_confirmation_instructions();
        } catch (\Orm\ValidationFailed $ex) {
            \Session::set_flash('error', $ex->getMessage());
            \Response::redirect('/signup');
        } catch (Exception $ex) {
            Session:set_flash('Oops, something went wrong.');
        }
    }

    /**
     * The user clicks a link in the email and is redirected to this action.
     */
    public function action_confirm()
    {
        try {
            $user = \Model_User::confirm_by_token($this->request()->param('token'));
            if ($user) {
                \Session::set_flash($ex->getMessage());
            } else {
                \Session::set_flash('Invalid token.');
            }
        } catch (\Warden_Failure $ex) {
            // token has expired (if enabled)
            \Session::set_flash($ex->getMessage());
        } catch (Exception $ex) {
            // Server/DB error
            \Session::set_flash('Oops, something went wrong.');
        }

        return \View::forge('user/confirm');
    }
}