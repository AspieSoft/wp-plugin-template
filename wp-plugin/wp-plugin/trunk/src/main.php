<?php

/**
* @package AspieSoftPluginTemplate
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('AspieSoft_PluginTemplate_Main')) {

  class AspieSoft_PluginTemplate_Main {

    public $plugin;
    private static $func;
    private static $options;

    public function init($pluginData) {
      // get plugin data and load common functions
      $this->plugin = $pluginData;
      if (!class_exists('AspieSoft_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $aspieSoft_Functions_v2;
      self::$func = $aspieSoft_Functions_v2::init($this->plugin);
      self::$options = self::$func['options']();
    }

    public function start() {
      // do stuff on frontend
    }
  }

  $aspieSoft_PluginTemplate_Main = new AspieSoft_PluginTemplate_Main();
}
