/**
 * wallet.js — Blockchain Ticketing: Wallet connection helper
 * Depends on: ethers (v6 UMD)
 *
 * Supports any EVM chain defined in BT_Chains.
 * To add a new chain: add an entry here AND deploy TicketNFT.sol on that chain.
 */
(function () {
  'use strict';

  // ── Chain registry ──────────────────────────────────────────────────────────
  // Mirrors backend/src/config/chains.ts — keep in sync.
  window.BT_Chains = {
    polygon: {
      hexChainId:        '0x89',
      chainName:         'Polygon Mainnet',
      rpcUrls:           ['https://polygon-rpc.com'],
      nativeCurrency:    { name: 'POL', symbol: 'POL', decimals: 18 },
      blockExplorerUrls: ['https://polygonscan.com'],
    },
    base: {
      hexChainId:        '0x2105',
      chainName:         'Base',
      rpcUrls:           ['https://mainnet.base.org'],
      nativeCurrency:    { name: 'ETH', symbol: 'ETH', decimals: 18 },
      blockExplorerUrls: ['https://basescan.org'],
    },
    arbitrum: {
      hexChainId:        '0xa4b1',
      chainName:         'Arbitrum One',
      rpcUrls:           ['https://arb1.arbitrum.io/rpc'],
      nativeCurrency:    { name: 'ETH', symbol: 'ETH', decimals: 18 },
      blockExplorerUrls: ['https://arbiscan.io'],
    },
    optimism: {
      hexChainId:        '0xa',
      chainName:         'Optimism',
      rpcUrls:           ['https://mainnet.optimism.io'],
      nativeCurrency:    { name: 'ETH', symbol: 'ETH', decimals: 18 },
      blockExplorerUrls: ['https://optimistic.etherscan.io'],
    },
  };

  // ── Wallet object ───────────────────────────────────────────────────────────
  window.BT_Wallet = {
    provider: null,
    signer:   null,
    address:  null,

    async connect() {
      if (!window.ethereum) {
        throw new Error('MetaMask not found. Please install MetaMask to buy NFT tickets.');
      }
      const provider = new ethers.BrowserProvider(window.ethereum);
      await provider.send('eth_requestAccounts', []);
      const signer  = await provider.getSigner();
      this.provider = provider;
      this.signer   = signer;
      this.address  = await signer.getAddress();
      return this.address;
    },

    /**
     * Switch MetaMask to the given chain.
     * @param {string} chainSlug  e.g. 'polygon', 'base', 'arbitrum', 'optimism'
     */
    async ensureChain(chainSlug) {
      const chain = window.BT_Chains[chainSlug] || window.BT_Chains.polygon;
      try {
        await window.ethereum.request({
          method: 'wallet_switchEthereumChain',
          params: [{ chainId: chain.hexChainId }],
        });
      } catch (e) {
        // Chain not added in MetaMask yet — add it automatically
        if (e.code === 4902) {
          await window.ethereum.request({
            method: 'wallet_addEthereumChain',
            params: [{
              chainId:           chain.hexChainId,
              chainName:         chain.chainName,
              rpcUrls:           chain.rpcUrls,
              nativeCurrency:    chain.nativeCurrency,
              blockExplorerUrls: chain.blockExplorerUrls,
            }],
          });
        } else {
          throw e;
        }
      }
    },

    /** Backward-compat alias */
    async ensurePolygon() {
      return this.ensureChain('polygon');
    },

    /**
     * Return the block-explorer transaction URL for a given chain.
     * @param {string} chainSlug
     * @param {string} txHash
     */
    getExplorerTxUrl(chainSlug, txHash) {
      const chain = window.BT_Chains[chainSlug] || window.BT_Chains.polygon;
      return chain.blockExplorerUrls[0] + '/tx/' + txHash;
    },

    shortAddress(addr) {
      return addr ? addr.slice(0, 6) + '…' + addr.slice(-4) : '';
    },
  };
})();
