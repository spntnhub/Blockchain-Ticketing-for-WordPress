<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Convert human-readable price to on-chain smallest unit.
 *  - Native token (POL/ETH): 18 decimals (wei)
 *  - ERC-20 (USDC): 6 decimals
 */
function spntn_nft_to_wei( string $amount, string $currency ): string {
    if ( '' === $amount || '0' === $amount ) return '0';
    $decimals = ( 'ERC20' === $currency ) ? 6 : 18;
    if ( function_exists( 'bcmul' ) ) {
        return bcmul( $amount, bcpow( '10', (string) $decimals ), 0 );
    }
    $parts   = explode( '.', $amount );
    $integer = $parts[0] ?? '0';
    $decimal = isset( $parts[1] ) ? substr( $parts[1], 0, $decimals ) : '';
    $decimal = str_pad( $decimal, $decimals, '0', STR_PAD_RIGHT );
    return ltrim( $integer . $decimal, '0' ) ?: '0';
}

/**
 * Convert on-chain smallest unit back to human-readable price.
 */
function spntn_nft_from_wei( string $amount, string $currency ): string {
    if ( '' === $amount || '0' === $amount ) return '';
    $decimals = ( 'ERC20' === $currency ) ? 6 : 18;
    if ( function_exists( 'bcdiv' ) ) {
        $result = bcdiv( $amount, bcpow( '10', (string) $decimals ), $decimals );
        return rtrim( rtrim( $result, '0' ), '.' );
    }
    $padded  = str_pad( $amount, $decimals + 1, '0', STR_PAD_LEFT );
    $integer = ltrim( substr( $padded, 0, -$decimals ), '0' ) ?: '0';
    $decimal = rtrim( substr( $padded, -$decimals ), '0' );
    return $decimal ? "$integer.$decimal" : $integer;
}

class SPNTN_NFT_Events {

    // ─── Register custom post type ────────────────────────────────────────────

    public static function register_post_type(): void {
        register_post_type( 'spntn_nft_event', [
            'labels' => [
                'name'          => __( 'Events',     'spntn-nft-ticketing' ),
                'singular_name' => __( 'Event',      'spntn-nft-ticketing' ),
                'add_new_item'  => __( 'Add New Event', 'spntn-nft-ticketing' ),
                'edit_item'     => __( 'Edit Event',    'spntn-nft-ticketing' ),
            ],
            'public'             => true,
            'show_in_menu'       => true,
            'menu_icon'          => 'dashicons-tickets-alt',
            'supports'           => [ 'title', 'editor', 'thumbnail' ],
            'has_archive'        => true,
            'rewrite'            => [ 'slug' => 'event' ],
            'show_in_rest'       => true,
        ] );
    }

    // ─── Meta boxes ───────────────────────────────────────────────────────────

    public static function add_meta_boxes(): void {
        add_meta_box(
            'spntn_nft_event_details',
            __( 'Ticket Details', 'spntn-nft-ticketing' ),
            [ __CLASS__, 'render_meta_box' ],
            'spntn_nft_event',
            'normal',
            'high'
        );

        add_meta_box(
            'spntn_nft_event_sync',
            __( 'Blockchain Sync', 'spntn-nft-ticketing' ),
            [ __CLASS__, 'render_sync_box' ],
            'spntn_nft_event',
            'side',
            'high'
        );
    }

    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'bt_save_event', 'bt_event_nonce' );
        $opts = get_option( SPNTN_NFT_OPTION_KEY, [] );

        $date             = get_post_meta( $post->ID, '_spntn_nft_date',             true );
        $location         = get_post_meta( $post->ID, '_spntn_nft_location',         true );
        $total_supply     = get_post_meta( $post->ID, '_spntn_nft_total_supply',     true );
        $price            = get_post_meta( $post->ID, '_spntn_nft_price',            true );
        $currency         = get_post_meta( $post->ID, '_spntn_nft_currency',         true ) ?: 'POL';
        $payment_token    = get_post_meta( $post->ID, '_spntn_nft_payment_token',    true );
        $organizer_wallet = get_post_meta( $post->ID, '_spntn_nft_organizer_wallet', true ) ?: ( $opts['organizer_wallet'] ?? '' );
        $contract_address = get_post_meta( $post->ID, '_spntn_nft_contract_address', true ) ?: ( $opts['contract_address'] ?? '' );
        $chain            = get_post_meta( $post->ID, '_spntn_nft_chain',             true ) ?: ( $opts['chain']             ?? 'polygon' );
        $chain_symbols    = [ 'polygon' => 'POL', 'base' => 'ETH', 'arbitrum' => 'ETH', 'optimism' => 'ETH' ];
        $display_price    = spntn_nft_from_wei( $price ?: '', $currency );
        $price_unit       = 'ERC20' === $currency ? 'USDC' : ( $chain_symbols[ $chain ] ?? 'POL' );
        $allowed_chains   = [
            'polygon'  => 'Polygon (POL)',
            'base'     => 'Base (ETH)',
            'arbitrum' => 'Arbitrum One (ETH)',
            'optimism' => 'Optimism (ETH)',
        ];        ?>
        <table class="form-table" style="margin-top:0;">
            <tr>
                <th><label for="bt_date"><?php esc_html_e( 'Event Date & Time', 'blockchain-ticketing' ); ?></label></th>
                <td><input type="datetime-local" id="bt_date" name="bt_date" value="<?php echo esc_attr( $date ? gmdate( 'Y-m-d\TH:i', strtotime( $date ) ) : '' ); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="bt_location"><?php esc_html_e( 'Location', 'blockchain-ticketing' ); ?></label></th>
                <td><input type="text" id="bt_location" name="bt_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="City, Venue" /></td>
            </tr>
            <tr>
                <th><label for="bt_price"><?php esc_html_e( 'Ticket Price', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="number" id="bt_price" name="bt_price" value="<?php echo esc_attr( $display_price ); ?>"
                               step="any" min="0" style="width:140px;" placeholder="0" />
                        <strong id="bt_price_unit"><?php echo esc_html( $price_unit ); ?></strong>
                    </div>
                    <p class="description"><?php esc_html_e( 'Enter in full units — e.g. 5 for 5 POL, or 10 for 10 USDC.', 'blockchain-ticketing' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bt_currency"><?php esc_html_e( 'Currency', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <select id="bt_currency" name="bt_currency">
                        <option value="POL"   <?php selected( $currency, 'POL'   ); ?>>POL (native)</option>
                        <option value="ERC20" <?php selected( $currency, 'ERC20' ); ?>>ERC-20 (USDC, etc.)</option>
                    </select>
                </td>
            </tr>
            <tr id="bt_token_row" style="<?php echo $currency !== 'ERC20' ? 'display:none;' : ''; ?>">
                <th><label for="bt_payment_token"><?php esc_html_e( 'ERC-20 Token Address', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <input type="text" id="bt_payment_token" name="bt_payment_token" value="<?php echo esc_attr( $payment_token ); ?>" class="regular-text" placeholder="0x3c499c... (USDC)" />
                    <p class="description"><?php esc_html_e( 'Auto-filled with USDC when you switch to ERC-20 currency. You can change to any ERC-20 token.', 'blockchain-ticketing' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bt_total_supply"><?php esc_html_e( 'Total Supply', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <input type="number" id="bt_total_supply" name="bt_total_supply" value="<?php echo esc_attr( $total_supply ?: '0' ); ?>" min="0" style="width:100px;" />
                    <p class="description"><?php esc_html_e( '0 = unlimited.', 'blockchain-ticketing' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="bt_organizer_wallet"><?php esc_html_e( 'Organizer Wallet', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <input type="text" id="bt_organizer_wallet" name="bt_organizer_wallet" value="<?php echo esc_attr( $organizer_wallet ); ?>" class="regular-text" placeholder="0x..." />
                    <p class="description"><?php esc_html_e( 'Receives 97% of each ticket sale. Defaults to plugin settings.', 'blockchain-ticketing' ); ?></p>
                </td>
            </tr>

            <tr>
                <th><label for="bt_chain"><?php esc_html_e( 'Chain', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <select id="bt_chain" name="bt_chain">
                        <?php foreach ( $allowed_chains as $slug => $label ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $chain, $slug ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">
                        <?php esc_html_e( 'USDC per chain:', 'blockchain-ticketing' ); ?>
                        Polygon: <code>0x3c499c...3359</code> &nbsp;
                        Base: <code>0x833589...2913</code> &nbsp;
                        Arbitrum: <code>0xaf88d0...5831</code> &nbsp;
                        Optimism: <code>0x0b2C63...7Ff85</code>
                    </p>
                </td>
            </tr>
        </table>

        <script>
        var USDC_BY_CHAIN = {
            polygon:  '0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359',
            base:     '0x833589fCD6eDb6E08f4c7C32D4f71b54bdA02913',
            arbitrum: '0xaf88d065e77c8cC2239327C5EDb3A432268e5831',
            optimism: '0x0b2C639c533813f4Aa9D7837CAf62653d097Ff85',
        };
        var BT_NATIVE_SYMBOL = { polygon: 'POL', base: 'ETH', arbitrum: 'ETH', optimism: 'ETH' };

        function updatePriceUnit() {
            var currency = document.getElementById('bt_currency').value;
            var chain    = document.getElementById('bt_chain').value;
            document.getElementById('bt_price_unit').textContent =
                currency === 'ERC20' ? 'USDC' : (BT_NATIVE_SYMBOL[chain] || 'POL');
        }

        document.getElementById('bt_currency').addEventListener('change', function() {
            document.getElementById('bt_token_row').style.display = this.value === 'ERC20' ? '' : 'none';
            if (this.value === 'ERC20') {
                var chain = document.getElementById('bt_chain').value;
                var field = document.getElementById('bt_payment_token');
                if (!field.value && USDC_BY_CHAIN[chain]) field.value = USDC_BY_CHAIN[chain];
            }
            updatePriceUnit();
        });

        document.getElementById('bt_chain').addEventListener('change', function() {
            var nativeLabels = { polygon: 'POL (native)', base: 'ETH (native)', arbitrum: 'ETH (native)', optimism: 'ETH (native)' };
            document.querySelector('#bt_currency option[value="POL"]').textContent = nativeLabels[this.value] || 'POL (native)';
            if (document.getElementById('bt_currency').value === 'ERC20' && USDC_BY_CHAIN[this.value]) {
                document.getElementById('bt_payment_token').value = USDC_BY_CHAIN[this.value];
            }
            updatePriceUnit();
        });
        </script>
        <?php
    }

    public static function render_sync_box( WP_Post $post ): void {
        $backend_id = get_post_meta( $post->ID, '_spntn_nft_backend_event_id', true );
        $slug       = get_post_meta( $post->ID, '_spntn_nft_slug',             true );
        ?>
        <?php if ( $backend_id ) : ?>
            <p>✅ <?php esc_html_e( 'Synced to backend', 'blockchain-ticketing' ); ?></p>
            <p><strong><?php esc_html_e( 'Backend ID:', 'blockchain-ticketing' ); ?></strong><br>
                <code style="word-break:break-all;"><?php echo esc_html( $backend_id ); ?></code></p>
            <?php if ( $slug ) : ?>
                <p><strong><?php esc_html_e( 'Slug:', 'blockchain-ticketing' ); ?></strong> <code><?php echo esc_html( $slug ); ?></code></p>
            <?php endif; ?>
            <p class="description"><?php esc_html_e( 'Re-save to sync changes.', 'blockchain-ticketing' ); ?></p>
        <?php else : ?>
            <p class="description"><?php esc_html_e( 'Will sync to backend when published.', 'blockchain-ticketing' ); ?></p>
        <?php endif; ?>
        <hr>
        <p><strong><?php esc_html_e( 'Shortcode:', 'spntn-nft-ticketing' ); ?></strong><br>
            <code>[spntn_nft_event id="<?php echo esc_html( $post->ID ); ?>"]</code></p>
        <?php
    }

    // ─── Save meta + sync to backend ─────────────────────────────────────────

    public static function save_meta( int $post_id, WP_Post $post ): void {
        if ( ! isset( $_POST['bt_event_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['bt_event_nonce'] ) ), 'bt_save_event' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( $post->post_status === 'auto-draft' ) return;

        $fields = [
            '_spntn_nft_date'             => 'sanitize_text_field',
            '_spntn_nft_location'         => 'sanitize_text_field',
            '_spntn_nft_currency'         => 'sanitize_text_field',
            '_spntn_nft_payment_token'    => 'sanitize_text_field',
            '_spntn_nft_total_supply'     => 'absint',
            '_spntn_nft_organizer_wallet' => 'sanitize_text_field',
            '_spntn_nft_contract_address' => 'sanitize_text_field',
            '_spntn_nft_chain'            => 'sanitize_text_field',
        ];

        foreach ( $fields as $key => $sanitizer ) {
            // Strip only the leading underscore to produce the form field name.
            // e.g. '_bt_total_supply' → 'bt_total_supply'
            // NOTE: ltrim($key, '_bt_') must NOT be used here — it treats the
            //       second arg as a character mask, not a prefix, and strips the
            //       't' from 'total', yielding 'bt_otal_supply'.
            $post_key = ltrim( $key, '_' );
            if ( isset( $_POST[ $post_key ] ) ) {
                $value = wp_unslash( $_POST[ $post_key ] );
                $value = call_user_func( $sanitizer, $value );
                update_post_meta( $post_id, $key, $value );
            }
        }

        // Convert human-readable price to wei before storing.
        if ( isset( $_POST['spntn_nft_price'] ) && '' !== $_POST['spntn_nft_price'] ) {
            $currency_for_price = sanitize_text_field( wp_unslash( $_POST['spntn_nft_currency'] ?? 'POL' ) );
            $price_human        = sanitize_text_field( wp_unslash( $_POST['spntn_nft_price'] ) );
            update_post_meta( $post_id, '_spntn_nft_price', spntn_nft_to_wei( $price_human, $currency_for_price ) );
        }

        // Convert datetime-local to ISO string for backend
        if ( ! empty( $_POST['spntn_nft_date'] ) ) {
            $dt = sanitize_text_field( wp_unslash( $_POST['spntn_nft_date'] ) );
            update_post_meta( $post_id, '_spntn_nft_date', $dt );
        }

        // Sync to backend (only if published)
        if ( $post->post_status === 'publish' ) {
            SPNTN_NFT_Admin::sync_event_to_backend( $post_id );
        }
    }
}
