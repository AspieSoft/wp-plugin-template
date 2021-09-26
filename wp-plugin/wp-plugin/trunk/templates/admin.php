<?php

if (!defined('ABSPATH') || !current_user_can('manage_options')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('AspieSoft_Settings')) {

  class AspieSoft_Settings {

    public $plugin;

    private static $func;
    private static $options;

    private $useJSDelivr;

    function start() {
      $optionList = array();

      // get option list from src directory
      $settingsClass = self::$func['loadPluginFile']('settings.php', true);
      if ($settingsClass) {
        $settingsClass->init($this->plugin);
        $optionList = $settingsClass->getOptionList();
      }


      if (isset($_POST['UpdateOptions'])) { // if post request, update options

        // unique identifier to allow multiple sessions
        $computerId = hash('sha256', sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . sanitize_text_field($_SERVER['LOCAL_ADDR']) . sanitize_text_field($_SERVER['LOCAL_PORT']) . sanitize_text_field($_SERVER['REMOTE_ADDR']));

        if (!isset($_POST['AspieSoft_Settings_Token_Key'])) {
          http_response_code(403);
          status_header(403, 'Session Token Invalid or Missing');
          nocache_headers();
          exit('<error>403</error>');
        }

        $tokenKey = sanitize_text_field($_POST['AspieSoft_Settings_Token_Key']);

        // verify session token
        $settingsToken = get_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId);

        // send expired if missing
        if (!isset($settingsToken) || $settingsToken == '' || $settingsToken == null) {
          http_response_code(401);
          status_header(401, 'Session Expired');
          nocache_headers();
          exit('<error>401</error>');
        }

        $sToken = json_decode($settingsToken, true);

        // send expired if expired
        if (!$sToken || !$sToken['expires'] || round(microtime(true) * 1000) > $sToken['expires']) {
          delete_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId);
          http_response_code(401);
          status_header(401, 'Session Expired');
          nocache_headers();
          exit('<error>401</error>');
        }

        // send permission error if token not sent
        if (!isset($_POST['AspieSoft_Settings_Token'])) {
          http_response_code(403);
          status_header(403, 'Session Token Invalid or Missing');
          nocache_headers();
          exit('<error>403</error>');
        }

        // send permission error if token is not valid
        if (sanitize_text_field($_POST['AspieSoft_Settings_Token']) !== $sToken['token']) {
          http_response_code(403);
          status_header(403, 'Session Token Invalid or Missing');
          nocache_headers();
          exit('<error>403</error>');
        }


        // update options
        $updateOptions = sanitize_text_field($_POST['UpdateOptions']);
        if ($updateOptions === 'RemoveSession') { // remove session token
          delete_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId);

          // end request with 204 response
          http_response_code(204);
          exit();
        } else if ($updateOptions === 'local' || ($updateOptions === 'network' && !is_multisite())) { // update site options
          $this->setOptionsList($optionList, false);

          // update session expiration
          $sToken['expires'] = round(microtime(true) * 1000) + 7200 * 1000;
          update_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId, wp_json_encode($sToken), false);

          // end request with 200 response
          http_response_code(200);
          exit();
        } else if ($updateOptions === 'network') { // update network default options (for multisite)
          $this->setOptionsList($optionList, true);

          // update session expiration
          $sToken['expires'] = round(microtime(true) * 1000) + 7200 * 1000;
          update_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId, wp_json_encode($sToken), false);

          // end request with 200 response
          http_response_code(200);
          exit();
        }

        // end request with 404 response (because update request not found)
        http_response_code(404);
        status_header(404, 'Updates Not Found');
        nocache_headers();
        exit('<error>404</error>');
      } else { // load settings form

        $json = wp_json_encode($this->getOptionsList($optionList));

        // generate random session token
        $settingsToken = esc_html(str_replace('"', '`', wp_generate_password(64)));
        $tokenKey = esc_html(str_replace('"', '`', wp_generate_password(16)));

        // unique identifier to allow multiple sessions
        $computerId = hash('sha256', sanitize_text_field($_SERVER['HTTP_USER_AGENT']) . sanitize_text_field($_SERVER['LOCAL_ADDR']) . sanitize_text_field($_SERVER['LOCAL_PORT']) . sanitize_text_field($_SERVER['REMOTE_ADDR']));

        // store session token with expiration ($wp_session was not working)
        update_option('AspieSoft_Settings_Token' . $tokenKey . '_' . $computerId, wp_json_encode(array(
          'token' => $settingsToken,
          'expires' => round(microtime(true) * 1000) + 7200 * 1000, // 2 hours
        )), false);


        $pluginInfoJson = wp_json_encode(array(
          'plugin_name' => esc_html__($this->plugin['name']),
          'is_multisite' => !!is_multisite(),
          'can_manage_network' => !!(current_user_can('manage_network_plugins') || current_user_can('manage_network_options') || current_user_can('manage_network')),
          'settingsToken' => esc_html($settingsToken),
          'settingsTokenKey' => esc_html($tokenKey),
          'author' => esc_html($this->plugin['author']),
          'setting' => esc_html($this->plugin['setting']),
        ));

        add_action('admin_enqueue_settings_scripts', array($this, 'enqueue'));
        do_action('admin_enqueue_settings_scripts', array(
          'json' => $json,
          'pluginInfo' => $pluginInfoJson,
        ));
      }
    }


    private function getOptionsList($optionList) {
      $newList = array();
      foreach ($optionList as $name => $data) {
        if ($data['type'] === 'tab') {
          $data['options'] = $this->getOptionsList($data['options']);
        } else {
          $data['value'] = self::$options['get']($name, null, $data['type'], null, $data['global']);
        }
        $newList[$name] = $data;
      }
      return $newList;
    }


    private function setOptionsList($optionList, $network = false) {
      foreach ($optionList as $name => $data) {
        if ($data['type'] === 'tab') {
          $this->setOptionsList($data['options'], $network);
        } else if (isset($_POST['OPTION_' . $name])) {
          $value = sanitize_text_field($_POST['OPTION_' . $name]);
          if ($value !== '') {
            if ($value === 'del' && !$data['global']) {
              self::$options['del']($name, $network, false);
            } else if ($value === 'gdel' && $data['global']) {
              self::$options['del']($name, $network, true);
            } else if (self::$func['startsWith']($value, 'set:') && !$data['global']) {
              $value = preg_replace('/^set:/', '', $value, 1);
              $autoload = $data['autoload'];
              if ($autoload === null) {
                $autoload = true;
              }
              self::$options['set']($name, $value, $network, false, $autoload);
            } else if (self::$func['startsWith']($value, 'gset:') && $data['global']) {
              $value = preg_replace('/^gset:/', '', $value, 1);
              $autoload = $data['autoload'];
              if ($autoload === null) {
                $autoload = true;
              }
              self::$options['set']($name, $value, $network, true, $autoload);
            }
          }
        }
      }
    }


    function enqueue($opts) {
      $jsonOptions = $opts['json'];
      $jsonInfo = $opts['pluginInfo'];

      $ver = '2.0';

      if($this->useJSDelivr){
        // styles
        wp_enqueue_style('toastr', 'https://cdn.jsdelivr.net/gh/CodeSeven/toastr@2.1.4/build/toastr.min.css', array(), '2.1.4');

        wp_enqueue_style('AspieSoft_Settings_Style', 'https://cdn.jsdelivr.net/gh/AspieSoft/random-number-js@'.$ver.'/wp-plugin/trunk/assets/settings.min.css', array(), $ver);

        // scripts
        wp_enqueue_script('AspieSoft_Settings_AdminPage_Script', 'https://cdn.jsdelivr.net/gh/AspieSoft/random-number-js@'.$ver.'/wp-plugin/trunk/assets/admin-page.min.js', array('jquery'), $ver, true);
        wp_add_inline_script('AspieSoft_Settings_AdminPage_Script', ";var AspieSoftAdminOptionsInfo = $jsonInfo;", 'before');

        wp_enqueue_script('toastr', 'https://cdn.jsdelivr.net/gh/CodeSeven/toastr@2.1.4/build/toastr.min.js', array('jquery'), '2.1.4', false);
        wp_enqueue_script('random-number-js', 'https://cdn.jsdelivr.net/gh/AspieSoft/random-number-js@1.3.2/script.min.js', array('jquery'), '1.3.2', false);

        wp_enqueue_script('AspieSoft_Settings_Script', 'https://cdn.jsdelivr.net/gh/AspieSoft/random-number-js@'.$ver.'/wp-plugin/trunk/assets/settings.min.js', array('jquery'), $ver, true);
        wp_add_inline_script('AspieSoft_Settings_Script', ";var AspieSoftAdminOptionsList = $jsonOptions;", 'before');
      }else{
        // styles
        wp_enqueue_style('toastr', plugins_url('/../assets/toastr/toastr.min.css', __FILE__), array(), '2.1.4');

        wp_enqueue_style('AspieSoft_Settings_Style', plugins_url('/../assets/settings.css', __FILE__), array(), $ver);


        // scripts
        wp_enqueue_script('AspieSoft_Settings_AdminPage_Script', plugins_url('/../assets/admin-page.js', __FILE__), array('jquery'), $ver, true);
        wp_add_inline_script('AspieSoft_Settings_AdminPage_Script', ";var AspieSoftAdminOptionsInfo = $jsonInfo;", 'before');

        wp_enqueue_script('toastr', plugins_url('/../assets/toastr/toastr.min.js', __FILE__), array('jquery'), '2.1.4', false);
        wp_enqueue_script('random-number-js', plugins_url('/../assets/random-number-js/script.min.js', __FILE__), array('jquery'), '1.3.2', false);

        wp_enqueue_script('AspieSoft_Settings_Script', plugins_url('/../assets/settings.js', __FILE__), array('jquery'), $ver, true);
        wp_add_inline_script('AspieSoft_Settings_Script', ";var AspieSoftAdminOptionsList = $jsonOptions;", 'before');
      }
    }

    function init() {
      // get plugin data
      $pluginData = get_plugin_data(WP_PLUGIN_DIR . '/' . sanitize_text_field(constant('PLUGIN_BASENAME_' . basename(plugin_dir_path(dirname(__FILE__, 1))))));
      $this->plugin = array(
        'name' => preg_replace('/\s*\(.*?\)/', '', sanitize_text_field($pluginData['Name'])),
        'setting' => str_replace('-', '', ucwords(sanitize_text_field($pluginData['TextDomain']), '-')),
        'slug' => sanitize_text_field($pluginData['TextDomain']),
        'version' => sanitize_text_field($pluginData['Version']),
        'author' => sanitize_text_field($pluginData['AuthorName']),
        'authorVar' => sanitize_text_field(lcfirst($pluginData['AuthorName'])),
        'pluginName' => str_replace('-', '', ucwords(trim(str_replace(strtolower(sanitize_text_field($pluginData['AuthorName'])), '', strtolower(sanitize_text_field($pluginData['TextDomain']))), '-'), '-')),
      );

      // load common functions
      if (!class_exists('AspieSoft_Functions_v2')) {
        require_once(plugin_dir_path(__FILE__) . 'functions.php');
      }
      global $aspieSoft_Functions_v2;
      self::$func = $aspieSoft_Functions_v2::init($this->plugin);
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
  }

  $aspieSoft_Settings = new AspieSoft_Settings();
  $aspieSoft_Settings->init();
  $aspieSoft_Settings->start();
}
