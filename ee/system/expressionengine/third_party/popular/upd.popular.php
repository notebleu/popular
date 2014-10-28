<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include_once(PATH_THIRD.'popular/config.php');

class Popular_upd {

    /**
    * Install
    *
    * @access public
    * @return bool
    */

    public function install()
    {
        ee()->load->dbforge();

        $data = array(
            'module_name'         => POPULAR_NAME,
            'module_version'      => POPULAR_VERSION,
            'has_cp_backend'      => 'n',
            'has_publish_fields'  => 'y'
        );

        ee()->db->insert('modules', $data);

        # create columns in the database to store information, contraints used for optimization
        $fields = array(
            'view_id'         => array('type' => 'int', 'constraint' => '11', 'unsigned' => TRUE, 'auto_increment' => TRUE),
            'entry_id'        => array('type' => 'int', 'constraint' => '11'),
            'site_id'         => array('type' => 'int', 'constraint' => '11', 'default' => 1),
            'view_date'       => array('type' => 'timestamp'),
            'ipv4'            => array('type' => 'int', 'unsigned' => TRUE),
            'ipv6'            => array('type' => 'binary', 'constraint' => '16'),
            'device'          => array('type' => 'varchar', 'constraint' => '100'),
            'device_simple'   => array('type' => 'varchar', 'constraint' => '100'),
            'uri'             => array('type' => 'varchar', 'constraint' => '300'),
            'country_code'    => array('type' => 'varchar', 'constraint' => '3'),
            'country_name'    => array('type' => 'varchar', 'constraint' => '100'),
            'region_code'     => array('type' => 'varchar', 'constraint' => '3'),
            'region_name'     => array('type' => 'varchar', 'constraint' => '100'),
            'city'            => array('type' => 'varchar', 'constraint' => '100'),
            'zipcode'         => array('type' => 'varchar', 'constraint' => '20'),
            'latitude'        => array('type' => 'varchar', 'constraint' => '20'),
            'longitude'       => array('type' => 'varchar', 'constraint' => '20'),


        );

        ee()->dbforge->add_field($fields);
        ee()->dbforge->add_key('view_id', TRUE);
        ee()->dbforge->add_key('entry_id');
        ee()->dbforge->add_key('device');

        ee()->dbforge->create_table('popular');

        unset($fields);
        
        ee()->load->library('layout');
        ee()->layout->add_layout_tabs($this->tabs(), 'popular');

        return TRUE;

    }

    /**
    * Tabs
    *
    * @access public
    * @return array
    */

    public function tabs()
    {
        
        $tabs['popular'] = array(
            'highchart'=> array(
                'visible'     => TRUE,
                'collapse'    => FALSE,
                'htmlbuttons' => 'false',
                'width'       => '100%'
                )
            );
    
        return $tabs;
    }

    /**
    * Uninstall
    *
    * @access public
    * @return bool
    */

    public function uninstall()
    {
        ee()->load->dbforge();

        ee()->db->select('module_id');
        $query = ee()->db->get_where('modules', array('module_name' => 'Popular'));

        ee()->db->where('module_id', $query->row('module_id'));
        ee()->db->delete('module_member_groups');

        ee()->db->where('module_name', 'Popular');
        ee()->db->delete('modules');

        ee()->db->where('class', 'Popular');
        ee()->db->delete('actions');

        ee()->dbforge->drop_table('popular');

        ee()->load->library('layout');
        ee()->layout->delete_layout_tabs($this->tabs(), 'popular');

        return TRUE;
    }

    /**
    * Update
    *
    * @access public
    * @return bool
    */

    public function update($current = '')
    {

        if ($current == $this->version){
            return FALSE;
        }

    }
}
