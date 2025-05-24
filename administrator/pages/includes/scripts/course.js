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

    // Function to show success message using SweetAlert
    function showSuccess(message) {
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: message,
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            location.reload();
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

    // Function to get course data
    function getRow(id) {
        if (!isModificationAllowed()) return;

        $.ajax({
            type: 'POST',
            url: `${BASE_URL}administrator/pages/includes/modals/controllers/CourseController.php`,
            data: {id: id, action: 'get'},
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (!response.error) {
                    $('.course_id').val(response.data.id);
                    $('#edit_description').val(response.data.description);
                    $('.course_description').html(response.data.description);
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

        const courseId = $(this).data('id');
        const courseName = $(this).closest('tr').find('td:first').text();
        
        Swal.fire({
            title: 'Delete Course',
            text: `Are you sure you want to delete course "${courseName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: 'POST',
                    url: `${BASE_URL}administrator/pages/includes/modals/controllers/CourseController.php`,
                    data: { id: courseId, action: 'delete' },
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (!response.error) {
                            showSuccess('Course has been deleted successfully!');
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr) {
                        handleServerError(xhr);
                    }
                });
            }
        });
    });

    // Form submission handler function
    function handleFormSubmission(form, action) {
        if (!isModificationAllowed()) {
            showWarning(getModificationMessage());
            return;
        }

        const formData = form.serialize();
        const isEdit = action === 'edit';
        const courseDesc = isEdit ? $('#edit_description').val() : form.find('input[name="description"]').val();

        $.ajax({
            type: 'POST',
            url: form.attr('action'),
            data: formData,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (!response.error) {
                    showSuccess(`Course "${courseDesc}" has been ${isEdit ? 'updated' : 'added'} successfully!`);
                    form.closest('.modal').modal('hide');
                } else {
                    showError(response.message);
                }
            },
            error: function(xhr) {
                handleServerError(xhr, form);
            }
        });
    }

    // Add form submission
    $('#addnew form').submit(function(e) {
        e.preventDefault();
        handleFormSubmission($(this), 'add');
    });

    // Edit form submission
    $('#edit form').submit(function(e) {
        e.preventDefault();
        handleFormSubmission($(this), 'edit');
    });
}); 