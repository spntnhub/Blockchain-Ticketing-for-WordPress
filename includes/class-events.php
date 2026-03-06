<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class BT_Events {

    // ─── Register custom post type ────────────────────────────────────────────

    public static function register_post_type(): void {
        register_post_type( 'bt_event', [
            'labels' => [
                'name'          => __( 'Events',     'blockchain-ticketing' ),
                'singular_name' => __( 'Event',      'blockchain-ticketing' ),
                'add_new_item'  => __( 'Add New Event', 'blockchain-ticketing' ),
                'edit_item'     => __( 'Edit Event',    'blockchain-ticketing' ),
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
            'bt_event_details',
            __( 'Ticket Details', 'blockchain-ticketing' ),
            [ __CLASS__, 'render_meta_box' ],
            'bt_event',
            'normal',
            'high'
        );

        add_meta_box(
            'bt_event_sync',
            __( 'Blockchain Sync', 'blockchain-ticketing' ),
            [ __CLASS__, 'render_sync_box' ],
            'bt_event',
            'side',
            'high'
        );
    }

    public static function render_meta_box( WP_Post $post ): void {
        wp_nonce_field( 'bt_save_event', 'bt_event_nonce' );
        $opts = get_option( BT_OPTION_KEY, [] );

        $date             = get_post_meta( $post->ID, '_bt_date',             true );
        $location         = get_post_meta( $post->ID, '_bt_location',         true );
        $total_supply     = get_post_meta( $post->ID, '_bt_total_supply',     true );
        $price            = get_post_meta( $post->ID, '_bt_price',            true );
        $currency         = get_post_meta( $post->ID, '_bt_currency',         true ) ?: 'POL';
        $payment_token    = get_post_meta( $post->ID, '_bt_payment_token',    true );
        $organizer_wallet = get_post_meta( $post->ID, '_bt_organizer_wallet', true ) ?: ( $opts['organizer_wallet'] ?? '' );
        $contract_address = get_post_meta( $post->ID, '_bt_contract_address', true ) ?: ( $opts['contract_address'] ?? '' );
        ?>
        <table class="form-table" style="margin-top:0;">
            <tr>
                <th><label for="bt_date"><?php esc_html_e( 'Event Date & Time', 'blockchain-ticketing' ); ?></label></th>
                <td><input type="datetime-local" id="bt_date" name="bt_date" value="<?php echo esc_attr( $date ? date( 'Y-m-d\TH:i', strtotime( $date ) ) : '' ); ?>" required /></td>
            </tr>
            <tr>
                <th><label for="bt_location"><?php esc_html_e( 'Location', 'blockchain-ticketing' ); ?></label></th>
                <td><input type="text" id="bt_location" name="bt_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="City, Venue" /></td>
            </tr>
            <tr>
                <th><label for="bt_price"><?php esc_html_e( 'Ticket Price', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <input type="text" id="bt_price" name="bt_price" value="<?php echo esc_attr( $price ); ?>" class="regular-text" placeholder="e.g. 5000000000000000000 (= 5 POL) or 10000000 (= 10 USDC)" />
                    <p class="description"><?php esc_html_e( 'In wei for POL, or in smallest token unit for ERC-20 (USDC has 6 decimals).', 'blockchain-ticketing' ); ?></p>
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
                    <p class="description"><?php esc_html_e( 'USDC on Polygon: 0x3c499c542cEF5E3811e1192ce70d8cC03d5c3359', 'blockchain-ticketing' ); ?></p>
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
                <th><label for="bt_contract_address"><?php esc_html_e( 'Contract Address', 'blockchain-ticketing' ); ?></label></th>
                <td>
                    <input type="text" id="bt_contract_address" name="bt_contract_address" value="<?php echo esc_attr( $contract_address ); ?>" class="regular-text" placeholder="0x..." />
                    <p class="description"><?php esc_html_e( 'TicketNFT contract on Polygon. Defaults to plugin settings.', 'blockchain-ticketing' ); ?></p>
                </td>
            </tr>
        </table>

        <script>
        document.getElementById('bt_currency').addEventListener('change', function(){
            document.getElementById('bt_token_row').style.display = this.value === 'ERC20' ? '' : 'none';
        });
        </script>
        <?php
    }

    public static function render_sync_box( WP_Post $post ): void {
        $backend_id = get_post_meta( $post->ID, '_bt_backend_event_id', true );
        $slug       = get_post_meta( $post->ID, '_bt_slug',             true );
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
        <p><strong><?php esc_html_e( 'Shortcode:', 'blockchain-ticketing' ); ?></strong><br>
            <code>[blockchain_event id="<?php echo $post->ID; ?>"]</code></p>
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
            '_bt_date'             => 'sanitize_text_field',
            '_bt_location'         => 'sanitize_text_field',
            '_bt_price'            => 'sanitize_text_field',
            '_bt_currency'         => 'sanitize_text_field',
            '_bt_payment_token'    => 'sanitize_text_field',
            '_bt_total_supply'     => 'absint',
            '_bt_organizer_wallet' => 'sanitize_text_field',
            '_bt_contract_address' => 'sanitize_text_field',
        ];

        foreach ( $fields as $key => $sanitizer ) {
            // Strip only the leading underscore to produce the form field name.
            // e.g. '_bt_total_supply' → 'bt_total_supply'
            // NOTE: ltrim($key, '_bt_') must NOT be used here — it treats the
            //       second arg as a character mask, not a prefix, and strips the
            //       't' from 'total', yielding 'bt_otal_supply'.
            $post_key = ltrim( $key, '_' );
            if ( isset( $_POST[ $post_key ] ) ) {
                update_post_meta( $post_id, $key, call_user_func( $sanitizer, wp_unslash( $_POST[ $post_key ] ) ) );
            }
        }

        // Convert datetime-local to ISO string for backend
        if ( ! empty( $_POST['bt_date'] ) ) {
            $dt = sanitize_text_field( wp_unslash( $_POST['bt_date'] ) );
            update_post_meta( $post_id, '_bt_date', $dt );
        }

        // Sync to backend (only if published)
        if ( $post->post_status === 'publish' ) {
            BT_Admin::sync_event_to_backend( $post_id );
        }
    }
}
