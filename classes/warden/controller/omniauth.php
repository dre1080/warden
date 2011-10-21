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
 * Controller_OmniAuth
 *
 * Ported from NinjAuth
 *
 * @package    Warden
 * @subpackage OmniAuth
 *
 * @author Phil Sturgeon, Modified by Andrew Wayne
 */
class Controller_OmniAuth extends \Controller
{
    public function before()
    {
        parent::before();

        if (\Config::get('warden.omniauthable.in_use') !== true) {
            throw new \Request404Exception();
        }
    }

    public function action_session($provider)
    {
        OmniAuth_Strategy::forge($provider)->authenticate();
    }

    public function action_callback($provider)
    {
        $strategy = OmniAuth_Strategy::forge($provider);
        OmniAuth_Strategy::login_or_register($strategy);
    }

    public function action_register()
    {
        $user_hash = \Session::get('omniauth');

//        $full_name = \Input::post('full_name') ? : \Arr::get($user_hash, 'name');
        $username  = \Input::post('username') ? : \Arr::get($user_hash, 'nickname');
        $email     = \Input::post('email') ? : \Arr::get($user_hash, 'email');
        $password  = \Input::post('password');

        $user = $service = null;

        if ($username /*&& $full_name*/ && $email && $password) {
            try {
                $user = new Model_User(array(
                    'email'    => $email,
                    'username' => $username,
                    'password' => $password
                ));

                $service = new Model_Service(array(
                    'uid'           => $user_hash['credentials']['uid'],
                    'provider'      => $user_hash['credentials']['provider'],
                    'access_token'  => $user_hash['credentials']['token'],
                    'access_secret' => $user_hash['credentials']['secret']
                ));

                if (\Config::get('warden.omniauthable.link_multiple') === true) {
                    $user->services[] = $service;
                } else {
                    $user->service = $service;
                }

                $user->save();

            } catch (\Exception $ex) {
                \Session::set_flash('warden.omniauthable.error', $ex->getMessage());
                goto display;
            }

            \Response::redirect(\Config::get('warden.omniauthable.urls.registered'));
        }

        display:

        $this->response->body = \View::forge('register', array(
            'user' => (object)compact('username', /*'full_name',*/ 'email', 'password')
        ), false);
    }
}