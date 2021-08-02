<?php
/**
* @package AspieSoftPluginTemplate
*/

if(!defined('ABSPATH')){
  http_response_code(404);
  die('404 Not Found');
}

if(!class_exists('AspieSoft_PluginTemplate_Main')){

  class AspieSoft_PluginTemplate_Main {

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
      // add shortcode
      add_shortcode('plugin-template', array($this, 'shortcode'));

      $altShortcode = self::$options['get']('altShortcode');
      if($altShortcode && $altShortcode !== null){
        add_shortcode($altShortcode, array($this, 'shortcode'));
      }
    }


    function shortcode($atts = ''){
      $attr = shortcode_atts(array(
        'attr' => null, 'altAttr' => null,
      ), $atts);

      $attr = self::$func::cleanShortcodeAtts($attr);

      $value = self::$func::getValue(array($attr['attr'], $attr['altAttr'], 'default'));

      return '<param class="plugin-template" attr="'.esc_html($value).'"></param>';
    }

  }

  $aspieSoft_PluginTemplate_Main = new AspieSoft_PluginTemplate_Main();

}
