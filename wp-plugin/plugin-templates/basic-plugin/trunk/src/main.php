<?php

/**
* @package X_AUTHOR_NAME_XX_PLUGIN_NAME_X
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Main')) {

  class X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Main {

    public $plugin;
    private static $func;
    private static $options;

    public function init($pluginData) {
      // get plugin data and load common functions
      $this->plugin = $pluginData;
      self::$func = $this->plugin['func'];
      self::$options = $this->plugin['options'];
    }

    public function start() {
      // do stuff on frontend
    }
  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X_Main = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Main();
}
