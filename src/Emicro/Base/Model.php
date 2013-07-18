<?php
namespace Emicro\Base;

/**
 * Model
 *
 * An extension to CodeIgniter's general Model class to assist
 * in writing very small amount of code to do general tasks that
 * are needed in an application.
 *
 * @author Md Emran Hasan <phpfour@gmail.com>
 * @author By Roni Kumar Saha <roni@emicrograph.com>
 * @version 1.2
 * @since 2012
 */
class Model extends \CI_Model
{
    protected $table=null;
    protected $primaryKey=null;

    private $fields = array();
    private $numRows = null;
    private $insertId = null;
    private $affectedRows = null;
    private $returnArray = true;
    protected $CI=null;
    
    public function __construct()
    {
        parent::__construct();
        $this->CI = & get_instance();
        ($this->table!=null) AND $this->loadTable($this->table,$this->primaryKey);
	}
    
    public function loadTable($table, $primaryKey = 'id')
    {
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->fields = $this->db->list_fields($table);
    }

    public function findAll($conditions = null, $fields = '*', $order = null, $start = 0, $limit = null)
    {
        if ($conditions != null)  {
            if(is_array($conditions)) {
                $this->db->where($conditions);
            } else {
                $this->db->where($conditions, null, false);
            }
        }

        if ($fields != null)  {
            $this->db->select($fields);
        }

        if ($order != null) {
            $this->order_by($order);
        }

        if ($limit != null)  {
            $this->db->limit($limit, $start);
        }

        return $this->getResult();
    }

    public function getResult($table_name=null){
        $table=$table_name==null?$this->table:$table_name;
        $query = $this->db->get($table);
        $this->numRows = $query->num_rows();
        return ($this->returnArray) ? $query->result_array() : $query->result();
    }

    public function order_by($orders=NULL){
        if ($orders == NULL) {
            return FALSE;
        }
        if(is_array($orders)){   //Multiple order by provided!
           //check if we got single order by passed as array!!
           if(isset($orders[1]) && (strtolower($orders[1])=='asc' || strtolower($orders[1])=='desc' || strtolower($orders[1]) == 'random')){
               $this->db->order_by($orders[0],$orders[1]);
               return;
           }
           foreach($orders as $order){
               $this->order_by($order);
           }
           return;
        }
        $this->db->order_by($orders); //its a string just call db order_by
    }

    public function find($conditions = null, $fields = '*', $order = null)
    {
        $data = $this->findAll($conditions, $fields, $order, 0, 1);

        if ($data) {
            return $data[0];
        } else  {
            return false;
        }
    }

    public function field($conditions = null, $name, $fields = '*', $order = null)
    {
        $data = $this->findAll($conditions, $fields, $order, 0, 1);

        if ($data) {
            $row = $data[0];
            if (isset($row[$name])) {
                return $row[$name];
            }
        }

        return false;
    }

    public function findCount($conditions = null)
    {
        $data = $this->findAll($conditions, 'COUNT(*) AS count', null, 0, 1);

        if ($data) {
            return $data[0]['count'];
        } else {
            return false;
        }
    }

    public function insert($data = null)
    {
        if ($data == null) {
            return false;
        }

        $data['entry_time']=date ( 'Y-m-d H:i:s' );
        $data['update_time']=$data['entry_time'];
        $data['entry_by']=$this->CI->session->userdata('user_id');
        $data['update_by']=$data['entry_by'];

        foreach ($data as $key => $value) {
            if (array_search($key, $this->fields) === false) {
                unset($data[$key]);
            }
        }

        $this->db->insert($this->table, $data);
        $this->insertId = $this->db->insert_id();
        
        return $this->insertId;
    }

    public function update($data = null, $id = null,$where=null)
    {
        if ($data == null) {
            return false;
        }

        $data['update_time']=date ( 'Y-m-d H:i:s' );
        $data['update_by']=$this->CI->session->userdata('user_id');

        foreach ($data as $key => $value) {
            if (array_search($key, $this->fields) === false) {
                unset($data[$key]);
            }
        }

        if ($id !== null) {
            $this->db->where($this->primaryKey, $id);
            $this->db->update($this->table, $data);
            $this->affectedRows = $this->db->affected_rows();
            return $id;
        } elseif($where!=null){
            $this->db->where($where);
            $this->db->update($this->table, $data);
            $this->affectedRows = $this->db->affected_rows();
            return $id;
        }else {
            return $this->insert($data);
        }
    }

    public function remove($id = null,$where=null)
    {
        if ($id != null){
            $this->db->where($this->primaryKey, $id);
        }elseif($where!=null) {
            $this->db->where($where);
        }else{
            return false;
        }
        return $this->db->delete($this->table);
    }

    public function remove_in($ids=null,$field=null)
    {
        if ($ids === null){
            return false;
        }
        if ($field === null){
            $field=$this->primaryKey;
        }

        $this->db->where_in($field, $ids);
        return $this->db->delete($this->table);
    }

    public function __call ($method, $args)
    {
        $watch = array('findBy','findAllBy','findFieldBy');

        foreach ($watch as $found) {
            if (stristr($method, $found)) {
                $field = strtolower(str_replace($found, '', $method));
                return $this->$found($field, $args);
            }
        }
    }

    public function findFieldBy($field, $value,$fields='*',$order=null){
        $arg_list=array();
        if(is_array($value)){
            $arg_list=$value;
            $value=$arg_list[0];
        }
        $fields = isset($arg_list[1])?$arg_list[1]:$fields;
        $order = isset($arg_list[2])?$arg_list[2]:$order;
        $where = array($field => $value);
        return $this->field($where, $fields, $fields, $order);
    }

    public function findBy($field, $value,$fields='*',$order=null)
    {
        $arg_list=array();
        if(is_array($value)){
            $arg_list=$value;
            $value=$arg_list[0];
        }
        $fields = isset($arg_list[1])?$arg_list[1]:$fields;
        $order = isset($arg_list[2])?$arg_list[2]:$order;

        $where = array($field => $value);
        return $this->find($where,$fields,$order);
    }

    public function findAllBy($field, $value, $fields='*',$order=null,$start=0,$limit=null)
    {
        $arg_list=array();
        if(is_array($value)){
            $arg_list=$value;
            $value=$arg_list[0];
        }
        $fields = isset($arg_list[1])?$arg_list[1]:$fields;
        $order = isset($arg_list[2])?$arg_list[2]:$order;
        $start = isset($arg_list[3])?$arg_list[3]:$start;
        $limit = isset($arg_list[4])?$arg_list[4]:$limit;

        $where = array($field => $value);
        return $this->findAll($where,$fields,$order,$start,$limit);
    }

    public function executeQuery($sql)
    {
        return $this->db->query($sql);
    }

    public function getLastQuery()
    {
        return $this->db->last_query();
    }

    public function getInsertString($data)
    {
        return $this->db->insert_string($this->table, $data);
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getNumRows()
    {
        return $this->numRows;
    }

    public function getInsertId()
    {
        return $this->insertId;
    }

    public function getAffectedRows()
    {
        return $this->affectedRows;
    }
    
    public function setPrimaryKey($primaryKey)
    {
        $this->primaryKey = $primaryKey;
    }

    public function setReturnArray($returnArray)
    {
        $this->returnArray = $returnArray;
    }
}
