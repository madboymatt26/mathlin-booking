<?php if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Global Audit Log view.
 *
 * Provided by MBS_Admin::render_audit_log():
 *   $entries (array of log rows), $search (string), $limit (int)
 */
$search  = isset( $search ) ? $search : '';
$limit   = isset( $limit ) ? $limit : 200;
$entries = isset( $entries ) ? $entries : array();
?>
<div class="wrap mbs-admin">
    <h1>📋 Audit Log</h1>
    <p>A complete history of booking and series actions — who did what, and when. Series actions (cancel, edit, extend, delete) are logged against the <code>SER-XXXXXX</code> reference.</p>

    <form method="get" style="margin:16px 0;display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <input type="hidden" name="page" value="mathlin-audit-log">
        <input type="search" name="s" value="<?php echo esc_attr( $search ); ?>" placeholder="Search ref, action, details or user…" style="min-width:280px;padding:6px 10px;">
        <select name="limit">
            <?php foreach ( array( 50, 100, 200, 500, 1000 ) as $opt ) : ?>
                <option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $limit, $opt ); ?>><?php echo esc_html( $opt ); ?> rows</option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="button button-primary">Search</button>
        <?php if ( $search !== '' ) : ?>
            <a href="?page=mathlin-audit-log" class="button">Clear</a>
        <?php endif; ?>
    </form>

    <div class="nms-card">
        <div class="nms-card-header">
            <h2><?php echo $search !== '' ? 'Results for "' . esc_html( $search ) . '"' : 'Recent Activity'; ?> (<?php echo count( $entries ); ?>)</h2>
        </div>
        <?php if ( empty( $entries ) ) : ?>
            <p style="padding:1.5rem;color:#6b7280;">No audit log entries found.</p>
        <?php else : ?>
            <div style="overflow-x:auto;">
                <table class="widefat striped" style="border:none;">
                    <thead>
                        <tr>
                            <th style="white-space:nowrap;">Date / Time</th>
                            <th>Reference</th>
                            <th>Action</th>
                            <th>Details</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $entries as $entry ) :
                            $ref         = (string) $entry->ref;
                            $is_series   = ( strpos( $ref, 'SER-' ) === 0 );
                            $is_linkable = ( $ref !== '' && ! $is_series && $ref !== 'BULK' );
                        ?>
                        <tr>
                            <td style="white-space:nowrap;color:#6b7280;font-size:0.8rem;">
                                <?php echo esc_html( wp_date( 'j M Y', strtotime( $entry->created_at ) ) ); ?><br>
                                <?php echo esc_html( wp_date( 'H:i', strtotime( $entry->created_at ) ) ); ?>
                            </td>
                            <td>
                                <?php if ( $is_linkable ) : ?>
                                    <a href="?page=mathlin-booking&action=view&ref=<?php echo esc_attr( $ref ); ?>"><strong><?php echo esc_html( $ref ); ?></strong></a>
                                <?php elseif ( $is_series ) : ?>
                                    <strong style="color:#7413DC;">⚜️ <?php echo esc_html( $ref ); ?></strong>
                                <?php else : ?>
                                    <strong><?php echo esc_html( $ref ?: '—' ); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td style="white-space:nowrap;"><?php echo MBS_Audit_Log::action_label( $entry->action ); ?></td>
                            <td style="font-size:0.85rem;"><?php echo esc_html( $entry->details ); ?></td>
                            <td style="white-space:nowrap;font-size:0.8rem;color:#6b7280;">
                                <?php echo esc_html( $entry->user_name ); ?>
                                <?php if ( ! empty( $entry->ip_address ) ) : ?>
                                    <br><span style="font-size:0.7rem;"><?php echo esc_html( $entry->ip_address ); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>
