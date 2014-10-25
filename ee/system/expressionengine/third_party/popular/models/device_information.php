<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Device_information {

    private $device_detail = array();
   
    /**
    * Constructor
    *
    * @access public
    * @return array
    */
        
    public function __construct($api_key,$user_agent)
    {
        $this->set_device_detail($api_key,$user_agent); 
    }

    /**
    * Set Device Detail
    *
    * @access private
    * @return void
    */
        
    private function set_device_detail($api_key,$user_agent)
    {
        $ch = curl_init();
        
        $user_agent = urlencode($user_agent);

        curl_setopt($ch, CURLOPT_URL, 'http://useragentapi.com/api/v2/json/'.$api_key.'/'.$user_agent);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        $reponse = curl_exec($ch);

        curl_close($ch);

        $this->device_detail = json_decode($reponse,true);


    }

    /**
    * Get Device Detail
    *
    * @access public
    * @return array
    */
    
    public function get_device_detail()
    {
        return $this->device_detail;
    }

}


