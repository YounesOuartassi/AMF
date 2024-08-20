$(document).ready(function() {
    // Hide registration modal and show login modal on button click
    $('#switchToLogin').on('click', function() {
        $('.iden').modal('hide'); // Hide the registration modal
        $('.conn').modal('show'); // Show the login modal
    });
});
