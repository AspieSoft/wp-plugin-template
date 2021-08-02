<?php
/**
* @package AspieSoftPluginTemplate
*/

if(!defined('ABSPATH')){
  http_response_code(404);
  die('404 Not Found');
}

if(!class_exists('AspieSoft_PluginTemplate_Admin')){

  class AspieSoft_PluginTemplate_Admin {

    public $plugin;
    private static $func;
    private static $options;
    private static $optionsGlobal;

    public function init($pluginData){
      // get plugin data and load common functions
      $this->plugin = $pluginData;
      require_once(plugin_dir_path(__FILE__).'../functions.php');
      global $AspieSoft_Functions_v1_3;
      self::$func = $AspieSoft_Functions_v1_3;
      self::$options = self::$func::options($this->plugin);
      self::$optionsGlobal = self::$func::options(array('setting' => 'global'));
    }

    public function start(){
      // do stuff...
    }

  }

  $aspieSoft_PluginTemplate_Admin = new AspieSoft_PluginTemplate_Admin();

}
