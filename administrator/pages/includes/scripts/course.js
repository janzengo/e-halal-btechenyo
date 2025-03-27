$(function(){
    // Initialize DataTable
    const courseTable = $('#coursesTable').DataTable({
        responsive: true
    });

    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

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

    // Function to get course data
    function getRow(id) {
        if (!isModificationAllowed()) return;

        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/CourseController.php`,
            data: {id: id, action: 'get'},
            dataType: 'json',
            success: function(response) {
                if (!response.error) {
                    $('.course_id').val(response.data.id);
                    $('#edit_description').val(response.data.description);
                    $('.course_description').html(response.data.description);
                } else {
                    showError(response.message);
                }
            },
            error: handleServerError
        });
    }

    // Prevent modal from showing if modifications are not allowed
    $('#addnew, #edit').on('show.bs.modal', function(e) {
        if (!isModificationAllowed()) {
            e.preventDefault();
            e.stopPropagation();
            return false;
        }
    });

    // Edit button click handler
    $(document).on('click', '.edit-course', function(e) {
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
    $(document).on('click', '.delete-course', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        if (!isModificationAllowed()) {
            showWarning(getModificationMessage());
            return;
        }

        const id = $(this).data('id');
        getRow(id);

        Swal.fire({
            title: 'Delete Course',
            text: 'Are you sure you want to delete this course?',
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
                    url: `${BASE_URL}administrator/pages/includes/modals/controllers/CourseController.php`,
                    data: {
                        id: id,
                        action: 'delete'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.error) {
                            Swal.fire(
                                'Deleted!',
                                'Course has been deleted.',
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