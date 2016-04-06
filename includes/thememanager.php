<?php

if (!defined('ABSPATH')) exit;

class ThemeManager {
	const AF_THEMEPATH = 'themes-asgarosforum';
	const AF_SKINPATH = 'skin';
	const AF_THEMES = 'themes';
	const AF_DEFAULT_THEME = 'default';

	protected static $instance = null;	// ThemeManager instance
	private static $themes_root;		// Path to themes directory
	private static $plugin_root;		// Path to plugin directory
	private static $themes = array();	// Array of available themes
	private static $current_theme;		// The current theme

	// ThemeManager instance creator
	public static function instance($plugin_root) {
		if (static::$instance === null) {
			static::$instance = new static($plugin_root);
		} else {
			return static::$instance;
		}
	}

	// ThemeManager constructor
	private function __construct($plugin_root) {
		global $asgarosforum;
		self::$themes_root = trailingslashit(WP_CONTENT_DIR.'/'.self::AF_THEMEPATH);
		self::$plugin_root = trailingslashit($plugin_root);
		static::find_themes();

		if (!empty(self::$themes[$asgarosforum->options['theme']])) {
			self::$current_theme = $asgarosforum->options['theme'];
		} else {
			// If the selected theme is not there, use the default.
			self::$current_theme = self::AF_DEFAULT_THEME;
		}
	}

	// Find what themes we have available
	private static function find_themes() {
		// Always ensure the default theme is available.
		self::$themes[ self::AF_DEFAULT_THEME ] = array(
			'name' => 'Default Asgaros theme',
			'path' => self::$plugin_root . '/' . self::AF_SKINPATH,
			'url'  => '',
		);

		// Check the themes directory for more themes.
		foreach ( glob( self::$themes_root . '/*' ) as $themepath ) {
			// Check that only directories with style.css files are considered.
			if ( is_dir( $themepath ) && is_file( $themepath . '/style.css' ) ) {
				$trimmed = preg_filter( '/^.*\//', '', $themepath, 1 );
				self::$themes[ $trimmed ] = array(
					'name' => $trimmed,
					'path' => $themepath,
					'url' => self::AF_THEMEPATH . '/' . $trimmed,
				);
			}
		}
	}

	/**
	 * Get the themes discovered
	 *
	 * @return array
	 */
	public static function get_themes() {
		return self::$themes;
	}

	/**
	 * Get the currently selected theme
	 *
	 * @return string
	 */
	public static function get_current_theme() {
		return self::$current_theme;
	}

	/**
	 * Set the current theme
	 *
	 * @param string $theme Name of theme selected.
	 */
	public static function set_current_theme( $theme ) {
		if ( empty( self::$themes[ $theme ] ) ) {
			self::$current_theme = 'default';
		}

		self::$current_theme = $theme;
	}

	/**
	 * Returns the URL to the path of the selected theme
	 *
	 * @return string
	 */
	public static function get_current_theme_path() {
		$path = self::$themes[ self::get_current_theme() ]['url'];

		if ( empty( $path ) ) {
			$path = self::$themes[ self::AF_DEFAULT_THEME ]['path'];
			return plugin_dir_url( $path . '/' . self::AF_SKINPATH );
		}

		$url = content_url( $path );
		return $url;
	}

	/**
	 * Check to see if the themes folder exists, if not create it and initiate a rescan
	 */
	public static function install() {
		if ( ! is_dir( self::$themes_root ) ) {
			wp_mkdir_p( self::$themes_root );
			static::copy_example_theme( 'Dark-theme' );
		}
	}

	/**
	 * Check the example exists and doesn't already exist in the themes directory, then copy it there.
	 *
	 * @param string $theme Name of the example theme to copy.
	 */
	private static function copy_example_theme( $theme ) {
		// Check the example theme exists first.
		if ( is_dir( self::$plugin_root . '/' . self::AF_THEMES . '/' . $theme ) ) {
			// Now make sure the destination is available.
			$theme_path = self::$themes_root . $theme;
			if ( ! is_dir( $theme_path ) ) {
				// All clear, copy the example into place.
				wp_mkdir_p( $theme_path );
				foreach ( glob( self::$plugin_root . self::AF_THEMES . '/' . $theme . '/*' ) as $source ) {
					$filename = preg_filter( '/^.*\//', '', $source, 1 );
					copy( $source, $theme_path . '/' . $filename );
				}
			}
		}
	}

	/**
	 * Following a change of themes (install for example) a rescan will be needed
	 */
	public static function rescan_themes() {
		self::$themes = array();
		static::find_themes();
	}
}

?>
