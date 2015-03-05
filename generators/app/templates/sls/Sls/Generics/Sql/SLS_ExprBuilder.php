<?php
/**
 * SLS_ExprBuilder class
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Sql
 * @since 1.0.7
 */
class SLS_ExprBuilder 
{
	// Comparison constants
	const EQ  = '=';
    const NEQ = '!=';
    const LT  = '<';
    const LTE = '<=';
    const GT  = '>';
    const GTE = '>=';
    const LIKE = 'LIKE';
    const NLIKE = 'NOT LIKE';
    const IN = 'IN';
	const NIN = 'NOT IN';
    const ESC = '`'; 
	
	// Vars
	private static $_instance;
	private $_db = null;	   

	/**
	 * Constructor
	 * 
	 * @access public
	 * @since 1.0.7
	 */
	public function __construct() 
	{
		$this->_db = SLS_Sql::getInstance();
	}
	
	/**
	 * Singleton 
	 *
	 * @access public static
	 * @return SLS_ExprBuilder $instance SLS_ExprBuilder instance
	 * @since 1.0.7
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance)) 		
			self::$_instance = new SLS_ExprBuilder();		
		return self::$_instance;
	}
	
	/**
	 * Protect a sql column
	 * 
	 * @param string $col the column to protect
	 * @return string column protected
	 * @since 1.0.7
	 * @example
     * protectColumn("news_id")
     * // will produce "`news_id`"
     * protectColumn("news.news_id")
     * // will produce "`news`.`news_id`"
	 */
	public function protectColumn($col)
	{
		return ((SLS_String::contains($col,'.'))
				? self::ESC.SLS_String::substrBeforeFirstDelimiter($col,'.').self::ESC.'.'.self::ESC.SLS_String::substrAfterFirstDelimiter($col,'.').self::ESC
				: self::ESC.$col.self::ESC);
	}
	
	/**
	 * Proceed equal comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * eq("news_id","4")
     * // will produce "`news_id` = '4'"
	 */
	public function eq($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::EQ.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed not equal comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * neq("news_id","4")
     * // will produce "`news_id` != '4'"
	 */
	public function neq($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::NEQ.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed lower comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * lt("news_id","4")
     * // will produce "`news_id` < '4'"
	 */
	public function lt($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::LT.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed lower or equal comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * lte("news_id","4")
     * // will produce "`news_id` <= '4'"
	 */
	public function lte($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::LTE.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed greater comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * gt("news_id","4")
     * // will produce "`news_id` > '4'"
	 */
	public function gt($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::GT.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed greater or equal comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * gte("news_id","4")
     * // will produce "`news_id` >= '4'"
	 */
	public function gte($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::GTE.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed is null comparison
	 * 
	 * @param string $col the column to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * isNull("news_id")
     * // will produce "`news_id` IS NULL"
	 */
	public function isNull($col)
	{
		return $this->protectColumn($col)
				.' IS NULL'.' ';			
	}
	
	/**
	 * Proceed is not null comparison
	 * 
	 * @param string $col the column to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * isNotNull("news_id")
     * // will produce "`news_id` IS NOT NULL"
	 */
	public function isNotNull($col)
	{
		return $this->protectColumn($col)
				.' IS NOT NULL'.' ';			
	}
	
	/**
	 * Proceed like comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * like("news_title","%e%")
     * // will produce "`news_title` LIKE '%e%'"
	 */
	public function like($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::LIKE.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed not like comparison
	 * 
	 * @param string $col the column to compare
	 * @param string $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * nlike("news_title","%e%")
     * // will produce "`news_title` NOT LIKE '%e%'"
	 */
	public function nlike($col,$value)
	{
		return $this->protectColumn($col)
				.' '.self::NLIKE.' '
				.$this->_db->quote($value).' ';
	}
	
	/**
	 * Proceed in comparison
	 * 
	 * @param string $col the column to compare
	 * @param array $value the value to compare
	 * @return string the string formated
	 * @since 1.0.7
	 * @example
     * in("news_id",array("1","3"))
     * // will produce "`news_id` IN (1,3)"
	 */
	public function in($col,$value)
	{
		$value = (is_array($value)) ? array_map(array($this->_db, 'quote'),$value) : array($this->_db->quote($value));
		
		return $this->protectColumn($col)
				.' '.self::IN.' '
				.'( '.implode(",",$value).' ) ';
	}
	
	/**
	 * Proceed not in comparison
	 * 
	 * @param string $col the column to compare
	 * @param array $value the value to compare
	 * @return string the string formated
	 * @since 1.0.9
	 * @example
     * in("news_id",array("1","3"))
     * // will produce "`news_id` NOT IN (1,3)"
	 */
	public function nin($col,$value)
	{
		$value = (is_array($value)) ? array_map(array($this->_db, 'quote'),$value) : array($this->_db->quote($value));
		
		return $this->protectColumn($col)
				.' '.self::NIN.' '
				.'( '.implode(",",$value).' ) ';
	}
}