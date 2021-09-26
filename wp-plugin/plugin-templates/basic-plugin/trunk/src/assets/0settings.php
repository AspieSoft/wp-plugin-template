<?php

/**
* @package X_AUTHOR_NAME_XX_PLUGIN_NAME_X
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

// file named 0settings so it will be indexed at the top of the src/assets dir

if (!class_exists('X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_AssetSettings')) {

  class X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_AssetSettings {

    public $plugin;

    public static $func;
    public static $options;

    private $opts = array();

    public function init($pluginData) {
      $this->plugin = $pluginData;
      if (!class_exists('X_AUTHOR_NAME_X_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $X_AUTHOR_NAME_VAR_X_Functions_v2;
      self::$func = $X_AUTHOR_NAME_VAR_X_Functions_v2::init($this->plugin);
      self::$options = self::$func['options']();
    }


    // send options to front end or enqueue inline scripts
    /* public function addScript($scriptBefore) {
      $opts = array(
        'key' => 'value',
      );

      $resOpts = wp_json_encode($this->opts);
      wp_add_inline_script($scriptBefore, ";var X_AUTHOR_NAME_XX_PLUGIN_NAME_XOptions = $resOpts;", 'before');
    } */

    // enqueue inline styles
    /* public function addStyle($styleBefore){
      wp_add_inline_style($styleBefore, 'body{display:block;}');
    } */


    // send admin options to front end or enqueue inline scripts
    /* public function addAdminScript($scriptBefore) {
      $opts = array(
        'key' => 'value',
      );

      $resOpts = wp_json_encode($this->opts);
      wp_add_inline_script($scriptBefore, ";var X_AUTHOR_NAME_XX_PLUGIN_NAME_XOptions = $resOpts;", 'before');
    } */

    // enqueue inline styles for admin
    /* public function addAdminStyle($styleBefore){
      wp_add_inline_style($styleBefore, 'body{display:block;}');
    } */

  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X_AssetSettings = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_AssetSettings();
}
