<?php

require_once(HELPDEZK_PATH . '/app/modules/helpdezk/controllers/hdkCommonController.php');
require_once(HELPDEZK_PATH . '/includes/classes/pipegrep/trello.php');

class hdkTrello extends hdkCommon
{
    /**
     * Create an instance, check session time
     *
     * @access public
     */
    public function __construct()
    {

        parent::__construct();
        session_start();
        $this->sessionValidate();

        $this->idPerson = $_SESSION['SES_COD_USUARIO'];

        $this->credential = $this->getCredentials();
        $this->_key = $this->credential['key'];
        $this->_token =  $this->credential['token'];
        $this->_secret = '';

        $this->trello = new trello($this->_key, $this->_secret, $this->_token);

        // Log settings
        $this->log = parent::$_logStatus;
        $this->program = basename(__FILE__);

        $this->loadModel('admin/userconfig_model');
        $this->dbUserConfig = new userconfig_model();

    }


    public function getCards()
    {
        $idList = $this->getParam('idlist');
        $data = $this->trello->getCards($idList);
        if($data['success']){
            echo $this->_tableCards($data['return']);
        } else {
            print $data['message'];
        }

    }

    function _tableCards($data)
    {

        foreach ($data as $row) {
            $fieldsID[] = $row['id'];
            $values[]   = $row['name'];
        }

        $arrCards['ids']    = $fieldsID;
        $arrCards['values'] = $values;

        $table = '<table class="table"><thead><tr><th>#</th><th>First Name</th></tr></thead><tbody>';

        $i = 0;
        foreach ( $arrCards['ids'] as $indexKey => $indexValue ) {
            $i++;
            $table .= '<tr><td>'.$i.'</td><td>'.$arrCards['values'][$indexKey].'</td></tr>';
        }

        $table .= '</tbody></table>';

        return $table;

    }

    public function getLists() {
        $idBoard = $this->getParam('idboard');
        $data = $this->trello->getLists($idBoard);
        if($data['success']){
            echo $this->_comboLists($data['return']);
        } else {
            print $data['message'];
        }

    }

    function _comboLists($data)
    {
        foreach ($data as $row) {
            $fieldsID[] = $row['id'];
            $values[]   = $row['name'];
        }

        $arrLists['ids']    = $fieldsID;
        $arrLists['values'] = $values;

        $select = '';
        foreach ( $arrLists['ids'] as $indexKey => $indexValue ) {
            if ($arrLists['default'][$indexKey] == 1) {
                $default = 'selected="selected"';
            } else {
                $default = '';
            }
            $select .= "<option value='$indexValue' $default>".$arrLists['values'][$indexKey]."</option>";
        }
        return $select;


    }

    public function getBoards()
    {

        $data = $this->trello->getBoards();
        if($data['success']){
            echo $this->_comboBoards($data['return']);
        } else {
            print $data['message'];
        }

    }


    function _comboBoards($data)
    {
        foreach ($data as $row) {
            $fieldsID[] = $row['id'];
            $values[]   = $row['name'];
        }

        $arrBoards['ids']    = $fieldsID;
        $arrBoards['values'] = $values;

        $select = '';
        foreach ( $arrBoards['ids'] as $indexKey => $indexValue ) {
            if ($arrBoards['default'][$indexKey] == 1) {
                $default = 'selected="selected"';
            } else {
                $default = '';
            }
            $select .= "<option value='$indexValue' $default>".$arrBoards['values'][$indexKey]."</option>";
        }
        return $select;


    }



    public function getCredentials()
    {

        $rsExternal = $this->getConfigExternalById($this->idPerson);
        $arrayRet = array();
        while (!$rsExternal->EOF) {
            if ($rsExternal->fields['idexternalapp'] == 50 && $rsExternal->fields['fieldname'] == 'key' ) {
                $arrayRet['key'] = $rsExternal->fields['value'];
            } elseif ($rsExternal->fields['idexternalapp'] == 50 && $rsExternal->fields['fieldname'] == 'token' ){
                $arrayRet['token'] = $rsExternal->fields['value'];
            }
            $rsExternal->MoveNext();
        }
        return $arrayRet;

    }
    public function jsonGrid()
    {
        $this->validasessao();
        $smarty = $this->retornaSmarty();

        $where = '';

        // create the query.
        $page  = $_POST['page'];
        $rows  = $_POST['rows'];
        $sidx  = $_POST['sidx'];
        $sord  = $_POST['sord'];

        if(!$sidx)
            $sidx ='area,type,item,service,reason,status';
        if(!$sord)
            $sord ='asc';

        if ($_POST['_search'] == 'true'){
            switch ($_POST['searchField']){
                case 'area':
                    $searchField = 'tba.`name`';
                    break;
                case 'type':
                    $searchField = 'tbt.`name`';
                    break;
                case 'item':
                    $searchField = 'tbi.`name`';
                    break;
                case 'service':
                    $searchField = 'tbs.`name`';
                    break;
                default:
                    $searchField = 'tbr.`name`';
                    break;
            }

            $where .= ' AND ' . $this->getJqGridOperation($_POST['searchOper'],$searchField,$_POST['searchString']);

        }

        $rsCount = $this->dbReason->selectReason($where);
        $count = $rsCount->RecordCount();

        if( $count > 0 && $rows > 0) {
            $total_pages = ceil($count/$rows);
        } else {
            $total_pages = 0;
        }
        if ($page > $total_pages) $page=$total_pages;
        $start = $rows*$page - $rows;
        if($start <0) $start = 0;

        $order = "ORDER BY $sidx $sord";
        $limit = "LIMIT $start , $rows";
        //

        $rsReqReason = $this->dbReason->selectReason($where,$order,$limit);
        
        while (!$rsReqReason->EOF) {
            $status_fmt = ($rsReqReason->fields['status'] == 'A' ) ? '<span class="label label-info">A</span>' : '<span class="label label-danger">I</span>';
            
            $aColumns[] = array(
                'id'=> $rsReqReason->fields['idreason'],
                'area'=> $rsReqReason->fields['area'],
                'type'=> $rsReqReason->fields['type'],
                'item' => $rsReqReason->fields['item'],
                'service' => $rsReqReason->fields['service'],
                'reason' => $rsReqReason->fields['reason'],
                'status' => $status_fmt,
                'statusval' => $rsReqReason->fields['status']
            );
            $rsReqReason->MoveNext();
        }

        $data = array(
            'page' => $page,
            'total' => $total_pages,
            'records' => $count,
            'rows' => $aColumns
        );

        echo json_encode($data);

    }

    public function formCreateReason()
    {
        $smarty = $this->retornaSmarty();

        $this->makeScreenReason($smarty,'','create');

        $smarty->assign('token', $this->_makeToken()) ;
        
        $this->makeNavVariables($smarty,'admin');
        $this->makeFooterVariables($smarty);
        $this->makeNavAdmin($smarty);
        
        $smarty->assign('navBar', 'file:'.$this->helpdezkPath.'/app/modules/main/views/nav-main.tpl');
        $smarty->display('reason-create.tpl');
    }

    public function formUpdateReason()
    {
        $token = $this->_makeToken();
        $this->logIt('token gerado: '.$token.' - program: '.$this->program.' - method: '. __METHOD__ ,7,'general',__LINE__);

        $smarty = $this->retornaSmarty();

        $idreason = $this->getParam('idreason');
        $where = "AND tbr.idreason = $idreason";
        
        $rsGetEmail = $this->dbReason->getReasonData($where);

        $this->makeScreenReason($smarty,$rsGetEmail,'update');

        $smarty->assign('token', $token) ;

        $smarty->assign('hidden_idreason', $idreason);

        $this->makeNavVariables($smarty,'admin');
        $this->makeFooterVariables($smarty);
        $this->makeNavAdmin($smarty);
        $smarty->assign('navBar', 'file:'.$this->helpdezkPath.'/app/modules/main/views/nav-main.tpl');
        $smarty->display('reason-update.tpl');

    }

    function makeScreenReason($objSmarty,$rs,$oper)
    {
        if ($oper == 'update') {
            $objSmarty->assign('hidden_idreason',  $rs->fields['idreason']);
            $objSmarty->assign('txtReason',  $rs->fields['reason']);
            $objSmarty->assign('checkedAvailable',  $rs->fields['status'] == 'A' ? 'checked=checked' : '');
        }

        // --- Area ---        
        if ($oper == 'update') {
            $idAreaDefault = $rs->fields['idarea'];
        } elseif ($oper == 'create') {
            $idAreaDefault = '';
        } 
        $arrArea = $this->_comboArea();

        $objSmarty->assign('areaids',  $arrArea['ids']);
        $objSmarty->assign('areavals', $arrArea['values']);
        $objSmarty->assign('idarea', $idAreaDefault);
        
        // --- Type ---
        $arrType = $this->_comboType($idAreaDefault);
        if ($oper == 'update') {
            $idtype = $rs->fields['idtype'];
        } elseif ($oper == 'create') {
            $idtype = $arrType['ids'][0];            
        }        
        $objSmarty->assign('typeids',  $arrType['ids']);
        $objSmarty->assign('typevals', $arrType['values']);
        $objSmarty->assign('idtype', $idtype);

        // --- Item ---
        $arrItem = $this->_comboItem($idtype);
        if ($oper == 'update') {
            $iditem = $rs->fields['iditem'];
        } elseif ($oper == 'create') {
            $iditem = $arrItem['ids'][0];            
        }
        $objSmarty->assign('itemids',  $arrItem['ids']);
        $objSmarty->assign('itemvals', $arrItem['values']);
        $objSmarty->assign('iditem', $iditem);

        // --- Service ---
        $arrService = $this->_comboService($iditem);
        if ($oper == 'update') {
            $idservice = $rs->fields['idservice'];
        } elseif ($oper == 'create') {
            $idservice = $arrService['ids'][0];            
        }
        $objSmarty->assign('serviceids',  $arrService['ids']);
        $objSmarty->assign('servicevals', $arrService['values']);
        $objSmarty->assign('idservice', $idservice);

        // --- Login layout ---        
        if ($oper == 'update') {
            $idLayoutDefault = $rs->fields['login_layout'];
        } elseif ($oper == 'create') {
            $idLayoutDefault = "";            
        } 
        $arrLoginLayout = $this->_comboLoginLayout();        
        $objSmarty->assign('loginlayoutids',  $arrLoginLayout['ids']);
        $objSmarty->assign('loginlayoutvals', $arrLoginLayout['values']);
        $objSmarty->assign('idloginlayout', $idLayoutDefault);

        // --- Company ---        
        if ($oper == 'update') {
            $idCompanyDefault = $rs->fields['idperson'];
        } elseif ($oper == 'create') {
            $idCompanyDefault = "";            
        } 
        $arrCompany = $this->_comboCompanies();        
        $objSmarty->assign('companyids',  $arrCompany['ids']);
        $objSmarty->assign('companyvals', $arrCompany['values']);
        $objSmarty->assign('idcompany', $idCompanyDefault);

        // --- Department ---        
        if ($oper == 'update' && $rs->fields['iddepartment']) {
            $idDepartmentDefault = $rs->fields['iddepartment'];
            
            $arrDepartment = $this->_comboDepartment($idCompanyDefault);        
            $objSmarty->assign('departmentids',  $arrDepartment['ids']);
            $objSmarty->assign('departmentvals', $arrDepartment['values']);
            $objSmarty->assign('iddepartment', $idDepartmentDefault);
        } 
        
        

    }

    function createReason()
    {
        if (!$this->_checkToken()) {
            if($this->log)
                $this->logIt('Error Token: '.$this->_getToken().' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }        

        $idservice = $_POST['cmbService'];
        $reason = addslashes($_POST['txtReason']);

        $this->dbReason->BeginTrans();
        
        $ret = $this->dbReason->insertReason($reason, $idservice);

        if(!$ret){
            $this->dbReason->RollbackTrans();
            if($this->log)
                $this->logIt('Insert Request Reason - User: '.$_SESSION['SES_LOGIN_PERSON'].' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }

        $this->dbReason->CommitTrans();
        
        $aRet = array(
            "status" => "Ok"
        );

        echo json_encode($aRet);

    }

    function updateReason()
    {
        if (!$this->_checkToken()) {
            if($this->log)
                $this->logIt('Error Token: '.$this->_getToken().' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }        
        
        $idreason = $_POST['idreason'];
        $idservice = $_POST['cmbService'];
        $reason = addslashes($_POST['txtReason']);
        $available = isset($_POST['checkAvailable']) ? 'A' : 'N';

        $this->dbReason->BeginTrans();
        $ret = $this->dbReason->updateReason($idreason, $reason, $idservice, $available);

        if(!$ret){
            $this->dbReason->RollbackTrans();
            if($this->log)
                $this->logIt('Update Request Reason - User: '.$_SESSION['SES_LOGIN_PERSON'].' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }

        $this->dbReason->CommitTrans();
        
        $aRet = array(
            "status" => "Ok"
        );

        echo json_encode($aRet);

    }

    function deleteReason()
    {
        if (!$this->_checkToken()) {
            if($this->log)
                $this->logIt('Error Token: '.$this->_getToken().' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }

        $idreason = $_POST['idreason'];

        $this->dbReason->BeginTrans();

        $dea = $this->dbReason->reasonDelete($idreason);
        if (!$dea) {
            $this->dbReason->RollbackTrans();
            if($this->log)
                $this->logIt('Delete Request Reason - User: '.$_SESSION['SES_LOGIN_PERSON'].' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
			return false;
        }

        $this->dbReason->CommitTrans();

        $aRet = array(
            "idreason" => $idreason,
            "status"   => 'OK'
        );

        echo json_encode($aRet);

    }

    public function ajaxTypes()
    {
        echo $this->_comboTypeHtml($_POST['areaId']);
    }

    public function ajaxItens()
    {
        echo $this->_comboItemHtml($_POST['typeId']);
    }

    public function ajaxServices()
    {
        echo $this->_comboServiceHtml($_POST['itemId']);
    }

    public function ajaxDepartments()
    {
        echo $this->_comboDepartmentHtml($_POST['companyId']);
    }

    function changeReasonStatus()
    {
        $idreason = $this->getParam('idreason');
        $newStatus = $_POST['newstatus'];

        $ret = $this->dbReason->updateReasonStatus($idreason,$newStatus);

        if (!$ret) {
            if($this->log)
                $this->logIt('Change Reason Status - User: '.$_SESSION['SES_LOGIN_PERSON'].' - program: '.$this->program.' - method: '. __METHOD__ ,3,'general',__LINE__);
            return false;
        }

        $aRet = array(
            "idreason" => $idreason,
            "status" => 'OK',
            "reasonstatus" => $newStatus
        );

        echo json_encode($aRet);

    }

}