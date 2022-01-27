<?php

    // config parameters
    $parameters= [
        "firebase_url"=>"",
        "api_key_android"=>"",
		"api_key_ios"=>"",
//        "token_prefix"=>"",
        "message_title"=>"",
		"message_lang"=>"",
        "actor_types"=>"",
        "admin_profiles"=>"",
        "write_log"=>""
       ];
   
    //   field in glpi_users for mobile_notification token
    $table="glpi_users";
    $field="mobile_notification";
    $type= "VARCHAR(255)";

    // plagin name detecting
    $a= explode("/",__DIR__);
    $plagin_name=$a[sizeof($a)-1];

    $config_table_name="glpi_plugin_".$plagin_name."_config";
    $configfile=__DIR__."/".$plagin_name.".cfg";
    $logfile=__DIR__."/".$plagin_name.".log";
    
    
    $fd = fopen($configfile, 'r') or die("Can't open config file '".$configfile."'");
    while(!feof($fd))
    {
        $str = trim(fgets($fd));
        $a= explode("=",$str,2);
		
        if (array_key_exists($a[0], $parameters)) {
            $parameters[$a[0]]=$a[1];
            
        }      
    }
    fclose($fd);

    foreach(array_keys($parameters) as $key) {
        if ($parameters[$key]=="") {
            die ("Parameter '".$key."' is empty. Check config file '".$configfile."'");
        }      
    }
    
    global $DB;

   //instanciate migration with version
   $migration = new Migration(100);

   //Create table only if it does not exists yet!
   if (!$DB->tableExists($config_table_name)) {
      //table creation query
      $query = "CREATE TABLE $config_table_name (
                  `id` INT AUTO_INCREMENT PRIMARY KEY,
                  `par_name` VARCHAR(255) NOT NULL,
                  `par_value` VARCHAR(255) NOT NULL                  
               )";
      $DB->queryOrDie($query, $DB->error());
    }
   
    // insert parameters
    foreach(array_keys($parameters) as $key) {
        $query = "INSERT INTO $config_table_name (par_name,par_value) VALUES ('$key','$parameters[$key]')";  
        $DB->queryOrDie($query, $DB->error());        
    }
    
    // add log file parameter
    $query = "INSERT INTO $config_table_name (par_name,par_value) VALUES ('log_file','$logfile')";  
    $DB->queryOrDie($query, $DB->error());

    // add notification field 
    if ($DB->tableExists($table)) {  
    
        if (!$DB->fieldExists($table, $field, false)) {
                    
            $migration->addField(
               $table,
               $field,
               $type
            );
                   
        }
    }
            
  //execute the whole migration
   $migration->executeMigration();