<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Called when user click on Install - Needed
 */
function plugin_glpimobilenotification_install() { 
    
   require_once 'install.php';

   return true; 
    
}
 
/**
 * Called when user click on Uninstall - Needed
 */
function plugin_glpimobilenotification_uninstall() { 
    
    require_once 'uninstall.php';
        
    return true; 
    
}


/**
 * @param Ticket $ticket
 *
 * @return bool
 */
function plugin_glpimobilenotification_item_add(Ticket $ticket)
{
    return PluginGlpimobilenotificationEvent::item_add_ticket($ticket);
}

function plugin_glpimobilenotification_followup_add(ITILFollowup $followup)
{
    return PluginGlpimobilenotificationEvent::item_add_followup($followup);
}