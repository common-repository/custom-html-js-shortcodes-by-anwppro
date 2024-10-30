<?php
/**
 * AnWP Custom HTML and JS Shortcodes :: Code.
 *
 * @since   0.1.0
 * @package AnWP_Custom_HTML_JS
 */

require_once dirname( __FILE__ ) . '/../vendor/cpt-core/CPT_Core.php';

/**
 * AnWP Custom HTML and JS Shortcodes :: Code post type class.
 *
 * @since 0.1.0
 * @see   https://github.com/WebDevStudios/CPT_Core
 */
class AnWPCS_Code extends CPT_Core {

	/**
	 * Parent plugin class.
	 *
	 * @var AnWP_Custom_HTML_JS
	 * @since  0.1.0
	 */
	protected $plugin = null;

	/**
	 * Constructor.
	 * Register Custom Post Types.
	 *
	 * See documentation in CPT_Core, and in wp-includes/post.php.
	 *
	 * @since  0.1.0
	 * @param  AnWP_Custom_HTML_JS $plugin Main plugin object.
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
		$this->hooks();

		// Register this cpt.
		parent::__construct(
			[ // array with Singular, Plural, and Registered name
				esc_html__( 'Shortcode', 'anwp-custom-html-js' ),
				esc_html__( 'Shortcodes', 'anwp-custom-html-js' ),
				'anwp_code',
			],
			[
				'supports'           => [
					'title',
					'revisions',
				],
				'menu_icon'          => 'dashicons-media-code',
				'public'             => false,
				'publicly_queryable' => false,
				'labels'             => [
					'all_items' => esc_html__( 'All Shortcodes', 'anwp-custom-html-js' ),
					'menu_name' => esc_html_x( 'HTML Shortcodes', 'Admin Menu text', 'anwp-custom-html-js' ),
				],
			]
		);
	}

	/**
	 * Initiate our hooks.
	 *
	 * @since  0.1.0
	 */
	public function hooks() {

		// Init main metabox
		add_action( 'load-post.php', [ $this, 'init_metaboxes' ] );
		add_action( 'load-post-new.php', [ $this, 'init_metaboxes' ] );

		// Init extra metabox
		add_action( 'load-post.php', [ $this, 'init_extra_metabox' ] );

		/**
		 * Save metabox data
		 * @since 0.1.0
		 */
		add_action( 'save_post', [ $this, 'save_metabox' ], 10, 2 );
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_metaboxes() {
		add_action( 'add_meta_boxes', function ( $post_type ) {

			if ( 'anwp_code' === $post_type ) {
				add_meta_box(
					'anwp_code',
					esc_html__( 'Code', 'anwp-custom-html-js' ),
					[ $this, 'render_metabox' ],
					$post_type,
					'normal',
					'high'
				);
			}

		} );
	}

	/**
	 * Meta box initialization.
	 *
	 * @since  0.1.0
	 */
	public function init_extra_metabox() {
		add_action( 'add_meta_boxes', function ( $post_type ) {

			if ( 'anwp_code' === $post_type ) {
				add_meta_box(
					'anwp_code_shortcode',
					esc_html__( 'Shortcode', 'anwp-custom-html-js' ),
					[ $this, 'render_extra_metabox' ],
					$post_type,
					'side'
				);
			}

		} );
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 * @since  0.1.0
	 */
	public function render_metabox( $post ) {

		// Add nonce for security and authentication.
		wp_nonce_field( 'anwp_save_metabox_' . $post->ID, 'anwp_metabox_nonce' );

		// Get data from the database.
		$code_content = $post->post_content;
		?>
		<div class="anwpcs-metabox-wrapper">
			<textarea id="anwpcs-editor" name="_anwp-code-content" cols="30" rows="10"><?php echo esc_textarea( $code_content ); // WPCS: XSS ok. ?></textarea>
		</div>
		<?php
	}

	/**
	 * Render Meta Box content.
	 *
	 * @param WP_Post $post The post object.
	 * @since  0.1.0
	 */
	public function render_extra_metabox( $post ) {

		?>
		<code>[anwpcode id="<?php echo (int) $post->ID; ?>"]</code>
		<?php echo esc_html__( 'or', 'anwp-custom-html-js' ); ?>
		<br>
		<code>[anwpcode title="<?php echo esc_html( get_the_title( $post->ID ) ); ?>"]</code>
		<?php
	}

	/**
	 * Save the meta when the post is saved.
	 *
	 * @param int $post_id The ID of the post being saved.
	 *
	 * @since  0.1.0
	 * @return bool|int
	 */
	public function save_metabox( $post_id ) {

		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['anwp_metabox_nonce'] ) ) {
			return $post_id;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['anwp_metabox_nonce'], 'anwp_save_metabox_' . $post_id ) ) {
			return $post_id;
		}

		// Check post type
		if ( 'anwp_code' !== $_POST['post_type'] ) {
			return $post_id;
		}

		/*
		 * If this is an autosave, our form has not been submitted,
		 * so we don't want to do anything.
		 */
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Check the user's permissions.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// check if there was a multisite switch before
		if ( is_multisite() && ms_is_switched() ) {
			return $post_id;
		}

		/* OK, it's safe for us to save the data now. */

		/** ---------------------------------------
		 * Save Custom Shortcode data
		 *
		 * @since 0.1.0
		 * ---------------------------------------*/

		// Prepare data
		$code_content = $_POST['_anwp-code-content'];

		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', [ $this, 'save_metabox' ] );

		wp_update_post( [
			'ID'           => $post_id,
			'post_content' => $code_content,
		] );

		// re-hook this function
		add_action( 'save_post', [ $this, 'save_metabox' ] );

		return $post_id;
	}

	/**
	 * Registers admin columns to display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param  array $columns Array of registered column names/labels.
	 * @return array          Modified array.
	 */
	public function columns( $columns ) {

		// Add new columns
		$columns = array_merge( [
			'shortcode' => esc_html__( 'Shortcode', 'anwp-custom-html-js' ),
		], $columns );

		// Change columns order
		$new_columns_order = [ 'cb', 'title', 'shortcode', 'date' ];
		$columns_updated   = [];

		foreach ( $new_columns_order as $c ) {

			if ( isset( $columns[ $c ] ) ) {
				$columns_updated[ $c ] = $columns[ $c ];
			}
		}

		return $columns_updated;
	}

	/**
	 * Handles admin column display. Hooked in via CPT_Core.
	 *
	 * @since  0.1.0
	 *
	 * @param array   $column   Column currently being rendered.
	 * @param integer $post_id  ID of post to display column for.
	 */
	public function columns_display( $column, $post_id ) {

		switch ( $column ) {
			case 'shortcode':
				?>
				<code>[anwpcode id="<?php echo (int) $post_id; ?>"]</code> <?php echo esc_html__( 'or', 'anwp-custom-html-js' ); ?>
				<code>[anwpcode title="<?php echo esc_html( get_the_title( $post_id ) ); ?>"]</code>
				<?php
				break;
		}
	}
}
