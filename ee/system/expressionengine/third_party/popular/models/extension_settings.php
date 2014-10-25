<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ext_settings {

    private $settings = array();
   
    /**
    * Constructor
    *
    * @access public
    * @return array
    */
        
    public function __construct()
    {
        $this->set_settings(); 
    }

    /**
    * Set Settings
    *
    * @access private
    * @return void
    */
        
    private function set_settings()
    {
        $settings_result = ee()->db->select('settings')->from('extensions')->where(array('class'=>'Popular_ext','method'=>'count_view'))->limit(1)->get();
        
        if( $settings_result->num_rows() > 0 ){
            $this->settings = unserialize($settings_result->row('settings'));
        }
        
    }

    /**
    * Get Settings
    *
    * @access public
    * @return array
    */
    
    public function get_settings()
    {
        return $this->settings;
    }

}


