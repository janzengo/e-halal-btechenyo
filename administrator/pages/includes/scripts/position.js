$(function(){
    // Initialize DataTable
    const positionTable = $('#positionsTable').DataTable({
        responsive: true,
        order: [[0, 'asc']], // Sort by priority column (first column) in ascending order
        columnDefs: [
            { 
                targets: 0, // Priority column
                width: '80px'
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

    // Function to show error message using SweetAlert
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message || 'An error occurred',
            showConfirmButton: true
        });
    }

    // Function to show warning message using SweetAlert
    function showWarning(message) {
        Swal.fire({
            icon: 'warning',
            title: 'Access Denied',
            text: message,
            showConfirmButton: true
        });
    }

    // Function to handle server errors
    function handleServerError(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
        showError('Server error occurred. Please try again.');
    }

    // Function to get position data
    function getRow(id) {
        if (!isModificationAllowed()) return;

        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/PositionController.php`,
            data: {id: id, action: 'get'},
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    const data = response.data;
                    $('.position_id').val(data.id);
                    $('#edit_description').val(data.description);
                    $('#edit_max_vote').val(data.max_vote);
                    $('#edit_priority').val(data.priority);
                    $('.position_description').html(data.description);
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    }

    // Prevent modal from showing if modifications are not allowed
    $('#addnew, #edit, #delete').on('show.bs.modal', function(e) {
        if (!isModificationAllowed()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Edit button click handler
    $(document).on('click', '.edit-position', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!isModificationAllowed()) {
            showWarning(getModificationMessage());
            return;
        }
        
        $('#edit').modal('show');
        getRow($(this).data('id'));
    });

    // Delete button click handler
    $(document).on('click', '.delete-position', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!isModificationAllowed()) {
            showWarning(getModificationMessage());
            return;
        }

        const id = $(this).data('id');
        getRow(id);

        Swal.fire({
            title: 'Delete Position',
            text: 'Are you sure you want to delete this position?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: `${BASE_URL}administrator/pages/includes/modals/controllers/PositionController.php`,
                    data: {
                        id: id,
                        action: 'delete'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            Swal.fire(
                                'Deleted!',
                                'Position has been deleted.',
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            showError(response.message);
                        }
                    },
                    error: handleServerError
                });
            }
        });
    });

    // Form submission handler function
    function handleFormSubmission(form) {
        if (!isModificationAllowed()) {
            showWarning(getModificationMessage());
            return;
        }

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    location.reload();
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    }

    // Add form submission
    $('#addnew form').submit(function(e) {
        e.preventDefault();
        handleFormSubmission($(this));
    });

    // Edit form submission
    $('#edit form').submit(function(e) {
        e.preventDefault();
        handleFormSubmission($(this));
    });
}); 