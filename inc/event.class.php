<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of PluginGlpimobilenotificationEvent
 *
 * @author mvv
 */
class PluginGlpimobilenotificationEvent extends CommonDBTM
{
       
    static $CONFIG_TABLE="glpi_plugin_glpimobilenotification_config";
    static $TOKEN_PREFIX_ANDROID="FBT:";
	static $TOKEN_PREFIX_IOS="FTIOS:";
	static $MESSAGE_LANG_RUS="RUS";
	
		  
    static $ID_KEY="ticketid";
    static $NAME_KEY="name";
    static $TYPE_KEY="objecttype";
     
    static $FIREBASE_URL=""; 
    static $API_KEY_ANDROID=""; 
	static $API_KEY_IOS="";
    static $MESSAGE_TITLE="";
	static $MESSAGE_LANG="";
    static $ACTOR_TYPES=""; 
    static $ADMIN_PROFILES="";  // 3,4
    static $WRITE_LOG ="";
    static $LOG_FILE ="";
    
    static $LOG_FILE_OLD ="";
    static $WRITE_LOG_OLD =false;       
    
    static function item_add_ticket(Ticket $ticket)   
    {
        
        self::getparams();
        
        global $DB;
                         
            $message="New ticket %d";// from %s";
			if (strtoupper(self::$MESSAGE_LANG)==self::$MESSAGE_LANG_RUS) $message="Новая заявка %d"; // от %s";

            $ticketfields=$ticket->fields;
            $ticketId=$ticketfields['id']; 
            $ticketname=$ticketfields['name']; 
            $ticketentity=$ticketfields['entities_id'];
            $ticketrecipient=$ticketfields['users_id_recipient'];
            $ticketentityname="?";
           
            // get ticket entity and its parent entities
            
            foreach ($DB->request("SELECT name FROM glpi_entities WHERE id=".$ticketentity) as $row) {
                $ticketentityname=$row['name'];
            }
 
            $entities=self::getentities($ticketentity); 
            
            // get users tokens for admins (from ADMIN_PROFILES)in these entities and actors of ticket other than author of the ticket
            $users=$DB->request("SELECT glpi_users.id, glpi_users.mobile_notification "
                    . "FROM glpi_users, glpi_profiles_users "
                    . "WHERE "
                    . "glpi_users.id <> $ticketrecipient "
                    . "and mobile_notification > '' "
                    . "and glpi_users.id=glpi_profiles_users.users_id "
                    . "and glpi_profiles_users.profiles_id in (".self::$ADMIN_PROFILES.") "
                    . "and glpi_profiles_users.entities_id in (".$entities.") "
                    . "UNION "
                    . "SELECT glpi_users.id, glpi_users.mobile_notification "
                    . "FROM glpi_users, glpi_tickets_users "
                    . "WHERE "
                    . "glpi_users.id <> $ticketrecipient "
                    . "and glpi_users.mobile_notification > '' "
                    . "and glpi_users.id=glpi_tickets_users.users_id "
                    . "and glpi_tickets_users.tickets_id=$ticketId "
                    . "and glpi_tickets_users.type in (".self::$ACTOR_TYPES.")" );      

            if (sizeof($users)>0) {
                
                $message_body=sprintf($message, $ticketId); //, $ticketentityname);
                
                $notification=array(
                            "body"  => $message_body,
                            "title"  => self::$MESSAGE_TITLE);
                $data=array(
                            self::$ID_KEY  =>  $ticketId,
                            self::$NAME_KEY  =>  $ticketname,
                            self::$TYPE_KEY => "ticket");

                self::send_event($users, $notification, $data, "Ticket $ticketId added");
            }
               
        if (self::$WRITE_LOG=="1")  {                       
             ini_set("log_errors", self::$WRITE_LOG_OLD);  
             ini_set('error_log', self::$LOG_FILE_OLD);
        }
    }
     
/**
 *отправка уведомлений при добавлении комментария к заявке
 * отправляется тем пользователям, кто по этой заявке указан в таблице glpi_tickets_users (кроме автора зявки)
 * и имеют firebase token
 */  
    static function item_add_followup(ITILFollowup $followup) {
        
        self::getparams();
        
        global $DB;
        
        $followupfields=$followup->fields;
        if ($followupfields['itemtype']=="Ticket") {
            
            $message="New followup to ticket %d"; // from %s";
			if (strtoupper(self::$MESSAGE_LANG)==self::$MESSAGE_LANG_RUS) $message="Новый комментарий к заявке %d"; // от %s";
			
			
			          
            $ticketId=$followupfields['items_id'];
            $userId=$followupfields['users_id']; // автор комментария
            $private=$followupfields['is_private'];
            $ticketentityname="?";
            $ticketentity="?";
            
            // определяем организацию для показа при открытии в приложении нужной заявки при получении уведомления
            $query="SELECT glpi_entities.name,glpi_entities.id "
                    . "FROM glpi_entities, glpi_tickets "
                    . "WHERE glpi_tickets.id=$ticketId "
                    . "and glpi_tickets.entities_id=glpi_entities.id";
            
            foreach ($DB->request($query) as $row) {
                $ticketentityname=$row['name'];
                $ticketentity=$row['id'];
            } 
            
            $entities= self::getEntities($ticketentity);
 
            // get users tokens for admins (from ADMIN_PROFILES)in these entities and actors of ticket ther than author of the followup 
            // if foolowup is privite get actors with type from ACTOR_TYPES (2)
            
            $users=$DB->request("SELECT glpi_users.id, glpi_users.mobile_notification "
                    . "FROM glpi_users, glpi_profiles_users "
                    . "WHERE "
                    . "glpi_users.id <> $userId "
                    . "and glpi_users.mobile_notification > '' "
                    . "and glpi_users.id=glpi_profiles_users.users_id "
                    . "and glpi_profiles_users.profiles_id in (".self::$ADMIN_PROFILES.") "
                    . "and glpi_profiles_users.entities_id in (".$entities.") "
                    . "UNION "
                    . "SELECT glpi_users.id, glpi_users.mobile_notification "
                    . "FROM glpi_users, glpi_tickets_users "
                    . "WHERE "
                    . "glpi_users.id <> $userId "
                    . "and glpi_users.mobile_notification > '' "
                    . "and glpi_users.id=glpi_tickets_users.users_id "
                    . "and glpi_tickets_users.tickets_id=$ticketId "
                    . "and ($private=0 or glpi_tickets_users.type in(".self::$ACTOR_TYPES."))" );           
                     

            if (sizeof($users)>0) {
                
                $message_body=sprintf($message, $ticketId); //, $ticketentityname);
                
                $notification=array(
                            "body"  => $message_body,
                            "title"  => self::$MESSAGE_TITLE);
                $data=array(
                            self::$ID_KEY  =>  $ticketId,
                            self::$NAME_KEY  =>  $ticketentityname,
                            self::$TYPE_KEY => "followup");
                
                self::send_event($users, $notification, $data, "Followup to ticket $ticketId added");
            
            }
            
        }
        
        if (self::$WRITE_LOG=="1")  {                      
             ini_set("log_errors", self::$WRITE_LOG_OLD);  
             ini_set('error_log', self::$LOG_FILE_OLD);
        }
             
    }
     
     
    static function send_event( $users, $notification, $data,  $logtitle) {
	
		$sendings = array();
		$sendings[0][0]=self::$TOKEN_PREFIX_ANDROID;
		$sendings[0][1]=self::$API_KEY_ANDROID;
		$sendings[1][0]=self::$TOKEN_PREFIX_IOS;
		$sendings[1][1]=self::$API_KEY_IOS;
		
		foreach ($sendings as $sending) { 
		   
			if (strlen($sending[0])>0 && strlen($sending[1])>0)  { 
	      
				$ids=array();
				foreach ($users as $row) {             
                 
					// выбираются пользователи у которых есть firebase token 					
					if (strpos($row['mobile_notification'], $sending[0]) !== false) {                   
						$a= explode(":",$row['mobile_notification'], 2);
						if (sizeof($a)==2) {                 
							$ids[]=$a[1]; 
						}                  
					}
				
				}
             
				// notifiing
				if (sizeof($ids)>0)  { 
                
					if($curl = curl_init() ) {
            
						curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

						curl_setopt($curl, CURLOPT_URL, self::$FIREBASE_URL);
						curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: key='.$sending[1]));
                    
						$json = array(
							"notification" => $notification,
							"data" => $data,
							"registration_ids" => $ids	
						);
                                                     
						curl_setopt($curl, CURLOPT_POST, true);
						curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($json));                                
                    
						$out = curl_exec($curl);

						curl_close($curl);
                    
error_log($logtitle.": ".sizeof( $ids)." users notified: ".$out); 
        
					} else { error_log($logtitle.": Can't set url connection."); }
								
                
				} else { error_log($logtitle.": No $sending[0] Firebase user's tokens found to notify"); }
                  
			} else { error_log($logtitle.": Firebase app key $sending[0] is empty"); }
		
		}
         
    }
     
    static function getparams() {
        
//ini_set("log_errors", TRUE);  
//ini_set('error_log', "/var/www/glpi/plugins/glpimobilenotification/glpimobilenotification.log");

        global $DB;
          
        $query="SELECT par_name, par_value FROM ".self::$CONFIG_TABLE;
          
        $rows= $DB->queryOrDie($query, $DB->error());
            
        foreach ($rows as $row) {

            $parname=strtoupper($row['par_name']);
            $parvalue=$row['par_value'];
           
            self::$$parname=$parvalue;

        }      
          
                    
        if (self::$WRITE_LOG=="1")  {
            
             self::$LOG_FILE_OLD=ini_get("error_log");
             self::$WRITE_LOG_OLD=ini_get("log_errors");
             
             ini_set("log_errors", TRUE);  
             ini_set('error_log', self::$LOG_FILE);
                               
        }
        
//error_log("FIREBASE_URL=".self::$FIREBASE_URL); 
//error_log("API_KEY=".self::$API_KEY); 
//error_log("MESSAGE_TITLE=".self::$MESSAGE_TITLE); 
//error_log("ACTOR_TYPES=".self::$ACTOR_TYPES); 
//error_log("ADMIN_PROFILES=".self::$ADMIN_PROFILES); 
//error_log("WRITE_LOG =".self::$WRITE_LOG);
//error_log("LOG_FILE =".self::$LOG_FILE);
//error_log("WRITE_LOG_OLD =".self::$WRITE_LOG_OLD);
//error_log("LOG_FILE_OLD =".self::$LOG_FILE_OLD);
       
    }
    
    static function getEntities($ticketentity) {
        
        // get ticket entity and its parent entities
        
        global $DB;
        
        $entities=$ticketentity; 
        
        foreach ($DB->request("SELECT completename, name FROM glpi_entities WHERE id=".$ticketentity) as $row) {
            
            if ($row['completename']) { 
                                    
                $names="'".str_replace(" > ","','", $row['completename'])."'";
 
                $query="SELECT id FROM glpi_entities WHERE name in ($names)";

                foreach ($DB->request($query) as $row_) {
                    $entities=$entities.",".$row_['id'];                   
                    
                }
            
                      
            }
        }
        
        return $entities;
        
        
    }
}

