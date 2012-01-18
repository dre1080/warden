<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    1.0
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * OmniAuth_Strategy_OAuth2
 *
 * Ported from NinjAuth
 *
 * @package    Warden
 * @subpackage OmniAuth
 *
 * @author Phil Sturgeon, Modified by Andrew Wayne
 */
class OmniAuth_Strategy_OAuth2 extends OmniAuth_Strategy
{
    public $provider;

    public function authenticate()
    {
        // Load the provider
        $provider = \OAuth2\Provider::factory($this->provider, $this->config);

        $provider->authorize(array(
            'redirect_uri' => \Uri::create(
                    \Config::get('warden.omniauthable.urls.callback',
                    \Request::active()->route->segments[0] . '/callback') . '/' . $this->provider
            )
        ));
    }

    public function callback()
    {
        // Load the provider
        $this->provider = \OAuth2\Provider::factory($this->provider, $this->config);

        try {
            $params = $this->provider->access(\Input::get('code'));

            return (object) array(
                'token'  => $params['access_token'],
                'secret' => null,
            );
        } catch (\OAuth2\Exception $ex) {
            exit(__('warden.omniauth_callbacks.failure', array(
                    'provider' => $this->provider->name,
                    'reason'   => $ex->getMessage()
                )
            ));
        }
    }

}
