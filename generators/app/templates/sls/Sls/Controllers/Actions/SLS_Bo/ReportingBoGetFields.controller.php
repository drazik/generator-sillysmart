<?php
/**
* Class ReportingBoGetFields into Bo Controller
* @author SillySmart
* @copyright SillySmart
* @package Mvc.Controllers.Bo
* @see Mvc.Controllers.Bo.ControllerProtected
* @see Mvc.Controllers.SiteProtected
* @see Sls.Controllers.Core.SLS_GenericController
* @since 1.0
*
*/
class SLS_BoReportingBoGetFields extends SLS_BoControllerProtected
{
	public function action()
	{	
		$user = $this->hasAuthorative();
		
		$json = array();
        $xml = new SLS_XMLToolbox(file_get_contents($this->_generic->getPathConfig('configSls').'fks.xml'));

		$tableName = $this->_http->getParam('table_name');
		$tmp = explode('.', $tableName);

		if(count($tmp) != 2)
			die;

		$slsGraphQueryDbAlias = $tmp[0];
		$slsGraphQueryTable = $tmp[1];
		$this->sql->changeDb($slsGraphQueryDbAlias);

		if($this->sql->tableExists($slsGraphQueryTable))
		{
			$json['status'] = 'OK';
			$json['fields'] = array();

			$fields = $this->sql->showColumns($slsGraphQueryTable);

            // Get Attributes
            $getAttributes = $xml->getTagsAttributes('//sls_configs/entry[@tableFk="'.$slsGraphQueryDbAlias."_".$slsGraphQueryTable.'"]', array('columnFk', 'labelPk', 'tablePk'));

            // Format Attributes
            $columnFk = array();
            $labelPk = array();
            $tablePk = array();

            for($i = 0; $i < $count = count($getAttributes); $i++)
            {
                array_push($columnFk, $getAttributes[$i]['attributes'][0]['value']);
                array_push($labelPk, $getAttributes[$i]['attributes'][1]['value']);
                array_push($tablePk, $getAttributes[$i]['attributes'][2]['value']);
            }

            foreach($fields as $field)
			{
                if(in_array($field->Field, $columnFk))
                {
                    $isFk = true;
                    $index = array_search($field->Field, $columnFk);
                    $labelPkValue = $labelPk[$index];
                    $tablePkValue = $tablePk[$index];
                }
                else
                {
                    $isFk = false;
                }

				array_push($json['fields'], array(
					'field_name' => $field->Field,
					'field_table' => $slsGraphQueryTable,
					'field_label' => $field->Field,
					'field_isFk' => $isFk,
					'field_labelPk' => $labelPkValue,
					'field_tablePk' => empty($tablePkValue) ? '' : $slsGraphQueryDbAlias.'.'.SLS_String::substrAfterFirstDelimiter(strtolower($tablePkValue), '_'))
				);
			}
		}
		else{
			$json['status'] = 'ERROR';
			$json['error'] = "Table doesn't exist";
		}
		echo json_encode($json);
		die;
	}
}
?>