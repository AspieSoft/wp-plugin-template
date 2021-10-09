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
  This plugin is made from a template by AspieSoft: https://github.com/AspieSoft/wp-plugin-template
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

    private $aspiesoftFunctionsMD5Sum_Functions = '';
    private $aspiesoftFunctionsMD5Sum_AdminTemplate = '';

    public $pluginName;
    public $plugin;

    private $muPluginsDir;
    private $canAddFunctions = true;

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
        'dirPath' => plugin_dir_path(__FILE__),
      );

      if (is_admin()) {
        // add plugin basename to php defined var, for admin template to use get_plugin_data on correct file
        //define('PLUGIN_BASENAME_' . basename(plugin_dir_path(__FILE__)), $this->pluginName);
        define('ASPIESOFT_CURRENT_PLUGIN_FILE', plugin_dir_path(__FILE__) . $this->pluginName);
      }

      $this->muPluginsDir = plugin_dir_path(__FILE__) . '../../mu-plugins/';

      // get common functions.php file
      // multiple plugins can use same file in the future (without functions.php class being loaded twice)
      // version added so updates to functions can still occur without breaking other plugins
      if (!class_exists('X_AUTHOR_NAME_X_Functions_v2_1')) {
        if (file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions/functions/v2_1.php')) {
          require_once($this->muPluginsDir . 'aspiesoft-plugin-functions/functions/v2_1.php');
        } else {
          $this->canAddFunctions = false;
        }
      }

      if ($this->canAddFunctions) {
        global $aspieSoft_Functions_v2_1;
        self::$func = $aspieSoft_Functions_v2_1::init($this->plugin);

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
      }


      if (!is_admin()) {
        // enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue'));
      } else {
        if ($this->canAddFunctions) {
          // add settings page if it exists
          if (file_exists(plugin_dir_path(__FILE__) . 'src/settings.php') && file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions/admin_template/v2_1.php')) {
            // add plugin basename to php defined var, for admin template to use get_plugin_data on correct file
            $GLOBALS['ASPIESOFT_CURRENT_PLUGIN_FILE'] = plugin_dir_path(__FILE__) . $this->pluginName;

            add_action('admin_menu', array($this, 'add_admin_pages'));
            add_filter("plugin_action_links_$this->pluginName", array($this, 'settings_link'));
          }
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
      require_once($this->muPluginsDir . 'aspiesoft-plugin-functions/admin_template/v2_1.php');
    }


    public function activate() {
      $this->installMuPlugin();

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

    public function update() {
      // grab plugin data to use dynamic to the plugin
      $pluginData = get_plugin_data(__FILE__);
      $plugin = array(
        'setting' => str_replace('-', '', ucwords(sanitize_text_field($pluginData['TextDomain']), '-')),
        'version' => sanitize_text_field($pluginData['Version']),
      );

      $name = sanitize_file_name($plugin['setting'] . '_Plugin_Version');
      $ver = null;
      if (is_multisite()) {
        $ver = sanitize_text_field(get_site_option($name));
        update_site_option($name, $plugin['version'], false);
      } else {
        $ver = sanitize_text_field(get_option($name));
        update_option($name, $plugin['version'], false);
      }

      if (isset($ver) && $ver !== null && $ver !== $plugin['version']) {
        $this->installMuPlugin();
      }
    }

    private function enableOptionsAutoload() {
      // ensure register function ran
      if (!$this->plugin || !self::$func || !self::$options) {
        $this->register();
      }
      if (!$this->canAddFunctions) {
        return;
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
      if (!$this->canAddFunctions) {
        return;
      }

      $settingsPage = self::$func['loadPluginFile']('settings.php', true);
      $optionList = $settingsPage->getOptionList();

      $this->toggleOptionsAutoload($optionList, false);
    }

    private function toggleOptionsAutoload($optionList, $autoload) {
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


    private function installMuPlugin() {
      $this->muPluginsDir = plugin_dir_path(__FILE__) . '../../mu-plugins/';

      if (!file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions') || !is_dir($this->muPluginsDir . 'aspiesoft-plugin-functions')) {
        if (file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions')) {
          unlink($this->muPluginsDir . 'aspiesoft-plugin-functions');
        }
        mkdir($this->muPluginsDir . 'aspiesoft-plugin-functions', 0755, true);

        $this->updateMuPlugin('index.php', $this->aspiesoftFunctionsMD5Sum_index);
        $this->updateMuPlugin('index.php', $this->aspiesoftFunctionsMD5Sum_index);
      }

      $this->updateMuPlugin('functions/v2_1.php', $this->aspiesoftFunctionsMD5Sum_Functions);
      $this->updateMuPlugin('admin-template/v2_1.php', $this->aspiesoftFunctionsMD5Sum_AdminTemplate);
    }

    private function updateMuPlugin($file, $sum) {
      if (!file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $file)) {
        $tmpFile = download_url('https://cdn.jsdelivr.net/gh/AspieSoft/wp-plugin-template@2.1/wp-plugin/mu-plugins/aspiesoft-plugin-functions/' . $file, 300);
        $md5check = verify_file_md5($tmpFile, $sum);
        if (!$md5check || is_wp_error($md5check)) {
          unlink($tmpFile);
          return;
        }

        $dir = preg_replace('/\/.*?$/', '', $file);

        if (!file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $dir) || !is_dir($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $dir)) {
          if (file_exists($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $dir)) {
            unlink($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $dir);
          }
          mkdir($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $dir, 0755, true);
        }

        copy($tmpFile, $this->muPluginsDir . 'aspiesoft-plugin-functions/' . $file);
        unlink($tmpFile);
      } else {
        $md5check = verify_file_md5($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $file, $sum);
        if (!$md5check || is_wp_error($md5check)) {
          unlink($this->muPluginsDir . 'aspiesoft-plugin-functions/' . $file);
          $this->updateMuPlugin($file, $sum);
          return;
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

  add_action('upgrader_process_complete', array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'update'));

  add_action('init', array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'start'));
}
