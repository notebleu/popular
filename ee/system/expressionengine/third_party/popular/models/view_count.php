<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class View_count {

    private $view_count;
   
    /**
    * Constructor
    *
    * @access public
    * @return array
    */
        
    public function __construct($entry_id,$site_id)
    {
        $this->set_view_count($entry_id,$site_id); 
    }

    /**
    * Set View Count
    *
    * @access private
    * @return void
    */
        
    private function set_view_count($entry_id,$site_id)
    {
        $view_count_result = ee()->db->select("*")->from('popular')->where(array('entry_id'=>$entry_id,'site_id'=>$site_id))->order_by('view_date','asc')->get();
                
        $this->view_count = $view_count_result;
        
    }

    /**
    * Get View Count
    *
    * @access public
    * @return object
    */
    
    public function get_view_count()
    {
        return $this->view_count;
    }

}


