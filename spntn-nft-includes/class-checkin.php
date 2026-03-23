<?php
if ( ! defined( 'ABSPATH' ) ) exit;

    class SPNTN_NFT_Checkin {

    // ─── Shortcode: [spntn_nft_checkin] ─────────────────────────────────────

    public static function render_shortcode( array $atts ): string {
        ob_start();
        ?>
        <div id="bt-checkin-container" class="bt-checkin-widget">
            <h2 class="bt-checkin-title"><?php esc_html_e( 'Ticket Check-In', 'spntn-nft-ticketing' ); ?></h2>

            <div class="bt-camera-wrap">
                <video id="bt-camera" autoplay playsinline muted></video>
                <canvas id="bt-canvas" style="display:none;"></canvas>
            </div>

            <p id="bt-scan-status" class="bt-scan-status">
                <?php esc_html_e( 'Point camera at the ticket QR code.', 'blockchain-ticketing' ); ?>
                            <?php esc_html_e( 'Point camera at the ticket QR code.', 'spntn-nft-ticketing' ); ?>
            </p>

            <div id="bt-scan-result" class="bt-scan-result" style="display:none;"></div>

            <button id="bt-scan-again" class="bt-btn bt-btn-secondary" style="display:none; margin-top:12px;">
                <?php esc_html_e( 'Scan Next Ticket', 'spntn-nft-ticketing' ); ?>
            </button>
        </div>
        <?php
        return ob_get_clean();
    }

    // ─── AJAX: verify QR + mark used ─────────────────────────────────────────

    public static function ajax_checkin(): void {
        check_ajax_referer( 'bt_nonce', 'nonce' );

        $token_id = isset($_POST['token_id']) ? sanitize_text_field( wp_unslash( $_POST['token_id'] ) ) : '';
        $wallet   = isset($_POST['wallet']) ? sanitize_text_field( wp_unslash( $_POST['wallet'] ) ) : '';

        if ( ! $token_id || ! $wallet ) wp_send_json_error( 'token_id and wallet required' );

        $opts    = get_option( BLOCTI_OPTION_KEY, [] );
            $opts    = get_option( SPNTN_NFTT_OPTION_KEY, [] );
        $api_key = $opts['api_key']     ?? '';
        $backend = rtrim( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app', '/' );

        $response = wp_remote_post( $backend . '/api/v2/ticketing/checkin', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => $api_key,
            ],
            'body'    => wp_json_encode( [
                'tokenId' => $token_id,
                'wallet'  => $wallet,
            ] ),
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) ) wp_send_json_error( $response->get_error_message() );

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        wp_send_json_success( $body );
    }
}
