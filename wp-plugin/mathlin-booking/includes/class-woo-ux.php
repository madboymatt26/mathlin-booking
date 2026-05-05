<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * WooCommerce UX Integration for Hirers
 *
 * Cleans up the WooCommerce My Account experience for mbs_hirer role users:
 *   1. Redirects hirers to the Bookings Portal on login (not WooCommerce dashboard)
 *   2. Adds a "My Hall Bookings" tab to the WooCommerce My Account menu
 *   3. Removes irrelevant tabs (Downloads, etc.) for hirers
 */
class MBS_Woo_UX {

    public function init() {
        if ( ! class_exists( 'WooCommerce' ) ) return;

        // Login redirect — fires for both WooCommerce and WordPress login
        add_filter( 'woocommerce_login_redirect', array( $this, 'hirer_login_redirect' ), 10, 2 );
        add_filter( 'login_redirect',             array( $this, 'wp_login_redirect' ), 10, 3 );

        // WooCommerce My Account menu customisation
        add_filter( 'woocommerce_account_menu_items', array( $this, 'modify_account_menu' ), 20 );
        add_action( 'init',                           array( $this, 'register_endpoint' ) );
        add_action( 'woocommerce_account_hall-bookings_endpoint', array( $this, 'render_bookings_tab' ) );

        // Set the endpoint title
        add_filter( 'the_title', array( $this, 'endpoint_title' ), 10, 2 );
    }

    // ── 1. Smart Login Redirect ────────────────────────────────────────────────

    /**
     * WooCommerce-specific login redirect.
     * Fires when a user logs in via the WooCommerce My Account form.
     */
    public function hirer_login_redirect( $redirect, $user ) {
        if ( $this->user_is_hirer( $user ) ) {
            $portal_url = $this->get_portal_url();
            if ( $portal_url ) return $portal_url;
        }
        return $redirect;
    }

    /**
     * WordPress core login redirect.
     * Fires when a user logs in via wp-login.php or any standard login form.
     */
    public function wp_login_redirect( $redirect_to, $requested_redirect_to, $user ) {
        if ( is_wp_error( $user ) || ! is_object( $user ) ) return $redirect_to;

        if ( $this->user_is_hirer( $user ) ) {
            $portal_url = $this->get_portal_url();
            if ( $portal_url ) return $portal_url;
        }
        return $redirect_to;
    }

    // ── 2. WooCommerce Account Menu: Add "My Hall Bookings" ────────────────────

    /**
     * Register the custom endpoint so WooCommerce recognises the URL.
     */
    public function register_endpoint() {
        add_rewrite_endpoint( 'hall-bookings', EP_ROOT | EP_PAGES );
    }

    /**
     * Modify the WooCommerce My Account menu items.
     * For hirers: add "My Hall Bookings" and remove irrelevant tabs.
     * For non-hirers: leave the menu unchanged.
     */
    public function modify_account_menu( $items ) {
        if ( ! $this->current_user_is_hirer() ) return $items;

        // Insert "My Hall Bookings" after Dashboard
        $new_items = array();
        foreach ( $items as $key => $label ) {
            $new_items[ $key ] = $label;
            if ( $key === 'dashboard' ) {
                $new_items['hall-bookings'] = 'My Hall Bookings';
            }
        }

        // 3. Remove irrelevant tabs for hirers
        $remove = array( 'downloads', 'edit-address' );
        foreach ( $remove as $key ) {
            unset( $new_items[ $key ] );
        }

        return $new_items;
    }

    /**
     * Render the "My Hall Bookings" tab content.
     * Loads the hirer dashboard view directly.
     */
    public function render_bookings_tab() {
        if ( ! is_user_logged_in() ) return;
        include MBS_PLUGIN_DIR . 'public/views/hirer-dashboard.php';
    }

    /**
     * Set the page title when viewing the hall-bookings endpoint.
     */
    public function endpoint_title( $title, $id = null ) {
        global $wp_query;
        if ( is_main_query() && in_the_loop() && is_account_page() && isset( $wp_query->query_vars['hall-bookings'] ) ) {
            return 'My Hall Bookings';
        }
        return $title;
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    /**
     * Check if a user object has the mbs_hirer role.
     */
    private function user_is_hirer( $user ) {
        if ( ! $user || ! is_object( $user ) || ! isset( $user->roles ) ) return false;
        return in_array( 'mbs_hirer', (array) $user->roles, true );
    }

    /**
     * Check if the currently logged-in user is a hirer.
     */
    private function current_user_is_hirer() {
        if ( ! is_user_logged_in() ) return false;
        $user = wp_get_current_user();
        return $this->user_is_hirer( $user );
    }

    /**
     * Get the URL of the Bookings Portal page (where [mathlin_portal] shortcode lives).
     * Falls back to the WooCommerce My Account page with the hall-bookings endpoint.
     */
    private function get_portal_url() {
        // Look for a page containing the [mathlin_portal] shortcode
        $pages = get_posts( array(
            'post_type'   => 'page',
            'post_status' => 'publish',
            's'           => 'mathlin_portal',
            'numberposts' => 1,
        ) );

        if ( ! empty( $pages ) ) {
            return get_permalink( $pages[0]->ID );
        }

        // Fallback: WooCommerce My Account → Hall Bookings tab
        if ( function_exists( 'wc_get_account_endpoint_url' ) ) {
            return wc_get_account_endpoint_url( 'hall-bookings' );
        }

        return '';
    }
}
