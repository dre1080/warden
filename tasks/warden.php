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
namespace Fuel\Tasks;

/**
 * Task that makes it easier to install Warden from console.
 */
class Warden
{
    /**
     * This method gets ran when a valid method name is not used in the command.
     *
     * Usage (from command line):
     *
     * php oil r warden
     */
    public function run()
    {
        \Cli::write('================================================================');
        \Cli::write('Warden: User authorization & authentication library for FuelPHP.');
        \Cli::write('Copyright (c) 2011 Andrew Wayne');

        $eye = \Cli::color("*", 'red');

		\Cli::write(\Cli::color("
					\"TO SERVE & PROTECT\"
			          _____     /
			         /_____\\", 'blue')."\n"
.\Cli::color("			    ____[\\", 'blue').$eye.\Cli::color('---', 'blue').$eye.\Cli::color('/]____', 'blue')."\n"
.\Cli::color("			   /\\ #\\ \\_____/ /# /\\
			  /  \\# \\_.---._/ #/  \\
			 /   /|\\  |   |  /|\\   \\
			/___/ | | | W | | | \\___\\
			|  |  | | |---| | |  |  |
			|__|  \\_| |_#_| |_/  |__|
			//\\\\  <\\ _//^\\\\_ />  //\\\\
			\\||/  |\\//// \\\\\\\\/|  \\||/
			      |   |   |   |
			      |---|   |---|
			      |---|   |---|
			      |   |   |   |
			      |___|   |___|
			      /   \\   /   \\
			     |_____| |_____|", 'blue'));

        \Cli::write('================================================================');

        \Cli::write("\nSetting up Warden config...");
        $this->_setup_config();

        $this->install();

        \Cli::write("\nSaving Warden config...");
        $this->_save_config();

        \Cli::write('================================================================');

        \Cli::write("\n".\Cli::color('Warden installed successfully!', 'green'));
    }

    public function install()
    {
        $fields = array(
            'id'    => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
            'email' => array('constraint' => 255, 'type' => 'varchar'),
            'username' => array('constraint' => 32, 'type' => 'varchar'),
            'encrypted_password' => array('constraint' => 60, 'type' => 'varbinary'),
            'authentication_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => \DB::expr('NULL')),
            'remember_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => \DB::expr('NULL')),
        );

        if (\Config::get('warden.recoverable.in_use') === true) {
            $fields = array_merge($fields, $this->recoverable(true));
        }

        if (\Config::get('warden.confirmable.in_use') === true) {
            $fields = array_merge($fields, $this->confirmable(true));
        }

        if (\Config::get('warden.trackable') === true) {
            $fields = array_merge($fields, $this->trackable(true));
        }

        if (\Config::get('warden.lockable.in_use') === true) {
            $fields = array_merge($fields, $this->lockable(true));
        }

        $fields = array_merge($fields, array(
            'created_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'updated_at' => array('type' => 'timestamp ON UPDATE CURRENT_TIMESTAMP', 'default' => \DB::expr('CURRENT_TIMESTAMP'))
        ));

        \DBUtil::create_table('users', $fields, array('id'), false, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE users
                        ADD UNIQUE index_users_on_email(email),
                        ADD UNIQUE index_users_on_username(username),
                        ADD INDEX index_users_on_authentication_token(authentication_token),
                        ADD INDEX index_users_on_remember_token(remember_token)",
                   \DB::UPDATE)->execute();

        \Cli::write("\n".\Cli::color('Created users table successfully', 'green'));

        $this->roles();
        $this->permissions();

        if (\Config::get('warden.profilable') === true) {
            $this->profilable(true);
        }

        if (\Config::get('warden.omniauthable.in_use') === true) {
            $this->omniauthable(true);
        }
    }

    public function roles()
    {
        \DBUtil::create_table('roles', array(
            'id'   => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
            'name' => array('constraint' => 20, 'type' => 'varchar'),
            'description' => array('constraint' => 100, 'type' => 'varchar'),
        ), array('id'), false, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE roles
                        ADD UNIQUE index_roles_on_name(name)",
                   \DB::UPDATE)->execute();

        \Cli::write(\Cli::color('Created roles table successfully', 'green'));

        \DBUtil::create_table('roles_users', array(
            'role_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'user_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
        ), array('role_id', 'user_id'), false, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE roles_users
                        ADD KEY index_roles_users_on_user_id(user_id),
                        ADD CONSTRAINT fk_index_roles_users_on_user_id
                            FOREIGN KEY (user_id)
                            REFERENCES users (id) ON DELETE CASCADE,
                        ADD CONSTRAINT fk_index_roles_users_on_role_id
                            FOREIGN KEY (role_id)
                            REFERENCES roles (id) ON DELETE CASCADE",
                   \DB::UPDATE)->execute();

        \Cli::write(\Cli::color('Created roles_users table successfully', 'green'));
    }

    public function permissions()
    {
        \DBUtil::create_table('permissions', array(
            'id'   => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
            'name' => array('constraint' => 20, 'type' => 'varchar'),
            'resource' => array('constraint' => 30, 'type' => 'varchar'),
            'action' => array('constraint' => 30, 'type' => 'varchar'),
            'description' => array('constraint' => 100, 'type' => 'varchar'),
        ), array('id'), false, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE permissions
                        ADD UNIQUE index_permissions_on_name(name),
                        ADD UNIQUE index_permissions_on_resource_and_action(resource, action)",
                   \DB::UPDATE)->execute();

        \Cli::write(\Cli::color('Created permissions table successfully', 'green'));

        \DBUtil::create_table('roles_permissions', array(
            'role_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
            'permission_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
        ), array('role_id', 'permission_id'), false, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE roles_permissions
                        ADD KEY index_roles_permissions_on_permission_id(permission_id),
                        ADD CONSTRAINT fk_index_roles_permissions_on_role_id
                            FOREIGN KEY (role_id)
                            REFERENCES roles (id) ON DELETE CASCADE,
                        ADD CONSTRAINT fk_index_roles_permissions_on_permission_id
                            FOREIGN KEY (permission_id)
                            REFERENCES permissions (id) ON DELETE CASCADE",
                   \DB::UPDATE)->execute();

        \Cli::write(\Cli::color('Created roles_permissions table successfully', 'green'));
    }

    public function recoverable($new = false)
    {
        $fields = array(
            'reset_password_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'reset_password_sent_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        );

        if (!$new) {
            $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);
            \Config::set('warden.recoverable.in_use', $delete ? false : true);
            $this->_save_config();
        }

        return $this->_alter_or_return_fields('Recoverable', $fields, $new);
    }

    public function confirmable($new = false)
    {
        $fields = array(
            'is_confirmed' => array('constraint' => 1, 'type' => 'tinyint', 'unsigned' => true, 'default' => '0'),
            'confirmation_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => \DB::expr('NULL')),
            'confirmation_sent_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        );

        if (!$new) {
            $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);
            \Config::set('warden.confirmable.in_use', $delete ? false : true);
            $this->_save_config();
        }

        return $this->_alter_or_return_fields('Confirmable', $fields, $new);
    }

    public function trackable($new = false)
    {
        $fields = array(
            'sign_in_count' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
            'confirmation_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => \DB::expr('NULL')),
            'current_sign_in_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'last_sign_in_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'current_sign_in_ip' => array('constraint' => 10, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
            'last_sign_in_ip' => array('constraint' => 10, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
        );

        if (!$new) {
            $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);
            \Config::set('warden.trackable', $delete ? false : true);
            $this->_save_config();
        }

        return $this->_alter_or_return_fields('Trackable', $fields, $new);
    }

    public function lockable($new = false)
    {
        $fields = array();

        $strategy = \Config::get('warden.lockable.lock_strategy');
        $strategy_opts = array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'default' => '0');

        if ($strategy != 'sign_in_count') {
            $fields[$strategy] = $strategy_opts;
        }

        $fields = array_merge($fields, array(
            'unlock_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => \DB::expr('NULL')),
            'locked_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        ));

        if (!$new) {
            $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);
            \Config::set('warden.lockable.in_use', $delete ? false : true);
            $this->_save_config();
        }

        return $this->_alter_or_return_fields('Lockable', $fields, $new);
    }

    public function omniauthable($new = false)
    {
        $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);

        if ($delete) {
            \DBUtil::drop_table('services');
        } else {
            \DBUtil::create_table('services', array(
                'id'   => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
                'user_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true),
                'uid' => array('constraint' => 255, 'type' => 'varchar'),
                'provider' => array('constraint' => 50, 'type' => 'varchar'),
                'access_token' => array('constraint' => 255, 'type' => 'varchar'),
                'access_secret' => array('constraint' => 255, 'type' => 'varchar'),
                'created_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
                'updated_at' => array('type' => 'timestamp ON UPDATE CURRENT_TIMESTAMP', 'default' => \DB::expr('CURRENT_TIMESTAMP'))
            ), array('id'), false, 'InnoDB', 'utf8_unicode_ci');

            \DB::query("ALTER TABLE services
                            ADD KEY index_services_on_access_token(access_token),
                            ADD KEY index_services_on_user_id(user_id),
                            ADD CONSTRAINT fk_index_services_on_user_id
                                FOREIGN KEY (user_id)
                                REFERENCES users (id) ON DELETE CASCADE",
                       \DB::UPDATE)->execute();
        }

        \Cli::write(\Cli::color('Created services table successfully', 'green'));

        if (!$new) {
            \Config::set('warden.omniauthable.in_use', $delete ? false : true);
            $this->_save_config();
        }
    }

    public function profilable($new = false)
    {
        $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);

        if ($delete) {
            \DBUtil::drop_table('profiles');
        } else {
            \DBUtil::create_table('profiles', array(
                'id'   => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
                'user_id' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true)
            ), array('id'), false, 'InnoDB', 'utf8_unicode_ci');

            \DB::query("ALTER TABLE profiles
                            ADD KEY index_profiles_on_user_id(user_id),
                            ADD CONSTRAINT fk_index_profiles_on_user_id
                                FOREIGN KEY (user_id)
                                REFERENCES users (id) ON DELETE CASCADE",
                       \DB::UPDATE)->execute();
        }

        if (!$new) {
            \Config::set('warden.profilable', $delete ? false : true);
            $this->_save_config();
        }

        \Cli::write(\Cli::color('Created profiles table successfully', 'green'));
    }

    private function _setup_config()
    {
        if (\Cli::option('r') || \Cli::option('recoverable')) {
            \Config::set('warden.recoverable.in_use', true);
        }

        if (\Cli::option('c') || \Cli::option('confirmable')) {
            \Config::set('warden.confirmable.in_use', true);
        }

        if (\Cli::option('t') || \Cli::option('trackable')) {
            \Config::set('warden.trackable', true);
        }

        if (\Cli::option('l') || \Cli::option('lockable')) {
            \Config::set('warden.lockable.in_use', true);
        }

        if (\Cli::option('o') || \Cli::option('omniauthable')) {
            \Config::set('warden.omniauthable.in_use', true);
        }

        if (\Cli::option('p') || \Cli::option('profilable')) {
            \Config::set('warden.profilable', true);
        }
    }

    private function _save_config()
    {
        try {
            \File::create(APPPATH.'config', 'warden.php');
        } catch (\FileAccessException $ex) {
            // Fail quietly
        }

        \Config::save('warden', \Config::get('warden'));
    }

    private function _alter_or_return_fields($name, array $fields, $new)
    {
        if (!$new) {
            $delete = \Cli::option('d', false) ? \Cli::option('d', false) : \Cli::option('delete', false);

            if ($delete) {
                \DBUtil::drop_fields('users', array_keys($fields));
                \Cli::write(\Cli::color("{$name} fields removed successfully", 'green'));
            } else {
                \DBUtil::add_fields('users', $fields);
                \Cli::write(\Cli::color("{$name} fields added successfully", 'green'));
            }
        } else {
            return $fields;
        }
    }
}