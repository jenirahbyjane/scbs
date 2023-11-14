<?php
require_once('./config.php');

$bookingId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($bookingId > 0) {
    $qry = $conn->query("SELECT b.*, f.name as facility, c.name as category, b.quantity FROM `booking_list` b inner join facility_list f on b.facility_id = f.id inner join category_list c on f.category_id = c.id where b.id = '{$bookingId}' and b.client_id = '{$_settings->userdata('id')}' order by unix_timestamp(b.date_created) desc");

    if ($qry->num_rows > 0) {
        $bookingDetails = $qry->fetch_assoc(); // Fetch the booking details
        foreach ($bookingDetails as $k => $v) {
            $$k = $v;
        }
    }
}
?>

<style>
    #uni_modal .modal-footer {
        display: none
    }
</style>

<div class="container-fluid">
    <fieldset class="border-bottom">
        <legend class="h5 text-muted"> Equipment Details</legend>
        <dl>
            <dt class="">Equipment Code</dt>
            <dd class="pl-4"><?= isset($facility_code) ? $facility_code : "" ?></dd>
            <dt class="">Name</dt>
            <dd class="pl-4"><?= isset($name) ? $name : "" ?></dd>
            <dt class="">Category</dt>
            <dd class="pl-4"><?= isset($category) ? $category : "" ?></dd>
            <dt class="">Quantity</dt>
            <dd class="pl-4"><?= isset($quantity) ? $quantity : "" ?></dd>
        </dl>
    </fieldset>
    <div class="clear-fix my-2"></div>
    <fieldset class="border">
        <legend class="h5 text-muted"> Booking Details</legend>
        <dl>
            <dt class="">Ref. Code</dt>
            <dd class="pl-4"><?= isset($ref_code) ? $ref_code : "" ?></dd>
            <dt class="">Schedule</dt>
            <dd class="pl-4">
                <?php
                if ($date_from == $date_to) {
                    echo date("M d, Y", strtotime($date_from));
                } else {
                    echo date("M d, Y", strtotime($date_from)) . " - " . date("M d, Y", strtotime($date_to));
                }
                ?>
            </dd>
            <dt class="">Status</dt>
            <dd class="pl-4">
                <?php
                switch ($status) {
                    case 0:
                        echo "<span class='badge badge-secondary bg-gradient-secondary px-3 rounded-pill'>Pending</span>";
                        break;
                    case 1:
                        echo "<span class='badge badge-primary bg-gradient-primary px-3 rounded-pill'>Confirmed</span>";
                        break;
                    case 2:
                        echo "<span class='badge badge-warning bg-gradient-success px-3 rounded-pill'>Done</span>";
                        break;
                    case 3:
                        echo "<span class='badge badge-danger bg-gradient-danger px-3 rounded-pill'>Cancelled</span>";
                        break;
                }
                ?>
            </dd>
        </dl>
    </fieldset>
    <div class="clear-fix my-3"></div>
    <div class="text-right">
        <?php if (isset($status) && $status == 0) : ?>
            <button class="btn btn-danger btn-flat bg-gradient-danger" type="button" id="cancel_booking">Cancel Book</button>
        <?php endif; ?>
        <button class="btn btn-dark btn-flat bg-gradient-dark" type="button" data-dismiss="modal"><i class="fa fa-times"></i> Close</button>
    </div>
</div>

<!-- Additional modal for confirmation -->
<div class="modal" id="cancelBookingModal" tabindex="-1" role="dialog" aria-labelledby="cancelBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cancelBookingModalLabel">Cancel Booking</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure to cancel your equipment booking [Ref. Code: <b><?= isset($ref_code) ? $ref_code : "" ?></b>]?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="cancelBooking">Cancel Booking</button>
                <button type="button" class="btn btn-success" id="confirmCancelBooking">Confirm Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(function () {
        $('#cancel_booking').click(function () {
            $('#cancelBookingModal').modal('show');
        });

        $('#confirmCancelBooking').click(function () {
            cancel_booking("<?= isset($id) ? $id : "" ?>");
        });

        function cancel_booking(bookingId) {
            start_loader();
            $.ajax({
                url: _base_url_ + "classes/Master.php?f=update_booking_status",
                method: "POST",
                data: { id: bookingId, status: 3 },
                dataType: "json",
                error: err => {
                    console.log(err);
                    alert_toast("An error occurred.", 'error');
                    end_loader();
                },
                success: function (resp) {
                    if (typeof resp == 'object' && resp.status == 'success') {
                        // Display a success message
                        alert_toast("Booking canceled successfully.", 'success');

                        // Reload the booking_list page after successful cancellation
                        window.location.href = _base_url_ + 'booking_list.php';
                    } else {
                        alert_toast("An error occurred.", 'error');
                        end_loader();
                    }
                }
            });
        }
    });
</script>
