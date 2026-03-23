=== SPNTN NFT Ticketing ===
Contributors:      spntn
Tags:              nft, blockchain, ticketing, polygon, web3, spntn
Requires at least: 6.0
Tested up to:      6.9
Requires PHP:      7.4
Stable tag:        1.0.1
License:           GPL v2 or later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Sell and verify NFT event tickets on Polygon. Each ticket is a unique ERC-721 NFT minted to the buyer's wallet. 3% protocol fee per sale.

== Description ==

**SPNTN NFT Ticketing** lets event organizers sell verifiable NFT tickets directly from their WordPress site — no centralized ticketing platform required.

**How it works:**

1. Create an event in WP Admin → set date, location, price (e.g. `5` for 5 POL), supply.
2. Publish the event → automatically synced to the backend.
3. Add `[blockchain_event id="POST_ID"]` to any page.
4. Buyers connect MetaMask and purchase. Each ticket is minted as a unique ERC-721 NFT on Polygon.
5. After purchase, the buyer receives a QR-code ticket stored in their browser.
6. At the event, staff use `[blockchain_checkin]` on a mobile browser to scan and verify QR codes.

= Features =

* On-chain NFT tickets (ERC-721, Polygon)
* Accepts POL (native) and USDC (ERC-20)
* QR-code generation for each ticket
* Mobile-friendly ticket verification (camera-based QR scanner)
* Fraud-proof: on-chain ownership verified at check-in
* Double-entry prevention via database
* 3% protocol fee; 97% goes to the event organizer
* MetaMask + WalletConnect compatible

== External services ==

The Blockchain Ticketing plugin interacts with several external services:

- **NFT SaaS Backend API**
  - URL: https://nft-saas-production.up.railway.app
  - Purpose: Handles ticket minting, check-in verification, event management, webhook events, and IPFS uploads. All plugin features rely on this API for core operations.

- **Polygon Mainnet**
  - Purpose: Ticket minting and verification are performed on Polygon Mainnet via smart contracts. The plugin interacts with Polygon using ethers.js.

- **IPFS**
  - Purpose: Ticket metadata and media files are uploaded to IPFS for decentralized storage.

- **Webhook Integrations**
  - Purpose: Organizers can configure webhook URLs to receive real-time HTTP POST events from the backend when ticket status changes.

- **Explorer Links**
  - Purpose: Plugin provides links to Polygon block explorers (e.g., Polygonscan) for transaction and ticket verification.

API keys and sensitive credentials are stored server-side and never exposed to frontend users.

Data sent: event details (name, date, location, price), wallet addresses, transaction hashes.  
Data transmitted when: on event publish, on ticket purchase, on check-in scan.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin through the Plugins menu.
3. Go to **Settings → Blockchain Ticketing** and enter your API key.
4. Create an event under **Events → Add New** in WP Admin.
5. Publish the event; add the shortcode to a ticket sales page.

= Getting an API Key =

Click **Get API Key** on the settings page, enter your email, and your key will appear instantly. Free to use. Email info@spntn.com for support.

== Frequently Asked Questions ==

= Is the plugin free? =
Yes, the plugin is free. The protocol charges a 3% fee per ticket sold on-chain; 97% goes to the organizer.

= Which wallets are supported? =
MetaMask and any EVM-compatible browser wallet (WalletConnect support coming).

= Which blockchain is used? =
Polygon Mainnet. Low gas fees, fast confirmations.

= Can tickets be resold? =
Yes. NFT tickets live in the buyer's wallet and can be transferred or resold on any NFT marketplace.

= Do buyers need a crypto wallet? =
Yes. Buyers need MetaMask (or equivalent) to purchase an NFT ticket.

== Screenshots ==

1. Ticket purchase widget on the event page.
2. QR ticket displayed after successful purchase.
3. Mobile check-in scanner interface.
4. Event creation in WP Admin.

== Changelog ==

= 1.0.1 =
* Price input now accepts human-readable values (e.g. 5 for 5 POL, 10 for 10 USDC).
* USDC token address auto-filled when ERC-20 currency is selected.
* USDC address updates automatically when the chain is changed.
* Removed redundant contract address fields from admin UI.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
UX improvements: human-readable price input, automatic USDC address filling.

= 1.0.0 =
Initial release.
