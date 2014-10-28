<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(PATH_THIRD.'popular/config.php');

require_once(PATH_THIRD.'popular/models/ip_location.php');

require_once(PATH_THIRD.'popular/models/device_information.php');

require_once(PATH_THIRD.'popular/models/count_view.php');

class Popular_ext {

    public $name           = POPULAR_NAME;
    public $version        = POPULAR_VERSION;
    public $description    = POPULAR_DESCRIPTION;
    public $settings_exist = 'y';
    public $docs_url       = POPULAR_DOCS;
    public $settings       = array();

    private $hooks         = array();

	/**
	* Constructor
	*
	* @access public
    * @return void
	*/

    public function __construct($settings='')
    {
        $this->settings = $settings; 
    }

    /**
    * Count View
    *
    * Record single entry views for allowed channels
    * @access public
    * @return $tagdata
    *
    */

    public function count_view($tagdata, $row, $obj)
    {    
        # if this is not a single entry or the channel is not allowed, return
        if( $row['total_results'] != '1' || !in_array($row['channel_id'], $this->settings['allowed_channel_ids']) ) return $tagdata; 

        $count_view = new Count_view($row); 

        return $tagdata;
    }

    /**
    * Set Hooks
    *
    * @access private
    * @return void
    */

    private function set_hooks()
    {
        $hooks = array();

        $count_view = array(
            'class'     => __CLASS__,
            'method'    => 'count_view',
            'hook'      => 'channel_entries_tagdata',
            'settings'  => serialize($this->settings),
            'priority'  => 10,
            'version'   => $this->version,
            'enabled'   => 'y'
        );

        array_push($hooks,$count_view);

        $this->hooks = $hooks;
    }

    /**
    * Settings
    *
    * @access public
    * @return array
    */

    public function settings()
    {
        $settings = array();
        
        ee()->load->library('api'); 
        ee()->api->instantiate('channel_structure');
        
        $channels = ee()->api_channel_structure->get_channels($this->site_id);
        
        $channel_array = array();
        foreach( $channels->result_array() as $channel )
        {
            $channel_array[$channel['channel_id']] = $channel['channel_name'];
        }

        $settings['allowed_channel_ids'] = array('ms', $channel_array);

        $settings['find_ip_location']    = array('r', array('yes' => "Yes", 'no' => "No"), 'yes');
    
        $settings['find_ip_api_url']     = array('s', array('http://freegeoip.net/json/' => 'Free Geo IP'), 'http://freegeoip.net/json/');
        
        $settings['improve_user_agent']  = array('r', array('yes' => "Yes", 'no' => "No"), 'no');
        
        $settings['user_agent_api_key']  = array('i', '','');
    
        return $settings;
    }
    
    /**
    * Activate Extension
    *
    * @access public
    * @return void
    */

    public function activate_extension()
    {

        $this->settings = array(
            'allowed_channel_ids' => '',
            'find_ip_location'    => 'yes',
            'find_ip_api_url'     => 'http://freegeoip.net/json/',
            'improve_user_agent'  => 'no',
            'user_agent_api_key'  => ''
        );

        $this->set_hooks();

        foreach( $this->hooks as $hook )
        {
            ee()->db->insert('extensions', $hook);
        }

    }

    /**
    * Update Extension
    *
    * @access public
    * @return  mixed   void on update / false if none
    */

    public function update_extension($current = '')
    {
        if ($current == '' OR $current == $this->version)
        {
            return FALSE;
        }

        ee()->db->where('class', __CLASS__);
        ee()->db->update(
                    'extensions',
                    array('version' => $this->version)
        );
    }

    /**
    * Disable Extension
    *
    * This method removes information from the exp_extensions table
    *
    * @access public
    * @return void
    */

    public function disable_extension()
    {
        ee()->db->where('class', __CLASS__);
        ee()->db->delete('extensions');
    }
}
