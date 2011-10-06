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
namespace Fuel\Tasks;

/**
 * Task that makes it easier to install Warden from console.
 *
 * WARNING: This is uncomplete.
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
        $table_name = \Cli::prompt("\nPlease enter the user table name:", 'users');

        \Cli::write('Setting up Warden config...');

        $this->install($table_name);

        \Cli::write('Saving Warden config...');
//        \Config::save('warden', \Config::get('warden'));
    }

    public function install($table_name = 'users')
    {
        \Cli::write('================================================================');
        \Cli::write('Creating "'.$table_name.'" table...');

        $fields = array(
            'id'    => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'auto_increment' => true),
            'email' => array('default' => '', 'constraint' => 255, 'type' => 'varchar'),
            'username' => array('constraint' => 32, 'type' => 'varchar'),
            'encrypted_password' => array('constraint' => 60, 'type' => 'varbinary'),
            'authentication_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'remember_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
        );

        if (\Config::get('warden.recoverable.in_use') === true) {
            $fields = array_merge($fields, $this->recoverable());
        }

        if (\Config::get('warden.confirmable.in_use') === true) {
            $fields = array_merge($fields, $this->confirmable());
        }

        if (\Config::get('warden.trackable') === true) {
            $fields = array_merge($fields, $this->trackable());
        }

        if (\Config::get('warden.lockable.in_use') === true) {
            $fields = array_merge($fields, $this->lockable());
        }

        $fields = array_merge($fields, array(
            'created_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'updated_at' => array('type' => 'timestamp ON UPDATE CURRENT_TIMESTAMP', 'default' => \DB::expr('CURRENT_TIMESTAMP'))
        ));

        var_dump($fields);exit;
        \DBUtil::create_table($table_name, $fields, array('id'), true, 'InnoDB', 'utf8_unicode_ci');

        \DB::query("ALTER TABLE $table_name ADD UNIQUE index_users_on_email(email)", \DB::UPDATE)->execute();
        \DB::query("ALTER TABLE $table_name ADD UNIQUE index_users_on_username(username)", \DB::UPDATE)->execute();
        \DB::query("ALTER TABLE $table_name ADD UNIQUE index_users_on_authentication_token(authentication_token)", \DB::UPDATE)->execute();

        \Cli::color('Created "'.$table_name.'" table successfully', 'green');
        \Cli::write('================================================================');
    }

    public function recoverable()
    {
        return array(
            'reset_password_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'reset_password_sent_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        );
    }

    public function confirmable()
    {
        return array(
            'is_confirmed' => array('constraint' => 1, 'type' => 'tinyint', 'unsigned' => true, 'default' => '0'),
            'confirmation_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'confirmation_sent_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        );
    }

    public function trackable()
    {
        return array(
            'sign_in_count' => array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
            'confirmation_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'current_sign_in_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'last_sign_in_at' => array('type' => 'timestamp', 'default' => '0000-00-00 00:00:00'),
            'current_sign_in_ip' => array('constraint' => 10, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
            'last_sign_in_ip' => array('constraint' => 10, 'type' => 'int', 'unsigned' => true, 'default' => '0'),
        );
    }

    public function lockable()
    {
        $return = array();

        $strategy = \Config::get('warden.lockable.lock_strategy');
        $strategy_opts = array('constraint' => 11, 'type' => 'int', 'unsigned' => true, 'default' => '0');

        if ($strategy != 'sign_in_count') {
            $return[$strategy] = $strategy_opts;
        }

        return array_merge($return, array(
            'unlock_token' => array('constraint' => 60, 'type' => 'varbinary', 'null' => true, 'default' => null),
            'locked_at' => array('type' => 'datetime', 'default' => '0000-00-00 00:00:00'),
        ));
    }
}