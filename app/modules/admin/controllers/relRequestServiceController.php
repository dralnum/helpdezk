<?php
class relRequestService extends Controllers {

    public function index() {
        
        $smarty = $this->retornaSmarty();

        $db = new logos_model();

        $reportslogo = $db->getReportsLogo();
        $smarty->assign('reportslogo', $reportslogo->fields['file_name']);
        $smarty->assign('reportsheight', $reportslogo->fields['height']);
        $smarty->assign('reportswidth', $reportslogo->fields['width']);
        
        $smarty->display('relRequestsService.tpl.html');
    }
	
	public function table_json() {
		include 'includes/classes/pipegrep/pipegrep.php';
		
		$pipe = new pipegrep();
		$db = new relRequestsService_model();
        
		$date_field_request = "a.entry_date";
      	$date_interval_request = $pipe->mysql_date_condition($date_field_request, $_POST['fromdate'] , $_POST['todate'], $this->getConfig('lang')) ;
		if ($date_interval_request) $date_interval_request = "AND " . $date_interval_request;
		
		$select = $db->getReport($date_interval_request);
				
		$output = array();
		
        while (!$select->EOF) {
            $area = $select->fields['name_area'];
			$type = $select->fields['name_type'];
			$item = $select->fields['name_item'];
			$service = $select->fields['name_service'];
            $total = $select->fields['total'];
			
			$output['result'][] = array(
            					"area"  => $area,
            					"type"  => $type,
            					"item"  => $item,
            					"service"  => $service,
            					"total"	=> $total            					
                            ) ;
						
            $total_all += $total;
            $select->MoveNext();
        }
		if($total_all>0)
			$output['total'] = $total_all;

        echo json_encode($output);

    }
}
