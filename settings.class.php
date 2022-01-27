<?php
class PluginGlpimobilenotificationSettings extends CommonDBTM {
    
   public function showForm($ID, $options = []) {
       
      global $CFG_GLPI;

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      if (!isset($options['display'])) {
         //display per default
         $options['display'] = true;
      }

      $params = $options;
      //do not display called elements per default; they'll be displayed or returned here
      $params['display'] = false;

      $out = 'Hello! It is GLPI MOBILE NOTIFICATION';


      if ($options['display'] == true) {
         echo $out;
      } else {
         return $out;
      }
   }
}