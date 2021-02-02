<?php
/**
 * Contains functions related to Invoicing plugin.
 *
 * @since 1.0.0
 * @package Invoicing
 */
 
// MUST have WordPress.
if ( !defined( 'WPINC' ) ) {
    exit( 'Do NOT access this file directly: ' . basename( __FILE__ ) );
}

function wpinv_bulk_actions( $actions ) {
    if ( isset( $actions['edit'] ) ) {
        unset( $actions['edit'] );
    }

    return $actions;
}
add_filter( 'bulk_actions-edit-wpi_invoice', 'wpinv_bulk_actions' );
add_filter( 'bulk_actions-edit-wpi_item', 'wpinv_bulk_actions' );

function wpinv_admin_post_id( $id = 0 ) {
    global $post;

    if ( isset( $id ) && ! empty( $id ) ) {
        return (int)$id;
    } else if ( get_the_ID() ) {
        return (int) get_the_ID();
    } else if ( isset( $post->ID ) && !empty( $post->ID ) ) {
        return (int) $post->ID;
    } else if ( isset( $_GET['post'] ) && !empty( $_GET['post'] ) ) {
        return (int) $_GET['post'];
    } else if ( isset( $_GET['id'] ) && !empty( $_GET['id'] ) ) {
        return (int) $_GET['id'];
    } else if ( isset( $_POST['id'] ) && !empty( $_POST['id'] ) ) {
        return (int) $_POST['id'];
    } 

    return null;
}
    
function wpinv_admin_post_type( $id = 0 ) {
    if ( !$id ) {
        $id = wpinv_admin_post_id();
    }
    
    $type = get_post_type( $id );
    
    if ( !$type ) {
        $type = isset( $_GET['post_type'] ) && !empty( $_GET['post_type'] ) ? $_GET['post_type'] : null;
    }
    
    return apply_filters( 'wpinv_admin_post_type', $type, $id );
}

function wpinv_admin_messages() {
	settings_errors( 'wpinv-notices' );
}
add_action( 'admin_notices', 'wpinv_admin_messages' );

add_action( 'admin_init', 'wpinv_show_test_payment_gateway_notice' );
function wpinv_show_test_payment_gateway_notice(){
    add_action( 'admin_notices', 'wpinv_test_payment_gateway_messages' );
}

function wpinv_test_payment_gateway_messages(){
    $gateways = wpinv_get_enabled_payment_gateways();
    $name = array(); $test_gateways = '';
    if ($gateways) {
        foreach ($gateways as $id => $gateway) {
            if (wpinv_is_test_mode($id)) {
                $name[] = $gateway['checkout_label'];
            }
        }
        $test_gateways = implode(', ', $name);
    }
    if(isset($test_gateways) && !empty($test_gateways)){
        $link = admin_url('admin.php?page=wpinv-settings&tab=gateways');
        $notice = wp_sprintf( __('<strong>Important:</strong> Payment Gateway(s) %s are in testing mode and will not receive real payments. Go to <a href="%s"> Gateway Settings</a>.', 'invoicing'), $test_gateways, $link );
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php echo $notice; ?></p>
        </div>
        <?php
    }
}

add_action('admin_init', 'wpinv_admin_search_by_invoice');

/**
 * hook the posts search if we're on the admin page for our type
 */
function wpinv_admin_search_by_invoice() {
    global $typenow;

    if ($typenow === 'wpi_invoice' || $typenow === 'wpi_quote' ) {
        add_filter('posts_search', 'wpinv_posts_search_example_type', 10, 2);
    }
}

/**
 * add query condition for search invoice by email
 * @param string $search the search string so far
 * @param WP_Query $query
 * @return string
 */
function wpinv_posts_search_example_type($search, $query) {
    global $wpdb;

    if ($query->is_main_query() && !empty($query->query['s'])) {
        $conditions_str = "{$wpdb->posts}.post_author IN ( SELECT ID FROM {$wpdb->users} WHERE user_email LIKE '%" . esc_sql( $query->query['s'] ) . "%' )";
        if ( ! empty( $search ) ) {
            $search = preg_replace( '/^ AND /', '', $search );
            $search = " AND ( {$search} OR ( {$conditions_str} ) )";
        } else {
            $search = " AND ( {$conditions_str} )";
        }
    }

    return $search;
}

/**
 * Resets invoice counts.
 */
function wpinv_reset_invoice_count(){
    if ( ! empty( $_GET['reset_invoice_count'] ) && isset( $_GET['_nonce'] ) && wp_verify_nonce( $_GET['_nonce'], 'reset_invoice_count' ) ) {
        wpinv_update_option('invoice_sequence_start', 1);
        delete_option('wpinv_last_invoice_number');
        getpaid_admin()->show_success( __( 'Invoice number sequence reset successfully.', 'invoicing' ) );
        $url = remove_query_arg( array('reset_invoice_count', '_nonce') );
        wp_redirect($url);
        exit();
    }
}
add_action( 'admin_init', 'wpinv_reset_invoice_count' );