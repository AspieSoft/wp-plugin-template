<?php

if (!defined('ABSPATH')) {
  http_response_code(404);
  die('404 Not Found');
}

if (!class_exists('AspieSoft_Functions_v2_0')) {

  class AspieSoft_Functions_v2_0 {

    public static function init($plugin) {
      $functions = array(
        'version' => 2.0,

        'options' => function () use ($plugin) {
          return self::options($plugin);
        },
        'inputList' => function ($optionList) use ($plugin) {
          return self::inputList($plugin, $optionList);
        },

        'startsWith' => function ($haystack, $needle) {
          return self::startsWith($haystack, $needle);
        },
        'endsWith' => function ($haystack, $needle) {
          return self::endsWith($haystack, $needle);
        },
        'encodeArrayOrStringForClient' => function ($value) {
          return self::encodeArrayOrStringForClient($value);
        },
        'cleanShortcodeAtts' => function ($attr) {
          return self::cleanShortcodeAtts($attr);
        },
        'getValue' => function ($attr) {
          return self::getValue($attr);
        },
        'cloneArray' => function ($arr) {
          return self::cloneArray($arr);
        },

        'loadPluginFile' => function ($name, $init = false) use ($plugin) {
          return self::loadPluginFile($plugin, $name, $init);
        },
        'loadJsonFile' => function ($name, $type = 'json') use ($plugin) {
          return self::loadJsonFile($plugin, $name, $type);
        }
      );

      return $functions;
    }


    public static function startsWith($haystack, $needle) {
      return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    public static function endsWith($haystack, $needle) {
      return substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }


    public static function encodeArrayOrStringForClient($value) {
      if (is_array($value)) {
        $list = array();
        foreach ($value as $key => $val) {
          $list[esc_html(sanitize_key($key))] = esc_html(sanitize_text_field($val));
        }
        $value = wp_json_encode($list);
      } else {
        $value = esc_html(sanitize_text_field($value));
      }
      return $value;
    }

    public static function cleanShortcodeAtts($attr) {
      foreach ($attr as $k => $v) {
        $vType = gettype($v);
        if ($vType === 'string') {
          $attr[sanitize_key($k)] = esc_html(sanitize_text_field($v));
        } else if ($vType === 'boolean') {
          $attr[sanitize_key($k)] = !!$v;
        } else if ($vType === 'integer') {
          $attr[sanitize_key($k)] = intval($v);
        } else if ($vType === 'double') {
          $attr[sanitize_key($k)] = floatval($v);
        } else {
          $attr[sanitize_key($k)] = null;
        }
      }
      return $attr;
    }

    public static function getValue($attr) {
      if (!is_array($attr)) {
        return $attr;
      }
      foreach ($attr as $v) {
        if ($v !== null) {
          return $v;
        }
      }
      return null;
    }

    public static function cloneArray($arr) {
      $newArr = array();
      foreach ($arr as $key => $val) {
        if (is_array($val)) {
          $val = self::cloneArray($val);
        }
        $newArr[$key] = $val;
      }
      return $newArr;
    }


    public static function loadPluginFile($plugin, $name, $init = false) {
      $plugin = self::cloneArray($plugin);
      $plugin['func'] = self::init($plugin);
      $plugin['options'] = $plugin['func']['options']();

      $path = $plugin['dirPath'] . 'src/' . $name;
      if (!self::endsWith($path, '.php')) {
        $path .= '.php';
      }
      if (file_exists($path)) {
        $name = str_replace('-', '', ucwords(preg_replace('/\.php$/', '', $name), '-'));
        require_once($path);
        $pName = str_replace('-', '_', sanitize_html_class($plugin['pluginName']));
        if (class_exists($plugin['author'] . '_' . $pName . '_' . $name)) {
          $pClass = ${$plugin['authorVar'] . '_' . $pName . '_' . $name};
          if ($init) {
            if (is_callable(array($pClass, 'init'))) {
              $pClass->init($plugin);
            }
            if (is_callable(array($pClass, 'start'))) {
              $pClass->start();
            }
          }
          return $pClass;
        }
      }
      return null;
    }

    public static function loadJsonFile($plugin, $name, $type = 'json') {
      $path = $plugin['dirPath'] . 'src/' . $name;
      if (file_exists($path)) {
        $file = file_get_contents($path);
        if (!$file) {
          return null;
        }
        if ($type === 'json') {
          try {
            return json_decode($file);
          } catch (Exception $e) {
          }
          return null;
        } else if ($type === 'yaml' || $type === 'yml') {
          try {
            return self::parse_yaml($file);
          } catch (Exception $e) {
          }
          return null;
        } else {
          return $file;
        }
      }
      return null;
    }

    private static function parse_yaml($str) {
      //todo: parse yaml
      return $str;
    }


    // options v2
    public static function options($plugin) {
      $functions = array(
        'get' => function ($name, $default = null, $type = 'string', $network = null, $global = null) use ($plugin) {
          return self::options_get($plugin, $name, $default, $type, $network, $global);
        },
        'set' => function ($name, $value, $network = false, $global = false, $autoload = true) use ($plugin) {
          return self::options_set($plugin, $name, $value, $network, $global, $autoload);
        },
        'del' => function ($name, $network = false, $global = false) use ($plugin) {
          return self::options_del($plugin, $name, $network, $global);
        }
      );

      return $functions;
    }

    private static function options_get($plugin, $name, $default = null, $type = 'string', $network = null, $global = null) {
      $origName = '';
      if ($global === true) {
        $name = sanitize_file_name($plugin['author'] . '_' . $name);
      } else if ($global === false) {
        $name = sanitize_file_name($plugin['setting'] . '_' . $name);
      } else if ($global === null) {
        $origName = $name;
        $name = sanitize_file_name($plugin['setting'] . '_' . $name);
      }

      $option = null;
      if (is_multisite() && $network === true) {
        $option = sanitize_text_field(get_site_option($name));
      } else if ($network === false) {
        $option = sanitize_text_field(get_option($name));
      } else if (is_multisite()) {
        $option = sanitize_text_field(get_option($name));
        if (!isset($option) || !$option || $option === null || $option === '') {
          $option = sanitize_text_field(get_site_option($name));
        }
      } else {
        $option = sanitize_text_field(get_option($name));
      }

      if ($type === 'bool' || $type === true || $type === false) {
        if ($option === true || $option === 1 || $option === 'true' || $option === 'TRUE' || $option === 'True' || $option === 'Selected' || $option === 'selected') {
          return true;
        } else if ($option === false || $option === 0 || $option === 'false' || $option === 'FALSE' || $option === 'False' || $option === 'Unselected' || $option === 'unselected') {
          return false;
        } else {
          return $default;
        }
      } else if ($type === 'list') {
        try {
          return json_decode($option);
        } catch (Exception $e) {
        }

        if (is_array($default)) {
          return $default;
        }

        try {
          return json_decode($default);
        } catch (Exception $e) {
        }

        return null;
      }

      if (!isset($option) || !$option || $option === null || $option === '') {
        if ($global === null) {
          return self::options_get($plugin, $origName, $default, $type, $network, true);
        }
        if ($type === 'number') {
          return floatval($default);
        }
        return $default;
      }

      if ($type === 'number') {
        return floatval($option);
      }

      return $option;
    }

    private static function options_set($plugin, $name, $value, $network = false, $global = false, $autoload = true) {
      if ($global === true) {
        $name = sanitize_file_name($plugin['author'] . '_' . $name);
      } else if ($global === false || $global === null) {
        $name = sanitize_file_name($plugin['setting'] . '_' . $name);
      }

      if (is_multisite() && $network) {
        update_site_option($name, sanitize_text_field($value), $autoload);
      } else {
        update_option($name, sanitize_text_field($value), $autoload);
      }
    }

    private static function options_del($plugin, $name, $network = false, $global = false) {
      if ($global === true) {
        $name = sanitize_file_name($plugin['author'] . '_' . $name);
      } else if ($global === false || $global === null) {
        $name = sanitize_file_name($plugin['setting'] . '_' . $name);
      }

      if (is_multisite() && $network) {
        delete_site_option($name);
      } else {
        delete_option($name);
      }
    }


    // settings inputs
    public static function inputList($plugin, $optionList) {
      $functions = array(
        'tab' => function ($name, $label, $heading, $priority, $global = false) use ($plugin, $optionList) {
          return self::inputList_addTab($plugin, $optionList, $name, $label, $heading, $priority, $global);
        }
      );

      return $functions;
    }

    private static function inputList_addTab($plugin, $optionList, $name, $label, $heading, $priority, $global = false) {
      $name = sanitize_text_field($name);

      $optionList[0]->{$optionList[1]}[$name] = array(
        'type' => 'tab',
        'label' => esc_html__(sanitize_text_field($label)),
        'priority' => intval($priority),
        'heading' => esc_html__(sanitize_text_field($heading)),
        'options' => array(),
        'global' => $global
      );

      return self::inputList_addInput($plugin, $optionList, $name);
    }

    private static function inputList_addInput($plugin, $optionList, $tabName) {
      $functions = array(
        'text' => function ($name, $label, $default, $layout = null, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Text($plugin, $optionList, $tabName, $name, $label, $default, $layout, $css, $autoload);
        },
        'number' => function ($name, $label, $default, $layout = null, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Number($plugin, $optionList, $tabName, $name, $label, $default, $layout, $css, $autoload);
        },
        'list' => function ($name, $label, $default, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_List($plugin, $optionList, $tabName, $name, $label, $default, $css, $autoload);
        },
        'textarea' => function ($name, $label, $default, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Textarea($plugin, $optionList, $tabName, $name, $label, $default, $css, $autoload);
        },
        'check' => function ($name, $label, $default, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Check($plugin, $optionList, $tabName, $name, $label, $default, $css, $autoload);
        },
        'select' => function ($name, $label, $default, $options, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Select($plugin, $optionList, $tabName, $name, $label, $default, $options, $css, $autoload);
        },
        'radio' => function ($name, $label, $default, $options, $css = null, $autoload = true) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_Radio($plugin, $optionList, $tabName, $name, $label, $default, $options, $css, $autoload);
        },

        'header' => function ($text, $size = 1, $css = null) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_header($plugin, $optionList, $tabName, $text, $size, $css);
        },

        'break' => function ($size = 1, $css = null) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_break($plugin, $optionList, $tabName, $size, $css);
        },
        'line' => function ($size = 1, $css = null) use ($plugin, $optionList, $tabName) {
          return self::inputList_addInput_line($plugin, $optionList, $tabName, $size, $css);
        },
      );

      return $functions;
    }

    // text input
    private static function inputList_addInput_Text($plugin, $optionList, $tabName, $name, $label, $default, $layout = null, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'text',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'layout' => $layout,
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // number input
    private static function inputList_addInput_Number($plugin, $optionList, $tabName, $name, $label, $default, $layout = null, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'number',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'layout' => $layout,
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // textarea (list)
    private static function inputList_addInput_List($plugin, $optionList, $tabName, $name, $label, $default, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      if (is_array($default)) {
        $default = wp_json_encode(self::cleanShortcodeAtts($default));
      } else {
        $default = sanitize_text_field($default);
      }

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'list',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => $default,
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // textarea
    private static function inputList_addInput_Textarea($plugin, $optionList, $tabName, $name, $label, $default, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'textarea',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // checkbox
    private static function inputList_addInput_Check($plugin, $optionList, $tabName, $name, $label, $default, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'check',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // select
    private static function inputList_addInput_Select($plugin, $optionList, $tabName, $name, $label, $default, $options, $css = null, $autoload = true) {
      $name = sanitize_text_field($name);

      if (!is_array($options)) {
        $options = array();
      }
      $options = self::cleanShortcodeAtts($options);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'select',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'options' => $options,
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }

    // radio
    private static function inputList_addInput_Radio($plugin, $optionList, $tabName, $name, $label, $default, $options, $css = null, $autoload = true) {
      if (!is_array($options)) {
        $options = array();
      }
      $name = sanitize_text_field($name);

      if (!is_array($options)) {
        $options = array();
      }
      $options = self::cleanShortcodeAtts($options);

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$name] = array(
        'type' => 'radio',
        'label' => esc_html__(sanitize_text_field($label)),
        'default' => sanitize_text_field($default),
        'options' => $options,
        'css' => wp_json_encode($css),
        'global' => $optionList[0]->{$optionList[1]}[$tabName]['global'],
        'autoload' => $autoload
      );
    }


    // h1 - h6
    private static function inputList_addInput_header($plugin, $optionList, $tabName, $text, $size = 1, $css = null) {
      // gen random key
      $randName = 'HEADER_' . wp_generate_password(16);
      // try 2 more times
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'HEADER_' . wp_generate_password(16);
      }
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'HEADER_' . wp_generate_password(16);
      }
      // fallback
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'HEADER_' . count($optionList[0]->{$optionList[1]}[$tabName]['options']) . '_' . wp_generate_password(16);
      }

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] = array(
        'type' => 'header',
        'text' => esc_html__(sanitize_text_field($text)),
        'size' => intval($size),
        'css' => wp_json_encode($css)
      );
    }


    // br
    private static function inputList_addInput_break($plugin, $optionList, $tabName, $size = 1, $css = null) {
      // gen random key
      $randName = 'BREAK_' . wp_generate_password(16);
      // try 2 more times
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'BREAK_' . wp_generate_password(16);
      }
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'BREAK_' . wp_generate_password(16);
      }
      // fallback
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'BREAK_' . count($optionList[0]->{$optionList[1]}[$tabName]['options']) . '_' . wp_generate_password(16);
      }

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] = array(
        'type' => 'break',
        'size' => intval($size),
        'css' => wp_json_encode($css)
      );
    }

    // hr
    private static function inputList_addInput_line($plugin, $optionList, $tabName, $size = 1, $css = null) {
      // gen random key
      $randName = 'LINE_' . wp_generate_password(16);
      // try 2 more times
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'LINE_' . wp_generate_password(16);
      }
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'LINE_' . wp_generate_password(16);
      }
      // fallback
      if ($optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] !== null) {
        $randName = 'LINE_' . count($optionList[0]->{$optionList[1]}[$tabName]['options']) . '_' . wp_generate_password(16);
      }

      $optionList[0]->{$optionList[1]}[$tabName]['options'][$randName] = array(
        'type' => 'line',
        'size' => intval($size),
        'css' => wp_json_encode($css)
      );
    }
  }

  global $aspieSoft_Functions_v2_0;
  $aspieSoft_Functions_v2_0 = new AspieSoft_Functions_v2_0();
}
