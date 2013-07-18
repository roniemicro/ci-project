<?php
/**
 * DataTable class file.
 * A Simple Wrapper Class for jquery DataTable Plugin, This is specially
 * Developed to work in Access Driving Schools Project and My not work properly
 * Outside this project without further modification
 *
 * @version	1.0
 * @package Access Driving Schools
 * @author Roni Kumar Saha<roni.cse@gmail.com>
 *
 * Uses:
 * $config=array(
 *           'id'=>"billing_content_table",
 *           'columns'=>array('Col1 Name','Col2 Name','....','Col n Name'),
 *           'checkbox'=>false,         //Show Hide CheckBox Column
 *           'print_grid'=>false,       //Print immediately upon load
 *           'action'=>true             //Show/Hide Action Column
 *         );
 * $this->load->library("DataTable",$config);
 * or
 * $this->load->library("DataTable");
 * $this->datatable->config($config)
 *                  ->print_grid();
 *
 * or
 * $this->load->library("DataTable",$config);
 * $grid=$this->datatable->grid();
 *
 */
class DataTable
{
    /**
     * @var string
     */
    /**
     * @var null|string
     */
    /**
     * @var null|string
     */
    /**
     * @var bool|null|string
     */
    /**
     * @var bool|null|string
     */
    /**
     * @var bool|null|string
     */
    private
    $CI="",
    $_id=NULL,
    $_columns=NULL,
    $_columns_attr=NULL,
    $_show_checkbox=TRUE,
    $_show_action=TRUE,
    $_show_footer=FALSE,
    $_prevConfig=FALSE,
    $_template="",
    $_noControl=FALSE;

    /**
     * The constructor function may used to inline generation of the grid!
     * @param array $option
     */
    function __construct($option=array()){
       $this->CI = & get_instance();
       if(!empty($option)){
           $this->config($option);
       }
       return $this;
    }

    /**
     * @param array $option
     * @return \DataTable
     */
    function config($option=array()){
        isset($option['id']) AND ($this->_id=$option['id']);
        isset($option['columns']) AND ($this->_columns=$option['columns']);
        isset($option['attr']) AND ($this->_columns_attr=$option['attr']);
        isset($option['action']) AND ($this->_show_action=$option['action']);
        isset($option['noControl']) AND ($this->_noControl=$option['noControl']);
        isset($option['checkbox']) AND ($this->_show_checkbox=$option['checkbox']);
        isset($option['footer']) AND ($this->_show_footer=$option['footer']);
        isset($option['template']) AND ($this->_template=str_replace("{t}",'%s',$option['template']));

        $prevConfig=isset($option['prevConfig'])?$option['prevConfig']:$this->CI->uri->asegment(1);
        if($prevConfig){
            $prevConfig=urldecode($prevConfig);
            if(!is_base64_encoded($prevConfig)){
                show_404();
            }
            $this->_prevConfig = $prevConfig;
        }
        isset($option['print_grid']) AND $option['print_grid'] AND $this->print_grid();
        return $this;
    }

    /**
     * @return string  the generated Table Skeletons
     */
    function grid(){
        //chk first if we have all our data!!
        if(!isset($this->_columns,$this->_id)){
            return "";
        }
        $data=array(
            '_id'=>$this->_id,
            '_columns'=>$this->_columns,
            '_columns_attr'=>$this->_columns_attr,
            '_prevConfig'=>$this->_prevConfig,
            '_show_checkbox'=>$this->_show_checkbox,
            '_show_action'=>$this->_show_action,
            '_show_footer'=>$this->_show_footer,
            '_noControl'=>$this->_noControl,
        );
        $grid=$this->CI->load->view("DataTable/grid",$data,true);
        if($this->_template!=""){
            $grid=sprintf($this->_template,$grid);
        }
        return $grid;
    }

    /**
     * display the generated Table Skeletons
     * @return DataTable
     */
    function print_grid(){
      echo $this->grid();
      return $this;
    }
}