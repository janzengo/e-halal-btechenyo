<?php include "includes/session.php"; ?>
<?php include "includes/header.php"; ?>
<body class="hold-transition skin-blue layout-top-nav">
    <div class="wrapper">
        <?php include "includes/navbar.php"; ?>
        <div class="content-wrapper">
            <div class="container">
                <section class="content">
                    <?php
                    $parse = parse_ini_file("admin/config.ini", false, INI_SCANNER_RAW);
                    $title = $parse["election_name"];
                    ?>
                    <?php
                    echo '<h1 class="page-header text-center title title-custom">'.'<b>'. strtoupper($title).'</b></h1>';
                    
                    ?>
                    <div class="row">
                        <div class="col-sm-10 col-sm-offset-1">
                            <?php
                            if (isset($_SESSION["error"])) { ?>
                            <div class="alert alert-danger alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <ul>
                                    <?php foreach ($_SESSION["error"] as $error) {
                                        echo "<i class='fa fa-exclamation-triangle'>&nbsp;</i>" . $error;
                                    } ?>
                                </ul>
                            </div>
                            <?php unset($_SESSION["error"]); }
                            if (isset($_SESSION["success"])) {
                                echo "
                                    <div class='alert alert-success alert-dismissible'>
                                        <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                                        <h4><i class='icon fa fa-check'></i> Success!</h4>
                                        " . $_SESSION["success"] . "
                                    </div>
                                ";
                                unset($_SESSION["success"]);
                            }
                            ?>

                            <div class="alert alert-danger alert-dismissible" id="alert" style="display:none;">
                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                <span class="message"></span>
                            </div>

                            <?php
                            $sql = "SELECT * FROM votes WHERE voters_id = '" . $voter["id"] . "'";
                            $vquery = $conn->query($sql);
                            if ($vquery->num_rows > 0) {
                                $_SESSION["voted"] = true;
                            }

                            if (isset($_SESSION["voted"])) { ?>
                                <div class="text-center vertical-center">
                                
                                
                                    <h3>You already casted your vote for this election. See you next year!</h3>
                                    <a href="#view" data-toggle="modal" class="btn btn-flat btn-primary btn-lg">View Ballot</a>
                                </div>
                            <?php } else { ?>
                            <!-- Voting Ballot -->
                            <form method="POST" id="ballotForm" action="submit_ballot.php">
                                <?php
                                include "includes/slugify.php";
                                $candidate = "";
                                $sql = "SELECT * FROM positions ORDER BY priority ASC";
                                $query = $conn->query($sql);
                                while ($row = $query->fetch_assoc()) {
                                    $sql = "SELECT candidates.*, partylists.name AS partylist_name 
                                            FROM candidates 
                                            LEFT JOIN partylists ON candidates.partylist_id = partylists.id 
                                            WHERE position_id='" . $row["id"] . "'";
                                    $cquery = $conn->query($sql);
                                    while ($crow = $cquery->fetch_assoc()) {
                                        $slug = slugify($row["description"]);
                                        $checked = "";
                                        if (isset($_SESSION["post"][$slug])) {
                                            $value = $_SESSION["post"][$slug];
                                            if (is_array($value)) {
                                                foreach ($value as $val) {
                                                    if ($val == $crow["id"]) {
                                                        $checked = "checked";
                                                    }
                                                }
                                            } else {
                                                if ($value == $crow["id"]) {
                                                    $checked = "checked";
                                                }
                                            }
                                        }
                                        $input = $row["max_vote"] > 1
                                            ? '<input type="checkbox" class="flat-red ' . $slug . '" name="' . $slug . "[]" . '" value="' . $crow["id"] . '" ' . $checked . ">"
                                            : '<input type="radio" class="flat-red ' . $slug . '" name="' . slugify($row["description"]) . '" value="' . $crow["id"] . '" ' . $checked . ">";
                                        $image = !empty($crow["photo"]) ? "images/" . $crow["photo"] : "images/profile.jpg";
                                        $candidate .= '
                                            <li>
                                                ' . $input . '
                                                <button type="button" class="btn btn-primary btn-sm btn-flat clist platform" data-platform="' . $crow["platform"] . '" data-fullname="' . $crow["firstname"] . " " . $crow["lastname"] . '"><i class="fa fa-search"></i> Platform</button>
                                                <a href="' . $image . '" data-fancybox data-caption="' . $crow["firstname"] . " " . $crow["lastname"] . '">
                                                    <img src="' . $image . '" height="100px" width="100px" class="clist">
                                                </a>
                                                <span class="cname clist">' . $crow["firstname"] . " " . $crow["lastname"] . " — " . $crow["partylist_name"] . '</span>
                                            </li>
                                        ';
                                    }
                                    $instruct = $row["max_vote"] > 1
                                        ? "You may select up to " . $row["max_vote"] . " candidates"
                                        : "Select only one candidate";
                                    echo '
                                        <div class="row">
                                            <div class="col-xs-12">
                                                <div class="box box-solid box-custom" id="' . $row["id"] . '">
                                                    <div class="box-header with-border">
                                                        <h3 class="box-title"><b>' . $row["description"] . '</b></h3>
                                                    </div>
                                                    <div class="box-body">
                                                        <p>' . $instruct . '
                                                            <span class="pull-right">
                                                                <button type="button" class="btn btn-danger btn-sm btn-flat reset" data-desc="' . slugify($row["description"]) . '"><i class="fa fa-refresh"></i> Reset</button>
                                                            </span>
                                                        </p>
                                                        <div id="candidate_list">
                                                            <ul>
                                                                ' . $candidate . '
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    ';
                                    $candidate = "";
                                }
                                ?>
                                <div class="text-center">
                                    <button type="button" class="btn btn-success btn-flat" id="preview"><i class="fa fa-file-text"></i> Preview</button>
                                    <button type="submit" class="btn btn-primary btn-flat custom-button" name="vote"><i class="fa fa-upload"></i> Submit</button>
                                </div>
                            </form>
                            <!-- End Voting Ballot -->
                            <?php } ?>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        <?php include "includes/footer.php"; ?>
        <?php include "includes/ballot_modal.php"; ?>
    </div>
    <?php include "includes/scripts.php"; ?>
    <!-- Fancybox script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fancybox/3.5.7/jquery.fancybox.min.css" />
    <script>
        $(function () {
    $('.content').iCheck({
        checkboxClass: 'icheckbox_flat-green',
        radioClass: 'iradio_flat-green'
    });

    $(document).on('click', '.reset', function (e) {
        e.preventDefault();
        var desc = $(this).data('desc');
        $('.' + desc).iCheck('uncheck');
    });

    $(document).on('click', '.platform', function (e) {
        e.preventDefault();
        $('#platform').modal('show');
        var platform = $(this).data('platform');
        var fullname = $(this).data('fullname');
        $('.candidate').html(fullname);
        $('#plat_view').html(platform);
    });

    $('#preview').click(function (e) {
        e.preventDefault();
        var form = $('#ballotForm').serialize();
        if (form == '') {
            $('.message').html('You must vote at least one candidate');
            $('#alert').show();
            // Scroll to the top
            $('html, body').animate({ scrollTop: 0 }, 'slow');
        } else {
            $.ajax({
                type: 'POST',
                url: 'preview.php',
                data: form,
                dataType: 'json',
                success: function (response) {
                    if (response.error) {
                        var errmsg = '';
                        var messages = response.message;
                        for (i in messages) {
                            errmsg += messages[i];
                        }
                        $('.message').html(errmsg);
                        $('#alert').show();
                        // Scroll to the top
                        $('html, body').animate({ scrollTop: 0 }, 'slow');
                    } else {
                        $('#preview_modal').modal('show');
                        $('#preview_body').html(response.list);
                    }
                }
            });
        }
    });

    // Initialize Fancybox
    $('[data-fancybox]').fancybox({
        loop: false,
        buttons: [
            "close"
        ]
    });
});

	</script>
</body>

</html>