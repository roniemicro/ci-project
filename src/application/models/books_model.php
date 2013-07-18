<?php
class Books_model extends MY_Model {
    protected $table = 'books';
    protected $primaryKey = 'book_id';


    function isItemExist($name="",$id=null){
        $where=array('lesson_name' => $name);
        if($id!==null){  //request upon update
            $where["$this->primaryKey !="]=$id;
        }
        return ($this->findCount($where) > 0);
    }

	function lists($config) {
        $old_config = array();
        $aColumns = array('book_id','name','title','author','isbn','publication','published_year','price');
        $wColumns = array ('name','title','author','isbn','publication');

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
                  `book_id`,
                  `name`,
                  `title`,
                  `author`,
                  `isbn`,
                  `publication`,
                  `published_year`,
                  `price`
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

        foreach ($rResult->result_array() as $aRow) {
            $row = array();
            $row['DT_RowId'] = 'row_'.$aRow[$this->primaryKey];
            $row[] = $aRow['name'];
            $row[] = $aRow['title'];
            $row[] = $aRow['author'];
            $row[] = $aRow['isbn'];
            $row[] = $aRow['publication'];
            $row[] = getBengali($aRow['published_year']);
            $row[] = '৳ '.getBengali($aRow['price']);
			$paging = urlencode(base64_encode(json_encode($old_config)));
            $edit_url = site_url('book/edit/'.$aRow[$this->primaryKey].'/'.auth_key('book_edit'.$aRow[$this->primaryKey]));
            $edit_url .= '/'.$paging;
			$delete_url = site_url('book/delete/'.$aRow[$this->primaryKey]);

            $row[] = '<a  title="Edit" class="with-tip" href="'.$edit_url.'"><img src="'.base_url().'assets/images/icons/fugue/pencil.png" width="16" height="16" /></a>'
                     .'&nbsp;<a auth="'.auth_key('book_delete'.$aRow[$this->primaryKey]).'" title="Delete" class="with-tip ajaxdelete" href="'.$delete_url.'"><img src="'.base_url().'assets/images/icons/fugue/cross-circle.png" width="16" height="16" /></a>';
                     ;

            $output['aaData'][] = $row;
        }
        return json_encode($output);
    }
}