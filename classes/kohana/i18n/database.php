<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Database-based i18n reader.
 *
 * @package    Kohana
 * @category   I18n
 * @author     Kohana Team
 * @copyright  (c) 2010 Kohana Team
 * @license    http://kohanaphp.com/license
 */
class Kohana_I18n_Database extends Kohana_I18n_Reader {

	protected $_database_instance = 'default';

	protected $_database_table = 'i18n';

	public function __construct(array $config = NULL)
	{
		if (isset($config['instance']))
		{
			$this->_database_instance = $config['instance'];
		}

		if (isset($config['table']))
		{
			$this->_database_table = $config['table'];
		}

		parent::__construct();
	}

	/**
	 * Query the i18n table for all values for this lang
	 *
	 * @param   string  i18n lang
	 * @return  $this   clone of the current object
	 */
	public function load($lang, array $messages = NULL)
	{
		if ($messages === NULL)
		{
			// Split the language: language, region, locale, etc
			$parts = explode('-', $lang);

			$table = array();

			do
			{
				// Create a locale for this set of parts
				$locale = implode('-', $parts);

				$query = DB::select('key', 'value')
						->from($this->_database_table)
						->where('locale', '=', $locale)
						//->limit(1)
						->execute($this->_database_instance);

				if (count($query) > 0)
				{
					$table += $query->as_array('key', 'value');
				}

				// Remove the last part
				array_pop($parts);
			}
			while ($parts);

			if ( ! empty($table))
			{
				$messages = $table;
			}
		}

		return parent::load($lang, $messages);
	}

	/**
	 * Insert a missing key to the i18n table
	 *
	 * @param   string   array key
	 * @return  boolean
	 */
	public function offsetExists($key)
	{
		if ( ! parent::offsetExists($key))
		{
			// Insert a new value
			DB::insert($this->_database_table, array('locale', 'key', 'value'))
				->values(array($this->_lang, $key, $key))
				->execute($this->_database_instance);

			return FALSE;
		}

		return TRUE;
	}

} // End Kohana_I18n_Database