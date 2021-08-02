# AspieSoft Plugin Template

I reccomend creating a fork of this project, and globally replacing (AspieSoft, aspieSoft, aspiesoft) with your github name (keep caps consistant).

You should also globally replace (PluginTemplate, Plugin Template, plugin-template) with your plugin name for each project (keep caps consistant).

Your wordpress plugin code lives instde the "wp-plugin/trunk" directory.
The "cdn" directory is in case you wanted to add a CDN that doesn't require wordpress, and does something similar.
The "wp-plugin" directory is what gets uploaded to svn.

Some of the plugin files do not need to be touched.

"index.php" can be ignored. It's just the "silence is golden" comment, and also contains a redirect script to /404 to help hide the plugins existance from hackers.

"readme.txt" is your wordpress readme, and you can change it however you want.
I recommend changing the donation link to your own, unless you want people paying me for your plugin :)

"plugin-name.php" (default: "aspiesoft-plugin-template.php") if the main file that runs, and should be named after the plugin.
You do not need to touch this file, just rename it to the proper plugin name.

"functions.php" contains some basic functions that may be useful.
This file should remain consistant for multiple plugins, and for any changes, you should also globally change the version number at the end for that plugin.
This file gets loaded once, and is shared between all your plugins. This can help with preformance, because only one plugin will load this file if the class isn't already loaded, and every other plugin will use that class thats already been loaded, rather than reloading it's duplicate.

"templates" is just a directory containing "admin.php" which is the settings template.
You do not need to touch this directory. "admin.php" does some complex stuff, and pulls from another php file I will mension later.

"assets" is a directory you do not need to touch.
It contains assets that the plugin template uses.

"src" is the main directory for you to edit stuff.

"src/main.php" is the main plugin file, and runs on any non admin page.
You can do anything here, as long as you keep the class name consistant.

"src/settings.php" is where you modify the plugins admin settings.
This page is used by a more advanced system, that allows your plugin settings to be saved with ajax requests, and automatically adds multi site support.
I recommend keeping the current options as they are, apart from the "altShortcode" example option.
The "jsdeliver" option is read by the plugin templates core system, and is used to automatically pull assets from jsdeliver.net to load them from github if the user chooses to do so for preformance.
The global settings are shared by multiple plugins, and you can add options there as well.

"src/assets" directory is dynamic, and any .js or .css files will automatically be loaded.
These files will also have automatic support for the jsdeliver option users are given.

"src/assets/0settings.php" can be used to load any inline scripts or styles.
This is useful if you need to pass any options to the client side.
You can uncomment the function, and it will automatically be detected.



# Below is a default readme.md you can use for your plugin

# Plugin Template

Plugin Description

## CDN Installation

```html
<script src="https://cdn.jsdelivr.net/gh/AspieSoft/aspiesoft-plugin-template@1.0/cdn/plugin-template.js"></script>
```

---

## Wordpress Installation

1. Upload plugin to the /wp-content/plugins
2. Activate the plugin through the "Plugins" menu in WordPress
3. Enjoy

## Usage

### How to use the wordpress shortcode

```WordPress
[plugin-template
  attr="value example or description"
]
```

---

### How to use this without wordpress

```javascript
<param attr="value example or description"></param>
```

---

### Question 1?

> Answer 1.

---

### Question 2?

> Answer 2.