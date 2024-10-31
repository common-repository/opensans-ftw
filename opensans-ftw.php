<?php

/**
 * OpenSans FTW
 *
 * Plugin Name: OpenSans FTW
 * Plugin URI: https://georgejipa.com/
 * Description: Just a simple plugin that brings back the old Open Sans font to WordPress 4.6+ dashboard.
 * Version: 1.0.1
 * Author: GeorgeJipa
 * Author URI: https://georgejipa.com/
 * Text Domain: opensans-ftw
 * Domain Path: /languages
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
// Exit if accessed directly
if (!defined('ABSPATH'))
	exit;

class OpenSans_FTW {

	protected static $instance = null;
	public $plugin_slug = 'opensans-ftw';
	public $action_name;

	public static function get_instance() {
		if (null == self::$instance) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Initialize
	 * 
	 * @return
	 */
	public function __construct() {
		// set localisation
		$this->load_plugin_textdomain();
		
		// check wordpress version
		$wp_version = get_bloginfo('version');
		
		if (version_compare($wp_version, '4.6', '<')) {
			add_action('admin_notices', array($this, 'add_notice'));
			return;
		}

		// set vars
		$this->action_name = $this->get_action_name();

		// hooks
		add_action($this->action_name, array($this, 'add_assets'), 999);
	}

	/**
	 * Load localisation files
	 *
	 * Locales found in:
	 *      - WP_LANG_DIR/opensans-ftw/opensans-ftw-LOCALE.mo
	 *      - WP_CONTENT_DIR/plugins/opensans-ftw-LOCALE.mo
	 */
	public function load_plugin_textdomain() {
		$locale = apply_filters('plugin_locale', get_locale(), $this->plugin_slug);

		load_textdomain($this->plugin_slug, WP_LANG_DIR . '/' . $this->plugin_slug . '/' . $this->plugin_slug . '-' . $locale . '.mo');
		load_plugin_textdomain($this->plugin_slug, false, plugin_basename(dirname(__FILE__)) . '/languages');
	}

	/**
	 * Get action_name based on page
	 * 
	 * @global string $pagenow
	 * @return string
	 */
	public function get_action_name() {
		global $pagenow;

		if (is_admin()) {
			$action_name = 'admin_enqueue_scripts';
		} elseif ($pagenow == 'wp-login.php') {
			$action_name = 'login_enqueue_scripts';
		} else {
			$action_name = 'wp_enqueue_scripts';
		}

		return $action_name;
	}

	/**
	 * Add notice
	 */
	public function add_notice() {
		echo '<div id="message" class="error"><p>' . esc_html__('OpenSans FTW requires WordPress 4.6 or higher.', $this->plugin_slug) . '</p></div>';
	}

	/**
	 * Add assets to backend (admin) or frontend
	 * 
	 * @return
	 */
	public function add_assets() {
		// we don't need to add the assets when the adminbar is disabled (on dashboard is always enabled, 
		// but on frontend can be disabled using `show_admin_bar` hook... so we need to check)
		// ! exception will be wp-login.php page !
		if (!is_admin_bar_showing() && $this->action_name != 'login_enqueue_scripts') {
			return;
		}

		// enqueue font
		$handle = 'open-sans';

		if (wp_style_is($handle, 'registered')) {
			wp_enqueue_style($handle);
		} else {
			wp_enqueue_style($handle, '//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,300,400,600&subset=latin,latin-ext');
		}

		// add custom css based on page
		switch ($this->action_name) {
			case 'wp_enqueue_scripts':
				$custom_css = '#wpadminbar *:not([class="ab-icon"]) { font-family: "Open Sans", sans-serif !important; }';
				break;
			case 'login_enqueue_scripts':
				$custom_css = 'body { font-family: "Open Sans", sans-serif !important; }';
				break;
			default:
				$custom_css = 'body, #wpadminbar *:not([class="ab-icon"]), .wp-core-ui, .media-frame input, .media-frame textarea, .media-frame select { font-family: "Open Sans", sans-serif !important; }';
				break;
		}

		wp_add_inline_style($handle, $custom_css);
	}

}

add_action('plugins_loaded', array('OpenSans_FTW', 'get_instance'));