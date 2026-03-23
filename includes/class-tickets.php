<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SPNTN_NFT_Tickets {

    // ─── Shortcode: [spntn_nft_event id="POST_ID"] ───────────────────────────

    public static function render_shortcode( array $atts ): string {
        $atts = shortcode_atts( [ 'id' => '' ], $atts, 'spntn_nft_event' );
        $post_id = (int) $atts['id'];

        if ( ! $post_id ) {
            return '<p class="bt-error">' . esc_html__( 'Invalid event ID.', 'spntn-nft-ticketing' ) . '</p>';
        }

        $backend_event_id = get_post_meta( $post_id, '_spntn_nft_backend_event_id', true );
        if ( ! $backend_event_id ) {
            return '<p class="bt-error">' . esc_html__( 'Event not yet synced to backend. Please publish the event from WP admin.', 'spntn-nft-ticketing' ) . '</p>';
        }

        ob_start();
        ?>
        <div id="bt-event-container" data-event-id="<?php echo esc_attr( $backend_event_id ); ?>" class="bt-ticket-widget">
            <div class="bt-event-header">
                <h2 id="bt-event-name" class="bt-event-title">Loading…</h2>
                <p id="bt-event-date" class="bt-event-meta"></p>
                <p id="bt-event-location" class="bt-event-meta"></p>
            </div>
            <div class="bt-ticket-info">
                <span class="bt-price-label"><?php esc_html_e( 'Price:', 'spntn-nft-ticketing' ); ?> <strong id="bt-event-price">—</strong></span>
                <span class="bt-supply-label" id="bt-supply"></span>
            </div>

            <div class="bt-actions">
                <button id="bt-connect-wallet" class="bt-btn bt-btn-secondary"><?php esc_html_e( 'Connect Wallet', 'spntn-nft-ticketing' ); ?></button>
                <button id="bt-buy-ticket" class="bt-btn bt-btn-primary" disabled><?php esc_html_e( 'Buy Ticket', 'spntn-nft-ticketing' ); ?></button>
            </div>

            <p id="bt-status" class="bt-status" style="display:none;"></p>

            <div id="bt-ticket-result" style="display:none;" class="bt-ticket-result">
                <h3><?php esc_html_e( 'Your Ticket', 'spntn-nft-ticketing' ); ?> <span id="bt-token-id"></span></h3>
                <p class="bt-success-msg"><?php esc_html_e( 'Show this QR code at the event entrance.', 'spntn-nft-ticketing' ); ?></p>
                <div id="bt-qr-code" class="bt-qr-container"></div>
                <p><a id="bt-tx-link" href="#" target="_blank" rel="noopener" style="display:none;"><?php esc_html_e( 'View on Explorer', 'spntn-nft-ticketing' ); ?></a></p>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    // ─── AJAX: get event info ─────────────────────────────────────────────────

    public static function ajax_get_event(): void {
        check_ajax_referer( 'bt_nonce', 'nonce' );

        $event_id = isset($_POST['event_id']) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : '';
        if ( ! $event_id ) wp_send_json_error( 'event_id required' );

        $opts    = get_option( SPNTN_NFT_OPTION_KEY, [] );
        $api_key = $opts['api_key']     ?? '';
        $backend = rtrim( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app', '/' );

        $response = wp_remote_get( $backend . '/api/v2/ticketing/events/id/' . rawurlencode( $event_id ), [
            'headers' => [ 'X-API-Key' => $api_key ],
            'timeout' => 10,
        ] );

        if ( is_wp_error( $response ) ) wp_send_json_error( $response->get_error_message() );

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! empty( $body['data'] ) ) {
            wp_send_json_success( $body['data'] );
        } else {
            wp_send_json_error( $body['error'] ?? 'Event not found' );
        }
    }

    // ─── AJAX: sign ticket (get backend signature for on-chain mint) ──────────

    public static function ajax_sign_ticket(): void {
        check_ajax_referer( 'bt_nonce', 'nonce' );

        $event_id      = isset($_POST['event_id']) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : '';
        $buyer_address = isset($_POST['buyer_address']) ? sanitize_text_field( wp_unslash( $_POST['buyer_address'] ) ) : '';

        if ( ! $event_id || ! $buyer_address ) wp_send_json_error( 'Missing params' );

        $opts    = get_option( SPNTN_NFT_OPTION_KEY, [] );
        $api_key = $opts['api_key']     ?? '';
        $backend = rtrim( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app', '/' );

        $response = wp_remote_post( $backend . '/api/v2/ticketing/sign', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => $api_key,
            ],
            'body'    => wp_json_encode( [
                'eventId'      => $event_id,
                'buyerAddress' => $buyer_address,
            ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) wp_send_json_error( $response->get_error_message() );

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! empty( $body['data'] ) ) {
            wp_send_json_success( $body['data'] );
        } else {
            wp_send_json_error( $body['error'] ?? 'Signing failed' );
        }
    }

    // ─── AJAX: record sale after on-chain mint ────────────────────────────────

    public static function ajax_record_sale(): void {
        check_ajax_referer( 'bt_nonce', 'nonce' );

        $event_id      = isset($_POST['event_id']) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : '';
        $token_id      = isset($_POST['token_id']) ? sanitize_text_field( wp_unslash( $_POST['token_id'] ) ) : '';
        $tx_hash       = isset($_POST['tx_hash']) ? sanitize_text_field( wp_unslash( $_POST['tx_hash'] ) ) : '';
        $buyer_address = isset($_POST['buyer_address']) ? sanitize_text_field( wp_unslash( $_POST['buyer_address'] ) ) : '';

        if ( ! $event_id || ! $token_id || ! $tx_hash || ! $buyer_address ) {
            wp_send_json_error( 'Missing params' );
        }

        $opts    = get_option( SPNTN_NFT_OPTION_KEY, [] );
        $api_key = $opts['api_key']     ?? '';
        $backend = rtrim( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app', '/' );

        $response = wp_remote_post( $backend . '/api/v2/ticketing/record', [
            'headers' => [
                'Content-Type' => 'application/json',
                'X-API-Key'    => $api_key,
            ],
            'body'    => wp_json_encode( [
                'eventId'      => $event_id,
                'tokenId'      => $token_id,
                'txHash'       => $tx_hash,
                'buyerAddress' => $buyer_address,
            ] ),
            'timeout' => 15,
        ] );

        if ( is_wp_error( $response ) ) wp_send_json_error( $response->get_error_message() );

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! empty( $body['data'] ) ) {
            wp_send_json_success( $body['data'] );
        } else {
            // 409 = already recorded — not a hard failure
            wp_send_json_success( [ 'recorded' => false ] );
        }
    }
}
