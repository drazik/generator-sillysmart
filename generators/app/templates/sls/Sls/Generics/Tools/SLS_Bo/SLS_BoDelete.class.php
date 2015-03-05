<?php
/**
 * SLS_BoDelete Tool - Generate back-office deleting
 *  
 * @author Laurent Bientz 
 * @copyright SillySmart
 * @package SLS.Generics.Tools.SLS_Bo
 * @since 1.1 
 */
class SLS_BoDelete extends __SLS_Bo
{		
	public $_xml = null;
	public $_db_alias = null;
	public $_table = null;
	public $_forward = true;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @since 1.0
	 */
	public function __construct($xml,$db,$table,$forward=true)
	{
		parent::__construct();
		
		$this->_xml = $xml;
		$this->_db_alias = $db;
		$this->_table = $table;
		$this->_forward = $forward;
		
		# Objects
		$className = ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table);
		$this->_generic->useModel(SLS_String::tableToClass($this->_table),ucfirst(strtolower($this->_db_alias)),"user");
		$this->_object = new $className();
		$this->_table = $this->_object->getTable();
		$nbDelete = 0;
		# /Objects
				
		# Params
		$ids = $this->_http->getParam("id");
		$ids = (SLS_String::contains($ids,"|")) ? explode("|",$ids) : array($ids);
		# /Params
		
		# Perform delete
		foreach($ids as $id)
			if ($this->_object->getModel($id) === true)
				$nbDelete += $this->_object->delete(true);
		if ($this->_object->isMultilanguage() && is_numeric($nbDelete) && $nbDelete > 0)
			$nbDelete = floor($nbDelete / count($this->_lang->getSiteLangs()));
		# Perform delete
		
		if ($this->_async)
		{
			if ($nbDelete !== false && is_numeric($nbDelete) && $nbDelete > 0)
			{
				$this->_render["status"] = "OK";
				$this->_render["result"]["message"] = ($nbDelete==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETES'],$nbDelete);
			}
			echo json_encode($this->_render);
			die();
		}
		else
		{
			# Notif
			if ($nbDelete !== false && is_numeric($nbDelete) && $nbDelete > 0)
				$this->pushNotif("success",($nbDelete==1) ? $GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETE'] : sprintf($GLOBALS[$GLOBALS['PROJECT_NAME']]['JS']['SLS_BO_GENERIC_SUBMIT_SUCCESS_DELETES'],$nbDelete));
			# /Notif
			
			# Forward
			if ($this->_forward)
			{
				$rememberList = (is_array($this->_session->getParam("SLS_BO_LIST"))) ? $this->_session->getParam("SLS_BO_LIST") : array();
				if (array_key_exists($this->_db_alias."_".$this->_table,$rememberList) && !empty($rememberList[$this->_db_alias."_".$this->_table]))
					$this->_generic->redirect($rememberList[$this->_db_alias."_".$this->_table]);
				else
					$this->_generic->forward($this->_boController,"List".ucfirst(strtolower($this->_db_alias))."_".SLS_String::tableToClass($this->_table));
			}
		}
		# /Forward
	}
}
?>