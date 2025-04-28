<?php
// Prevent direct access to this file
if (!defined('BASE_URL')) {
    die('Direct access to this file is not allowed');
}
?>

<!-- Complete Election Password Modal -->
<div class="modal fade" id="completeElectionPasswordModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
                <h4 class="modal-title"><b>Confirm End Election</b></h4>
            </div>
            <form class="form-horizontal" method="POST" action="<?php echo BASE_URL; ?>administrator/pages/includes/modals/controllers/ConfigController.php">
                <div class="modal-body">
                    <input type="hidden" name="action" value="complete_election">
                    <div class="form-group">
                        <label for="completeElectionPassword" class="col-sm-3 control-label">Password</label>
                        <div class="col-sm-9">
                            <input type="password" class="form-control" id="completeElectionPassword" name="password" placeholder="Enter your password" required autocomplete="current-password">
                            <small class="help-block">Enter your password to confirm ending the election</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal">
                        <i class="fa fa-close"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-danger btn-flat" id="confirmCompleteElection">
                        <i class="fa fa-check"></i> Complete Election
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Modal Success/Error Alert Container -->
<div id="modalAlertContainer"></div>

<!-- Modal Processing Scripts -->
<script>
$(document).ready(function() {
    $('#completeElectionForm').on('submit', function(e) {
        e.preventDefault();
        
        const $form = $(this);
        const $submitBtn = $('#confirmCompleteElection');
        const $modal = $('#completeElectionPasswordModal');
        const $password = $('#completeElectionPassword');
        
        // Validate password
        if (!$password.val()) {
            $password.addClass('is-invalid');
            return false;
        }
        $password.removeClass('is-invalid');
        
        // Disable submit button and show loading state
        $submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
        
        $.ajax({
            url: $form.attr('action'),
            method: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Show success message
                    $('#modalAlertContainer').html(`
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h4><i class="icon fa fa-check"></i> Success!</h4>
                            ${response.message}
                        </div>
                    `);
                    
                    // Close modal and refresh page after delay
                    setTimeout(function() {
                        $modal.modal('hide');
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    $('#modalAlertContainer').html(`
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h4><i class="icon fa fa-warning"></i> Error!</h4>
                            ${response.message}
                        </div>
                    `);
                    
                    // Reset submit button
                    $submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Complete Election');
                }
            },
            error: function() {
                // Show error message for server/network errors
                $('#modalAlertContainer').html(`
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <h4><i class="icon fa fa-warning"></i> Error!</h4>
                        An error occurred while processing your request. Please try again.
                    </div>
                `);
                
                // Reset submit button
                $submitBtn.prop('disabled', false).html('<i class="fa fa-check"></i> Complete Election');
            }
        });
    });

    // Clear password and validation state on modal close
    $('#completeElectionPasswordModal').on('hidden.bs.modal', function() {
        $('#completeElectionPassword').val('').removeClass('is-invalid');
    });
});
</script>
