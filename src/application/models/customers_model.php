<?php

class Customers_model extends MY_Model {
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';


    function isItemExist($name="",$id=null){
        $where = array();

        if($id!==null){  //request upon update
            $where["$this->primaryKey !="]=$id;
        }

        $this->db->like('name', $name, 'none');

        return ($this->find($where) !== false);
    }

	function lists($config) {
        $old_config = array();
        $aColumns = array('customer_id','name','phone','address','is_cimpany');
        $wColumns = array ('name','phone','address');

        $sLimit = '';

        if (isset($config['iDisplayStart']) && $config['iDisplayLength'] != '-1') {
            $old_config['iDisplayStart'] = $config['iDisplayStart'];
            $old_config['iDisplayLength'] = $config['iDisplayLength'];
            $sLimit = "LIMIT ".$this->db->escape_str($config['iDisplayStart']).", ".
			$this->db->escape_str($config['iDisplayLength']);
        }
        $sOrder = '';
        if (isset($config['iSortCol_0'])) {
            $old_config['iSortingCols'] = $config['iSortingCols'];
            $sOrder = 'ORDER BY  ';
            for ($i=0; $i<intval($config['iSortingCols']); $i++) {
                $old_config['iSortCol_'.$i] = $config['iSortCol_'.$i];
                $old_config['sSortDir_'.$i] = $config['sSortDir_'.$i];
                $old_config['bSortable_'.intval($config['iSortCol_'.$i])] = $config['bSortable_'.intval($config['iSortCol_'.$i])];
                $old_config['sSortDir_'.$i] = $config['sSortDir_'.$i];
                if ($config['bSortable_'.intval($config['iSortCol_'.$i])] == 'true') {
                    $sOrder .= $aColumns[intval($config['iSortCol_'.$i])]." ".$this->db->escape_str($config['sSortDir_'.$i]) .", ";
                }
            }
            $sOrder = substr_replace($sOrder,'',-2);
            if ($sOrder == 'ORDER BY') {
                $sOrder = '';
            }
        }
        $sWhere = '';
        if (isset($config['sSearch']) && $config['sSearch'] != '') {
            $oldconfig['sSearch'] = $config['sSearch'];
            $sWhere = 'WHERE (';
            foreach($wColumns as $column_name) {
                $sWhere .= $column_name." LIKE '%".$this->db->escape_str($config['sSearch'])."%' OR ";
            }
            $sWhere = substr_replace($sWhere,'',-3);
            $sWhere .= ')';
        }
        $sQuery = "SELECT SQL_CALC_FOUND_ROWS
                   `customer_id`,
                  `name`,
                  `phone`,
                  `address`,
                  `is_company`
                  FROM {$this->table}
                  $sWhere
                  $sOrder
                  $sLimit";

        $rResult = $this->executeQuery($sQuery);

        $sQuery = 'SELECT FOUND_ROWS() as count';
        $rResultFilterTotal = $this->executeQuery($sQuery);
        $aResultFilterTotal =  $rResultFilterTotal->row_array();
        $iFilteredTotal = $aResultFilterTotal['count'];

        $sQuery = "SELECT COUNT(".$this->primaryKey.") as count FROM $this->table";

        $rResultTotal = $this->db->query($sQuery);
        $aResultTotal = $rResultTotal->row_array();
        $iTotal = $aResultTotal['count'];

        $config['sEcho'] = isset($config['sEcho']) ? intval($config['sEcho']) : 1;
        $output = array (
            'iTotalRecords' => $iTotal,
            'iTotalDisplayRecords' => $iFilteredTotal,
            'aaData' => array()
        );

        $codeTemplate = '<img src="'.base_url().'assets/images/icons/fugue/%1$s" width="16" height="16"> &nbsp; %2$s';

        $userImage = array('user-green.png','building.png');

        foreach ($rResult->result_array() as $aRow) {
            $row = array();
            $row['DT_RowId'] = 'row_'.$aRow[$this->primaryKey];
            $row[] = sprintf($codeTemplate, $userImage[(int)$aRow['is_company']], $aRow['customer_id']);
            $row[] = $aRow['name'];
            $row[] = getBengali($aRow['phone']);
            $row[] = nl2br($aRow['address']);
			$paging = urlencode(base64_encode(json_encode($old_config)));
            $edit_url = site_url('customer/edit/'.$aRow[$this->primaryKey].'/'.auth_key('customer_edit'.$aRow[$this->primaryKey]));
            $edit_url .= '/'.$paging;
			$delete_url = site_url('customer/delete/'.$aRow[$this->primaryKey]);

            $row[] = '<a  title="Edit" class="with-tip" href="'.$edit_url.'"><img src="'.base_url().'assets/images/icons/fugue/pencil.png" width="16" height="16" /></a>'
                     .'&nbsp;<a auth="'.auth_key('customer_delete'.$aRow[$this->primaryKey]).'" title="Delete" class="with-tip ajaxdelete" href="'.$delete_url.'"><img src="'.base_url().'assets/images/icons/fugue/cross-circle.png" width="16" height="16" /></a>';
                     ;

            $output['aaData'][] = $row;
        }
        return json_encode($output);
    }
}