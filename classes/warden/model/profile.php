<?php
/**
 * Warden: User authorization & authentication library for FuelPHP.
 *
 * @package    Warden
 * @subpackage Warden
 * @version    2.0
 * @author     Andrew Wayne <lifeandcoding@gmail.com>
 * @license    MIT License
 * @copyright  (c) 2011 - 2013 Andrew Wayne
 */
namespace Warden;

/**
 * Model_Profile
 *
 * @package    Warden
 * @subpackage Warden
 */
class Model_Profile extends \Orm\Model
{
	/**
	 * Validation regular expression for a name
	 */
	// const REGEX_NAME = '\^[^;]+$';

	/**
	 * Belongs to; relationship properties
	 *
	 * @var array
	 */
	protected static $_belongs_to = array(
		'user' => array(
			'key_from'       => 'user_id',
			'model_to'       => 'Model_User',
			'key_to'         => 'id',
			'cascade_save'   => true,
			'cascade_delete' => true
		)
	);

	/**
	 * Object properties
	 *
	 * @var array
	 */
	protected static $_properties = array(
        'id',
        'nome',
        'foto',
        'sexo',
        'nascimento',
        'cidade',
        'estado',
        'sicard',
        'cpf',
        'identidade',
        'orgexp',
        'situacao',
        'data_situacao',
        'filiacao',
        'user_id',
    );

	/**
	 * Observer classes to use
	 *
	 * @var array
	 */
	// protected static $_observers = array(
	//   'Warden\\Observer_Timestamps'
	// );
}
