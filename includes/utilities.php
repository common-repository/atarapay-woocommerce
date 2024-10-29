<?php

if (! defined('ABSPATH') ) {
    exit; // Exit if accessed directly
}

function at_wc_atara_load_template($template_name, $args = array())
{
    $locations = array(
    get_stylesheet_directory() . '/templates/' . $template_name . '.php',
    get_template_directory() . '/templates/' . $template_name . '.php', 
    AT_WC_ATARA_TEMPLATES . $template_name . '.php'
    );

    // Check each file location and load the first one that exists.
    foreach ($locations as $location) {
        if (file_exists($location)) {

            load_template($location, false, $args);

            return;
        }
    }
}