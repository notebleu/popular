<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(PATH_THIRD.'popular/models/extension_settings.php');

require_once(PATH_THIRD.'popular/models/ip_location.php');

require_once(PATH_THIRD.'popular/models/device_information.php');

class Count_view {

    public function __construct($entry_information)
    {
        
        $user_agent     = ee()->session->userdata('user_agent');
        $remote_address = ee()->session->userdata('ip_address');
        
        $ipv4 = ip2long($remote_address);
        $ipv6 = inet_pton($remote_address);

        $ipv4 = ( $ipv4 !== FALSE ) ? $ipv4 : NULL;
        $ipv6 = ( $ipv4 === FALSE && $ipv6 !== FALSE ) ? $ipv6 : NULL;

        $data = array(
           'entry_id'  => $entry_information['entry_id'],
           'view_date' => date('Y-m-d H:i:s'),
           'site_id'   => (int) ee()->config->item('site_id'),
           'ipv4'      => $ipv4,
           'ipv6'      => $ipv6,
           'device'    => $user_agent,
           'uri'       => $entry_information['page_uri']
        );
        
        $extension_model = new Ext_settings;
        
        $extension_settings = $extension_model->get_settings();

        if( $extension_settings['find_ip_location'] === 'yes' && inet_pton($remote_address) !== FALSE ){

            
            $ip_location_model = new Ip_location($remote_address,$extension_settings['find_ip_api_url']);
        
            $ip_location = $ip_location_model->get_location();

            $data['country_code'] = ( $ip_location['country_code'] ) ? $ip_location['country_code'] : NULL;
            $data['country_name'] = ( $ip_location['country_name'] ) ? $ip_location['country_name'] : NULL;
            $data['region_code']  = ( $ip_location['region_code'] ) ? $ip_location['region_code'] : NULL;
            $data['region_name']  = ( $ip_location['region_name'] ) ? $ip_location['region_name'] : NULL;
            $data['city']         = ( $ip_location['city'] ) ? $ip_location['city'] : NULL;
            $data['zipcode']      = ( $ip_location['zip_code'] ) ? $ip_location['zip_code'] : NULL;
            $data['latitude']     = ( $ip_location['latitude'] ) ? $ip_location['latitude'] : NULL;
            $data['longitude']    = ( $ip_location['longitude'] ) ? $ip_location['longitude'] : NULL;
        }

        if( $extension_settings['improve_user_agent'] === 'yes' && $extension_settings['user_agent_api_key'] != '' ){

            $device_info_model = new Device_information($extension_settings['user_agent_api_key'],$user_agent);
        
            $device_detail = $device_info_model->get_device_detail();

            $data['device_simple'] = ( $device_detail['platform_type'] ) ? $device_detail['platform_type'] : NULL;
        }

        ee()->db->insert('popular',$data);        
        
    }

}