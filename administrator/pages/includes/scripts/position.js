$(function(){
    // Initialize DataTable
    const positionTable = $('#positionsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']], // Sort by priority column in ascending order
        columnDefs: [
            {
                targets: 0, // Priority column
                visible: false // Hide the priority column
            },
            {
                targets: -1, // Actions column
                orderable: false // Disable sorting for actions column
            }
        ]
    });

    // Initialize tooltips with custom options
    $('[data-toggle="tooltip"]').tooltip({
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner" style="background-color: #000;"></div></div>',
        placement: 'top',
        trigger: 'hover',
        container: 'body'
    });

    // Function to check if modifications are allowed
    function isModificationAllowed() {
        return window.canModify || false;
    }

    // Function to get modification message
    function getModificationMessage() {
        return window.modificationMessage || 'Modifications are not allowed';
    }

    // Function to show success message
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 2000,
            timerProgressBar: true,
            showConfirmButton: false
        }).then(() => {
            window.location.reload();
        });
    }

    // Function to show error message
    function showError(message, form = null) {
        const modal = form ? form.closest('.modal') : null;
        
        // If there's a modal, keep track of its state
        const wasModalOpen = modal ? modal.is(':visible') : false;
        
        // If modal is open, hide it properly before showing error
        if (wasModalOpen) {
            modal.modal('hide');
        }

        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message,
            confirmButtonText: 'OK',
            confirmButtonColor: '#d33'
        }).then(() => {
            // Clean up any modal artifacts
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
            
            // Reset form if it exists
            if (form) {
                form[0].reset();
                form.find('button[type="submit"]').prop('disabled', false);
            }
        });
    }

    function showWarning(message) {
        return Swal.fire({
            icon: 'warning',
            title: 'Access Denied',
            text: message,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }

    function showDeleteConfirmation() {
        return Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        });
    }

    // Function to handle server errors
    function handleServerError(xhr, form = null) {
        console.error('Server Error:', xhr.responseText);
        let errorMessage = 'A server error occurred. Please try again.';
        try {
            const response = JSON.parse(xhr.responseText);
            errorMessage = response.message || errorMessage;
        } catch (e) {
            console.error('Error parsing response:', e);
        }
        showError(errorMessage, form);
    }

    // Function to get position data
    function getRow(id) {
        if (!isModificationAllowed()) return;

        $.ajax({
            type: 'POST',
            url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
            data: {id: id, action: 'get'},
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    const data = response.data;
                    $('.position_id').val(data.id);
                    $('#edit_description').val(data.description);
                    $('#edit_max_vote').val(data.max_vote);
                    $('#edit_priority').val(data.priority);
                    $('.description').html(data.description);
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                handleServerError(xhr);
            }
        });
    }

    // Prevent modal from showing if modifications are not allowed
    $('#addnew, #edit, #delete').on('show.bs.modal', function(e) {
        if (!isModificationAllowed()) {
            e.preventDefault();
            showWarning(getModificationMessage());
            return false;
        }
    });

    // Edit button click handler
    $(document).on('click', '.edit-position', function(e) {
        e.preventDefault();
        $('#edit').modal('show');
        getRow($(this).data('id'));
    });

    // Delete button click handler
    $(document).on('click', '.delete-position', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        const description = $(this).closest('tr').find('td:eq(1)').text();

        Swal.fire({
            title: 'Delete Position',
            text: `Are you sure you want to delete "${description}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
                    method: 'POST',
                    data: {
                        action: 'delete',
                        id: id
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            showSuccess('Position deleted successfully!');
                        } else {
                            showError(response.message || 'An error occurred. Please try again.');
                        }
                    },
                    error: handleServerError
                });
            }
        });
    });

    // Function to handle form submission
    function handleFormSubmission(form, action) {
        const submitBtn = form.find('button[type="submit"]');
        const originalBtnText = submitBtn.html();
        const modal = form.closest('.modal');
        
        // Disable button and show loading state
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fa fa-spinner fa-spin"></i> Processing...');

        $.ajax({
            url: baseUrl + 'administrator/pages/includes/modals/controllers/PositionController.php',
            method: 'POST',
            data: form.serialize() + '&action=' + action,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    // Hide modal and show success
                    modal.modal('hide');
                    showSuccess(action === 'add' ? 'Position added successfully!' : 
                              action === 'edit' ? 'Position updated successfully!' :
                              'Position deleted successfully!');
                } else {
                    // Re-enable button and restore text
                    submitBtn.prop('disabled', false);
                    submitBtn.html(originalBtnText);
                    showError(response.message || 'An error occurred. Please try again.', form);
                }
            },
            error: function(xhr) {
                // Re-enable button and restore text
                submitBtn.prop('disabled', false);
                submitBtn.html(originalBtnText);
                handleServerError(xhr, form);
            }
        });

        return false; // Prevent form submission
    }

    // Add Position Form Submit
    $('#addPositionForm').submit(function(e) {
        e.preventDefault();
        return handleFormSubmission($(this), 'add');
    });

    // Edit Position Form Submit
    $('#editPositionForm').submit(function(e) {
        e.preventDefault();
        return handleFormSubmission($(this), 'edit');
    });

    // Delete Position Form Submit
    $('#deletePositionForm').submit(function(e) {
        e.preventDefault();
        showDeleteConfirmation().then((result) => {
            if (result.isConfirmed) {
                return handleFormSubmission($(this), 'delete');
            }
        });
        return false;
    });

    // Handle modal cleanup
    $('.modal').on('hidden.bs.modal', function() {
        // Reset the form
        const form = $(this).find('form');
        if (form.length) {
            form[0].reset();
            form.find('button[type="submit"]').prop('disabled', false);
        }
        
        // Remove any leftover backdrops and cleanup body
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open').css('padding-right', '');
    });

    // Prevent modal from showing if there's an error alert visible
    $('#addnew').on('show.bs.modal', function(e) {
        if ($('.swal2-container').length) {
            e.preventDefault();
        }
    });
}); 