<?php
/**
 * The Warden: User authorization library for FuelPHP.
 * Handles user login and logout, as well as secure password hashing.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    0.3
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 Andrew Wayne
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

/**
 * Warden library configuration.
 *
 * @package    Warden
 * @subpackage Warden
 */
return array(
    /**
     * Set the remember-me cookie lifetime, in seconds. The default
     * lifetime is two weeks.
     *
     * (integer)
     */
    'lifetime' => 1209600,

    /**
     * Set to track information about user sign ins.
     * It tracks and requires the following columns:
     *
     *   - sign_in_count      - Increased every time a sign in is made (by form, openid, oauth)
     *   - current_sign_in_at - A timestamp updated when the user signs in
     *   - last_sign_in_at    - Holds the timestamp of the previous sign in
     *   - current_sign_in_ip - The remote ip updated when the user sign in
     *   - last_sign_in_at    - Holds the remote ip of the previous sign in
     *
     * (bool)
     */
    'trackable' => true,

    /**
     * Set the default role a newly created user has, it must already exist,
     * Set to `null` to disable. This role is also used by Warden's driver as
     * a default when checking if a user is logged in.
     *
     * (string|null)
     */
    'default_role' => null
);