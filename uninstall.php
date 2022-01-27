<?php

    global $DB;
    
    $table="glpi_users";
    $field="mobile_notification";
    
    $a= explode("/",__DIR__);
    $plagin_name=$a[sizeof($a)-1];

    $config_table_name="glpi_plugin_".$plagin_name."_config";

    //instanciate migration with version
    $migration = new Migration(100);

    if ($DB->tableExists($config_table_name)) {
        $query = "DROP TABLE `$config_table_name`";
        $DB->queryOrDie(
            $query,
            $DB->error()
         );

    }

    if ($DB->tableExists($table)) {  
    
        if ($DB->fieldExists($table, $field, false)) {

            $migration->dropField(
               $table,
               $field
            );
        }        
        
    }

    //execute the whole migration
    $migration->executeMigration();