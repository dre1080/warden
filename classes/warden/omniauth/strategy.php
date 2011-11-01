<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.8.6
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */
namespace Warden;

/**
 * OmniAuth_Strategy
 *
 * Ported from NinjAuth
 *
 * @package    Warden
 * @subpackage OmniAuth
 *
 * @author Phil Sturgeon, Modified by Andrew Wayne
 */
abstract class OmniAuth_Strategy
{
    public $name;

    protected static $providers = array(
        'facebook' => 'OAuth2',
        'twitter'  => 'OAuth',
        'dropbox'  => 'OAuth',
        'flickr'   => 'OAuth',
        'google'   => 'OAuth2',
        'github'   => 'OAuth2',
        'linkedin' => 'OAuth',
        'unmagnify'=> 'OAuth2',
        'youtube'  => 'OAuth',
    );

    public function __construct($provider)
    {
        $this->provider = $provider;

        $this->config = \Config::get("warden.omniauthable.providers.{$provider}");

        if (!$this->name) {
            // Attempt to guess the name from the class name
            $this->name = strtolower(substr(get_class($this), 25));
        }
    }

    public static function forge($provider)
    {
        $strategy = \Arr::get(static::$providers, $provider);

        if (!$strategy) {
            throw new OmniAuth_Exception(sprintf(
                    'Provider "%s" has no strategy.',
                    $provider
            ));
        }

        $class = "Warden\\OmniAuth_Strategy_{$strategy}";
        return new $class($provider);
    }

    public static function login_or_register(OmniAuth_Strategy $strategy)
    {
        $response = $strategy->callback();

        if (Warden::check()) {
            $user_id = Warden::current_user()->id;

            $num_linked = Model_Service::count_by_user_id($user_id);

            // Allowed multiple providers, or not authed yet?
            if ($num_linked === 0 || \Config::get('warden.omniauthable.link_multiple_providers') === true) {
                switch ($strategy->name) {
                    case 'oauth':
                        $user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
                        break;

                    case 'oauth2':
                        $user_hash = $strategy->provider->get_user_info($response->token);
                        break;
                }

                // Attach this account to the logged in user
                $service = new Model_Service(array(
                    'user_id'       => $user_id,
                    'uid'           => $user_hash['credentials']['uid'],
                    'provider'      => $user_hash['credentials']['provider'],
                    'access_token'  => $user_hash['credentials']['token'],
                    'access_secret' => $user_hash['credentials']['secret']
                ));

                $service->save();

                // Attachment went ok so we'll redirect
                \Response::redirect(\Config::get('warden.omniauthable.urls.logged_in'));
            } else {
                $service = Model_Service::find_by_user_id($user_id);
                throw new OmniAuth_Exception(sprintf(
                        'This user is already connected to "%s"',
                        $service->provider
                ));
            }
        }

        // The user exists, so send him on his merry way as a user
        else if (($service = Model_Service::find_by_access_token_and_access_secret($response->token, $response->secret))) {
            // Force a login with this username
            if (\Warden::force_login($service->user->username)) {
                // credentials ok, go right in
                \Response::redirect(\Config::get('warden.omniauthable.urls.logged_in'));
            }
        }

        // They aren't a user, so redirect to registration page
        else {
            switch ($strategy->name) {
                case 'oauth':
                    $user_hash = $strategy->provider->get_user_info($strategy->consumer, $response);
                    break;

                case 'oauth2':
                    $user_hash = $strategy->provider->get_user_info($response->token);
                    break;

                default:
                    exit('Ummm....');
            }

            \Session::set('omniauth', $user_hash);

            \Response::redirect(\Config::get('warden.omniauthable.urls.registration'));
        }
    }

    abstract public function authenticate();
}