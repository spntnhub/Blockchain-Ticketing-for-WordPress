<?php
/**
 * Plugin Name:       SPNTN NFT Ticketing
 * Plugin URI:        https://github.com/spntnhub/SPNTN-NFT-Ticketing-for-WordPress
 * Description:       Sell and verify NFT event tickets on Polygon. Each ticket is a unique ERC-721 token minted directly to the buyer's wallet. 3% protocol fee per sale.
 * Version:           1.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            spntn
 * Author URI:        https://spntn.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       spntn-nft-ticketing
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'SPNTN_NFT_VERSION',     '1.0.1' );
define( 'SPNTN_NFT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'SPNTN_NFT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'SPNTN_NFT_OPTION_KEY',  'spntn_nft_settings' );

require_once SPNTN_NFT_PLUGIN_DIR . 'spntn-nft-includes/class-admin.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'spntn-nft-includes/class-events.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'spntn-nft-includes/class-tickets.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'spntn-nft-includes/class-checkin.php';

// ─── Bootstrap ───────────────────────────────────────────────────────────────

define( 'SPNTN_NFT_VERSION',     '1.0.1' );
define( 'SPNTN_NFT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'SPNTN_NFT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'SPNTN_NFT_OPTION_KEY',  'spntn_nft_settings' );
add_action( 'admin_menu', [ 'SPNTN_NFT_Admin', 'register_menu' ] );
require_once SPNTN_NFT_PLUGIN_DIR . 'includes/class-admin.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'includes/class-events.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'includes/class-tickets.php';
require_once SPNTN_NFT_PLUGIN_DIR . 'includes/class-checkin.php';
// ─── Shortcodes ───────────────────────────────────────────────────────────────
add_action( 'init', function () {
    SPNTN_NFT_Events::register_post_type();
} );

add_action( 'admin_menu', [ 'SPNTN_NFT_Admin', 'register_menu' ] );
add_action( 'admin_init', [ 'SPNTN_NFT_Admin', 'register_settings' ] );
add_action( 'add_meta_boxes', [ 'SPNTN_NFT_Events', 'add_meta_boxes' ] );
add_action( 'save_post_spntn_nft_event', [ 'SPNTN_NFT_Events', 'save_meta' ], 10, 2 );

add_shortcode( 'spntn_nft_event', [ 'SPNTN_NFT_Tickets', 'render_shortcode' ] );
add_shortcode( 'spntn_nft_checkin', [ 'SPNTN_NFT_Checkin', 'render_shortcode' ] );

add_action( 'wp_enqueue_scripts', 'spntn_nft_enqueue_assets' );
function spntn_nft_enqueue_assets() {
    wp_enqueue_style(
        'spntn-nft-styles',
        SPNTN_NFT_PLUGIN_URL . 'spntn-nft-assets/styles.css',
        [],
        SPNTN_NFT_VERSION
    );
    wp_enqueue_script(
        'spntn-nft-ethers',
        SPNTN_NFT_PLUGIN_URL . 'spntn-nft-assets/ethers.umd.min.js',
        [],
        '6.13.2',
        true
    );
    wp_enqueue_script(
        'spntn-nft-qrcodejs',
        SPNTN_NFT_PLUGIN_URL . 'spntn-nft-assets/qrcode.min.js',
        [],
        '1.0.0',
        true
    );
    wp_enqueue_script(
        'spntn-nft-jsqr',
        SPNTN_NFT_PLUGIN_URL . 'spntn-nft-assets/jsQR.js',
        [],
        '1.4.0',
        true
    );
}

add_action( 'wp_ajax_spntn_nft_get_event',        [ 'SPNTN_NFT_Tickets', 'ajax_get_event'    ] );
add_action( 'wp_ajax_nopriv_spntn_nft_get_event', [ 'SPNTN_NFT_Tickets', 'ajax_get_event'    ] );
add_action( 'wp_ajax_spntn_nft_sign_ticket',        [ 'SPNTN_NFT_Tickets', 'ajax_sign_ticket'  ] );
add_action( 'wp_ajax_nopriv_spntn_nft_sign_ticket', [ 'SPNTN_NFT_Tickets', 'ajax_sign_ticket'  ] );
add_action( 'wp_ajax_spntn_nft_record_sale',        [ 'SPNTN_NFT_Tickets', 'ajax_record_sale'  ] );
add_action( 'wp_ajax_nopriv_spntn_nft_record_sale', [ 'SPNTN_NFT_Tickets', 'ajax_record_sale'  ] );
add_action( 'wp_ajax_spntn_nft_checkin',            [ 'SPNTN_NFT_Checkin', 'ajax_checkin'      ] );
add_action( 'wp_ajax_nopriv_spntn_nft_checkin',     [ 'SPNTN_NFT_Checkin', 'ajax_checkin'      ] );
