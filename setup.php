<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function plugin_version_glpimobilenotification() {
   return array('name'           => "Glpi mobile notification",
                'version'        => '1.0.0',
                'author'         => 'mvv',
                'license'        => '',
                'homepage'       => '',
                'minGlpiVersion' => '');
}

/**
 *  Check if the config is ok - Needed
 */
function plugin_glpimobilenotification_check_config() {
    return true;
}
 
/**
 * Check if the prerequisites of the plugin are satisfied - Needed
 */
function plugin_glpimobilenotification_check_prerequisites() {
 
    // Check that the GLPI version is compatible
//    if (version_compare(GLPI_VERSION, '0.85', 'lt') || version_compare(GLPI_VERSION, '0.86', 'gt')) {
//        echo "This plugin Requires GLPI >= 0.85 and GLPI <0.86";
//        return false;
//    }
 
    return true;
}

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_glpimobilenotification() {
   global $PLUGIN_HOOKS;

   Plugin::registerClass(PluginGlpimobilenotificationEvent::class);

   $PLUGIN_HOOKS['csrf_compliant']['glpimobilenotification'] = true;

   $PLUGIN_HOOKS['item_add']['glpimobilenotification'] = [
       Ticket::class => 'plugin_glpimobilenotification_item_add',
       ITILFollowup::class => 'plugin_glpimobilenotification_followup_add'
   ];
   
 
}


