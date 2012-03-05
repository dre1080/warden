<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    1.1
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 - 2012 Andrew Wayne
 */
namespace Warden;

/**
 * OmniAuth_Strategy_OAuth
 *
 * Ported from NinjAuth
 *
 * @package    Warden
 * @subpackage OmniAuth
 *
 * @author Phil Sturgeon, Modified by Andrew Wayne
 */
class OmniAuth_Strategy_OAuth extends OmniAuth_Strategy
{
    public $provider;

    public function authenticate()
    {
        // Create an consumer from the config
        $consumer = \OAuth\Consumer::factory($this->config);

        // Load the provider
        $provider = \OAuth\Provider::factory($this->provider);

        // Create the URL to return the user to
        $callback = \Uri::create(
                \Config::get('warden.omniauthable.urls.callback',
                \Request::active()->route->segments[0] . '/callback') . '/' . $this->provider
        );

        // Add the callback URL to the consumer
        $consumer->callback($callback);

        // Get a request token for the consumer
        $token = $provider->request_token($consumer);

        // Store the token
        \Cookie::set('oauth_token',
                     base64_encode(serialize($token)),
                     null, null, null, null, true);

        // Redirect to the login page
        \Response::redirect($provider->authorize_url($token, array(
            'oauth_callback' => $callback,
        )));
    }

    public function callback()
    {
        // Create an consumer from the config
        $this->consumer = \OAuth\Consumer::factory($this->config);

        // Load the provider
        $this->provider = \OAuth\Provider::factory($this->provider);

        if (($token = \Cookie::get('oauth_token'))) {
            // Get the token from storage
            $this->token = unserialize(base64_decode($token));
        }

        if ($this->token AND $this->token->token !== \Input::get_post('oauth_token')) {
            // Delete the token, it is not valid
            \Cookie::delete('oauth_token');

            // Send the user back to the beginning
            exit(__('warden.omniauth_callbacks.failure', array(
                    'provider' => $this->provider->name,
                    'reason'   => 'Invalid token after coming back to site'
                )
            ));
        }

        // Get the verifier
        $verifier = \Input::get_post('oauth_verifier');

        // Store the verifier in the token
        $this->token->verifier($verifier);

        // Exchange the request token for an access token
        return $this->provider->access_token($this->consumer, $this->token);
    }
}
