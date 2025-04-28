// archive-progress.js: Handles Archive Election progress bar/modal using SweetAlert2
$(document).ready(function() {
    $('#archive-election-btn').on('click', function(e) {
        e.preventDefault();
        
        // Initialize SweetAlert2
        Swal.fire({
            title: 'Archive Election',
            text: 'This will save all election data and results. Continue?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show progress dialog
                Swal.fire({
                    title: 'Archiving Election...',
                    html: '<div id="archive-progress">Initializing archive process...</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                        // Start the archive process with progress monitoring
                        startArchiveProcess();
                    }
                });
            }
        });
    });
});

/**
 * Start the archive process with delays and progress updates
 */
function startArchiveProcess() {
    const progressElement = document.getElementById('archive-progress');
    const steps = [
        { message: 'Initializing archive process...', delay: 1000 },
        { message: 'Preparing election data...', delay: 1500 },
        { message: 'Generating election results PDF...', delay: 2500 },
        { message: 'Generating election summary PDF...', delay: 2500 },
        { message: 'Saving archive to database...', delay: 1800 },
        { message: 'Finalizing archive...', delay: 1200 }
    ];
    
    let currentStep = 0;
    
    // Function to update the progress message
    function updateProgress() {
        if (currentStep < steps.length) {
            progressElement.innerHTML = steps[currentStep].message;
            currentStep++;
            
            // Schedule next update after delay
            setTimeout(updateProgress, steps[currentStep - 1].delay);
        } else {
            // All steps done, make the actual AJAX request
            executeArchiveRequest();
        }
    }
    
    // Start the progress updates
    updateProgress();
    
    // Function to make the actual AJAX request
    function executeArchiveRequest() {
        progressElement.innerHTML = 'Submitting archive request to server...';
        
        // Start AJAX request with more options
        $.ajax({
            url: window.BASE_URL + 'administrator/pages/includes/subpages/controllers/archive_process.php',
            method: 'POST',
            dataType: 'json',
            timeout: 60000, // Set a longer timeout (60 seconds)
            xhrFields: {
                withCredentials: true
            },
            error: function(xhr, status, error) {
                console.error('Archive error:', xhr.responseText);
                
                let errorMessage = 'Server Error';
                let errorDetails = '';
                
                // Check the specific error type
                if (status === 'timeout') {
                    errorMessage = 'The request timed out. The operation may be taking too long.';
                } else if (status === 'parsererror') {
                    errorMessage = 'Invalid response from server (parsing error).';
                } else if (status === 'abort') {
                    errorMessage = 'Request was aborted.';
                } else {
                    // Handle HTTP errors
                    switch(xhr.status) {
                        case 403:
                            errorMessage = 'Access denied (403)';
                            break;
                        case 404:
                            errorMessage = 'Endpoint not found (404)';
                            break;
                        case 500:
                            errorMessage = 'Internal server error (500)';
                            break;
                        default:
                            errorMessage = `Error: ${error} (${xhr.status})`;
                    }
                }
                
                // Try to parse the response if possible
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message;
                        if (response.error_details) {
                            errorDetails = response.error_details;
                        }
                    }
                } catch (e) {
                    // If response isn't JSON, try to extract error message from HTML
                    const errorMatch = /<b>.*?<\/b>:\s*(.*?)<br/i.exec(xhr.responseText);
                    if (errorMatch && errorMatch[1]) {
                        errorMessage = 'PHP Error: ' + errorMatch[1];
                    } else if (xhr.responseText) {
                        errorDetails = 'Raw response: ' + xhr.responseText.substring(0, 200) + 
                                      (xhr.responseText.length > 200 ? '...' : '');
                    }
                }
                
                // Log full details to console for debugging
                console.error('Error Details:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    responseText: xhr.responseText
                });
                
                // Show detailed error in SweetAlert2
                Swal.fire({
                    icon: 'error',
                    title: 'Archiving Failed',
                    html: `<p>${errorMessage}</p>
                           ${errorDetails ? `<p class="text-muted small">${errorDetails}</p>` : ''}
                           <p class="text-muted small">Check server logs for more details.</p>`,
                    confirmButtonText: 'OK'
                });
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Archiving Complete!',
                        text: 'The election has been successfully archived.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    // For debug purposes, log to console
                    console.error('Archive failed:', response);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Archiving Failed',
                        text: response.message,
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    }
}
