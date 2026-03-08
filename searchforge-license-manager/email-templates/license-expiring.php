<?php
/**
 * E-Mail-Template: Ablauf-Warnung.
 *
 * @var array{license_key: string, expires_at: string} $data
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$expires_formatted = wp_date( 'd.m.Y', strtotime( $data['expires_at'] ) );
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">

<div style="background: #f0ad4e; padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
    <h1 style="color: white; margin: 0; font-size: 24px;">SearchForge</h1>
    <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0;">Lizenz-Warnung</p>
</div>

<div style="background: #fff; padding: 30px; border: 1px solid #e1e4e8; border-top: none; border-radius: 0 0 8px 8px;">
    <h2 style="margin-top: 0; color: #856404;">Ihre Lizenz läuft bald ab</h2>

    <p>Ihre SearchForge-Lizenz läuft am <strong><?php echo esc_html( $expires_formatted ); ?></strong> ab.</p>

    <div style="background: #fff3cd; border: 1px solid #ffc107; padding: 15px; border-radius: 6px; margin: 20px 0;">
        <p style="margin: 0;"><strong>Lizenzschlüssel:</strong> <?php echo esc_html( $data['license_key'] ); ?></p>
    </div>

    <p>Nach Ablauf haben Sie eine 14-tägige Karenzzeit mit voller Funktionalität. Danach werden Premium-Features deaktiviert.</p>

    <p>Verlängern Sie Ihre Lizenz rechtzeitig, um unterbrechungsfreien Service zu gewährleisten.</p>

    <p style="color: #586069; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e4e8;">
        Bei Fragen kontaktieren Sie uns unter
        <a href="mailto:support@drossmedia.de" style="color: #667eea;">support@drossmedia.de</a>
    </p>
</div>

</body>
</html>
