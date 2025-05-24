<?php
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/User.php';
require_once __DIR__ . '/../classes/Ballot.php';
require_once __DIR__ . '/../classes/Votes.php';

$user = new User();
$ballot = new Ballot();
$votes = new Votes();
$currentVoter = $user->getCurrentUser();
?>

<!-- Preview -->
<div class="modal fade" id="preview_modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Vote Preview</h4>
            </div>
            <div class="modal-body">
                <div id="preview_body" class="scrollable-content"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" form="ballotForm" class="btn btn-success btn-flat" name="submitPreview"><i class="fa fa-check"></i> Submit</button>
                <button type="button" class="btn btn-default btn-flat" name="closePreview" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Platform Modal -->
<div class="modal fade" id="platform">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><b><span class="candidate"></span></b></h4>
            </div>
            <div class="modal-body">
                <div class="platform-content">
                    <img src="" id="platform_image" class="candidate-platform-image" alt="Candidate Photo">
                    <div class="platform-text">
                        <p id="plat_view"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat" name="closePlatform" data-dismiss="modal">
                    <i class="fa fa-close"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Modal styling */
    .modal-content {
        display: flex;
        flex-direction: column;
        min-height: 50vh;
        max-height: 90vh;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        border: none;
    }

    .modal-header {
        flex: 0 0 auto;
        background: #fff;
        border-bottom: 1px solid #e5e5e5;
        padding: 15px 20px;
        position: relative;
        z-index: 1;
    }

    .modal-header .modal-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .modal-body {
        flex: 1 1 auto;
        position: relative;
        padding: 0;
        background: #f8f9fa;
        overflow-y: auto;
        -webkit-overflow-scrolling: touch;
    }

    .scrollable-content {
        padding: 20px;
    }

    .modal-footer {
        flex: 0 0 auto;
        background: #fff;
        border-top: 1px solid #e5e5e5;
        padding: 15px 20px;
        position: relative;
        z-index: 1;
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    /* Content styling */
    #preview_modal .well {
        margin-bottom: 20px;
        background-color: #fff;
        border: 1px solid #e3e3e3;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    #preview_modal .well h4 {
        color: #249646;
        margin: 0 0 15px 0;
        font-weight: 600;
        font-size: 1.2rem;
    }

    /* Preview Card styling */
    #preview_modal .candidate-row {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 12px;
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }

    #preview_modal .candidate-image-container {
        width: 42px;
        height: 42px;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        border: 1px solid #e0e0e0;
        background-color: #f8f9fa;
    }

    #preview_modal .candidate-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    #preview_modal .candidate-details {
        flex: 1;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    #preview_modal .candidate-name {
        font-size: 1rem;
        font-weight: 500;
        color: #333;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.3;
    }

    #preview_modal .candidate-partylist {
        font-size: 0.85rem;
        color: #666;
        margin: 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        line-height: 1.2;
    }

    /* Button styling */
    #preview_modal .modal-footer {
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        gap: 15px;
    }

    #preview_modal .modal-footer .btn {
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-width: 180px;
        font-size: 1rem;
    }

    #preview_modal .modal-footer .btn i {
        font-size: 1rem;
    }

    #preview_modal .modal-footer .btn-default {
        background-color: #dc3545;
        color: white;
    }

    #preview_modal .modal-footer .btn-success {
        background-color: #249646;
        color: white;
    }

    #preview_modal .modal-footer .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Mobile Styles */
    @media (max-width: 768px) {
        #preview_modal .modal-dialog {
            margin: 0;
            width: 100%;
            height: 100%;
            max-width: none;
        }

        #preview_modal .modal-content {
            min-height: 100vh;
            border-radius: 0;
            margin: 0;
        }

        #preview_modal .modal-header {
            padding: 12px 15px;
        }

        #preview_modal .modal-header .modal-title {
            font-size: 1.1rem;
        }

        #preview_modal .scrollable-content {
            padding: 12px;
        }

        #preview_modal .well {
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 6px;
        }

        #preview_modal .well h4 {
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        #preview_modal .candidate-row {
            padding: 10px;
            margin-bottom: 8px;
            gap: 10px;
        }

        #preview_modal .candidate-image-container {
            width: 40px;
            height: 40px;
        }

        #preview_modal .candidate-name {
            font-size: 0.95rem;
        }

        #preview_modal .candidate-partylist {
            font-size: 0.8rem;
        }

        #preview_modal .modal-footer {
            padding: 12px;
            flex-direction: column-reverse;
            gap: 8px;
        }

        #preview_modal .modal-footer .btn {
            width: 100%;
            min-width: unset;
            padding: 12px;
            margin: 0;
            font-size: 0.95rem;
        }
    }

    /* Small screen adjustments */
    @media (max-width: 480px) {
        .scrollable-content {
            padding: 10px;
        }

        .well {
            padding: 10px;
            margin-bottom: 10px;
        }

        .candidate-row {
            padding: 8px;
            gap: 8px;
        }

        .mobile-flex {
            gap: 8px;
        }

        .candidate-image-container {
            width: 38px;
            height: 38px;
        }

        .modal-footer {
            padding: 10px;
        }

        .modal-footer .btn {
            padding: 10px;
        }
    }

    /* Landscape mode */
    @media (max-height: 600px) and (orientation: landscape) {
        .modal-dialog {
            margin: 0;
        }

        .modal-content {
            height: 100vh;
        }

        .modal-footer {
            flex-direction: row;
            padding: 10px;
        }

        .modal-footer .btn {
            flex: 1;
        }

        .scrollable-content {
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }
    }

    /* Platform Modal Styling */
    #platform .modal-dialog {
        max-width: 600px;
        margin: 30px auto;
    }

    #platform .modal-body {
        padding: 20px;
        background: #fff;
    }

    .platform-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 20px;
    }

    .candidate-platform-image {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .platform-text {
        background-color: #f8f9fa;
        border-radius: 8px;
        padding: 20px;
        width: 100%;
        text-align: left;
        line-height: 1.6;
        color: #333;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    #plat_view {
        margin: 0;
        white-space: pre-line;
    }

    @media (max-width: 768px) {
        .candidate-platform-image {
            width: 100px;
            height: 100px;
        }

        .platform-text {
            padding: 15px;
        }
    }

    @media (max-height: 600px) and (orientation: landscape) {
        .modal-dialog {
            margin: 0;
        }

        .modal-content {
            height: 100vh;
            border-radius: 0;
        }

        .modal-footer {
            flex-direction: row;
        }
    }
</style>

<script>
    $(document).ready(function() {
        $('.platform').on('click', function() {
            var platform = $(this).data('platform');
            var fullname = $(this).data('fullname');
            var image = $(this).data('image');

            // Update the modal content
            $('.candidate').text(fullname);
            $('#plat_view').text(platform);
            $('#platform_image').attr('src', !image ? 'administrator/assets/images/profile.jpg' : 'administrator/' + image);

            // Show the modal
            $('#platform').modal('show');
        });

        // Add dynamic height adjustment
        $('#preview_modal').on('show.bs.modal', function() {
            setTimeout(function() {
                const modalContent = document.querySelector('#preview_modal .modal-content');
                const modalBody = document.querySelector('#preview_modal .modal-body');
                const contentHeight = modalBody.scrollHeight;
                
                if (window.innerHeight > 600) {
                    modalContent.style.height = Math.min(Math.max(contentHeight + 120, window.innerHeight * 0.5), window.innerHeight * 0.9) + 'px';
                }
            }, 100);
        });
    });
</script>