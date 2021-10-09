<?php

/**
* @package AuthorNamePluginName
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('AuthorName_PluginName_Main')) {

  class AuthorName_PluginName_Main {

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

  $authorName_PluginName_Main = new AuthorName_PluginName_Main();
}
