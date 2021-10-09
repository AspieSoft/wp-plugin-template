<?php

/**
* @package AuthorNamePluginName
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

// file named 0settings so it will be indexed at the top of the src/assets dir

if (!class_exists('AuthorName_PluginName_AssetSettings')) {

  class AuthorName_PluginName_AssetSettings {

    public $plugin;

    public static $func;
    public static $options;

    private $opts = array();

    public function init($pluginData) {
      $this->plugin = $pluginData;
      self::$func = $this->plugin['func'];
      self::$options = $this->plugin['options'];
    }


    // send options to front end or enqueue inline scripts
    /* public function addScript($scriptBefore) {
      $opts = array(
        'key' => 'value',
      );

      $resOpts = wp_json_encode($this->opts);
      wp_add_inline_script($scriptBefore, ";var AuthorNamePluginNameOptions = $resOpts;", 'before');
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
      wp_add_inline_script($scriptBefore, ";var AuthorNamePluginNameOptions = $resOpts;", 'before');
    } */

    // enqueue inline styles for admin
    /* public function addAdminStyle($styleBefore){
      wp_add_inline_style($styleBefore, 'body{display:block;}');
    } */

  }

  $authorName_PluginName_AssetSettings = new AuthorName_PluginName_AssetSettings();
}
