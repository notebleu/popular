<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Ip_location {

    private $location = array();
   
    /**
    * Constructor
    *
    * @access public
    * @return array
    */
        
    public function __construct($remote_address,$api_url)
    {
        $this->set_location($remote_address,$api_url); 
    }

    /**
    * Set Location
    *
    * @access private
    * @return void
    */
        
    private function set_location($remote_address,$api_url)
    {
        $ch = curl_init();
        

        curl_setopt($ch, CURLOPT_URL, $api_url . $remote_address);

        $headers   = array('Content-Type: application/json');

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        $reponse = curl_exec($ch);

        curl_close($ch);

        $this->location = json_decode($reponse,true);
        
    }

    /**
    * Get Location
    *
    * @access public
    * @return array
    */
    
    public function get_location()
    {
        return $this->location;
    }

}


