$(document).ready(function() {
    $('.modalbox').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        $('#ideaModal').modal('show');
        $('#modalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
        $.get(contentUrl, function(data) {
            $('#modalContent').html(data);
        });
    });

    $('.modalbox-comments').on('click', function(event) {
        event.preventDefault();
        var contentUrl = $(this).data('content-url');
        $('#commentsModal').modal('show');
        $('#commentsModalContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading comments...</div>');
        $.get(contentUrl, function(data) {
            $('#commentsModalContent').html(data);
        });
    });

    // Ensure only one modal is shown at a time
    $('.modal').on('hidden.bs.modal', function() {
        $('.modal').modal('hide');
    });
});


// Handle form submission inside modal
$('#ideaModal').on('submit', 'form', function(event) {
    event.preventDefault();
    
    var form = $(this);
    var submitBtn = form.find('button[type="submit"]');
    
    // Disable submit button to prevent multiple submissions
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');
    
    $.ajax({
        type: form.attr('method'),
        url: form.attr('action'),
        data: form.serialize(),
        success: function(response) {
            $('#ideaModal').modal('hide');
            // Handle success response as needed
            // For example, update the relevant part of the page
            $('#refreshContent').html(response);
        },
        error: function(xhr) {
            // Handle errors
            console.log(xhr.responseText);
        },
        complete: function() {
            // Re-enable submit button after request completes (success or error)
            submitBtn.prop('disabled', false).html('Submit');
        }
    });
});
