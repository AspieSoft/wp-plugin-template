<?php

/**
 * @package X_AUTHOR_NAME_XX_PLUGIN_NAME_X
 */

if (!defined('ABSPATH') || !current_user_can('manage_options')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Settings')) {
  class X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Settings {

    public $plugin;

    private static $func;
    private static $inputList;

    public $optionList = array();


    public function init($plugin) {
      $this->plugin = $plugin;

      if (!class_exists('X_AUTHOR_NAME_X_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $X_AUTHOR_NAME_VAR_X_Functions_v2;
      self::$func = $X_AUTHOR_NAME_VAR_X_Functions_v2::init($this->plugin);

      self::$inputList = self::$func['inputList'](array($this, 'optionList'));

      $this->setOptionList();
    }


    private function setOptionList() {

      $global = self::$inputList['tab'](
        'global',
        'Global Settings',
        'Global Settings Shared By All Plugins Made By ' . $this->plugin['author'],
        10000, // end
        true // global
      );

      $global['select']('jsdelivr', 'Load Assets From', 'local', array(
        'local' => 'Your Site',
        'jsdelivr' => 'Github (jsdelivr.net)',
      ), null /* optional css changes */, true /* set autoload (default: true) */);


      $local = self::$inputList['tab'](
        'local',
        'Settings',
        'Settings For ' . $this->plugin['name'],
        1 // start
      );

      $local['select']('jsdelivr', 'Load Assets From', 'default', array(
        'default' => 'Default (Use Global)',
        'local' => 'Your Site',
        'jsdelivr' => 'Github (jsdelivr.net)',
      ));
    }


    public function getOptionList() {
      return $this->optionList;
    }
  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X_Settings = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X_Settings();
}
