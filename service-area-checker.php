<?php

/**
 * Plugin Name: Service Area Checker
 * Plugin URI: https://www.example.com/
 * Description: Plugin that can help to check if your user inside or outside service area you provide 
 * Version: 1.0.0
 * Author: Rifky Maulana
 * Author URI: https://rifkymol.my.id (under maintance)
 **/

 
require_once dirname( __FILE__ ) . '/classes/service-area-checker-class.php';

if ( class_exists( 'Service_Area_Checker' ) ) {
    new Service_Area_Checker();
}

