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
            <div class="nms-form-row">
                <div class="nms-form-group">
                    <label for="nms-mod-date">New Date (if changing)</label>
                    <input type="date" id="nms-mod-date" name="new_date" min="<?php echo esc_attr( date( 'Y-m-d', strtotime( '+1 day' ) ) ); ?>">
                </div>
                <div class="nms-form-group">
                    <label>New Time (if changing)</label>
                    <div style="display:flex;gap:0.5rem;">
                        <input type="time" id="nms-mod-start" name="new_start_time" style="flex:1;">
                        <span style="align-self:center;">to</span>
                        <input type="time" id="nms-mod-end" name="new_end_time" style="flex:1;">
                    </div>
                </div>
            </div>
            <div class="nms-form-group">
                <label for="nms-mod-changes">What would you like to change? <span class="nms-req">*</span></label>
                <textarea id="nms-mod-changes" name="changes" rows="4" required
                          placeholder="e.g. I need to change the date to the following week, or I need to add kitchen facilities, or I need to reduce the number of attendees..."></textarea>
            </div>
        </div>

        <div class="nms-form-actions">
            <button type="submit" class="nms-btn nms-btn-primary nms-btn-lg" id="nms-mod-submit">
                Submit Change Request
            </button>
        </div>
    </form>
</div>

<script>
jQuery(function($) {
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
