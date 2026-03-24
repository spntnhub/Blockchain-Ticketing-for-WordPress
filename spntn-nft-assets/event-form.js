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
