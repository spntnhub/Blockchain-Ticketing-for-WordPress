<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SPNTN_NFTT_Admin {

    public static function register_menu(): void {
        add_options_page(
            __( 'SPNTN NFT Ticketing', 'spntn-nft-ticketing' ),
            __( 'SPNTN NFT Ticketing', 'spntn-nft-ticketing' ),
            'manage_options',
            'spntn-nft-ticketing',
            [ __CLASS__, 'settings_page' ]
        );
    }

    public static function register_settings(): void {
        register_setting(
            'spntn_nftt_settings_group',
            SPNTN_NFTT_OPTION_KEY,
            [ 'sanitize_callback' => [ __CLASS__, 'sanitize' ] ]
        );
    }

    public static function sanitize( array $input ): array {
        $allowed_chains = [ 'polygon', 'base', 'arbitrum', 'optimism' ];
        $chain = in_array( $input['chain'] ?? '', $allowed_chains, true ) ? $input['chain'] : 'polygon';
            register_setting(
                'spntn_nft_settings_group',
                SPNTN_NFT_OPTION_KEY,
                [ 'sanitize_callback' => [ __CLASS__, 'sanitize' ] ]
            );
            'chain'              => $chain,
        ];
    }
            $opts = get_option( SPNTN_NFT_OPTION_KEY, [] );
    public static function settings_page(): void {
        $opts = get_option( SPNTN_NFTT_OPTION_KEY, [] );
        ?>
        <div class="wrap">
                    <?php settings_fields( 'spntn_nft_settings_group' ); ?>

            <!-- Get API Key modal -->
            <div id="bt-api-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,.5); z-index:9999;">
                <div style="background:#fff; max-width:460px; margin:80px auto; padding:28px; border-radius:8px;">
                                <input type="text" id="bt_api_key" name="<?php echo esc_attr( SPNTN_NFT_OPTION_KEY ); ?>[api_key]"
                    <p><?php esc_html_e( 'Enter your email to receive a free API key.', 'spntn-nft-ticketing' ); ?></p>
                    <input type="email" id="bt-modal-email" placeholder="your@email.com" style="width:100%; margin-bottom:8px;" class="regular-text" />
                    <br>
                    <button id="bt-modal-submit" class="button button-primary"><?php esc_html_e( 'Send', 'spntn-nft-ticketing' ); ?></button>
                    <button id="bt-modal-close" class="button" style="margin-left:8px;"><?php esc_html_e( 'Cancel', 'spntn-nft-ticketing' ); ?></button>
                    <div id="bt-modal-result" style="margin-top:12px;"></div>
                </div>
                                <select id="bt_chain" name="<?php echo esc_attr( SPNTN_NFT_OPTION_KEY ); ?>[chain]">

            <form method="post" action="options.php">
                <?php settings_fields( 'spntn_nftt_settings_group' ); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="bt_api_key"><?php esc_html_e( 'API Key', 'spntn-nft-ticketing' ); ?></label></th>
                        <td>
                            <input type="text" id="bt_api_key" name="<?php echo esc_attr( BLOCTI_OPTION_KEY ); ?>[api_key]"
                                   value="<?php echo esc_attr( $opts['api_key'] ?? '' ); ?>"
                                   class="regular-text" autocomplete="off" />
                            <button type="button" id="bt-get-key-btn" class="button" style="margin-left:8px;">
                                <?php esc_html_e( 'Get API Key', 'blockchain-ticketing' ); ?>
                            </button>
                            <p class="description"><?php esc_html_e( 'Free API key. Email info@spntn.com for support.', 'blockchain-ticketing' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bt_chain"><?php esc_html_e( 'Default Chain', 'blockchain-ticketing' ); ?></label></th>
                        <td>
                            <select id="bt_chain" name="<?php echo esc_attr( BLOCTI_OPTION_KEY ); ?>[chain]">
                                <?php
                                $cur_chain = $opts['chain'] ?? 'polygon';
                                $chains = [
                                    'polygon'  => 'Polygon (POL)',
                                    'base'     => 'Base (ETH)',
                                    'arbitrum' => 'Arbitrum One (ETH)',
                                    'optimism' => 'Optimism (ETH)',
                                ];
                                foreach ( $chains as $slug => $label ) :
                                ?>
                                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cur_chain, $slug ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Default chain for new events. Can be overridden per event.', 'blockchain-ticketing' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bt_backend_url"><?php esc_html_e( 'Backend URL', 'blockchain-ticketing' ); ?></label></th>
                        <td>
                            <input type="url" id="bt_backend_url" name="<?php echo esc_attr( BLOCTI_OPTION_KEY ); ?>[backend_url]"
                                   value="<?php echo esc_attr( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app' ); ?>"
                                   class="regular-text" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="bt_organizer"><?php esc_html_e( 'Default Organizer Wallet', 'blockchain-ticketing' ); ?></label></th>
                        <td>
                            <input type="text" id="bt_organizer" name="<?php echo esc_attr( BLOCTI_OPTION_KEY ); ?>[organizer_wallet]"
                                   value="<?php echo esc_attr( $opts['organizer_wallet'] ?? '' ); ?>"
                                   class="regular-text" placeholder="0x..." />
                            <p class="description"><?php esc_html_e( 'Wallet that receives 97% of each ticket sale. Can be overridden per event.', 'blockchain-ticketing' ); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <script>
        (function($){
            var backendUrl = <?php echo wp_json_encode( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app' ); ?>;

            $('#bt-get-key-btn').on('click', function() { $('#bt-api-modal').show(); });
            $('#bt-modal-close').on('click', function() { $('#bt-api-modal').hide(); });

            $('#bt-modal-submit').on('click', function() {
                var email   = $('#bt-modal-email').val().trim();
                var siteUrl = window.location.origin;
                if (!email) { $('#bt-modal-result').text('Please enter your email.'); return; }

                $('#bt-modal-submit').prop('disabled', true).text('Sending...');
                $.ajax({
                    url: backendUrl + '/api/auth/activate',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ email: email, siteUrl: siteUrl }),
                    success: function(res) {
                        if (res.apiKey) {
                            $('#bt-modal-result').html(
                                '<strong>Your API Key:</strong><br>' +
                                '<code style="user-select:all">' + res.apiKey + '</code>' +
                                '<br><small>Copy it into the field above and save your settings.</small>'
                            );
                            $('#bt_api_key').val(res.apiKey);
                        } else {
                            $('#bt-modal-result').text(res.message || 'Check your email for your API key.');
                        }
                    },
                    error: function(xhr) {
                        var msg = xhr.responseJSON && xhr.responseJSON.error ? xhr.responseJSON.error : 'Request failed — email info@spntn.com for help.';
                        $('#bt-modal-result').text(msg);
                    },
                    complete: function() {
                        $('#bt-modal-submit').prop('disabled', false).text('Send');
                    }
                });
            });
        })(jQuery);
        </script>
        <?php
    }

    // ─── Called from WP admin via AJAX to sync event to backend ──────────────

    /**
     * @return string|WP_Error  JSON-encoded event from backend, or WP_Error.
     */
    public static function sync_event_to_backend( int $post_id ): string|WP_Error {
        $opts    = get_option( BT_OPTION_KEY, [] );
        $api_key = $opts['api_key']          ?? '';
        $backend = rtrim( $opts['backend_url'] ?? 'https://nft-saas-production.up.railway.app', '/' );
        $post    = get_post( $post_id );

        if ( ! $api_key ) return new WP_Error( 'no_api_key', 'API key not configured.' );

        $meta    = get_post_meta( $post_id );
        $default_contract  = $opts['contract_address']  ?? '';
        $default_organizer = $opts['organizer_wallet']  ?? '';

        $default_chain = $opts['chain'] ?? 'polygon';

        $body = wp_json_encode( [
            'name'             => $post->post_title,
            'description'      => wp_strip_all_tags( $post->post_content ),
            'date'             => get_post_meta( $post_id, '_bt_date', true ),
            'location'         => get_post_meta( $post_id, '_bt_location', true ),
            'totalSupply'      => (int) get_post_meta( $post_id, '_bt_total_supply', true ),
            'price'            => get_post_meta( $post_id, '_bt_price', true ),
            'currency'         => get_post_meta( $post_id, '_bt_currency', true ) ?: 'POL',
            'paymentToken'     => get_post_meta( $post_id, '_bt_payment_token', true ),
            'organizerWallet'  => get_post_meta( $post_id, '_bt_organizer_wallet', true ) ?: $default_organizer,
            'contractAddress'  => get_post_meta( $post_id, '_bt_contract_address', true ) ?: $default_contract,
            'chain'            => get_post_meta( $post_id, '_bt_chain', true ) ?: $default_chain,
            'imageUrl'         => get_the_post_thumbnail_url( $post_id, 'medium' ) ?: '',
        ] );

        $existing_id = get_post_meta( $post_id, '_bt_backend_event_id', true );

        if ( $existing_id ) {
            // Update
            $response = wp_remote_post( $backend . '/api/v2/ticketing/events/' . rawurlencode( $existing_id ), [
                'method'  => 'PATCH',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $api_key,
                ],
                'body'    => $body,
                'timeout' => 15,
            ] );
        } else {
            // Create
            $response = wp_remote_post( $backend . '/api/v2/ticketing/events', [
                'method'  => 'POST',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key'    => $api_key,
                ],
                'body'    => $body,
                'timeout' => 15,
            ] );
        }

        if ( is_wp_error( $response ) ) return $response;

        $data = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( ! empty( $data['data']['_id'] ) ) {
            update_post_meta( $post_id, '_bt_backend_event_id', sanitize_text_field( $data['data']['_id'] ) );
            update_post_meta( $post_id, '_bt_slug', sanitize_text_field( $data['data']['slug'] ) );
        }

        return wp_remote_retrieve_body( $response );
    }
}
