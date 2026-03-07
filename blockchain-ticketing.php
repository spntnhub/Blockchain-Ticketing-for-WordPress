<?php
/**
 * Plugin Name:       Blockchain Ticketing for WordPress
 * Plugin URI:        https://github.com/spntnhub/Blockchain-Ticketing-for-WordPress
 * Description:       Sell and verify NFT event tickets on Polygon. Each ticket is a unique ERC-721 token minted directly to the buyer's wallet. 3% protocol fee per sale.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            spntn
 * Author URI:        https://spntn.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       blockchain-ticketing
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'BT_VERSION',     '1.0.1' );
define( 'BT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'BT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'BT_OPTION_KEY',  'bt_settings' );

require_once BT_PLUGIN_DIR . 'includes/class-admin.php';
require_once BT_PLUGIN_DIR . 'includes/class-events.php';
require_once BT_PLUGIN_DIR . 'includes/class-tickets.php';
require_once BT_PLUGIN_DIR . 'includes/class-checkin.php';

// ─── Bootstrap ───────────────────────────────────────────────────────────────

add_action( 'init', function () {
    BT_Events::register_post_type();
} );

add_action( 'admin_menu', [ 'BT_Admin', 'register_menu' ] );
add_action( 'admin_init', [ 'BT_Admin', 'register_settings' ] );
add_action( 'add_meta_boxes', [ 'BT_Events', 'add_meta_boxes' ] );
add_action( 'save_post_bt_event', [ 'BT_Events', 'save_meta' ], 10, 2 );

// ─── Shortcodes ───────────────────────────────────────────────────────────────

// [blockchain_event id="WP_POST_ID"]  — ticket purchase page
add_shortcode( 'blockchain_event', [ 'BT_Tickets', 'render_shortcode' ] );

// [blockchain_checkin]                — QR scan / check-in interface
add_shortcode( 'blockchain_checkin', [ 'BT_Checkin', 'render_shortcode' ] );

// ─── Scripts & Styles ────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', function () {
    $opts = get_option( BT_OPTION_KEY, [] );

    wp_enqueue_style(
        'bt-styles',
        BT_PLUGIN_URL . 'assets/styles.css',
        [],
        BT_VERSION
    );

    // ethers.js v6 (same version used by Token Membership & Wallet Login)
    wp_enqueue_script(
        'ethers',
        'https://cdnjs.cloudflare.com/ajax/libs/ethers/6.7.0/ethers.umd.min.js',
        [],
        '6.7.0',
        true
    );

    // QRCode.js for ticket QR generation
    wp_enqueue_script(
        'qrcodejs',
        'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js',
        [],
        '1.0.0',
        true
    );

    // jsQR for QR scanning on check-in page
    wp_enqueue_script(
        'jsqr',
        'https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js',
        [],
        '1.4.0',
        true
    );

    wp_enqueue_script(
        'bt-wallet',
        BT_PLUGIN_URL . 'assets/wallet.js',
        [ 'ethers', 'jquery' ],
        BT_VERSION,
        true
    );

    wp_enqueue_script(
        'bt-ticket',
        BT_PLUGIN_URL . 'assets/ticket.js',
        [ 'bt-wallet', 'qrcodejs', 'jquery' ],
        BT_VERSION,
        true
    );

    wp_enqueue_script(
        'bt-checkin',
        BT_PLUGIN_URL . 'assets/checkin.js',
        [ 'jsqr', 'jquery' ],
        BT_VERSION,
        true
    );

    wp_localize_script( 'bt-ticket', 'bt_ajax', [
        'url'           => admin_url( 'admin-ajax.php' ),
        'nonce'         => wp_create_nonce( 'bt_nonce' ),
        'backend_url'   => esc_url( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app' ),
    ] );

    wp_localize_script( 'bt-checkin', 'bt_checkin_ajax', [
        'url'   => admin_url( 'admin-ajax.php' ),
        'nonce' => wp_create_nonce( 'bt_nonce' ),
    ] );
} );

// ─── AJAX Handlers ───────────────────────────────────────────────────────────

add_action( 'wp_ajax_bt_get_event',        [ 'BT_Tickets', 'ajax_get_event'    ] );
add_action( 'wp_ajax_nopriv_bt_get_event', [ 'BT_Tickets', 'ajax_get_event'    ] );
add_action( 'wp_ajax_bt_sign_ticket',        [ 'BT_Tickets', 'ajax_sign_ticket'  ] );
add_action( 'wp_ajax_nopriv_bt_sign_ticket', [ 'BT_Tickets', 'ajax_sign_ticket'  ] );
add_action( 'wp_ajax_bt_record_sale',        [ 'BT_Tickets', 'ajax_record_sale'  ] );
add_action( 'wp_ajax_nopriv_bt_record_sale', [ 'BT_Tickets', 'ajax_record_sale'  ] );
add_action( 'wp_ajax_bt_checkin',            [ 'BT_Checkin', 'ajax_checkin'      ] );
add_action( 'wp_ajax_nopriv_bt_checkin',     [ 'BT_Checkin', 'ajax_checkin'      ] );
