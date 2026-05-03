<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$ref   = sanitize_text_field( $_GET['ref'] ?? '' );
$token = sanitize_text_field( $_GET['token'] ?? '' );

if ( ! $ref || ! $token || ! MBS_Modification::verify_token( $ref, $token ) ) {
    echo '<div class="nms-alert nms-alert-error">Invalid or expired modification link. Please contact us directly.</div>';
    return;
}

$booking = MBS_Bookings::get( $ref );
if ( ! $booking || in_array( $booking->status, array( 'cancelled', 'archived' ) ) ) {
    echo '<div class="nms-alert nms-alert-error">This booking cannot be modified.</div>';
    return;
}

$all_spaces = MBS_Bookings::get_spaces();
$spaces   = $all_spaces;

$spaces   = MBS_Bookings::get_spaces();
$is_daily = ! empty( $booking->all_day );
$time_str = $is_daily ? 'All day' : ( $booking->start_time . ' – ' . $booking->end_time );
?>

<div class="nms-wrap">
    <h2 class="nms-section-title">Request a Booking Change</h2>
    <p class="nms-section-sub">Use this form to request changes to your booking. Changes are subject to availability and admin approval.</p>

    <div id="nms-mod-success" class="nms-alert nms-alert-success" style="display:none"></div>
    <div id="nms-mod-error" class="nms-alert nms-alert-error" style="display:none"></div>

    <!-- Current booking details -->
    <div class="nms-form-section">
        <h3>Current Booking</h3>
        <table style="width:100%;font-size:0.9rem;">
            <tr><td style="padding:6px 0;font-weight:600;color:#6b7280;width:35%;">Reference</td><td style="padding:6px 0;"><?php echo esc_html( $booking->ref ); ?></td></tr>
            <tr><td style="padding:6px 0;font-weight:600;color:#6b7280;">Space</td><td style="padding:6px 0;"><?php echo esc_html( $booking->space ); ?></td></tr>
            <tr><td style="padding:6px 0;font-weight:600;color:#6b7280;">Date</td><td style="padding:6px 0;"><?php echo esc_html( date( 'l j F Y', strtotime( $booking->booking_date ) ) ); ?></td></tr>
            <tr><td style="padding:6px 0;font-weight:600;color:#6b7280;">Time</td><td style="padding:6px 0;"><?php echo esc_html( $time_str ); ?></td></tr>
            <tr><td style="padding:6px 0;font-weight:600;color:#6b7280;">Purpose</td><td style="padding:6px 0;"><?php echo esc_html( $booking->purpose ); ?></td></tr>
        </table>
    </div>

    <!-- Modification form -->
    <form id="nms-modification-form" class="nms-form" style="margin-top:1rem;">
        <?php wp_nonce_field( 'mbs_public_nonce', 'nonce' ); ?>
        <input type="hidden" name="ref" value="<?php echo esc_attr( $ref ); ?>">
        <input type="hidden" name="token" value="<?php echo esc_attr( $token ); ?>">

        <div class="nms-form-section">
            <h3>Requested Changes</h3>
            <p style="font-size:0.85rem;color:#6b7280;margin-bottom:1rem;">Change any fields below. Leave unchanged fields as they are. The cost will update automatically.</p>

            <div class="nms-form-row">
                <div class="nms-form-group">
                    <label for="nms-mod-space">Space</label>
                    <select id="nms-mod-space" name="new_space">
                        <option value="">— No change —</option>
                        <?php foreach ( $all_spaces as $sname => $sinfo ) : ?>
                        <option value="<?php echo esc_attr( $sname ); ?>" <?php selected( $booking->space, $sname ); ?>><?php echo esc_html( $sname ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="nms-form-group">
                    <label for="nms-mod-kitchen">Kitchen</label>
                    <select id="nms-mod-kitchen" name="new_kitchen">
                        <option value="0" <?php selected( $booking->kitchen, 0 ); ?>>No</option>
                        <option value="1" <?php selected( $booking->kitchen, 1 ); ?>>Yes (+&pound;<?php echo number_format( MBS_Bookings::get_kitchen_price(), 0 ); ?>)</option>
                    </select>
                </div>
            </div>
            <div class="nms-form-row">
                <div class="nms-form-group">
                    <label for="nms-mod-date">New Date</label>
                    <input type="date" id="nms-mod-date" name="new_date" value="<?php echo esc_attr( $booking->booking_date ); ?>"
                           min="<?php echo esc_attr( date( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>">
                </div>
                <div class="nms-form-group">
                    <label for="nms-mod-attendees">Attendees</label>
                    <input type="number" id="nms-mod-attendees" name="new_attendees" value="<?php echo esc_attr( $booking->attendees ); ?>" min="1">
                </div>
            </div>
            <div class="nms-form-row">
                <div class="nms-form-group">
                    <label for="nms-mod-start">Start Time</label>
                    <input type="time" id="nms-mod-start" name="new_start_time" value="<?php echo esc_attr( $booking->start_time ); ?>">
                </div>
                <div class="nms-form-group">
                    <label for="nms-mod-end">End Time</label>
                    <input type="time" id="nms-mod-end" name="new_end_time" value="<?php echo esc_attr( $booking->end_time ); ?>">
                </div>
            </div>

            <!-- Cost preview -->
            <div style="margin-top:12px;padding:12px 16px;background:#f9f7ff;border-radius:8px;border:1px solid #e0d0f0;">
                <div style="display:flex;justify-content:space-between;font-size:0.9rem;">
                    <span>Current amount:</span>
                    <span>&pound;<?php echo number_format( $booking->amount, 2 ); ?></span>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:0.9rem;margin-top:4px;">
                    <span>Estimated new amount:</span>
                    <strong id="nms-mod-new-cost">&pound;<?php echo number_format( $booking->amount, 2 ); ?></strong>
                </div>
                <div id="nms-mod-cost-diff" style="display:none;margin-top:8px;padding:6px 10px;border-radius:4px;font-size:0.8rem;font-weight:600;"></div>
                <p style="font-size:0.75rem;color:#6b7280;margin:6px 0 0;">* Final amount confirmed by admin after review.</p>
            </div>

            <div class="nms-form-group" style="margin-top:1rem;">
                <label for="nms-mod-changes">Additional notes about your change request</label>
                <textarea id="nms-mod-changes" name="changes" rows="3"
                          placeholder="Any other details about what you'd like to change..."></textarea>
            </div>
        </div>

        <div class="nms-form-actions">
            <button type="submit" class="nms-btn nms-btn-primary nms-btn-lg" id="nms-mod-submit">
                Submit Change Request
            </button>
        </div>
    </form>
</div>

<?php // spaces data already loaded above ?>
<script>
jQuery(function($) {
    var spacesData = <?php echo wp_json_encode( $all_spaces ); ?>;
    var kitchenPrice = <?php echo (float) MBS_Bookings::get_kitchen_price(); ?>;
    var originalAmount = <?php echo (float) $booking->amount; ?>;

    // Live cost recalculation on modification form
    $('#nms-mod-space, #nms-mod-start, #nms-mod-end, #nms-mod-kitchen, #nms-mod-date').on('change', function() {
        var space   = $('#nms-mod-space').val() || '<?php echo esc_js( $booking->space ); ?>';
        var start   = $('#nms-mod-start').val();
        var end     = $('#nms-mod-end').val();
        var kitchen = $('#nms-mod-kitchen').val() === '1';
        var info    = spacesData[space];

        var cost = 0;
        if (info) {
            var rateHourly = parseFloat(info.rate_hourly || 0);
            if (start && end) {
                var sh = parseInt(start.split(':')[0]) * 60 + parseInt(start.split(':')[1]);
                var eh = parseInt(end.split(':')[0]) * 60 + parseInt(end.split(':')[1]);
                var hrs = Math.ceil(Math.max(0, (eh - sh) / 60));
                cost = hrs * rateHourly;
            }
            if (kitchen) cost += kitchenPrice;
        }

        $('#nms-mod-new-cost').text('£' + cost.toFixed(2));

        var diff = cost - originalAmount;
        var $diffEl = $('#nms-mod-cost-diff');
        if (Math.abs(diff) > 0.01) {
            $diffEl.show();
            if (diff > 0) {
                $diffEl.css({ background: '#fee2e2', color: '#991b1b' }).text('Price would increase by £' + diff.toFixed(2));
            } else {
                $diffEl.css({ background: '#d1fae5', color: '#065f46' }).text('Price would decrease by £' + Math.abs(diff).toFixed(2));
            }
        } else {
            $diffEl.hide();
        }
    });
    $('#nms-modification-form').on('submit', function(e) {
        e.preventDefault();
        var $btn = $('#nms-mod-submit');
        var $err = $('#nms-mod-error');
        var $ok  = $('#nms-mod-success');
        $err.hide(); $ok.hide();

        $btn.prop('disabled', true).text('Submitting…');

        var data = $(this).serializeArray();
        data.push({ name: 'action', value: 'mbs_submit_modification' });

        $.post(NMS.ajax_url, data, function(res) {
            $btn.prop('disabled', false).text('Submit Change Request');
            if (res.success) {
                $ok.text(res.data.message).show();
                $('#nms-modification-form')[0].reset();
                $('html, body').animate({ scrollTop: $ok.offset().top - 80 }, 400);
            } else {
                $err.text(res.data.message || 'An error occurred.').show();
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Submit Change Request');
            $err.text('A network error occurred.').show();
        });
    });
});
</script>
