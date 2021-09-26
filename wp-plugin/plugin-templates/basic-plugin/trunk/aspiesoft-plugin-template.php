<?php

/**
 * @package X_AUTHOR_NAME_XX_PLUGIN_NAME_X
 */

/*
Plugin Name: X_AUTHOR_NAME_X X_PLUGIN_DISPLAY_NAME_X
Plugin URI: https://github.com/X_AUTHOR_NAME_X/wp-X_PLUGIN_SLUG_X
Description: A Plugin Template by X_AUTHOR_NAME_X.
Version: X_PLUGIN_VERSION_STRICT_X
Author: X_AUTHOR_NAME_X
Author URI: X_WEBSITE_URL_X
License: GPLv2 or later
Text Domain: X_AUTHOR_SLUG_X-X_PLUGIN_SLUG_X
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

/*
  This plugin is made from a template by X_AUTHOR_NAME_X: https://github.com/X_AUTHOR_NAME_X/wp-X_PLUGIN_SLUG_X
  The main source code that is modified from the template is in the "src" directory
  The code outside the "src" directory is still necessary to run the plugin
*/

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('X_AUTHOR_NAME_X_X_PLUGIN_NAME_X')) {
  class X_AUTHOR_NAME_X_X_PLUGIN_NAME_X {

    public $jsdelivrURL = 'https://cdn.jsdelivr.net/gh/X_AUTHOR_NAME_X/wp-X_PLUGIN_SLUG_X';

    public $pluginName;
    public $plugin;

    private static $func;
    private static $options;

    private $useJSDelivr = false;

    function __construct() {
      $this->pluginName = plugin_basename(__FILE__);
    }


    public function start() {
      if (!is_admin()) {
        self::$func['loadPluginFile']('main.php');
      } else if (is_admin()) {
        self::$func['loadPluginFile']('admin.php');
      }
    }


    public function register() {
      // ensure get_plugin_data function is loaded on frontend
      if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
      }

      // grab plugin data to use dynamic to the plugin
      $pluginData = get_plugin_data(__FILE__);
      $this->plugin = array(
        'name' => preg_replace('/\s*\(' . preg_quote($pluginData['Name'], '/i') . '\)\s*/', '', sanitize_text_field($pluginData['Name'])),
        'setting' => str_replace('-', '', ucwords(sanitize_text_field($pluginData['TextDomain']), '-')),
        'slug' => sanitize_text_field($pluginData['TextDomain']),
        'version' => sanitize_text_field($pluginData['Version']),
        'author' => sanitize_text_field($pluginData['AuthorName']),
        'authorVar' => sanitize_text_field(lcfirst($pluginData['AuthorName'])),
        'pluginName' => str_replace('-', '', ucwords(trim(str_replace(strtolower(sanitize_text_field($pluginData['AuthorName'])), '', strtolower(sanitize_text_field($pluginData['TextDomain']))), '-'), '-')),
      );

      if (is_admin()) {
        // add plugin basename to php defined var, for admin template to use get_plugin_data on correct file
        define('PLUGIN_BASENAME_' . basename(plugin_dir_path(__FILE__)), $this->pluginName);
      }

      // get common functions.php file
      // multiple plugins can use same file in the future (without functions.php class being loaded twice)
      // version added so updates to functions can still occur without breaking other plugins
      if (!class_exists('X_AUTHOR_NAME_X_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $X_AUTHOR_NAME_VAR_X_Functions_v2;
      self::$func = $X_AUTHOR_NAME_VAR_X_Functions_v2::init($this->plugin);

      // init options
      self::$options = self::$func['options']();

      // check if admin chosen to use jsdelivr
      $jsdelivrOption = self::$options['get']('jsdelivr', 'default');
      if ($jsdelivrOption === 'default') {
        $jsdelivrOption = self::$options['get']('jsdelivr', 'local', 'string', null, true);
      }

      if ($jsdelivrOption === 'jsdelivr') {
        $this->useJSDelivr = true;
      } else {
        $this->useJSDelivr = false;
      }


      if (!is_admin()) {
        // enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
      }else{
        // add settings page if it exists
        if (file_exists(plugin_dir_path(__FILE__) . 'src/settings.php')) {
          add_action('admin_menu', array($this, 'add_admin_pages'));
          add_filter("plugin_action_links_$this->pluginName", array($this, 'settings_link'));
        }

        // admin enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
      }
    }


    public function settings_link($links) {
      array_unshift($links, '<a href="options-general.php?page=' . $this->plugin['slug'] . '">Settings</a>');
      return $links;
    }

    public function add_admin_pages() {
      add_options_page($this->plugin['name'], $this->plugin['name'], 'manage_options', $this->plugin['slug'], array($this, 'admin_index'));
    }

    public function admin_index() {
      require_once(plugin_dir_path(__FILE__) . 'templates/admin.php');
    }


    public function activate() {
      if (file_exists(plugin_dir_path(__FILE__) . 'src/settings.php')) {
        $this->enableOptionsAutoload();
      }
      //flush_rewrite_rules();
    }

    public function deactivate() {
      if (file_exists(plugin_dir_path(__FILE__) . 'src/settings.php')) {
        $this->disableOptionsAutoload();
      }
      //flush_rewrite_rules();
    }

    private function enableOptionsAutoload() {
      // ensure register function ran
      if (!$this->plugin || !self::$func || !self::$options) {
        $this->register();
      }

      $settingsPage = self::$func['loadPluginFile']('settings.php', true);
      $optionList = $settingsPage->getOptionList();

      $this->toggleOptionsAutoload($optionList, true);
    }

    private function disableOptionsAutoload() {
      // when looking at my test sites database, I noticed an autoload feature on options, and looked it up
      // it seems autoload can slow down sites, and can be disabled if its not always used
      // if the plugin disables autoload for it's options, on deactivation, then their still saved, but not loaded when unneeded
      // then reactivating autoload (with enableOptionsAutoload function) on activation because options are being used again

      // ensure register function ran
      if (!$this->plugin || !self::$func || !self::$options) {
        $this->register();
      }

      $settingsPage = self::$func['loadPluginFile']('settings.php', true);
      $optionList = $settingsPage->getOptionList();

      $this->toggleOptionsAutoload($optionList, false);
    }

    private function toggleOptionsAutoload($optionList, $autoload){
      foreach ($optionList as $name => $data) {
        if (!$data['global']) {
          if ($data['type'] === 'tab') {
            $this->toggleOptionsAutoload($data['options'], $autoload);
          } else {
            $value = self::$options['get']($name, null, $data['type'], false, false);
            if (isset($value) && $value !== null) {
              self::$options['set']($name, $value, false, false, $autoload);
            }
          }
        }
      }
    }


    public function enqueue() {
      // dynamically enqueue all js and css assets from src/assets

      $assetsDir = plugin_dir_path(__FILE__) . 'src/assets/';
      if (file_exists($assetsDir) && is_dir($assetsDir)) {
        // check if inline settings scripts or styles file and functions exist
        // 0settings.php is used to load client side settings that should be sent (this is separate from the settings.php file outside the assets dir)
        $addInlineSettingsScript = false;
        $addInlineSettingsStyle = false;
        $inlineSettings = self::$func['loadPluginFile']('assets/0settings.php', true);
        if ($inlineSettings) {
          if (is_callable(array($inlineSettings, 'addScript'))) {
            $addInlineSettingsScript = true;
          }
          if (is_callable(array($inlineSettings, 'addStyle'))) {
            $addInlineSettingsStyle = true;
          }
        }

        // enqueue assets list
        $assets = scandir($assetsDir);
        foreach ($assets as $asset) {
          if (self::$func['endsWith']($asset, '.js') && !self::$func['endsWith']($asset, 'admin.js')) {
            wp_enqueue_script($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array('jquery'), $this->plugin['version'], true);

            if ($addInlineSettingsScript) {
              $addInlineSettingsScript = false;
              $inlineSettings->addScript($this->plugin['setting'] . '_' . $asset);
            }
          } else if (self::$func['endsWith']($asset, '.css') && !self::$func['endsWith']($asset, 'admin.css')) {
            wp_enqueue_style($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array(), $this->plugin['version']);

            if ($addInlineSettingsStyle) {
              $addInlineSettingsStyle = $this->plugin['setting'] . '_' . $asset;
            }
          }
        }
        if ($addInlineSettingsStyle && $addInlineSettingsStyle !== true) {
          $inlineSettings->addStyle($addInlineSettingsStyle);
        }
      }
    }

    public function admin_enqueue() {
      // dynamically enqueue all js and css assets from src/assets

      $assetsDir = plugin_dir_path(__FILE__) . 'src/assets/';
      if (file_exists($assetsDir) && is_dir($assetsDir)) {
        // check if inline settings scripts or styles file and functions exist
        // 0settings.php is used to load client side settings that should be sent (this is separate from the settings.php file outside the assets dir)
        $addInlineSettingsScript = false;
        $addInlineSettingsStyle = false;
        $inlineSettings = self::$func['loadPluginFile']('assets/0settings.php', true);
        if ($inlineSettings) {
          if (is_callable(array($inlineSettings, 'addAdminScript'))) {
            $addInlineSettingsScript = true;
          }
          if (is_callable(array($inlineSettings, 'addAdminStyle'))) {
            $addInlineSettingsStyle = true;
          }
        }

        // enqueue assets list
        $assets = scandir($assetsDir);
        foreach ($assets as $asset) {
          if (self::$func['endsWith']($asset, 'admin.js')) {
            wp_enqueue_script($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array('jquery'), $this->plugin['version'], true);

            if ($addInlineSettingsScript) {
              $addInlineSettingsScript = false;
              $inlineSettings->addAdminScript($this->plugin['setting'] . '_' . $asset);
            }
          } else if (self::$func['endsWith']($asset, 'admin.css')) {
            wp_enqueue_style($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array(), $this->plugin['version']);

            if ($addInlineSettingsStyle) {
              $addInlineSettingsStyle = $this->plugin['setting'] . '_' . $asset;
            }
          }
        }
        if ($addInlineSettingsStyle && $addInlineSettingsStyle !== true) {
          $inlineSettings->addAdminStyle($addInlineSettingsStyle);
        }
      }
    }

    private function pluginAssetPath($path) {
      if (substr($path, 0, 1) !== '/') {
        $path = '/' . $path;
      }
      if ($this->useJSDelivr) {
        return $this->jsdelivrURL . '@' . $this->plugin['version'] . '/wp-plugin/wp-plugin/trunk/src/assets' . preg_replace('/(?:\.min|)\.(js|css)$/', '.min.$1', $path);
      }
      return plugins_url('/src/assets' . $path, __FILE__);
    }
  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X();
  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X->register();

  register_activation_hook(__FILE__, array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'activate'));
  register_deactivation_hook(__FILE__, array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'deactivate'));

  add_action('init', array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'start'));
}
