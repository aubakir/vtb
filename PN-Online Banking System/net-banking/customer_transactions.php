<?php
    /* Avoid multiple sessions warning
    Check if session is set before starting a new one. */
    if(!isset($_SESSION)) {
        session_start();
    }

    include "validate_customer.php";
    include "connect.php";
    include "customer_navbar.php";
    include "customer_sidebar.php";

    if (isset($_SESSION['loggedIn_cust_id'])) {
        $sql0 = "SELECT * FROM passbook".$_SESSION['loggedIn_cust_id'];
    }

    // Recive sort variables as $_GET
    if (isset($_GET['sort'])) {
        $sort = $_GET['sort'];
    }

    // Recieve filter variables as session variables
    if (isset($_POST['search_term'])) {
        $_SESSION['search_term'] = $_POST['search_term'];
    }
    if (isset($_POST['date_from'])) {
        $_SESSION['date_from'] = $_POST['date_from'];
    }
    if (isset($_POST['date_to'])) {
        $_SESSION['date_to'] = $_POST['date_to'];
    }

    // Filter indicator variable
    $filter_indicator = "None";

    // Queries when search is set
    if (!empty($_SESSION['search_term'])) {
        $sql0 .= " WHERE remarks COLLATE latin1_GENERAL_CI LIKE '%".$_SESSION['search_term']."%'";
        $filter_indicator = "Remarks";

        if (!empty($_SESSION['date_from']) && empty($_SESSION['date_to'])) {
            $sql0 .= " AND trans_date > '".$_SESSION['date_from']." 00:00:00'";
            $filter_indicator = "Remarks & Date From";
        }
        if (empty($_SESSION['date_from']) && !empty($_SESSION['date_to'])) {
            $sql0 .= " AND trans_date < '".$_SESSION['date_to']." 23:59:59'";
            $filter_indicator = "Remarks & Date To";
        }
        if (!empty($_SESSION['date_from']) && !empty($_SESSION['date_to'])) {
            $sql0 .=  " AND trans_date BETWEEN '".$_SESSION['date_from']." 00:00:00' AND '".$_SESSION['date_to']." 23:59:59'";
            $filter_indicator = "Remarks, Date From & Date To";
        }
    }

    // Queries when search is not set
    if (empty($_SESSION['search_term'])) {
        if (!empty($_SESSION['date_from']) && empty($_SESSION['date_to'])) {
            $sql0 .= " WHERE trans_date > '".$_SESSION['date_from']." 00:00:00'";
            $filter_indicator = "Date From";
        }
        if (empty($_SESSION['date_from']) && !empty($_SESSION['date_to'])) {
            $sql0 .= " WHERE trans_date < '".$_SESSION['date_to']." 23:59:59'";
            $filter_indicator = "Date To";
        }
        if (!empty($_SESSION['date_from']) && !empty($_SESSION['date_to'])) {
            $sql0 .=  " WHERE trans_date BETWEEN '".$_SESSION['date_from']." 00:00:00' AND '".$_SESSION['date_to']." 23:59:59'";
            $filter_indicator = "Date From & Date To";
        }
    }

    // Sort Queries
    // Sort acts independent of the filter
    if (isset($_GET['sort'])) {
        if ($sort == 'tid_down') {
            $sql0 .= " ORDER BY trans_id ASC";
        }
        if ($sort == 'tid_up') {
            $sql0 .= " ORDER BY trans_id DESC";
        }
        if ($sort == 'date_down') {
            $sql0 .= " ORDER BY trans_date ASC";
        }
        if ($sort == 'date_up') {
            $sql0 .= " ORDER BY trans_date DESC";
        }
    }
?>


<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="transactions_style.css">
</head>

<body>

    <div id="id01" class="modal">

      <form class="modal-content animate" action="" method="post">
        <div class="imgcontainer">
          <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Filter">&times;</span>
        </div>



      </form>
    </div>

    <div class="flex-container">
        <p id="none">Filter : <?php echo $filter_indicator ?></p>
    </div>

    <div class="flex-container">

        <?php
            $result = $conn->query($sql0);

            if ($result->num_rows > 0) {?>
                <table id="transactions">
                    <tr>
                        <th>Trans. ID</th>
                        <th>Date & Time (IST)</th>
                        <th>Remarks</th>
                        <th>Debit </th>
                        <th>Credit </th>
                        <th>Balance </th>
                    </tr>
        <?php
            // output data of each row
            while($row = $result->fetch_assoc()) {?>
                    <tr>
                        <td><?php echo $row["trans_id"]; ?></td>
                        <td>
                            <?php
                                $time = strtotime($row["trans_date"]);
                                $sanitized_time = date("d/m/Y, g:i A", $time);
                                echo $sanitized_time;
                             ?>
                        </td>
                        <td><?php echo $row["remarks"]; ?></td>
                        <td><?php echo number_format($row["debit"]); ?></td>
                        <td><?php echo number_format($row["credit"]); ?></td>
                        <td><?php echo number_format($row["balance"]); ?></td>
                    </tr>
            <?php } ?>
            </table>
            <?php
            } else {  ?>
                <p id="none"> No results found :(</p>
            <?php }
            $conn->close(); ?>

    </div>

    <script>
    // Sticky search-bar
    $(document).ready(function() {
        var curr_scroll;

        $(window).scroll(function () {
            curr_scroll = $(window).scrollTop();

            if ($(window).scrollTop() > 120) {
                $("#the-search-bar").addClass('search-bar-fixed');

              if ($(window).width() > 855) {
                  $("#fi-search-bar").addClass('fi-search-bar-fixed');
              }
            }

            if ($(window).scrollTop() < 121) {
                $("#the-search-bar").removeClass('search-bar-fixed');

              if ($(window).width() > 855) {
                  $("#fi-search-bar").removeClass('fi-search-bar-fixed');
              }
            }
        });

        $(window).resize(function () {
            var class_name = $("#fi-search-bar").attr('class');

            if ((class_name == "flex-item-search-bar fi-search-bar-fixed") && ($(window).width() < 856)) {
                $("#fi-search-bar").removeClass('fi-search-bar-fixed');
            }

            if ((class_name == "flex-item-search-bar") && ($(window).width() > 855) && (curr_scroll > 120)) {
                $("#fi-search-bar").addClass('fi-search-bar-fixed');
            }
        });

        // Get the modal
        var modal = document.getElementById('id01');

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    });
    </script>

</body>
</html>
