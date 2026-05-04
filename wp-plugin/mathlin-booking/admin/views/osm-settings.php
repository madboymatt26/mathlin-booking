<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$osm = MBS_OSM_Integration::get_settings();
$gwc_available = MBS_OSM_Integration::gilbertweb_available();
?>

<div class="wrap">
    <h1>OSM Integration</h1>
    <p class="description">Push financial data to Online Scout Manager when bookings are marked as paid.</p>

    <div id="mbs-osm-msg" class="notice" style="display:none;"></div>

    <table class="form-table">
        <tr>
            <th>Enable OSM Integration</th>
            <td>
                <label>
                    <input type="checkbox" id="osm_enabled" <?php checked( $osm['enabled'] ); ?>>
                    Push payment records to OSM when bookings are marked as paid
                </label>
            </td>
        </tr>

        <tr>
            <th>🧪 Sandbox Mode</th>
            <td>
                <label>
                    <input type="checkbox" id="osm_sandbox_mode" <?php checked( $osm['sandbox_mode'] ); ?>>
                    Log payloads to <code>error_log</code> instead of sending to OSM
                </label>
                <p class="description">Enable this to test the integration without making live API calls. Check your PHP error log for the output.</p>
            </td>
        </tr>

        <tr>
            <th colspan="2"><hr><h2 style="margin:0;">Authentication</h2></th>
        </tr>

        <tr>
            <th>Auth Source</th>
            <td>
                <select id="osm_auth_source">
                    <option value="gilbertweb" <?php selected( $osm['auth_source'], 'gilbertweb' ); ?>>
                        GilbertWeb Connector (shared tokens)
                        <?php echo $gwc_available ? ' ✅' : ' ⚠️ Not connected'; ?>
                    </option>
                    <option value="standalone" <?php selected( $osm['auth_source'], 'standalone' ); ?>>
                        Standalone (own credentials)
                    </option>
                </select>
                <?php if ( $gwc_available ) : ?>
                    <p class="description" style="color:#065f46;">✅ GilbertWeb Connector tokens detected — no additional credentials needed.</p>
                <?php else : ?>
                    <p class="description" style="color:#dc3232;">⚠️ GilbertWeb Connector not authenticated. Select "Standalone" and enter your own OSM OAuth credentials, or authenticate the GilbertWeb Connector plugin first.</p>
                <?php endif; ?>
            </td>
        </tr>

        <tr class="mbs-osm-standalone" style="<?php echo $osm['auth_source'] === 'standalone' ? '' : 'display:none;'; ?>">
            <th>OSM OAuth Client ID</th>
            <td><input type="text" id="osm_client_id" class="regular-text" value="<?php echo esc_attr( $osm['client_id'] ); ?>"></td>
        </tr>

        <tr class="mbs-osm-standalone" style="<?php echo $osm['auth_source'] === 'standalone' ? '' : 'display:none;'; ?>">
            <th>OSM OAuth Client Secret</th>
            <td><input type="password" id="osm_client_secret" class="regular-text" value="<?php echo esc_attr( $osm['client_secret'] ); ?>"></td>
        </tr>

        <tr>
            <th>Connection Test</th>
            <td>
                <button type="button" id="mbs-osm-test" class="button">🔌 Test Connection</button>
                <span id="mbs-osm-test-msg" style="margin-left:10px;"></span>
            </td>
        </tr>

        <tr>
            <th colspan="2"><hr><h2 style="margin:0;">OSM Mapping</h2></th>
        </tr>

        <tr>
            <th>Section ID</th>
            <td>
                <input type="text" id="osm_section_id" class="regular-text" value="<?php echo esc_attr( $osm['section_id'] ); ?>" placeholder="e.g. 40703">
                <button type="button" id="mbs-osm-load-sections" class="button button-small">Load Sections</button>
                <p class="description">The OSM section to post financial records to. Click "Load Sections" to see available sections after connecting.</p>
                <div id="mbs-osm-sections-list" style="margin-top:8px;"></div>
            </td>
        </tr>

        <tr>
            <th>Finance Category ID</th>
            <td>
                <input type="text" id="osm_category_id" class="regular-text" value="<?php echo esc_attr( $osm['category_id'] ); ?>" placeholder="e.g. hall_hire">
                <p class="description">The finance category in OSM to assign payments to (e.g. "Hall Hire", "Venue Income").</p>
            </td>
        </tr>

        <tr>
            <th>Account ID</th>
            <td>
                <input type="text" id="osm_account_id" class="regular-text" value="<?php echo esc_attr( $osm['account_id'] ); ?>" placeholder="Optional">
                <p class="description">Optional: specific OSM account to post to.</p>
            </td>
        </tr>

        <tr>
            <th>Description Template</th>
            <td>
                <input type="text" id="osm_description_template" class="large-text" value="<?php echo esc_attr( $osm['description_tpl'] ); ?>">
                <p class="description">
                    Available placeholders: <code>{ref}</code> <code>{name}</code> <code>{space}</code> <code>{date}</code> <code>{purpose}</code> <code>{organisation}</code>
                </p>
            </td>
        </tr>
    </table>

    <p>
        <button type="button" id="mbs-osm-save" class="button button-primary button-hero">💾 Save OSM Settings</button>
    </p>
</div>

<script>
jQuery(function($) {
    // Toggle standalone fields
    $('#osm_auth_source').on('change', function() {
        if ($(this).val() === 'standalone') {
            $('.mbs-osm-standalone').show();
        } else {
            $('.mbs-osm-standalone').hide();
        }
    });

    // Save settings
    $('#mbs-osm-save').on('click', function() {
        var $btn = $(this);
        var $msg = $('#mbs-osm-msg');
        $btn.prop('disabled', true).text('Saving…');

        $.post(MBS_Admin.ajax_url, {
            action:                   'mbs_save_osm_settings',
            nonce:                    MBS_Admin.nonce,
            osm_enabled:              $('#osm_enabled').is(':checked') ? 1 : 0,
            osm_sandbox_mode:         $('#osm_sandbox_mode').is(':checked') ? 1 : 0,
            osm_auth_source:          $('#osm_auth_source').val(),
            osm_client_id:            $('#osm_client_id').val(),
            osm_client_secret:        $('#osm_client_secret').val(),
            osm_section_id:           $('#osm_section_id').val(),
            osm_category_id:          $('#osm_category_id').val(),
            osm_account_id:           $('#osm_account_id').val(),
            osm_description_template: $('#osm_description_template').val()
        }, function(res) {
            $btn.prop('disabled', false).text('💾 Save OSM Settings');
            if (res.success) {
                $msg.removeClass('notice-error').addClass('notice-success').html('<p>✓ OSM settings saved.</p>').show();
            } else {
                $msg.removeClass('notice-success').addClass('notice-error').html('<p>✗ Error saving settings.</p>').show();
            }
            setTimeout(function() { $msg.hide(); }, 4000);
        }).fail(function() {
            $btn.prop('disabled', false).text('💾 Save OSM Settings');
            $msg.removeClass('notice-success').addClass('notice-error').html('<p>✗ Network error.</p>').show();
        });
    });

    // Test connection
    $('#mbs-osm-test').on('click', function() {
        var $btn = $(this);
        var $msg = $('#mbs-osm-test-msg');
        $btn.prop('disabled', true).text('Testing…');

        $.post(MBS_Admin.ajax_url, {
            action: 'mbs_test_osm_connection',
            nonce:  MBS_Admin.nonce
        }, function(res) {
            $btn.prop('disabled', false).text('🔌 Test Connection');
            if (res.success) {
                $msg.text('✅ ' + res.data.message).css('color', '#065f46');
            } else {
                $msg.text('❌ ' + (res.data && res.data.message ? res.data.message : res.data)).css('color', '#dc3232');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('🔌 Test Connection');
            $msg.text('❌ Network error').css('color', '#dc3232');
        });
    });

    // Load sections
    $('#mbs-osm-load-sections').on('click', function() {
        var $btn = $(this);
        var $list = $('#mbs-osm-sections-list');
        $btn.prop('disabled', true).text('Loading…');

        $.post(MBS_Admin.ajax_url, {
            action: 'mbs_osm_get_sections',
            nonce:  MBS_Admin.nonce
        }, function(res) {
            $btn.prop('disabled', false).text('Load Sections');
            if (res.success && res.data) {
                var html = '<strong>Available sections:</strong><ul style="margin:4px 0 0 16px;">';
                // OSM returns sections in various formats
                var sections = res.data;
                if (Array.isArray(sections)) {
                    sections.forEach(function(s) {
                        var id = s.sectionid || s.section_id || s.id || '';
                        var name = s.sectionname || s.section_name || s.name || 'Unknown';
                        html += '<li><code>' + id + '</code> — ' + name + ' <button type="button" class="button button-small mbs-osm-pick-section" data-id="' + id + '">Use</button></li>';
                    });
                } else if (typeof sections === 'object') {
                    for (var key in sections) {
                        if (sections[key] && typeof sections[key] === 'object') {
                            var s = sections[key];
                            var id = s.sectionid || s.section_id || key;
                            var name = s.sectionname || s.section_name || s.name || key;
                            html += '<li><code>' + id + '</code> — ' + name + ' <button type="button" class="button button-small mbs-osm-pick-section" data-id="' + id + '">Use</button></li>';
                        }
                    }
                }
                html += '</ul>';
                $list.html(html);
            } else {
                $list.html('<span style="color:#dc3232;">Could not load sections. ' + (res.data || '') + '</span>');
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Load Sections');
            $list.html('<span style="color:#dc3232;">Network error.</span>');
        });
    });

    $(document).on('click', '.mbs-osm-pick-section', function() {
        $('#osm_section_id').val($(this).data('id'));
    });
});
</script>
