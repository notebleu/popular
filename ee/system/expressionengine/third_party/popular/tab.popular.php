<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(PATH_THIRD.'popular/config.php');

require_once(PATH_THIRD.'popular/models/extension_settings.php');

class Popular_tab {

    public function __construct()
    {
        ee()->lang->loadfile('popular');
    }

    /**
    * Publish Tabs
    *
    * @access public
    * @return array
    */
    
    public function publish_tabs($channel_id, $entry_id = '')
    {
        $settings = array();
        
        $extension_model = new Ext_settings;
        
        $extension_settings = $extension_model->get_settings();
        
        if( is_array($extension_settings['allowed_channel_ids']) ){
            
            if( in_array($channel_id, $extension_settings['allowed_channel_ids']) ){
     
        		$settings[] = array(
        			'field_id'				=> 'highchart',
        			'field_label'			=> lang('popular_tab_field_label'),
        			'field_required' 		=> 'n',
        			'field_data'			=> '',
        			'field_list_items'		=> '',
        			'field_fmt'				=> '',
        			'field_instructions' 	=> '',
        			'field_show_fmt'		=> 'n',
        			'field_fmt_options'		=> array(),
        			'field_text_direction'	=> 'ltr',
        			'field_type' 			=> 'popular'
        		); 
                
            }            
            
        }
        
		return $settings;        
    }


}