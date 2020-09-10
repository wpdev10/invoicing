<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Template Class
 *
 */
class GetPaid_Template {

    /**
     * @param string
     */
    public $templates_dir;

    /**
     * @param string
     */
    public $templates_url;

    /**
	 * Class constructor.
	 *
	 * @since 1.0.19
	 */
	public function __construct() {

        $this->templates_dir = apply_filters( 'getpaid_default_templates_dir', WPINV_PLUGIN_DIR . 'templates' );
        $this->templates_url = apply_filters( 'getpaid_default_templates_url', WPINV_PLUGIN_URL . 'templates' );
    }

    /**
	 * Checks if this is a preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_preview() {
        return 
            $this->is_divi_preview() ||
            $this->is_elementor_preview() ||
            $this->is_beaver_preview() ||
            $this->is_siteorigin_preview() ||
            $this->is_cornerstone_preview() ||
            $this->is_fusion_preview() ||
            $this->is_oxygen_preview();
    }

    /**
	 * Checks if this is an elementor preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_elementor_preview() {
		return isset( $_REQUEST['elementor-preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor' ) || ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'elementor_ajax' );
	}

	/**
	 * Checks if this is a DIVI preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_divi_preview() {
		return isset( $_REQUEST['et_fb'] ) || isset( $_REQUEST['et_pb_preview'] ) || ( is_admin() && isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'et_pb' );
	}

	/**
	 * Checks if this is a beaver builder preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_beaver_preview() {
		return isset( $_REQUEST['fl_builder'] );
	}

	/**
	 * Checks if this is a siteorigin builder preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_siteorigin_preview() {
		return ! empty( $_REQUEST['siteorigin_panels_live_editor'] );
	}

	/**
	 * Checks if this is a cornerstone builder preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_cornerstone_preview() {
		return ! empty( $_REQUEST['cornerstone_preview'] ) || basename( $_SERVER['REQUEST_URI'] ) == 'cornerstone-endpoint';
	}

	/**
	 * Checks if this is a fusion builder preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_fusion_preview() {
		return ! empty( $_REQUEST['fb-edit'] ) || ! empty( $_REQUEST['fusion_load_nonce'] );
	}

	/**
	 * Checks if this is an oxygen builder preview page
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	public function is_oxygen_preview() {
		return ! empty( $_REQUEST['ct_builder'] ) || ( ! empty( $_REQUEST['action'] ) && ( substr( $_REQUEST['action'], 0, 11 ) === "oxy_render_" || substr( $_REQUEST['action'], 0, 10 ) === "ct_render_" ) );
    }

    /**
     * Locates a template path.
     * 
     * @param string $template_name e.g payment-forms/cart.php The template to locate.
     * @param string $template_path The template path relative to the theme's root dir. Defaults to 'invoicing'.
     * @param string $default_path The root path to the default template. Defaults to invoicing/templates
     */
	public function locate_template( $template_name, $template_path = '', $default_path = '' ) {

        // Load the defaults for the template path and default path.
        $template_path = empty( $template_path ) ? 'invoicing' : $template_path;
        $default_path  = empty( $default_path ) ? $this->templates_dir : $default_path;

         // Is it overidden?
        $template = locate_template(
            array( trailingslashit( $template_path ) . $template_name ),
            array( 'wpinv-' . $template_name )
        );

        // If not, load the default template.
        if ( empty( $template ) ) {
            $template = trailingslashit( $default_path ) . $template_name;
        }

        return apply_filters( 'wpinv_locate_template', $template, $template_name, $template_path, $default_path );
    }
    
    /**
	 * Loads a template
	 *
	 * @since 1.0.19
	 * @return bool
	 */
	protected function load_template( $template_name, $template_path, $args ) {

        if ( is_array( $args ) ){
            extract( $args );
        }

        // Fires before loading a template.
	    do_action( 'wpinv_before_template_part', $template_name, $template_path, $args );

        // Load the template.
	    include( $template_path );

        // Fires after loading a template.
        do_action( 'wpinv_after_template_part', $template_name, $template_path, $args );

    }

    /**
     * Displays a template.
     * 
     * First checks if there is a template overide, if not it loads the default template.
     * 
     * @param string $template_name e.g payment-forms/cart.php The template to locate.
     * @param array $args An array of args to pass to the template.
     * @param string $template_path The templates directory relative to the theme's root dir. Defaults to 'invoicing'.
     * @param string $default_path The root path to the default template. Defaults to invoicing/templates
     */
	public function display_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {

        // Locate the template.
        $located = wpinv_locate_template( $template_name, $template_path, $default_path );

        // Abort if the file does not exist.
        if ( ! file_exists( $located ) ) {
            getpaid_doing_it_wrong( __CLASS__ . '::' .__METHOD__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0.19' );
            return;
        }

        $this->load_template( $template_name, $located, $args );

    }
    
    /**
     * Retrieves a template.
     * 
     * First checks if there is a template overide, if not it loads the default template.
     * 
     * @param string $template_name e.g payment-forms/cart.php The template to locate.
     * @param array $args An array of args to pass to the template.
     * @param string $template_path The templates directory relative to the theme's root dir. Defaults to 'invoicing'.
     * @param string $default_path The root path to the default template. Defaults to invoicing/templates
     */
	public function get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
        ob_start();
        $this->display_template( $template_name, $args, $template_path, $default_path );
        return ob_get_clean();
    }

}
