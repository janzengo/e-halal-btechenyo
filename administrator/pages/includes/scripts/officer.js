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
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true
        }).then(() => {
            location.reload();
        });
    }

    // Function to handle server errors
    function handleServerError(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
        showError('Server error occurred. Please try again.');
    }

    // Function to validate email
    function validateEmail(email) {
        if (!email) return true; // Empty email is valid (optional field)
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
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
                    $('#edit_email').val(data.email || '');
                    $('.fullname').html(data.firstname + ' ' + data.lastname);
                    // Store current role for comparison
                    $('#edit form').data('current-role', data.role);
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
        
        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/OfficerController.php`,
            data: {id: id, action: 'get'},
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    const data = response.data;
                    const officerName = data.firstname + ' ' + data.lastname;
                    
                    Swal.fire({
                        title: 'Delete Officer',
                        text: `Are you sure you want to delete ${officerName}?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete',
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
                                        showSuccess(`${officerName} has been deleted successfully.`);
                                    } else {
                                        showError(response.message);
                                    }
                                },
                                error: handleServerError
                            });
                        }
                    });
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    });

    // Form validation function
    function validateForm(form) {
        const email = form.find('input[name="email"]').val();
        if (email && !validateEmail(email)) {
            showError('Please enter a valid email address');
            return false;
        }
        return true;
    }

    // Form submission handler function
    function handleFormSubmission(form) {
        if (!validateForm(form)) {
            return;
        }

        const submitBtn = form.find('button[type="submit"]');
        const originalContent = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Saving...');

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
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError,
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false)
                        .html(originalContent);
            }
        });
    }

    // Add form submission
    $('#addnew form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const officerName = $('#add_firstname').val() + ' ' + $('#add_lastname').val();
        const role = $('#add_role').val();

        if (role === 'head') {
            Swal.fire({
                title: 'Add Head Officer',
                text: `Warning: You are about to add ${officerName} as a Head Officer. Head Officers have full permissions to modify election settings and manage the entire system. Are you sure you want to proceed?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, add as Head',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleFormSubmission(form);
                }
            });
        } else {
            handleFormSubmission(form);
        }
    });

    // Edit form submission
    $('#edit form').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const officerName = $('#edit_firstname').val() + ' ' + $('#edit_lastname').val();
        const newRole = $('#edit_role').val();
        const currentRole = $(this).data('current-role');

        if (newRole === 'head' && currentRole !== 'head') {
            Swal.fire({
                title: 'Change to Head Officer',
                text: `Warning: You are about to change ${officerName}'s role to Head Officer. Head Officers have full permissions to modify election settings and manage the entire system. Are you sure you want to proceed?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#dc3545',
                confirmButtonText: 'Yes, change to Head',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    handleFormSubmission(form);
                }
            });
        } else {
            handleFormSubmission(form);
        }
    });

    // Remove delete form submission since we're handling it in the delete button click handler
    $('#delete form').off('submit');

    // Real-time email validation
    $('input[name="email"]').on('blur', function() {
        const email = $(this).val();
        if (email && !validateEmail(email)) {
            $(this).addClass('is-invalid');
            $(this).next('.text-muted').hide();
            if (!$(this).next('.invalid-feedback').length) {
                $(this).after('<div class="invalid-feedback">Please enter a valid email address</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
            $(this).next('.text-muted').show();
        }
    });
}); 