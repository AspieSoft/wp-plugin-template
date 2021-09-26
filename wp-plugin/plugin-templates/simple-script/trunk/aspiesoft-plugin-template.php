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

    private $useJSDelivr = false;

    function __construct() {
      $this->pluginName = plugin_basename(__FILE__);
    }

    public function register() {
      // ensure get_plugin_data function is loaded on frontend
      if (!function_exists('get_plugin_data')) {
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
      }

      // grab plugin data to use dynamic to the plugin
      $pluginData = get_plugin_data(__FILE__);
      $this->plugin = array(
        'setting' => str_replace('-', '', ucwords(sanitize_text_field($pluginData['TextDomain']), '-')),
        'version' => sanitize_text_field($pluginData['Version']),
        'author' => sanitize_text_field($pluginData['AuthorName']),
      );

      // grab common functions if another aspiesoft plugin is already loaded
      if (class_exists('X_AUTHOR_NAME_X_Functions_v2')) {
        global $X_AUTHOR_NAME_VAR_X_Functions_v2;
        $func = $X_AUTHOR_NAME_VAR_X_Functions_v2::init($this->plugin);

        // init options
        $options = $func['options']();

        // check if admin chosen to use jsdelivr as a global option from another plugin
        $jsdelivrOption = $options['get']('jsdelivr', 'local', 'string', null, true);
        if ($jsdelivrOption === 'jsdelivr') {
          $this->useJSDelivr = true;
        } else {
          $this->useJSDelivr = false;
        }
      } else {
        // check if admin chosen to use jsdelivr as a global option from another plugin
        $this->options_get($this->plugin, 'jsdelivr', 'local');
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
        // admin enqueue scripts
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue'));
      }
    }


    public function activate() {
      //flush_rewrite_rules();
    }

    public function deactivate() {
      //flush_rewrite_rules();
    }


    function enqueue() {
      // dynamically enqueue all js and css assets from assets
      $assetsDir = plugin_dir_path(__FILE__) . 'assets/';
      if (file_exists($assetsDir) && is_dir($assetsDir)) {
        // enqueue assets list
        $assets = scandir($assetsDir);
        foreach ($assets as $asset) {
          if ($this->endsWith($asset, '.js') && !$this->endsWith($asset, 'admin.js')) {
            wp_enqueue_script($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array('jquery'), $this->plugin['version'], true);
          } else if ($this->endsWith($asset, '.css') && !$this->endsWith($asset, 'admin.css')) {
            wp_enqueue_style($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array(), $this->plugin['version']);
          }
        }
      }
    }

    function admin_enqueue() {
      // dynamically enqueue all js and css assets from assets
      $assetsDir = plugin_dir_path(__FILE__) . 'assets/';
      if (file_exists($assetsDir) && is_dir($assetsDir)) {
        // enqueue assets list
        $assets = scandir($assetsDir);
        foreach ($assets as $asset) {
          if ($this->endsWith($asset, 'admin.js')) {
            wp_enqueue_script($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array('jquery'), $this->plugin['version'], true);
          } else if ($this->endsWith($asset, 'admin.css')) {
            wp_enqueue_style($this->plugin['setting'] . '_' . $asset, $this->pluginAssetPath($asset), array(), $this->plugin['version']);
          }
        }
      }
    }

    function pluginAssetPath($path) {
      if (substr($path, 0, 1) !== '/') {
        $path = '/' . $path;
      }
      if ($this->useJSDelivr) {
        return $this->jsdelivrURL . '@' . $this->plugin['version'] . '/wp-plugin/trunk/src/assets' . preg_replace('/(?:\.min|)\.(js|css)$/', '.min.$1', $path);
      }
      return plugins_url('/src/assets' . $path, __FILE__);
    }


    // functions
    private function options_get($plugin, $name, $default = null) {
      $name = sanitize_file_name(sanitize_text_field($plugin['author'] . '_' . $name));

      $option = null;
      if (is_multisite()) {
        $option = sanitize_text_field(get_option($name));
        if (!isset($option) || !$option || $option === null || $option === '') {
          $option = sanitize_text_field(get_site_option($name));
        }
      } else {
        $option = sanitize_text_field(get_option($name));
      }

      if (!isset($option) || !$option || $option === null || $option === '') {
        return $default;
      }

      return $option;
    }

    private function endsWith($haystack, $needle) {
      return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
  }

  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X = new X_AUTHOR_NAME_X_X_PLUGIN_NAME_X();
  $X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X->register();

  register_activation_hook(__FILE__, array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'activate'));
  register_deactivation_hook(__FILE__, array($X_AUTHOR_NAME_VAR_X_X_PLUGIN_NAME_X, 'deactivate'));
}
