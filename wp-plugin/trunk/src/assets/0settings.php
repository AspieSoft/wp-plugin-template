<?php
/**
* @package AspieSoftPluginTemplate
*/

if(!defined('ABSPATH')){
  http_response_code(404);
  die('404 Not Found');
}

// file named 0settings so it will be indexed at the top of the src/assets dir

if(!class_exists('AspieSoft_PluginTemplate_AssetSettings')){

  class AspieSoft_PluginTemplate_AssetSettings{

    public $plugin;
    public static $func;

    public function init($pluginData){
      $this->plugin = $pluginData;
      require_once(plugin_dir_path(__FILE__).'../../functions.php');
      global $AspieSoft_Functions_v1_2;
      self::$func = $AspieSoft_Functions_v1_2;
    }

    /*public function addScript($scriptBefore){
      // add inline scripts
    }*/

    /*public function addStyle($scriptBefore){
      // add inline styles
    }*/

  }
  
  $aspieSoft_PluginTemplate_AssetSettings = new AspieSoft_PluginTemplate_AssetSettings();

}
