<?php

/**
* @package X_AUTHOR_NAME_XX_PLUGIN_NAME_X
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_X_FILECLASS_X')) {

  class X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_X_FILECLASS_X {

    public $plugin;
    private static $func;
    private static $options;

    public function init($pluginData) {
      // get plugin data and load common functions
      $this->plugin = $pluginData;
      if (!class_exists('X_AUTHOR_NAME_X_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $X_AUTHOR_NAME_VAR_X_Functions_v2;
      self::$func = $X_AUTHOR_NAME_VAR_X_Functions_v2::init($this->plugin);
      self::$options = self::$func['options']();
    }

    public function start() {
      // do stuff...
    }
  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X_X_FILECLASS_X = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_X_FILECLASS_X();
}
