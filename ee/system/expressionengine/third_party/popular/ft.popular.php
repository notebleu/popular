<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

include(PATH_THIRD.'popular/config.php');

require(PATH_THIRD.'popular/models/view_count.php');

class Popular_ft extends EE_Fieldtype {

    public $info = array(
        'name'      => POPULAR_NAME,
        'version'   => POPULAR_VERSION
    );
    
    /**
    * Display Field
    *
    * @access public
    * @return string
    */

    public function display_field($data)
    {
        $entry_id = ee()->input->get('entry_id');
        
        if( $entry_id == '' ) return 'No results to display';
        
        $view_count_model = new View_count($entry_id,ee()->config->item('site_id'));
        
        $view_count = $view_count_model->get_view_count();
	   
	    $activity = array();
	    
	    if( $view_count->num_rows() > 0 ){
    	    
    	    foreach( $view_count->result_array() as $plot )
    	    {
    	        $year   = substr($plot['view_date'], 0,4);
                $month  = substr($plot['view_date'], 5,2);
                $date   = substr($plot['view_date'], 8,2);
                
                if( array_key_exists($year.'-'.$month.'-'.$date, $activity) ){
                    $activity[$year.'-'.$month.'-'.$date]['total'] += 1;
                } else {
                    $activity[$year.'-'.$month.'-'.$date]['total'] = 1;
                }
                
                if( array_key_exists($plot['device_simple'], $activity[$year.'-'.$month.'-'.$date]) ){
                    $activity[$year.'-'.$month.'-'.$date][$plot['device_simple']] += 1;
                } else {
                    $activity[$year.'-'.$month.'-'.$date][$plot['device_simple']] = 1;
                }
                
                
    	    }
    	    
    	    $total = $mobile = $desktop = $tablet = $other = array();
    	    
    	    foreach( $activity as $date => $hits )
    	    {
                $year   = substr($date, 0,4);
                $month  = substr($date, 5,2) - 1;
                $date   = substr($date, 8,2);
                
                $total_hits   = ( isset( $hits['total'] ) ) ? $hits['total']: 0;
                $mobile_hits  = ( isset( $hits['Mobile'] ) ) ? $hits['Mobile']: 0;
                $desktop_hits = ( isset( $hits['Desktop'] ) ) ? $hits['Desktop']: 0;
                $tablet_hits  = ( isset( $hits['Tablet'] ) ) ? $hits['Tablet']: 0;
                $other_hits   = ( !isset( $hits['Tablet'] ) && !isset( $hits['Mobile'] ) && !isset( $hits['Desktop'] ) ) ? $hits['total']: 0;
                
        	    array_push($total, "{x: Date.UTC($year,  $month, $date, 00, 00, 00), y: $total_hits}");
        	    array_push($mobile, "{x: Date.UTC($year,  $month, $date, 00, 00, 00), y: $mobile_hits}");
        	    array_push($desktop, "{x: Date.UTC($year,  $month, $date, 00, 00, 00), y: $desktop_hits}");
        	    array_push($tablet, "{x: Date.UTC($year,  $month, $date, 00, 00, 00), y: $tablet_hits}");
        	    array_push($other, "{x: Date.UTC($year,  $month, $date, 00, 00, 00), y: $other_hits}");
    	    }
    	    
	    } else {
    	     return 'No results to display';
	    }
    
        ee()->cp->add_to_foot('<link rel="stylesheet" href="'. URL_THIRD_THEMES .'popular/css/style.css" type="text/css" media="screen" charset="utf-8" />');
        
        ee()->cp->add_to_foot('<script type="text/javascript" src="'. URL_THIRD_THEMES .'popular/js/highcharts.js"></script>');

	    ee()->cp->add_to_foot("
	        <script type=\"text/javascript\"> 
                $(function () {
                    $('#popular_highcharts').highcharts({
                        chart: {
                            zoomType: 'x',
                            type: 'area'
                        },
                        title: {
                            text: 'Views of this Article'
                        },
                        subtitle: {
                            text: document.ontouchstart === undefined ?
                                    'Click and drag in the plot area to zoom in' :
                                    'Pinch the chart to zoom in'
                        },
                        xAxis: {
                            type: 'datetime',
                            minRange: 24 * 3600000 // one day
                        },
                        yAxis: {
                            title: {
                                text: 'Number of Views'
                            }
                        },
                        legend: {
                            enabled: false
                        },
                        plotOptions: {
                            area: {
                                stacking: 'normal',
                                lineColor: '#666666',
                                lineWidth: 1,
                                marker: {
                                    lineWidth: 1,
                                    lineColor: '#666666'
                                }
                            }
                        },
                        legend: {
                            layout: 'vertical',
                            align: 'right',
                            verticalAlign: 'middle',
                            borderWidth: 0
                        },
                        series: [{
                            name: 'Total Views',
                            pointInterval: 24 * 3600000,
                            pointStart: Date.UTC(2014, 10, 01),
                            marker: {
                                enabled: false
                            },
                            data: [".implode(',', $total)."]
                        },
                        {
                            name: 'Desktop',
                            pointInterval: 24 * 3600000,
                            pointStart: Date.UTC(2014, 10, 01),
                            marker: {
                                enabled: false
                            },
                            data: [".implode(',', $desktop)."]
                        },
                        {
                            name: 'Tablet',
                            pointInterval: 24 * 3600000,
                            pointStart: Date.UTC(2014, 10, 01),
                            marker: {
                                enabled: false
                            },
                            data: [".implode(',', $tablet)."]
                        },
                        {
                            name: 'Mobile',
                            pointInterval: 24 * 3600000,
                            pointStart: Date.UTC(2014, 10, 01),
                            marker: {
                                enabled: false
                            },
                            data: [".implode(',', $mobile)."]
                        },
                        {
                            name: 'Other Devices',
                            pointInterval: 24 * 3600000,
                            pointStart: Date.UTC(2014, 10, 01),
                            marker: {
                                enabled: false
                            },
                            data: [".implode(',', $other)."]
                        }]
                    });
                });	    
            </script>
	    
	    ");
        
        
        
        return '<div id="popular_highcharts"></div>';
    }


    /**
    * Display Settings
    *
    * @access public
    * @return string
    */ 
    
    public function display_settings()
    {
        ee()->lang->loadfile('popular');
        ee()->table->add_row(
            lang('this_is_for_popular'),
            lang('')
        );
    }

    /**
    * Install
    *
    * @access public
    * @return array
    */    

    public function install()
    {
        return array();
    }    

    
}