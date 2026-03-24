(function($){
    var backendUrl = window.btApiModalBackendUrl || 'https://nft-saas-production.up.railway.app';

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
            data: JSON.stringify({ email: email, site_url: siteUrl }),
            success: function(res) {
                $('#bt-modal-result').text('API key sent! Check your email.');
                $('#bt-modal-submit').prop('disabled', false).text('Send');
            },
            error: function() {
                $('#bt-modal-result').text('Error sending API key.');
                $('#bt-modal-submit').prop('disabled', false).text('Send');
            }
        });
    });
})(jQuery);
