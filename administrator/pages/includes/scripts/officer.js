$(function() {
    // Initialize DataTable
    const officerTable = $('#officerTable').DataTable({
        responsive: true,
        order: [[4, 'desc']], // Sort by created date in descending order
        columnDefs: [
            { 
                targets: 5, // Actions column
                orderable: false
            }
        ]
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip({
        template: '<div class="tooltip" role="tooltip"><div class="tooltip-inner" style="background-color: #000;"></div></div>',
        placement: 'top',
        trigger: 'hover',
        container: 'body'
    });

    // Function to show error message using SweetAlert
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message || 'An error occurred',
            showConfirmButton: true
        });
    }

    // Function to show success message using SweetAlert
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            showConfirmButton: true,
            timer: 2000
        });
    }

    // Function to handle server errors
    function handleServerError(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
        showError('Server error occurred. Please try again.');
    }

    // Function to get officer data
    function getRow(id) {
        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/OfficerController.php`,
            data: {id: id, action: 'get'},
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    const data = response.data;
                    $('.admin_id').val(data.id);
                    $('#edit_username').val(data.username);
                    $('#edit_firstname').val(data.firstname);
                    $('#edit_lastname').val(data.lastname);
                    $('#edit_gender').val(data.gender);
                    $('#edit_role').val(data.role);
                    $('.fullname').html(data.firstname + ' ' + data.lastname);
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    }

    // Edit button click handler
    $(document).on('click', '.edit', function(e) {
        e.preventDefault();
        $('#edit').modal('show');
        getRow($(this).data('id'));
    });

    // Delete button click handler
    $(document).on('click', '.delete', function(e) {
        e.preventDefault();
        const id = $(this).data('id');
        getRow(id);

        Swal.fire({
            title: 'Delete Officer',
            text: 'Are you sure you want to delete this officer?',
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
                    url: `${BASE_URL}administrator/pages/includes/modals/controllers/OfficerController.php`,
                    data: {
                        id: id,
                        action: 'delete'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            showSuccess(response.message);
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
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
        const formData = new FormData(form[0]);
        
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    showSuccess(response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
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

    // Delete form submission
    $('#delete form').submit(function(e) {
        e.preventDefault();
        handleFormSubmission($(this));
    });
}); 