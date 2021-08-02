<?php
/**
* @package AspieSoftPluginTemplate
*/

if(!defined('ABSPATH') || !current_user_can('manage_options')){
  http_response_code(404);
  die('404 Not Found');
}

if(!class_exists('AspieSoft_PluginTemplate_Settings')){

  class AspieSoft_PluginTemplate_Settings{

    // settings for admin page (client side assets/settings.js file reads this, and loads html inputs from it)
    public function getOptionList(){
      $optionList = array(
        'jsdelivr' => array('label' => 'Load Assets From', 'default' => 'default', 'form' => '[label][select][br]', 'type' => 'select', 'options' => array(
          'default' => 'Default',
          'local' => 'Your Site',
          'jsdelivr' => 'Github (jsdelivr.net) (recommended)',
        )),

        'altShortcode' => array('label' => 'Alternate Shortcode Name', 'default' => '', 'form' => '[label][text][br][br]'),
      );
      return $optionList;
    }

    // global settings shared by all plugins
    public function getOptionListGlobal(){
      $optionList = array(
        'jsdelivr' => array('label' => 'Load Assets From', 'default' => 'local', 'form' => '[label][select][br][hr]', 'type' => 'select', 'options' => array(
          'local' => 'Your Site',
          'jsdelivr' => 'Github (jsdelivr.net) (recommended)',
        )),
      );
      return $optionList;
    }

  }

  $aspieSoft_PluginTemplate_Settings = new AspieSoft_PluginTemplate_Settings();

}
