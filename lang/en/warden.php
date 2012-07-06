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
 * Example lang file.
 *
 * @package    Warden
 * @subpackage Warden
 */
return array(
    'failure' => array(
        'already_authenticated' => 'You are already signed in.',
        'unauthenticated'       => 'You need to sign in or sign up before continuing.',
        'unconfirmed'           => 'You have to confirm your account before continuing.',
        'already_confirmed'     => ':email was already confirmed, please try signing in.',
        'locked'                => 'Your account is locked.',
        'invalid'               => 'Invalid email or password.',
        'invalid_token'         => 'Invalid authentication token.',
        'expired_token'         => ':name token has expired, please request a new one',
        'timeout'               => 'Your session expired, please sign in again to continue.',
        'inactive'              => 'Your account was not activated yet.'
    ),

    'validation' => array(
        'unique' => ':field already exists',
        'password' => array(
            'required'  => 'Password is required',
            'invalid'   => 'Password is invalid.',
            'too_short' => 'Password is too short (minimum is 6 characters)'
        )
    ),

    'sessions' => array(
        'signed_in'  => 'Signed in successfully.',
        'signed_out' => 'Signed out successfully.'
    ),

    'passwords' => array(
        'send_instructions' => 'You will receive an email with instructions about how to reset your password in a few minutes.',
        'updated'           => 'Your password was changed successfully. You are now signed in.'
    ),

    'confirmations' => array(
        'send_instructions' => 'You will receive an email with instructions about how to confirm your account in a few minutes.',
        'confirmed'         => 'Your account was successfully confirmed. You are now signed in.'
    ),

    'unlocks' => array(
        'send_instructions' => 'You will receive an email with instructions about how to unlock your account in a few minutes.',
        'unlocked'          => 'Your account was successfully unlocked. You are now signed in.'
    ),

    'unauthorized' => array(
        'default' => '',
    ),

    'omniauth_callbacks' => array(
        'success' => 'Successfully authorized from :provider account.',
        'failure' => 'Could not authorize you from :provider because ":reason".'
    ),

    'mailer' => array(
        'subject' => array(
            'reset_password' => 'Reset password instructions',
            'confirmation'   => 'Confirmation instructions',
            'unlock'         => 'Unlock instructions',
        )
    )
);