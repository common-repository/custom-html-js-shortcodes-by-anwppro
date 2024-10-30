<?php
/**
 * AnWP Custom HTML and JS Shortcodes :: Main Class
 *
 * @since   0.1.0
 * @package AnWP_Custom_HTML_JS
 */


/**
 * Autoloads files with classes when needed.
 *
 * @since  0.1.0
 * @param  string $class_name Name of the class being requested.
 */
function anwp_custom_html_js_autoload_classes( $class_name ) {

	// If our class doesn't have our prefix, don't load it.
	if ( 0 !== strpos( $class_name, 'AnWPCS_' ) ) {
		return;
	}

	// Set up our filename.
	$filename = strtolower( str_replace( '_', '-', substr( $class_name, strlen( 'AnWPCS_' ) ) ) );

	// Include our file.
	AnWP_Custom_HTML_JS::include_file( 'includes/class-anwpcs-' . $filename );
}
spl_autoload_register( 'anwp_custom_html_js_autoload_classes' );

/**
 * Main initiation class.
 *
 * @since  0.1.0
 */
final class AnWP_Custom_HTML_JS {

	/**
	 * Current version.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	const VERSION = '0.2.1';

	/**
	 * URL of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $path = '';

	/**
	 * Plugin basename.
	 *
	 * @var    string
	 * @since  0.1.0
	 */
	protected $basename = '';

	/**
	 * Detailed activation error messages.
	 *
	 * @var    array
	 * @since  0.1.0
	 */
	protected $activation_errors = [];

	/**
	 * Singleton instance of plugin.
	 *
	 * @var    AnWP_Custom_HTML_JS
	 * @since  0.1.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of AnWPCS_Code
	 *
	 * @since 0.1.0
	 * @var AnWPCS_Code
	 */
	protected $code;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since   0.1.0
	 * @return  AnWP_Custom_HTML_JS A single instance of this class.
	 */
	public static function get_instance() {

		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin.
	 *
	 * @since  0.1.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  0.1.0
	 */
	public function plugin_classes() {

		// CPT
		$this->code = new AnWPCS_Code( $this );

	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters.
	 * Priority needs to be
	 * < 10 for CPT_Core,
	 * < 5 for Taxonomy_Core,
	 * and 0 for Widgets because widgets_init runs at init priority 1.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		add_action( 'init', [ $this, 'init' ], 0 );

		add_action( 'init', [ $this, 'shortcodes_init' ] );

		/**
		 * Enqueue admin scripts
		 *
		 * @since 0.1.0
		 */
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
	}

	/**
	 * Activate the plugin.
	 *
	 * @since  0.1.0
	 */
	public function activate() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 * Uninstall routines should be in uninstall.php.
	 *
	 * @since  0.1.0
	 */
	public function deactivate() {
		// Add deactivation cleanup functionality here.
	}

	/**
	 * Init hooks
	 *
	 * @since  0.1.0
	 */
	public function init() {

		// Bail early if requirements aren't met.
		if ( ! $this->check_requirements() ) {
			return;
		}

		// Load translated strings for plugin.
		load_plugin_textdomain( 'anwp-custom-html-js', false, dirname( $this->basename ) . '/languages/' );

		// Initialize plugin classes.
		$this->plugin_classes();
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean True if requirements met, false if not.
	 */
	public function check_requirements() {

		// Bail early if plugin meets requirements.
		if ( $this->meets_requirements() ) {
			return true;
		}

		// Add a dashboard notice.
		add_action( 'all_admin_notices', [ $this, 'requirements_not_met_notice' ] );

		// Deactivate our plugin.
		add_action( 'admin_init', [ $this, 'deactivate_me' ] );

		// Didn't meet the requirements.
		return false;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  0.1.0
	 */
	public function deactivate_me() {

		// We do a check for deactivate_plugins before calling it, to protect
		// any developers from accidentally calling it too early and breaking things.
		if ( function_exists( 'deactivate_plugins' ) ) {
			deactivate_plugins( $this->basename );
		}
	}

	/**
	 * Check that all plugin requirements are met.
	 *
	 * @since  0.1.0
	 *
	 * @return boolean True if requirements are met.
	 */
	public function meets_requirements() {

		// Do checks for required classes / functions or similar.
		// Add detailed messages to $this->activation_errors array.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met.
	 *
	 * @since  0.1.0
	 */
	public function requirements_not_met_notice() {

		// Compile default message.
		/* translators: %s: link to plugins page. */
		$default_message = sprintf( __( 'Custom HTML and JS Shortcodes is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'anwp-custom-html-js' ), admin_url( 'plugins.php' ) );

		// Default details to null.
		$details = null;

		// Add details if any exist.
		if ( $this->activation_errors && is_array( $this->activation_errors ) ) {
			$details = '<small>' . implode( '</small><br /><small>', $this->activation_errors ) . '</small>';
		}

		// Output errors.
		?>
		<div id="message" class="error">
			<p><?php echo wp_kses_post( $default_message ); ?></p>
			<?php echo wp_kses_post( $details ); ?>
		</div>
		<?php
	}

	/**
	 * Initialize shortcodes
	 *
	 * @since 0.1.0
	 */
	public function shortcodes_init() {
		add_shortcode( 'anwpcode', [ $this, 'shortcode_render' ] );
	}

	/**
	 * Rendering shortcode
	 *
	 * @param $atts
	 *
	 * @since 0.1.0
	 * @return string
	 */
	public function shortcode_render( $atts ) {

		if ( empty( $atts['id'] ) && empty( $atts['title'] ) ) {
			return '';
		}

		$post = (object) [];

		// Try to get Code by id
		if ( ! empty( $atts['id'] ) && (int) $atts['id'] ) {
			$post_obj = get_post( (int) $atts['id'] );

			if ( ! empty( $post_obj->ID ) && 'publish' === $post_obj->post_status && 'anwp_code' === $post_obj->post_type ) {
				$post = $post_obj;
			}
		}

		// Try to get Code by title
		if ( ! empty( $atts['title'] ) && empty( $post->ID ) ) {
			$post_obj = get_page_by_title( sanitize_text_field( $atts['title'] ), OBJECT, 'anwp_code' );

			if ( ! empty( $post_obj->ID ) && 'publish' === $post_obj->post_status && 'anwp_code' === $post_obj->post_type ) {
				$post = $post_obj;
			}
		}

		// Check post is set
		if ( empty( $post->ID ) || 'anwp_code' !== $post->post_type ) {
			return '';
		}

		$code_content = $post->post_content;

		// Apply shortcodes
		$code_content = do_shortcode( $code_content );

		return $this->special_replace( $code_content );
	}

	/**
	 * Replace special variables in shortcode content.
	 *
	 * @param $content
	 * @return mixed
	 */
	private function special_replace( $content ) {
		$content = str_replace( '$$site_url$$', esc_url( get_site_url() ), $content );
		$content = str_replace( '$$nonce$$', wp_nonce_field( 'anwp_code', 'anwp_code', true, false ), $content );

		return $content;
	}

	/**
	 * Load admin scripts and styles
	 *
	 * @since 0.1.0
	 */
	public function admin_enqueue_scripts() {

		$current_screen = get_current_screen();

		$plugin_pages = [ 'anwp_code' ];

		// Load Common files
		if ( in_array( $current_screen->id, $plugin_pages, true ) ) {

			wp_enqueue_style( 'anwpcs_styles', plugins_url( '/admin/css/styles.css', __FILE__ ), [], self::VERSION );

			// Enqueue code editor and settings for manipulating HTML.
			$settings = wp_enqueue_code_editor( array( 'type' => 'text/html' ) );

			if ( false !== $settings ) {
				wp_add_inline_script(
					'code-editor',
					sprintf(
						'jQuery( function() { wp.codeEditor.initialize( "anwpcs-editor", %s ); } );',
						wp_json_encode( $settings )
					)
				);
			}
		}
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $field Field to get.
	 * @throws Exception     Throws an exception if the field is invalid.
	 * @return mixed         Value of the field.
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'code':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $filename Name of the file to be included.
	 * @return boolean          Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once $file;
		}
		return false;
	}

	/**
	 * This plugin's directory.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path.
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url.
	 *
	 * @since  0.1.0
	 *
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path.
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}
