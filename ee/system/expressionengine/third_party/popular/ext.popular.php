<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include(PATH_THIRD.'popular/config.php');

require(PATH_THIRD.'popular/models/ip_location.php');

require(PATH_THIRD.'popular/models/device_information.php');

class Popular_ext {

    public $name           = POPULAR_NAME;
    public $version        = POPULAR_VERSION;
    public $description    = POPULAR_DESCRIPTION;
    public $settings_exist = 'y';
    public $docs_url       = POPULAR_DOCS;
    public $settings       = array();

    private $hooks         = array();
    private $site_id       = 1;

	/**
	* Constructor
	*
	* @access public
    * @return void
	*/

    public function __construct($settings='')
    {
        $this->settings = $settings;
        $this->site_id  = (int) ee()->config->item('site_id');  
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

        $referrer       = ( isset( $_SERVER['HTTP_REFERER'] ) ) ? $_SERVER['HTTP_REFERER'] : '';
        $user_agent     = ee()->session->userdata('user_agent');
        $remote_address = ee()->session->userdata('ip_address');
        
        

        $ipv4 = ip2long($remote_address);
        $ipv6 = inet_pton($remote_address);

        $ipv4 = ( $ipv4 !== FALSE ) ? $ipv4 : NULL;
        $ipv6 = ( $ipv4 === FALSE && $ipv6 !== FALSE ) ? $ipv6 : NULL;

        $data = array(
           'entry_id'  => $row['entry_id'],
           'view_date' => date('Y-m-d H:i:s'),
           'site_id'   => $this->site_id,
           'ipv4'      => $ipv4,
           'ipv6'      => $ipv6,
           'device'    => $user_agent,
           'uri'       => $row['page_uri']
        );

        if( $this->settings['find_ip_location'] === 'yes' && inet_pton($remote_address) !== FALSE ){

            
            $ip_location_model = new Ip_location($remote_address,$this->settings['find_ip_api_url']);
        
            $ip_location = $ip_location_model->get_location();

            $data['country_code'] = ( $ip_location['country_code'] ) ? $ip_location['country_code'] : NULL;
            $data['country_name'] = ( $ip_location['country_name'] ) ? $ip_location['country_name'] : NULL;
            $data['region_code']  = ( $ip_location['region_code'] ) ? $ip_location['region_code'] : NULL;
            $data['region_name']  = ( $ip_location['region_name'] ) ? $ip_location['region_name'] : NULL;
            $data['city']         = ( $ip_location['city'] ) ? $ip_location['city'] : NULL;
            $data['zipcode']      = ( $ip_location['zipcode'] ) ? $ip_location['zipcode'] : NULL;
            $data['latitude']     = ( $ip_location['latitude'] ) ? $ip_location['latitude'] : NULL;
            $data['longitude']    = ( $ip_location['longitude'] ) ? $ip_location['longitude'] : NULL;
        }

        if( $this->settings['improve_user_agent'] === 'yes' && $this->settings['user_agent_api_key'] != '' ){

            $device_info_model = new Device_information($this->settings['user_agent_api_key'],$user_agent);
        
            $device_detail = $device_info_model->get_device_detail();

            $data['device_simple'] = ( $device_detail['platform_type'] ) ? $device_detail['platform_type'] : NULL;
        }

        ee()->db->insert('popular',$data);

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
