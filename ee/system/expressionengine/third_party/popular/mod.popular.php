<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include(PATH_THIRD.'popular/config.php');

require(PATH_THIRD.'popular/models/extension_settings.php');

require(PATH_THIRD.'popular/models/ip_location.php');

require(PATH_THIRD.'popular/models/device_information.php');

class Popular {

    public $return_data = '';

    private $num_rows = 0;
    private $entry_pool;

    public function __construct()
    {

    }

    /**
    * Set Results
    *
    * @access private
    * @return void
    */

    private function set_results($limit,$channel_ids,$start_on,$stop_before,$user_device,$device_type,$user_country_code,$country_code,$user_region_code,$region_code,$user_city,$city,$order_by,$sort)
    {
        
        $extension_model = new Ext_settings;
        
        $extension_settings = $extension_model->get_settings();
        
        $ip_location = NULL;
        
        if( $extension_settings['find_ip_location'] == 'yes' ){
            
            $ip_location_model = new Ip_location(ee()->session->userdata('ip_address'),$extension_settings['find_ip_api_url']);
        
            $ip_location = $ip_location_model->get_location();            
        }
        
        ee()->db->select('COUNT(P.entry_id) AS views, P.*, T.title, T.url_title, T.year, T.month')
        ->from('popular P')
        ->join('channel_titles T', 'T.entry_id = P.entry_id')
        ->group_by('P.entry_id');

        if( $channel_ids != '' ){
            
            $channel_ids = str_replace('|', ',', $channel_ids);
            
            ee()->db->where_in('T.channel_id',$channel_ids);
        }
        
        if( $start_on != '' ){
            ee()->db->where('P.view_date >=',$start_on);
        }
        
        if( $stop_before != '' ){
            ee()->db->where('P.view_date <',$stop_before);
        }

        if( $device_type != '' ){      
            ee()->db->where('P.device_simple',$device_type);        
        }
        
        
        if( $user_device == 'yes' ){
        
            
        
            if( $extension_settings['improve_user_agent'] == 'yes' && $extension_settings['user_agent_api_key'] != '' ){
            
                $device_info_model = new Device_information($extension_settings['user_agent_api_key'],ee()->session->userdata('user_agent'));
        
                $device_detail = $device_info_model->get_device_detail();
                
                ee()->db->where('P.device_simple',$device_detail['platform_type']);
            }
            
        }

        if( $user_country_code == 'yes' && !is_null($ip_location) ){
            
            ee()->db->where('P.country_code',$ip_location['country_code']); 
                       
        } 
        
        if( $country_code != ''){
            
            ee()->db->where('P.country_code',$country_code); 
                       
        } 
           
        if( $user_region_code == 'yes' && !is_null($ip_location) ){
            
            ee()->db->where('P.region_code',$ip_location['region_code']); 
                       
        } 
        
        if( $region_code != ''){
            
            ee()->db->where('P.region_code',$region_code); 
                       
        } 
        
        if( $user_city == 'yes' && !is_null($ip_location) ){
            
            ee()->db->where('P.country_code',$ip_location['country_code']); 
                       
        } 
        
        if( $city != ''){
            
            ee()->db->where('P.city',$city); 
                       
        }   
        
        ee()->db->order_by($order_by,$sort);
                              
        
        $entry_pool = ee()->db->limit($limit)
        ->get();

        $this->num_rows   = $entry_pool->num_rows();
        $this->entry_pool = $entry_pool;

    }
    
    /**
    * Most Popular
    *
    * @access public
    * @return string
    */
    
    public function most_popular()
    {
        $tagparams = ee()->TMPL->tagparams;
        $tagdata   = ee()->TMPL->tagdata;

        if (ee()->extensions->active_hook('popular_most_popular_start') === TRUE)
		{
			ee()->extensions->call('popular_most_popular_start',$tagparams,$tagdata);
			if (ee()->extensions->end_script === TRUE) return;
		}
        
        $limit             = ee()->TMPL->fetch_param('limit',10);
        $channel_ids       = ee()->TMPL->fetch_param('channel_ids');
        $prefix            = ee()->TMPL->fetch_param('prefix');
        $start_on          = ee()->TMPL->fetch_param('start_on');
        $stop_before       = ee()->TMPL->fetch_param('stop_before');
        $user_device       = ee()->TMPL->fetch_param('user_device','no');
        $device_type       = ee()->TMPL->fetch_param('device_type');
        $user_country_code = ee()->TMPL->fetch_param('user_country_code','no');
        $country_code      = ee()->TMPL->fetch_param('country_code');
        $user_region_code  = ee()->TMPL->fetch_param('user_region_code','no');
        $region_code       = ee()->TMPL->fetch_param('region_code');
        $user_city         = ee()->TMPL->fetch_param('user_city','no');
        $city              = ee()->TMPL->fetch_param('city');
        $order_by          = ee()->TMPL->fetch_param('order_by','COUNT(P.entry_id)');
        $sort              = ee()->TMPL->fetch_param('sort','desc');
        
        $cache             = ee()->TMPL->fetch_param('cache','no');
        $refresh           = ee()->TMPL->fetch_param('refresh','60');
        
        $cache_key = base64_encode(serialize($tagparams)); 
        
        if( $cache == 'yes' ){
        
            $cache_meta = ee()->cache->get_metadata('/popular/'.$cache_key);
            
            if( $cache_meta['expire'] >= date('U') ){
            
                return ee()->cache->get('/popular/'.$cache_key);
            }
                     
        }
        
        
        if( $order_by != 'COUNT(P.entry_id)' ){
        
            if( $order_by == 'title' ){
                $order_by = 'T.'.$order_by;
            } else {
                $order_by = 'P.'.$order_by;
            }           
            
        }

        $this->set_results($limit,$channel_ids,$start_on,$stop_before,$user_device,$device_type,$user_country_code,$country_code,$user_region_code,$region_code,$user_city,$city,$order_by,$sort);
        

        if( $this->num_rows === 0 ) return ee()->TMPL->no_results();

        foreach( $this->entry_pool->result_array() as $entry )
        {
            $variables[] = array(
                $prefix . 'entry_id'     => $entry['entry_id'],
                $prefix . 'month'        => $entry['month'],
                $prefix . 'year'         => $entry['year'],
                $prefix . 'url_title'    => $entry['url_title'],
                $prefix . 'title'        => $entry['title'],
                $prefix . 'views'        => $entry['views'],
            );
        }
        
        if (ee()->extensions->active_hook('popular_most_popular_end') === TRUE)
		{
			ee()->extensions->call('popular_most_popular_end',$tagdata,$variables);
			if (ee()->extensions->end_script === TRUE) return;
			if (ee()->extensions->last_call  !== FALSE) return ee()->extensions->last_call;
		}
		
		$output = ee()->TMPL->parse_variables(ee()->TMPL->tagdata, $variables);
		
        if( $cache == 'yes' ){ 
            ee()->cache->save('/popular/'.$cache_key, $output, $refresh * 60);            
        }

        return $output;

    }

}
