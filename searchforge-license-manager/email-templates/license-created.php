<?php
/**
 * E-Mail-Template: Lizenzschlüssel-Zustellung.
 *
 * @var array{license_key: string, tier: string, plan: string} $data
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$tier_labels = [
    'free'         => 'Free',
    'professional' => 'Professional',
    'enterprise'   => 'Enterprise',
    'development'  => 'Development',
];

$tier_label = $tier_labels[ $data['tier'] ] ?? $data['tier'];
?>
<!DOCTYPE html>
<html lang="de">
<head><meta charset="UTF-8"></head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333;">

<div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 8px 8px 0 0; text-align: center;">
    <h1 style="color: white; margin: 0; font-size: 24px;">SearchForge</h1>
    <p style="color: rgba(255,255,255,0.9); margin: 5px 0 0;">License Manager</p>
</div>

<div style="background: #fff; padding: 30px; border: 1px solid #e1e4e8; border-top: none; border-radius: 0 0 8px 8px;">
    <h2 style="margin-top: 0;">Ihr Lizenzschlüssel</h2>

    <p>Vielen Dank für Ihren Kauf! Hier ist Ihr SearchForge Lizenzschlüssel:</p>

    <div style="background: #f6f8fa; border: 2px dashed #d1d5da; padding: 20px; text-align: center; border-radius: 6px; margin: 20px 0;">
        <code style="font-size: 20px; font-weight: bold; color: #24292e; letter-spacing: 1px;">
            <?php echo esc_html( $data['license_key'] ); ?>
        </code>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
        <tr>
            <td style="padding: 8px 0; color: #586069;">Lizenztyp:</td>
            <td style="padding: 8px 0; font-weight: bold;"><?php echo esc_html( $tier_label ); ?></td>
        </tr>
        <?php if ( $data['plan'] ) : ?>
        <tr>
            <td style="padding: 8px 0; color: #586069;">Plan:</td>
            <td style="padding: 8px 0; font-weight: bold;"><?php echo esc_html( ucfirst( $data['plan'] ) ); ?></td>
        </tr>
        <?php endif; ?>
    </table>

    <h3>Nächste Schritte</h3>
    <ol style="padding-left: 20px; line-height: 1.8;">
        <li>Installieren Sie SearchForge auf Ihrem Server</li>
        <li>Fügen Sie den Lizenzschlüssel in Ihre Konfiguration ein</li>
        <li>Starten Sie den Service – die Lizenz wird automatisch aktiviert</li>
    </ol>

    <p style="color: #586069; font-size: 14px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e1e4e8;">
        Bei Fragen kontaktieren Sie uns unter
        <a href="mailto:support@drossmedia.de" style="color: #667eea;">support@drossmedia.de</a>
    </p>
</div>

</body>
</html>
