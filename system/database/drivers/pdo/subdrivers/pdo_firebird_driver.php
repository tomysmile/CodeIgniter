<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2012, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 3.0.0
 * @filesource
 */

/**
 * PDO Firebird Database Adapter Class
 *
 * Note: _DB is an extender class that the app controller
 * creates dynamically based on whether the query builder
 * class is being used or not.
 *
 * @package		CodeIgniter
 * @subpackage	Drivers
 * @category	Database
 * @author		EllisLab Dev Team
 * @link		http://codeigniter.com/user_guide/database/
 */
class CI_DB_pdo_firebird_driver extends CI_DB_pdo_driver {

	public $subdriver = 'firebird';

	/**
	 * The syntax to count rows is slightly different across different
	 * database engines, so this string appears in each driver and is
	 * used for the count_all() and count_all_results() functions.
	 */
	protected $_random_keyword = ' RANDOM()'; // Currently not supported

	/**
	 * Constructor
	 *
	 * Builds the DSN if not already set.
	 *
	 * @param	array
	 * @return	void
	 */
	public function __construct($params)
	{
		parent::__construct($params);

		if (empty($this->dsn))
		{
			$this->dsn = 'firebird:';

			if ( ! empty($this->database))
			{
				$this->dsn .= 'dbname='.$this->database;
			}
			elseif ( ! empty($this->hostname))
			{
				$this->dsn .= 'dbname='.$this->hostname;
			}

			empty($this->char_set) OR $this->dsn .= ';charset='.$this->char_set;
			empty($this->role) OR $this->dsn .= ';role='.$this->role;
		}
		elseif ( ! empty($this->char_set) && strpos($this->dsn, 'charset=', 9) === FALSE)
		{
			$this->dsn .= ';charset='.$this->char_set;
		}
	}

	// --------------------------------------------------------------------

	/**
	 * Show table query
	 *
	 * Generates a platform-specific query string so that the table names can be fetched
	 *
	 * @param	bool
	 * @return	string
	 */
	protected function _list_tables($prefix_limit = FALSE)
	{
		$sql = 'SELECT "RDB$RELATION_NAME" FROM "RDB$RELATIONS" WHERE "RDB$RELATION_NAME" NOT LIKE \'RDB$%\' AND "RDB$RELATION_NAME" NOT LIKE \'MON$%\'';

		if ($prefix_limit === TRUE && $this->dbprefix !== '')
		{
			return $sql.' AND "RDB$RELATION_NAME" LIKE \''.$this->escape_like_str($this->dbprefix)."%' "
				.sprintf($this->_like_escape_str, $this->_like_escape_chr);
		}

		return $sql;
	}

	// --------------------------------------------------------------------

	/**
	 * Show column query
	 *
	 * Generates a platform-specific query string so that the column names can be fetched
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _list_columns($table = '')
	{
		return 'SELECT "RDB$FIELD_NAME" FROM "RDB$RELATION_FIELDS" WHERE "RDB$RELATION_NAME" = '.$this->escape($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Field data query
	 *
	 * Generates a platform-specific query so that the column data can be retrieved
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _field_data($table)
	{
		return 'SELECT FIRST 1 * FROM '.$this->protect_identifiers($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Update statement
	 *
	 * Generates a platform-specific update string from the supplied data
	 *
	 * @param	string	the table name
	 * @param	array	the update data
	 * @return	string
	 */
	protected function _update($table, $values)
	{
		$this->qb_limit = FALSE;
		return parent::_update($table, $values);
	}

	// --------------------------------------------------------------------

	/**
	 * Truncate statement
	 *
	 * Generates a platform-specific truncate string from the supplied data
	 *
	 * If the database does not support the truncate() command,
	 * then this method maps to 'DELETE FROM table'
	 *
	 * @param	string	the table name
	 * @return	string
         */
	protected function _truncate($table)
	{
		return 'DELETE FROM '.$table;
	}

	// --------------------------------------------------------------------

	/**
	 * Delete statement
	 *
	 * Generates a platform-specific delete string from the supplied data
	 *
	 * @param	string	the table name
	 * @return	string
	 */
	protected function _delete($table)
	{
		$this->qb_limit = FALSE;
		return parent::_delete($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Limit string
	 *
	 * Generates a platform-specific LIMIT clause
	 *
	 * @param	string	the sql query string
	 * @return	string
	 */
	protected function _limit($sql)
	{
		// Limit clause depends on if Interbase or Firebird
		if (stripos($this->version(), 'firebird') !== FALSE)
		{
			$select = 'FIRST '.$this->qb_limit
				.($this->qb_offset > 0 ? ' SKIP '.$this->qb_offset : '');
		}
		else
		{
			$select = 'ROWS '
				.($this->qb_offset > 0 ? $this->qb_offset.' TO '.($this->qb_limit + $this->qb_offset) : $this->qb_limit);
		}

		return preg_replace('`SELECT`i', 'SELECT '.$select, $sql);
	}

}

/* End of file pdo_firebird_driver.php */
/* Location: ./system/database/drivers/pdo/subdrivers/pdo_firebird_driver.php */