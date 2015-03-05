<?php
/**
 * SLS_QueryBuilder class
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package Sls.Generics.Sql
 * @since 1.0.7
 */
class SLS_QueryBuilder 
{
	// Query types
	const SELECT = 0;
    const DELETE = 1;
    const UPDATE = 2;
	
    // Vars
	private static $_instance;
	private $_type = SELECT;
	private $_db = null;
	private $_expr = null;
	private $_query = "";
	private $_parts = array('select'  => array(),
							'from'    => array(),
							'join'    => array(),
							'set'     => array(),
							'where'   => null,
							'groupBy' => array(),
							'having'  => null,
							'orderBy' => array(),
							'limit'	  => null
    					);
    					
	/**
	 * Constructor
	 * 
	 * @access public
	 * @since 1.0.7
	 */
	public function __construct() 
	{
		$this->_db = SLS_Sql::getInstance();
		$this->_expr = new SLS_ExprBuilder();
	}
	
	/**
	 * Singleton 
	 *
	 * @access public static
	 * @return SLS_QueryBuilder $instance SLS_QueryBuilder instance
	 * @since 1.0.7
	 */
	public static function getInstance() 
	{
		if (is_null(self::$_instance)) 		
			self::$_instance = new SLS_QueryBuilder();		
		return self::$_instance;
	}
	
	/**
	 * Get the SLS_ExprBuilder reference
	 * 
	 * @access public
	 * @return SLS_ExprBuilder SLS_ExprBuilder reference
	 * @since 1.0.7
	 */
	public function expr()
	{
		return $this->_expr;
	}
	
	/**
	 * Reset current querry
	 * 
	 * @access public
	 * @since 1.0.7
	 */
	public function resetQuery()
	{
		$this->_parts = array('select'  => array(),
							  'from'    => array(),
							  'join'    => array(),
							  'set'     => array(),
							  'where'   => null,
							  'groupBy' => array(),
							  'having'  => null,
							  'orderBy' => array(),
							  'limit'	=> null
    					);
	}
	
	/**
     * Appends to or replaces a single, generic query part ('select', 'from', 'set', 'where', 'groupBy', 'having', 'orderBy', 'limit')
     *
     * @access public
     * @param string $partName
     * @param mixed $part
     * @param bool $append
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     */
	public function add($partName, $part, $append = false)
	{
		$isArray = is_array($part);
        $isMultiple = is_array($this->_parts[$partName]);

        if ($isMultiple && !$isArray)
            $part = array($part);       
	
        if ($append) 
        {
            if ($partName == "orderBy" || $partName == "groupBy" || $partName == "select" || $partName == "set") 
            {
                foreach ($part as $cur_part)
                    $this->_parts[$partName][] = $cur_part;                
            } 
            else if ($isArray && is_array($part[key($part)])) 
            {            	
                $key = key($part);
                $this->_parts[$partName][$key][] = $part[$key];	            
            } 
            else if ($isMultiple)
                $this->_parts[$partName][] = $part;
            else
                $this->_parts[$partName] = $part;
            
            return $this;
        }

        $this->_parts[$partName] = $part;

        return $this;
	}
	
	/**
     * Start a select query & specifies item(s) to be selected
     *
     * @access public     
     * @param mixed $columns the columns to select. 
     * <code>
     * // Can be called with multiple type of parameters and with/without alias
	 * $qbd->select()
	 * // Or
	 * $qbd->select('*')
	 * // Or
	 * $qbd->select(array('*'))
	 * // Or
	 * $qbd->select('alias.col1','col2',...,'colN')
	 * // Or
	 * $qbd->select(array('col1','alias.col2',...,'colN'))
	 * </code>     
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select("news_id","news_title")
     *     ->from("news","n")
     */
	public function select($columns=null)
	{
		$args = func_get_args();
		$this->resetQuery();
		
		$this->_type = SELECT;
		
		if (!$this->startsWithRecursive(strtolower($columns),array("max(", "min(", "avg(", "sum(", "count(", "group_concat(")))
			$columns = ($columns == null || $columns == '*' || (is_array($columns) && count($columns) == 1 && array_shift($columns) == '*')) ? array('*') : (is_array($columns) ? array_map(array($this->_expr, 'protectColumn'),$columns) : array_map(array($this->_expr, 'protectColumn'),$args));
		return $this->add('select',$columns,false);
	}
	
	/**
     * Start a delete query
     *
     * @access public
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->delete()
     *     ->from("news","n")
     *     ->where($qbd->expr()->eq('n.news_deprecated','true'))
     */
	public function delete()
	{
		$this->resetQuery();
		
		$this->_type = DELETE;
		
		return $this;
	}
	
	/**
     * Start an update query
     *
     * @access public
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->update()
     *     ->from("news","n")
     *     ->set($qbd->expr()->eq('news_title','title updated'))
     *     ->where($qbd->expr()->eq('n.news_id',3))
     */
	public function update()
	{
		$this->resetQuery();
		
		$this->_type = UPDATE;
		
		return $this;
	}
	
	/**
     * Specifies main table for select, update or delete statment
     *
     * @access public
     * @param string $table the table on wich you want to process query
     * @param string $alias the alias of your table, if empty table name will be used
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     */
	public function from($table,$alias="")
	{
		return $this->add('from',array (
			'table' => $table, 
			'alias' => (empty($alias)) ? $table : $alias
		), true);
	}
	
	/**
     * Specifies natural join for select statment
     *
     * @access public
     * @param string $joinTable the table on wich you want to process a natural join
     * @param string $joinAlias the alias of your join, if empty table name will be used
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->naturalJoin("user","u")
     */
	public function naturalJoin($joinTable,$joinAlias="")
	{
		return $this->add('join', array(            
			'joinType'      => 'natural',
			'joinTable'     => $joinTable,
			'joinAlias'     => (empty($joinAlias)) ? $joinTable : $joinAlias,
			'joinCondition' => ''
            
        ), true);
	}
	
	/**
     * Specifies inner join for select statment
     *
     * @access public
     * @param string $joinTable the table on wich you want to process a inner join
     * @param string $joinCondition the condition for your join
     * @param string $joinAlias the alias of your join, if empty table name will be used
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->innerJoin("user","n.user_id = u.user_id","u")     
     */
	public function innerJoin($joinTable,$joinCondition,$joinAlias="")
	{	
		return $this->add('join', array(            
			'joinType'      => 'inner',
			'joinTable'     => $joinTable,
			'joinAlias'     => (empty($joinAlias)) ? $joinTable : $joinAlias,
			'joinCondition' => $joinCondition
            
        ), true);
	}
	
	/**
     * Specifies left join for select statment
     *
     * @access public
     * @param string $joinTable the table on wich you want to process a left join
     * @param string $joinCondition the condition for your join
     * @param string $joinAlias the alias of your join, if empty table name will be used
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->leftJoin("user","n.user_id = u.user_id","u")     
     */
	public function leftJoin($joinTable,$joinCondition,$joinAlias="")
	{
		return $this->add('join', array(            
			'joinType'      => 'left',
			'joinTable'     => $joinTable,
			'joinAlias'     => (empty($joinAlias)) ? $joinTable : $joinAlias,
			'joinCondition' => $joinCondition
            
        ), true);
	}
	
	/**
     * Specifies right join for select statment
     *
     * @access public
     * @param string $joinTable the table on wich you want to process a right join
     * @param string $joinCondition the condition for your join
     * @param string $joinAlias the alias of your join, if empty table name will be used
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->rightJoin("user","n.user_id = u.user_id","u")     
     */
	public function rightJoin($joinTable,$joinCondition,$joinAlias="")
	{
		return $this->add('join', array(            
			'joinType'      => 'right',
			'joinTable'     => $joinTable,
			'joinAlias'     => (empty($joinAlias)) ? $joinTable : $joinAlias,
			'joinCondition' => $joinCondition
            
        ), true);
	}
	
	/**
     * Specifies set for update statment
     *
     * @access public     
     * @param mixed $set expressions to set. 
     * <code>
     * // Can be called with multiple type of parameters and with/without alias
	 * $qbd->update()
	 *     ->from("news","n")
	 *     ->set($qbd->expr()->eq('news_title','title updated'))
	 * // Or
	 * $qbd->update()
	 *     ->from("news","n")
	 *     ->set($qbd->expr()->eq('news_title','title updated'),$qbd->expr()->eq('news_updated','1'))
	 * // Or
	 * * $qbd->update()
	 *     ->from("news","n")
	 *     ->set(array($qbd->expr()->eq('news_title','title updated'),$qbd->expr()->eq('news_updated','1')))
	 * </code>
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->update()
     *     ->from("news","n")
     *     ->set($qbd->expr()->eq('news_title','title updated')))
     */
	public function set($set)
	{
		$args = func_get_args();
		$set = is_array($set) ? $set : $args;
		return $this->add('set',$set,false);
	}
	
	/**
     * Specifies where for select, update or delete statment
     *
     * @access public     
     * @param string $where the expression where for select, update or delete statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->set($qbd->expr()->eq('category_id',8)))     
     */
	public function where($where)
	{		
		return $this->add('where',$this->_parts['where'].' '.$where, true);
	}
	
	/**
     * Specifies bracket opening in where for select, update or delete statment
     *
     * @access public  
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->whereOpenBracket()      
     */
	public function whereOpenBracket()
	{
		return $this->add('where',$this->_parts['where'].' ( ', true);
	}
	
	/**
     * Specifies bracket closing in where for select, update or delete statment
     *
     * @access public  
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->whereOpenBracket()      
     */
	public function whereCloseBracket()
	{
		return $this->add('where',$this->_parts['where'].' ) ', true);
	}
	
	/**
     * Specifies where AND for select, update or delete statment
     *
     * @access public     
     * @param string $where the expression where AND for select, update or delete statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->where($qbd->expr()->eq('category_id',4))
     *     ->whereAnd($qbd->expr()->eq('user_id',2))
     * // will produce "SELECT * FROM `news` n WHERE `category_id` = 4 AND `user_id` = 2"
     */
	public function whereAnd($where="")
	{
		return $this->add('where',$this->_parts['where'].' AND '.$where, true);
	}
	
	/**
     * Specifies where OR for select, update or delete statment
     *
     * @access public     
     * @param string $where the expression where OR for select, update or delete statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->where($qbd->expr()->eq('category_id',4))
     *     ->whereOr($qbd->expr()->eq('user_id',2))
     * // will produce "SELECT * FROM `news` n WHERE `category_id` = 4 OR `user_id` = 2"     
     */
	public function whereOr($where="")
	{
		return $this->add('where',$this->_parts['where'].' OR '.$where, true);
	}
	
	/**
     * Specifies item(s) to be grouped
     *
     * @access public     
     * @param mixed $columns the columns to group. 
     * <code>
     * // Can be called with multiple type of parameters and with/without alias
	 * $qbd->select()
	 *     ->from("news","n")
	 *     ->groupBy(array('news_id'))
	 * // Or
	 * $qbd->select()
	 *     ->from("news","n")
	 *     ->groupBy('n.news_id','category_id',...,'user_id')
	 * // Or
	 * * $qbd->select()
	 *     ->from("news","n")
	 *     ->groupBy(array('n.news_id','n.category_id',...,'user_id'))
	 * </code>
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     */
	public function groupBy($columns)
	{
		$args = func_get_args();
		$columns = is_array($columns) ? array_map(array($this->_expr, 'protectColumn'),$columns) : array_map(array($this->_expr, 'protectColumn'),$args);
		return $this->add('groupBy',$columns,false);
	}
	
	/**
     * Specifies having for select
     *
     * @access public     
     * @param string $having the expression having for select statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")
     *     ->where($qbd->expr()->eq('category_id',4))
     *     ->groupBy(array('news_id'))
     *     ->having($qbd->expr()->eq('news_id',2))
     * // will produce "SELECT * FROM `news` n WHERE `category_id` = 4 GROUP BY `news_id` HAVING `news_id` = 2"     
     */
	public function having($having)
	{
		return $this->add('having',$having,false);
	}
	
	/**
     * Specifies bracket opening in having for select
     *
     * @access public  
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")    
     *     ->groupBy(array('news_id'))
     *     ->havingOpenBracket()      
     */
	public function havingOpenBracket()
	{
		return $this->add('having',' ( ', true);
	}
	
	/**
     * Specifies bracket closing in having for select
     *
     * @access public  
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")    
     *     ->groupBy(array('news_id'))
     *     ->havingCloseBracket()       
     */
	public function havingCloseBracket()
	{
		return $this->add('having',' ) ', true);
	}
	
	/**
     * Specifies having AND for select
     *
     * @access public     
     * @param string $where the expression where AND for select statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")    
     *     ->groupBy(array('news_id'))
     *     ->having($qbd->expr()->eq('category_id',4))
     *     ->havingAnd($qbd->expr()->eq('user_id',2))
     * // will produce "SELECT * FROM `news` n GROUP BY `news_id` HAVING `category_id` = 4 AND `user_id` = 2"     
     */
	public function havingAnd($having)
	{
		return $this->add('having',' AND '.$having, true);
	}
	
	/**
     * Specifies having OR for select
     *
     * @access public     
     * @param string $where the expression where OR for select statment
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")    
     *     ->groupBy(array('news_id'))
     *     ->having($qbd->expr()->eq('category_id',4))
     *     ->havingOr($qbd->expr()->eq('user_id',2))
     * // will produce "SELECT * FROM `news` n GROUP BY `news_id` HAVING `category_id` = 4 OR `user_id` = 2"     
     */
	public function havingOr($having)
	{
		return $this->add('having',' OR '.$having, true);
	}
	
	/**
     * Specifies order
     *
     * @access public     
     * @param mixed $columns the columns to order.
     * @param mixed $order the order of columns 
     * <code>
     * // Can be called with multiple type of parameters and with/without alias
	 * $qbd->select()
	 *     ->from("news","n")
	 *     ->order('news_title')
	 * // Or
	 * $qbd->select()
	 *     ->from("news","n")
	 *     ->order('news_title','news_id')
	 * // Or
	 * $qbd->select()
	 *     ->from("news","n")
	 *     ->order(array('news.news_title','news_id'),array('asc','desc'))
	 * </code>     
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     */
	public function order($columns,$order="")
	{
		$columns = is_array($columns) ? array_map(array($this->_expr, 'protectColumn'),$columns) : array_map(array($this->_expr, 'protectColumn'),array($columns));
		$order = (empty($order)) ? array("ASC") : (is_array($order) ? $order : array($order));
		$orders = array();
		for($i=0 ; $i<$count=count($columns) ; $i++)
			array_push($orders,$columns[$i].' '.((isset($order[$i]) && in_array(strtoupper($order[$i]),array("ASC","DESC"))) ? strtoupper($order[$i]) : "ASC"));
		
		return $this->add('orderBy',$orders,false);
	}
	
	/**
     * Specifies random order
     *
     * @access public
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
	 *     ->from("news","n")
	 *     ->orderRand()
	 * // will produce "SELECT * FROM `news` n ORDER BY rand()"	 
	 * </code>     
     */
	public function orderRand()
	{
		return $this->add('orderBy',array('rand()'),false);
	}
	
	/**
     * Specifies limit for select, update or delete statment
     *
     * @access public     
     * @param string $start start offset if length is set, else number of wanted recordsets
     * @param string $length number of wanted recordsets
     * @return SLS_QueryBuilder $this current queryBuilder instance.
     * @since 1.0.7
     * @example
     * $qbd->select()
     *     ->from("news","n")    
     *     ->limit(5)
     * // will produce "SELECT * FROM `news` n LIMIT 5"     
     * $qbd->select()
     *     ->from("news","n")    
     *     ->limit(10,5)
     * // will produce "SELECT * FROM `news` n LIMIT 10,5"     
     */
	public function limit($start,$length="")
	{
		return $this->add('limit',$start.((empty($length)) ? ' ' : ', '.$length.' '), true);
	}
	
	/**
	 * Prepare SELECT query
	 * 
	 * @access private
	 * @since 1.0.7
	 */
	private function prepareSelect()
	{
		$fromClause = array();
		
		$query = 'SELECT '.implode(', ', $this->_parts['select']) . ' FROM ';		
		
		foreach($this->_parts['from'] as $from)
			array_push($fromClause,$this->_expr->protectColumn($from['table']).' '.$from['alias']);
		
		$query .= implode(', ',$fromClause);
			
		foreach($this->_parts['join'] as $join)
			$query .= ' '.strtoupper($join['joinType']).' JOIN '.$this->_expr->protectColumn($join['joinTable']).' '.$join['joinAlias'].' '.((strtolower($join['joinType']) != 'natural') ? 'ON '.$join['joinCondition'] : '');
		
		$query .= ($this->_parts['where'] !== null ? ' WHERE ' . ((string) $this->_parts['where']) : '')
                . ($this->_parts['groupBy'] ? ' GROUP BY ' . implode(', ', $this->_parts['groupBy']) : '')
                . ($this->_parts['having'] !== null ? ' HAVING ' . ((string) $this->_parts['having']) : '')
                . ($this->_parts['orderBy'] ? ' ORDER BY ' . implode(', ', $this->_parts['orderBy']) : '')
                . ($this->_parts['limit'] !== null ? ' LIMIT ' . ((string) $this->_parts['limit']) : '');
			
		$this->_query = $query;
	}
	
	/**
	 * Prepare DELETE query
	 * 
	 * @access private
	 * @since 1.0.7
	 */
	private function prepareDelete()
	{
		$fromClause = array();
		foreach($this->_parts['from'] as $from)
			array_push($fromClause,$this->_expr->protectColumn($from['table']));
				
		$query = 'DELETE FROM ' . implode(', ',$fromClause)
               . ($this->_parts['where'] !== null ? ' WHERE ' . ((string) $this->_parts['where']) : '')
               . ($this->_parts['limit'] !== null ? ' LIMIT ' . ((string) $this->_parts['limit']) : '');

		$this->_query = $query;		
	}
	
	/**
	 * Prepare UPDATE query
	 * 
	 * @access private
	 * @since 1.0.7
	 */
	private function prepareUpdate()
	{
		$fromClause = array();
		foreach($this->_parts['from'] as $from)
			array_push($fromClause,$this->_expr->protectColumn($from['table']).' '.$from['alias']);
		
		$query = 'UPDATE ' . implode(', ',$fromClause)
               . ' SET ' . implode(", ", $this->_parts['set'])
               . ($this->_parts['where'] !== null ? ' WHERE ' . ((string) $this->_parts['where']) : '')
               . ($this->_parts['limit'] !== null ? ' LIMIT ' . ((string) $this->_parts['limit']) : '');

		$this->_query = $query;		
	}
	
	/**
	 * Execute prepared query
	 * 
	 * @access public
	 * @return mixed array of PDO if "select", else int (number of rows deleted/updated)
	 * @since 1.0.7
	 * @example
	 * $qbd->select()
     *     ->from("news","n")    
     *     ->limit(10,5)
     *     ->execute();
	 */
	public function execute()
	{
		$this->_query = "";
		
		switch ($this->_type)
		{
			case SELECT:
				$this->prepareSelect();				
				return $this->_db->select($this->_query);
				break;
			case DELETE:
				$this->prepareDelete();
				return $this->_db->delete($this->_query);
				break;
			case UPDATE:
				$this->prepareUpdate();				
				return $this->_db->update($this->_query);
				break;
		}
	}
	
	/**
	 * Get litteral prepared query
	 * 
	 * @access public
	 * @return string $query the litteral sql query
	 * @since 1.0.7
	 */
	public function getQuery()
	{
		switch ($this->_type)
		{
			case SELECT:
				$this->prepareSelect();
				break;
			case DELETE:
				$this->prepareDelete();
				break;
			case UPDATE:
				$this->prepareUpdate();
				break;
		}
		
		return $this->_query;
	}
	
	/**
	 * StartsWith recursive
	 * 
	 * @access private
	 * @param string $hay the string in which you search
	 * @param mixed $needles the string or the array of occurences searched
	 * @return bool false if not starts with, else true
	 * @since 1.1
	 */
	private function startsWithRecursive($hay,$needles)
	{
		if (is_array($needles))
		{
			foreach($needles as $needle)
			{				
				if (SLS_String::startsWith($hay,$needle))				
					return true;
			}
		}
		else
			return SLS_String::startsWith($hay,$needles);
			
		return false;
	}
}
?>