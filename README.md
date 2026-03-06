# Blockchain Ticketing for WordPress

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![License](https://img.shields.io/badge/license-GPL--2.0-green)
![Chain](https://img.shields.io/badge/chain-Polygon-8247e5)

Sell and verify NFT event tickets directly from your WordPress site.  
Each ticket is a unique ERC-721 token on Polygon — impossible to forge, easy to verify.

---

## How It Works

```
Organizer creates event in WP Admin
     ↓
Publishes → backend syncs automatically
     ↓
Add [blockchain_event id="123"] to any page
     ↓
Buyer connects MetaMask → buys ticket
     ↓
NFT minted to buyer's wallet (3% fee → protocol, 97% → organizer)
     ↓
Buyer receives QR code ticket
     ↓
Staff scans QR at event door with [blockchain_checkin]
     ↓
System verifies on-chain + marks used → entry granted / denied
```

---

## Features

- **NFT tickets** — ERC-721 on Polygon, lazily minted at purchase
- **POL + USDC** — accepts native POL or USDC payment
- **QR tickets** — auto-generated after purchase, stored in browser
- **Mobile check-in** — camera-based QR scanner for event staff
- **Fraud-proof** — ownership verified on-chain at check-in
- **Double-entry prevention** — database tracks used status
- **3% protocol fee**, 97% to organizer — set your own wallet

---

## Quick Start

### 1. Install Plugin

Upload the `blockchain-ticketing` folder to `/wp-content/plugins/` and activate.

### 2. Get API Key (Free)

Go to **Settings → Blockchain Ticketing** → click **Get API Key** → enter your email.  
Your key appears instantly. Free forever.  
Support: info@spntn.com

### 3. Create an Event

**Events → Add New** in WP Admin. Fill in:

| Field | Description |
|---|---|
| Title | Event name |
| Content | Event description |
| Event Date | Date and time |
| Location | Venue / city |
| Ticket Price | Human-readable — e.g. `5` for 5 POL, `10` for 10 USDC |
| Currency | POL (native) or ERC-20 (USDC auto-filled) |
| Total Supply | 0 = unlimited |
| Organizer Wallet | Receives 97% of sales |

Publish → automatically synced to backend.

### 4. Add Ticket Page

Add this shortcode to any WordPress page:

```
[blockchain_event id="POST_ID"]
```

### 5. Check-In Interface

Add to a page accessible by event staff (mobile browser):

```
[blockchain_checkin]
```

---

## Smart Contract

**TicketNFT.sol** — ERC-721 on Polygon

| | |
|---|---|
| Standard | ERC-721 (one token per ticket) |
| Protocol fee | 3% per ticket sale |
| Accepted payment | Native POL, USDC (ERC-20) |
| Signature scheme (native) | `keccak256(organizer, eventId, tokenURI, price)` |
| Signature scheme (ERC-20) | `keccak256(organizer, eventId, tokenURI, price, token)` |
| Deployed (Polygon) | `0x20C8c6e569c00A5C0E660165f4d513D61424541E` |

The shared contract on Polygon is used by default — no setup needed.
You can deploy your own with:

```bash
cd contracts
npx hardhat run scripts/deploy-ticketnft.js --network polygon
```

---

## Pricing

| | |
|---|---|
| Plugin | **Free** |
| API key | **Free** |
| Protocol fee | **3% per ticket sale** (on-chain, automatic) |
| Organizer receives | **97% of each ticket price** |

No subscriptions. No monthly fees. The protocol only earns when you sell tickets.

---

## Plugin Directory

Coming soon to wordpress.org/plugins/blockchain-ticketing-for-wordpress

---

## License

GPL v2 or later — https://www.gnu.org/licenses/gpl-2.0.html
