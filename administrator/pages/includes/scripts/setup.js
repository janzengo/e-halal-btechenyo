$(document).ready(function() {
    // Get base URL from meta tag or PHP constant
    const baseUrl = $('meta[name="base-url"]').attr('content') || BASE_URL || '';
    const hasOfficer = $('meta[name="has-officer"]').attr('content') === 'true';
    
    // Setup progress tracking
    let setupProgress = {
        generalSettings: false,
        settingsSaved: false
    };

    // Initialize checklist state
    function initializeChecklist() {
        const electionName = $('#election_name').val().trim();
        const endTime = $('#end_time').val();
        
        // If both fields have values, consider them as saved
        if (electionName && endTime) {
            setupProgress.generalSettings = true;
            setupProgress.settingsSaved = true;
            
            // Update election name checklist item
            $('.checklist-item:contains("election name")')
                .addClass('completed text-success')
                .find('i')
                .removeClass('far fa-square')
                .addClass('far fa-check-square');
            
            // Update end time checklist item
            $('.checklist-item:contains("end date")')
                .addClass('completed text-success')
                .find('i')
                .removeClass('far fa-square')
                .addClass('far fa-check-square');
            
            // Enable complete setup button
            $('#completeSetupBtn').prop('disabled', false);
        }
        // Officer checklist item
        if (hasOfficer) {
            $('.checklist-item:contains("Add election officers")')
                .addClass('completed text-success')
                .find('i')
                .removeClass('far fa-square')
                .addClass('far fa-check-square');
        } else {
            $('.checklist-item:contains("Add election officers")')
                .removeClass('completed text-success')
                .find('i')
                .removeClass('far fa-check-square')
                .addClass('far fa-square');
        }
    }

    // Call initialization immediately
    initializeChecklist();

    // Enhanced tab functionality
    $('.custom-tab-btn').on('click', function(e) {
        e.preventDefault();
        const targetTab = $(this).data('tab');
        
        // Remove active class from all buttons and content
        $('.custom-tab-btn').removeClass('active');
        $('.custom-tab-content').removeClass('active');
        
        // Add active class to clicked button and corresponding content
        $(this).addClass('active');
        $(`#${targetTab}`).addClass('active');
    });

    // Fix modal issues
    $(document).on('show.bs.modal', '.modal', function () {
        $('body').css({
            'padding-right': '0',
            'height': '',
            'min-height': ''
        });
    });

    $(document).on('hidden.bs.modal', '.modal', function () {
        $('body').css({
            'padding-right': '0',
            'height': '',
            'min-height': ''
        });
        // Remove any inline styles Bootstrap might have added
        $('body').attr('style', '');
    });

    // Form validation
    function validateField(input) {
        const value = input.val().trim();
        
        if (value === '') {
            input.addClass('is-invalid').removeClass('is-valid');
            input.next('.validation-message').remove();
            input.after('<div class="validation-message error">This field is required</div>');
            return false;
        } else if (input.attr('id') === 'end_time') {
            const endTime = new Date(value);
            const now = new Date();
            
            if (endTime <= now) {
                input.addClass('is-invalid').removeClass('is-valid');
                input.next('.validation-message').remove();
                input.after('<div class="validation-message error">End time must be in the future</div>');
                return false;
            }
        }
        
        input.removeClass('is-invalid').addClass('is-valid');
        input.next('.validation-message').remove();
        return true;
    }

    // Handle general settings form submission
    $('#generalSettingsForm').on('submit', function(e) {
        e.preventDefault();
        
        const electionName = $('#election_name');
        const endTime = $('#end_time');
        
        if (!validateField(electionName) || !validateField(endTime)) {
            return false;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

        // Submit form via AJAX
        $.ajax({
            url: $('#generalSettingsForm').attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                setupProgress.settingsSaved = true;
                updateProgress();
                updateChecklist(true);
                
                // Show success message
                Swal.fire({
                    title: 'Settings Saved',
                    text: response.message || 'Election settings have been saved successfully.',
                    icon: 'success',
                    confirmButtonColor: '#3c8dbc'
                });

                // Enable complete setup button if all requirements are met
                if (setupProgress.generalSettings && setupProgress.settingsSaved) {
                    $('#completeSetupBtn').prop('disabled', false);
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to save settings. Please try again.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    errorMessage = response.error || errorMessage;
                } catch (e) {
                    errorMessage = xhr.responseText || errorMessage;
                }
                
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#3c8dbc'
                });
                
                // Reset progress state
                setupProgress.settingsSaved = false;
                updateProgress();
                updateChecklist(false);
            },
            complete: function() {
                // Restore button state
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    function updateProgress() {
        // Check general settings
        const electionName = $('#election_name').val().trim();
        const endTime = $('#end_time').val();
        setupProgress.generalSettings = electionName !== '' && endTime !== '';

        // Update checklist items
        updateChecklist(setupProgress.settingsSaved);
        
        // Enable/disable complete setup button based on all conditions
        const allComplete = setupProgress.generalSettings && setupProgress.settingsSaved;
        $('#completeSetupBtn').prop('disabled', !allComplete);
    }

    // Function to update checklist items based on form completion and save status
    function updateChecklist(saved) {
        const electionName = $('#election_name').val().trim();
        const endTime = $('#end_time').val();
        
        // Update election name checklist item
        if (electionName && saved) {
            $('.checklist-item:contains("election name")').addClass('completed')
                .find('i').removeClass('far fa-square').addClass('far fa-check-square');
        } else {
            $('.checklist-item:contains("election name")').removeClass('completed')
                .find('i').removeClass('far fa-check-square').addClass('far fa-square');
        }
        
        // Update end time checklist item
        if (endTime && saved) {
            $('.checklist-item:contains("end date")').addClass('completed')
                .find('i').removeClass('far fa-square').addClass('far fa-check-square');
        } else {
            $('.checklist-item:contains("end date")').removeClass('completed')
                .find('i').removeClass('far fa-check-square').addClass('far fa-square');
        }

        // Update visual feedback
        if (saved) {
            $('.checklist-item.completed').addClass('text-success');
        } else {
            $('.checklist-item').removeClass('text-success');
        }

        // Officer checklist item (dynamic check)
        const officerRows = $('#officerTable tbody tr').length;
        if (officerRows > 0) {
            $('.checklist-item:contains("Add election officers")')
                .addClass('completed text-success')
                .find('i')
                .removeClass('far fa-square')
                .addClass('far fa-check-square');
        } else {
            $('.checklist-item:contains("Add election officers")')
                .removeClass('completed text-success')
                .find('i')
                .removeClass('far fa-check-square')
                .addClass('far fa-square');
        }
    }

    // Handle complete setup form submission
    $('#completeSetupForm').on('submit', function(e) {
        e.preventDefault();
        
        if (!setupProgress.generalSettings || !setupProgress.settingsSaved) {
            Swal.fire({
                title: 'Cannot Complete Setup',
                text: 'Please save your general settings first.',
                icon: 'warning',
                confirmButtonColor: '#3c8dbc'
            });
            return false;
        }

        Swal.fire({
            title: 'Complete Setup?',
            text: "This will finalize the election setup and move to pending status.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#17693a',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, complete setup'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Completing Setup...');

                // Submit to new controller endpoint
                $.ajax({
                    url: $('#completeSetupForm').attr('action'),
                    type: 'POST',
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            title: 'Setup Completed',
                            text: response.message || 'Election setup has been completed successfully.',
                            icon: 'success',
                            confirmButtonColor: '#3c8dbc'
                        }).then(() => {
                            // Redirect to configure page instead of reloading
                            window.location.href = `${BASE_URL}administrator/configure`;
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Failed to complete setup. Please try again.';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                        } catch (e) {
                            errorMessage = xhr.responseText || errorMessage;
                        }
                        
                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonColor: '#3c8dbc'
                        });
                    },
                    complete: function() {
                        // Restore button state
                        submitBtn.prop('disabled', false).html(originalText);
                    }
                });
            }
        });
    });

    // Track changes in general settings
    $('#election_name, #end_time').on('input change', function() {
        setupProgress.settingsSaved = false;
        validateField($(this));
        updateProgress();
    });
    
    // Initial state updates
    updateProgress();

    // Function to show error message using SweetAlert
    function showError(message) {
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: message || 'An error occurred',
            showConfirmButton: true
        });
    }

    // Function to show success message using SweetAlert with delay
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            showConfirmButton: false,
            timer: 3000, // 3 seconds delay
            timerProgressBar: true
        }).then(() => {
            window.location.reload(); // Reload after message
        });
    }

    // Function to handle server errors
    function handleServerError(xhr, status, error) {
        console.error('Error:', error);
        console.log('Response:', xhr.responseText);
        showError('Server error occurred. Please try again.');
    }

    // Delete handler
    $(document).on('click', '.delete', function() {
        const id = $(this).data('id');
        
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
                        } else {
                            showError(response.message);
                        }
                    },
                    error: handleServerError
                });
            }
        });
    });

    // Edit handler
    $(document).on('click', '.edit', function() {
        const id = $(this).data('id');
        
        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/OfficerController.php`,
            data: {
                id: id,
                action: 'get'
            },
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    const data = response.data;
                    $('#edit_id').val(data.id);
                    $('#edit_firstname').val(data.firstname);
                    $('#edit_lastname').val(data.lastname);
                    $('#edit_username').val(data.username);
                    $('#edit_gender').val(data.gender);
                    $('#edit_email').val(data.email);
                    $('#edit').modal('show');
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    });

    // Add form submission handler
    $('#addForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        // Get the submit button and store original content
        const submitBtn = $('#addOfficerBtn');
        const originalContent = submitBtn.html();
        
        // Show loading state
        submitBtn.prop('disabled', true)
                .html('<i class="fa fa-spinner fa-spin"></i> Saving...');
        
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#addnew').modal('hide');
                    showSuccess(response.message);
                    form[0].reset();
                    // Refresh the page to update the officer list
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
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
    });

    // Edit form submission handler
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        
        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('#edit').modal('hide');
                    showSuccess(response.message);
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    });

    // Add officer form submission handler
    $(document).on('submit', '#addOfficerForm', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#addOfficerBtn');
        var btnText = btn.find('span');
        var btnIcon = btn.find('i');

        // Disable form and show loading state
        form.find('input, button').prop('disabled', true);
        btn.addClass('disabled');
        btnIcon.removeClass('fa-save').addClass('fa-spinner fa-spin');
        btnText.text('Sending...');

        $.ajax({
            type: 'POST',
            url: '../includes/php/OfficerController.php',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.status == 'success') {
                    $('#addOfficerModal').modal('hide');
                    form[0].reset();
                    showAlert('success', 'Success!', response.message);
                    loadOfficers();
                } else {
                    showAlert('error', 'Error!', response.message);
                }
            },
            error: function(xhr, status, error) {
                showAlert('error', 'Error!', 'An error occurred while processing your request.');
            },
            complete: function() {
                // Re-enable form and restore button state
                form.find('input, button').prop('disabled', false);
                btn.removeClass('disabled');
                btnIcon.removeClass('fa-spinner fa-spin').addClass('fa-save');
                btnText.text('Save');
            }
        });
    });

    // Clear session messages when alerts are dismissed
    $('.alert-dismissible .close').on('click', function() {
        const alert = $(this).closest('.alert');
        
        // Make AJAX call to clear session messages
        $.ajax({
            url: `${baseUrl}administrator/includes/clear_session_messages.php`,
            type: 'POST',
            success: function() {
                // Fade out the alert
                alert.fadeOut('fast');
            }
        });
    });
}); 