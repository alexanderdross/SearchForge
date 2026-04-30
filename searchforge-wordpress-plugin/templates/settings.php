<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
defined( 'ABSPATH' ) || exit;

$settings    = SearchForge\Admin\Settings::get_all();
$property_id = SearchForge\Models\Property::get_active_property_id();
$property    = SearchForge\Models\Property::get( $property_id );
$properties  = SearchForge\Models\Property::get_all();

$properties_js = [];
foreach ( $properties as $prop ) {
	$has_gsc_creds = ! empty( $prop['gsc_client_id'] ) && ! empty( $prop['gsc_client_secret'] );
	$gsc_connected = ! empty( $prop['gsc_access_token'] );
	$properties_js[ (int) $prop['id'] ] = [
		'id'                    => (int) $prop['id'],
		'label'                 => $prop['label'],
		'domain'                => $prop['domain'],
		'is_default'            => ! empty( $prop['is_default'] ),
		'gsc_client_id'         => $prop['gsc_client_id'] ?? '',
		'gsc_client_secret'     => ! empty( $prop['gsc_client_secret'] ) ? '••••••••' : '',
		'gsc_client_secret_set' => ! empty( $prop['gsc_client_secret'] ),
		'gsc_connected'         => $gsc_connected,
		'gsc_property'          => $prop['gsc_property'] ?? '',
		'gsc_auth_url'          => ( $has_gsc_creds && ! $gsc_connected ) ? SearchForge\Integrations\GSC\OAuth::get_auth_url( (int) $prop['id'] ) : '',
		'bing_enabled'          => ! empty( $prop['bing_enabled'] ),
		'bing_api_key'          => ! empty( $prop['bing_api_key'] ) ? '••••••••' : '',
		'bing_api_key_set'      => ! empty( $prop['bing_api_key'] ),
		'bing_site_url'         => $prop['bing_site_url'] ?? '',
		'ga4_enabled'           => ! empty( $prop['ga4_enabled'] ),
		'ga4_property_id'       => $prop['ga4_property_id'] ?? '',
		'adobe_enabled'         => ! empty( $prop['adobe_enabled'] ),
		'adobe_org_id'          => $prop['adobe_org_id'] ?? '',
		'adobe_client_id'       => $prop['adobe_client_id'] ?? '',
		'adobe_client_secret'   => ! empty( $prop['adobe_client_secret'] ) ? '••••••••' : '',
		'adobe_client_secret_set' => ! empty( $prop['adobe_client_secret'] ),
		'adobe_report_suite_id' => $prop['adobe_report_suite_id'] ?? '',
	];
}

if ( isset( $_GET['gsc_connected'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Google Search Console connected successfully!', 'searchforge-wordpress-plugin' ); ?></p>
	</div>
<?php endif; ?>

<div class="wrap searchforge-wrap">
	<h1><?php esc_html_e( 'SearchForge Settings', 'searchforge-wordpress-plugin' ); ?></h1>

	<?php include SEARCHFORGE_PATH . 'templates/partials/property-selector.php'; ?>

	<form method="post" action="options.php">
		<?php settings_fields( 'searchforge_settings' ); ?>

		<!-- License -->
		<h2><?php esc_html_e( 'License', 'searchforge-wordpress-plugin' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="license_key"><?php esc_html_e( 'License Key', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="text" name="searchforge_settings[license_key]" id="license_key"
						value="<?php echo esc_attr( $settings['license_key'] ); ?>" class="regular-text"
						placeholder="SF-PRO-XXXXXXXXXXXXXXXX" />
					<p class="description">
						<?php esc_html_e( 'Current tier:', 'searchforge-wordpress-plugin' ); ?>
						<strong><?php echo esc_html( ucfirst( $settings['license_tier'] ) ); ?></strong>
					</p>
				</td>
			</tr>
		</table>

		<!-- Properties & Service Accounts -->
		<h2><?php esc_html_e( 'Properties & Service Accounts', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() && count( $properties ) >= 1 ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<p class="description">
			<?php esc_html_e( 'Each property maintains its own GSC, Bing, GA4, and Adobe Analytics credentials. Click "Configure" to manage service connections per property.', 'searchforge-wordpress-plugin' ); ?>
		</p>

		<table class="widefat sf-table sf-properties-table" id="sf-properties-table">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Label', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'Domain', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'GSC', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'Bing', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'GA4', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'Adobe', 'searchforge-wordpress-plugin' ); ?></th>
					<th><?php esc_html_e( 'Actions', 'searchforge-wordpress-plugin' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $properties as $prop ) :
					$svc_count = 0;
					if ( ! empty( $prop['gsc_access_token'] ) ) $svc_count++;
					if ( ! empty( $prop['bing_enabled'] ) && ! empty( $prop['bing_api_key'] ) ) $svc_count++;
					if ( ! empty( $prop['ga4_enabled'] ) && ! empty( $prop['ga4_property_id'] ) ) $svc_count++;
					if ( ! empty( $prop['adobe_enabled'] ) && ! empty( $prop['adobe_client_id'] ) ) $svc_count++;
				?>
					<tr data-property-id="<?php echo esc_attr( $prop['id'] ); ?>">
						<td>
							<strong><?php echo esc_html( $prop['label'] ); ?></strong>
							<?php if ( ! empty( $prop['is_default'] ) ) : ?>
								<em>(<?php esc_html_e( 'default', 'searchforge-wordpress-plugin' ); ?>)</em>
							<?php endif; ?>
						</td>
						<td><code><?php echo esc_html( $prop['domain'] ); ?></code></td>
						<td><?php echo ! empty( $prop['gsc_access_token'] )
							? '<span class="sf-status sf-status-connected">' . esc_html__( 'Connected', 'searchforge-wordpress-plugin' ) . '</span>'
							: '<span class="sf-status sf-status-disconnected">&mdash;</span>'; ?></td>
						<td><?php echo ( ! empty( $prop['bing_enabled'] ) && ! empty( $prop['bing_api_key'] ) )
							? '<span class="sf-status sf-status-connected">' . esc_html__( 'Connected', 'searchforge-wordpress-plugin' ) . '</span>'
							: '<span class="sf-status sf-status-disconnected">&mdash;</span>'; ?></td>
						<td><?php echo ( ! empty( $prop['ga4_enabled'] ) && ! empty( $prop['ga4_property_id'] ) )
							? '<span class="sf-status sf-status-connected">' . esc_html__( 'Connected', 'searchforge-wordpress-plugin' ) . '</span>'
							: '<span class="sf-status sf-status-disconnected">&mdash;</span>'; ?></td>
						<td><?php echo ( ! empty( $prop['adobe_enabled'] ) && ! empty( $prop['adobe_client_id'] ) )
							? '<span class="sf-status sf-status-connected">' . esc_html__( 'Connected', 'searchforge-wordpress-plugin' ) . '</span>'
							: '<span class="sf-status sf-status-disconnected">&mdash;</span>'; ?></td>
						<td class="sf-property-actions">
							<button type="button" class="button button-small sf-configure-property" data-id="<?php echo esc_attr( $prop['id'] ); ?>">
								<?php esc_html_e( 'Configure', 'searchforge-wordpress-plugin' ); ?>
							</button>
							<?php if ( $svc_count > 0 ) : ?>
								<button type="button" class="button button-small sf-sync-property-btn" data-id="<?php echo esc_attr( $prop['id'] ); ?>">
									<?php esc_html_e( 'Sync', 'searchforge-wordpress-plugin' ); ?>
								</button>
							<?php endif; ?>
							<?php if ( empty( $prop['is_default'] ) ) : ?>
								<button type="button" class="button button-small sf-remove-property" data-id="<?php echo esc_attr( $prop['id'] ); ?>">
									<?php esc_html_e( 'Remove', 'searchforge-wordpress-plugin' ); ?>
								</button>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<?php if ( SearchForge\Admin\Settings::is_pro() ) : ?>
			<div class="sf-add-property" style="margin-top: 12px;">
				<input type="text" id="sf-new-property-label" placeholder="<?php esc_attr_e( 'Label (e.g. Blog, Shop DE)', 'searchforge-wordpress-plugin' ); ?>" class="regular-text" />
				<input type="text" id="sf-new-property-domain" placeholder="<?php esc_attr_e( 'domain.com', 'searchforge-wordpress-plugin' ); ?>" class="regular-text" />
				<button type="button" class="button button-primary" id="sf-add-property-btn">
					<?php esc_html_e( 'Add Property', 'searchforge-wordpress-plugin' ); ?>
				</button>
			</div>
		<?php endif; ?>

		<!-- Google Keyword Planner (Pro) -->
		<h2><?php esc_html_e( 'Google Keyword Planner', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[kwp_enabled]" value="1"
							<?php checked( $settings['kwp_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Enable Keyword Planner integration (volume enrichment & content gaps)', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="kwp_customer_id"><?php esc_html_e( 'Customer ID', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="text" name="searchforge_settings[kwp_customer_id]" id="kwp_customer_id"
						value="<?php echo esc_attr( $settings['kwp_customer_id'] ); ?>" class="regular-text"
						placeholder="123-456-7890"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
					<p class="description">
						<?php esc_html_e( 'Google Ads Customer ID (requires active Ads account, even with $0 spend)', 'searchforge-wordpress-plugin' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="kwp_developer_token"><?php esc_html_e( 'Developer Token', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="password" name="searchforge_settings[kwp_developer_token]" id="kwp_developer_token"
						value="<?php echo esc_attr( $settings['kwp_developer_token'] ); ?>" class="regular-text"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
					<p class="description">
						<?php esc_html_e( 'From Google Ads > Tools > API Center', 'searchforge-wordpress-plugin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Google Trends (Pro) -->
		<h2><?php esc_html_e( 'Google Trends', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[trends_enabled]" value="1"
							<?php checked( $settings['trends_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Enable Google Trends integration (seasonality, rising queries)', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="serpapi_key"><?php esc_html_e( 'SerpApi Key', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="password" name="searchforge_settings[serpapi_key]" id="serpapi_key"
						value="<?php echo esc_attr( $settings['serpapi_key'] ); ?>" class="regular-text"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
					<p class="description">
						<?php esc_html_e( 'From serpapi.com — used for Google Trends data', 'searchforge-wordpress-plugin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- AI Content Briefs (Pro) -->
		<h2><?php esc_html_e( 'AI Content Briefs', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row">
					<label for="ai_provider"><?php esc_html_e( 'AI Provider', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<select name="searchforge_settings[ai_provider]" id="ai_provider"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?>>
						<option value="openai" <?php selected( $settings['ai_provider'], 'openai' ); ?>>
							<?php esc_html_e( 'OpenAI', 'searchforge-wordpress-plugin' ); ?>
						</option>
						<option value="anthropic" <?php selected( $settings['ai_provider'], 'anthropic' ); ?>>
							<?php esc_html_e( 'Anthropic (Claude)', 'searchforge-wordpress-plugin' ); ?>
						</option>
					</select>
					<p class="description">
						<?php esc_html_e( 'Optional. Without an API key, briefs use built-in heuristic analysis.', 'searchforge-wordpress-plugin' ); ?>
					</p>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="ai_api_key"><?php esc_html_e( 'API Key', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="password" name="searchforge_settings[ai_api_key]" id="ai_api_key"
						value="<?php echo esc_attr( $settings['ai_api_key'] ); ?>" class="regular-text"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
					<p class="description">
						<?php esc_html_e( 'Your own API key. Briefs work without it using heuristic analysis.', 'searchforge-wordpress-plugin' ); ?>
					</p>
				</td>
			</tr>
		</table>

		<!-- Alerts & Monitoring (Pro) -->
		<h2><?php esc_html_e( 'Alerts & Monitoring', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Alerts', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[alerts_enabled]" value="1"
							<?php checked( $settings['alerts_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Enable email alerts for ranking drops, traffic anomalies, and content decay', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="alert_email"><?php esc_html_e( 'Alert Email', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="email" name="searchforge_settings[alert_email]" id="alert_email"
						value="<?php echo esc_attr( $settings['alert_email'] ); ?>" class="regular-text"
						placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Ranking Drop Threshold', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<input type="number" name="searchforge_settings[alert_ranking_drop_threshold]"
						value="<?php echo esc_attr( $settings['alert_ranking_drop_threshold'] ); ?>"
						min="1" max="20" class="small-text"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
					<?php esc_html_e( 'positions', 'searchforge-wordpress-plugin' ); ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Traffic Anomaly Detection', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[alert_traffic_anomaly]" value="1"
							<?php checked( $settings['alert_traffic_anomaly'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Alert on unusual traffic spikes or drops (statistical outlier detection)', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Weekly Digest', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[weekly_digest_enabled]" value="1"
							<?php checked( $settings['weekly_digest_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Send weekly summary email with key metrics and changes', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Broken Link Detection', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[broken_links_enabled]" value="1"
							<?php checked( $settings['broken_links_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Scan pages for broken outbound links during daily sync', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<!-- Webhook Notifications (Pro) -->
		<h2><?php esc_html_e( 'Webhook Notifications', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'Enable Webhooks', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[webhook_enabled]" value="1"
							<?php checked( $settings['webhook_enabled'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Send webhook notifications on sync events and alerts', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="webhook_url"><?php esc_html_e( 'Webhook URL', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<input type="url" name="searchforge_settings[webhook_url]" id="webhook_url"
						value="<?php echo esc_attr( $settings['webhook_url'] ); ?>" class="regular-text"
						placeholder="https://hooks.slack.com/services/..."
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
				</td>
			</tr>
			<tr>
				<th scope="row">
					<label for="webhook_format"><?php esc_html_e( 'Format', 'searchforge-wordpress-plugin' ); ?></label>
				</th>
				<td>
					<select name="searchforge_settings[webhook_format]" id="webhook_format"
						<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?>>
						<option value="json" <?php selected( $settings['webhook_format'], 'json' ); ?>>
							<?php esc_html_e( 'JSON (generic)', 'searchforge-wordpress-plugin' ); ?>
						</option>
						<option value="slack" <?php selected( $settings['webhook_format'], 'slack' ); ?>>
							<?php esc_html_e( 'Slack', 'searchforge-wordpress-plugin' ); ?>
						</option>
					</select>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Alert Notifications', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[webhook_on_alerts]" value="1"
							<?php checked( $settings['webhook_on_alerts'] ); ?>
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?> />
						<?php esc_html_e( 'Also send webhook for new alerts (ranking drops, anomalies)', 'searchforge-wordpress-plugin' ); ?>
					</label>
				</td>
			</tr>
		</table>

		<!-- General Settings -->
		<h2><?php esc_html_e( 'General', 'searchforge-wordpress-plugin' ); ?></h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'llms.txt', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<label>
						<input type="checkbox" name="searchforge_settings[llms_txt_enabled]" value="1"
							<?php checked( $settings['llms_txt_enabled'] ); ?> />
						<?php esc_html_e( 'Enable llms.txt and llms-full.txt endpoints', 'searchforge-wordpress-plugin' ); ?>
					</label>
					<?php if ( $settings['llms_txt_enabled'] ) : ?>
						<p class="description">
							<?php echo esc_html( home_url( '/llms.txt' ) ); ?> |
							<?php echo esc_html( home_url( '/llms-full.txt' ) ); ?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Sync Frequency', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<?php $schedule_options = SearchForge\Scheduler\Manager::get_schedule_options(); ?>
					<select name="searchforge_settings[sync_frequency]">
						<?php foreach ( $schedule_options as $value => $label ) : ?>
							<option value="<?php echo esc_attr( $value ); ?>"
								<?php selected( $settings['sync_frequency'], $value ); ?>>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<?php
					$next_run = SearchForge\Scheduler\Manager::get_next_run();
					if ( $next_run ) :
					?>
						<p class="description">
							<?php
							printf(
								/* translators: %s: next scheduled run date/time */
								esc_html__( 'Next sync: %s', 'searchforge-wordpress-plugin' ),
								esc_html( $next_run )
							);
							?>
						</p>
					<?php endif; ?>
				</td>
			</tr>
		</table>

		<!-- REST API Access (Pro) -->
		<h2><?php esc_html_e( 'REST API Access', 'searchforge-wordpress-plugin' ); ?>
			<?php if ( ! SearchForge\Admin\Settings::is_pro() ) : ?>
				<span class="sf-pro-badge">Pro</span>
			<?php endif; ?>
		</h2>
		<table class="form-table">
			<tr>
				<th scope="row"><?php esc_html_e( 'API Key', 'searchforge-wordpress-plugin' ); ?></th>
				<td>
					<?php if ( SearchForge\Api\ApiKeyAuth::has_key() ) : ?>
						<span class="sf-status sf-status-connected">
							<?php esc_html_e( 'Active', 'searchforge-wordpress-plugin' ); ?>
						</span>
						<button type="button" class="button" id="sf-regenerate-api-key"
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?>>
							<?php esc_html_e( 'Regenerate', 'searchforge-wordpress-plugin' ); ?>
						</button>
						<button type="button" class="button" id="sf-revoke-api-key"
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?>>
							<?php esc_html_e( 'Revoke', 'searchforge-wordpress-plugin' ); ?>
						</button>
					<?php else : ?>
						<button type="button" class="button button-primary" id="sf-generate-api-key"
							<?php disabled( ! SearchForge\Admin\Settings::is_pro() ); ?>>
							<?php esc_html_e( 'Generate API Key', 'searchforge-wordpress-plugin' ); ?>
						</button>
					<?php endif; ?>
					<p class="description">
						<?php esc_html_e( 'Use this key to access the SearchForge REST API from external tools.', 'searchforge-wordpress-plugin' ); ?>
					</p>
					<p class="description">
						<?php
							/* translators: %s: REST API base URL */
							echo esc_html( sprintf( __( 'Base URL: %s', 'searchforge-wordpress-plugin' ), rest_url( 'searchforge/v1/' ) ) ); ?>
					</p>
					<div id="sf-api-key-display" style="display:none;">
						<p class="description" style="color:#d63638;">
							<?php esc_html_e( 'Copy this key now — it will not be shown again:', 'searchforge-wordpress-plugin' ); ?>
						</p>
						<code id="sf-api-key-value" style="font-size:14px;padding:4px 8px;"></code>
					</div>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	</form>

	<!-- Property Configuration Modal -->
	<div id="sf-property-config-modal" class="sf-modal" style="display:none;">
		<div class="sf-modal-content sf-property-modal-content">
			<span class="sf-modal-close">&times;</span>
			<h2 id="sf-prop-config-title"></h2>
			<input type="hidden" id="sf-prop-config-id" value="" />

			<nav class="nav-tab-wrapper sf-config-nav">
				<a href="#" class="nav-tab nav-tab-active" data-config-tab="sf-cfg-gsc">
					<span class="sf-cfg-tab-indicator" id="sf-cfg-tab-gsc-dot"></span>
					<?php esc_html_e( 'Google Search Console', 'searchforge-wordpress-plugin' ); ?>
				</a>
				<a href="#" class="nav-tab" data-config-tab="sf-cfg-bing">
					<span class="sf-cfg-tab-indicator" id="sf-cfg-tab-bing-dot"></span>
					<?php esc_html_e( 'Bing Webmaster', 'searchforge-wordpress-plugin' ); ?>
				</a>
				<a href="#" class="nav-tab" data-config-tab="sf-cfg-ga4">
					<span class="sf-cfg-tab-indicator" id="sf-cfg-tab-ga4-dot"></span>
					<?php esc_html_e( 'Google Analytics 4', 'searchforge-wordpress-plugin' ); ?>
				</a>
				<a href="#" class="nav-tab" data-config-tab="sf-cfg-adobe">
					<span class="sf-cfg-tab-indicator" id="sf-cfg-tab-adobe-dot"></span>
					<?php esc_html_e( 'Adobe Analytics', 'searchforge-wordpress-plugin' ); ?>
				</a>
			</nav>

			<!-- GSC Panel -->
			<div class="sf-cfg-panel sf-cfg-panel-active" id="sf-cfg-gsc">
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="sf-cfg-gsc-client-id"><?php esc_html_e( 'Client ID', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-gsc-client-id" class="regular-text" />
							<p class="description">
								<?php esc_html_e( 'From Google Cloud Console > APIs & Services > Credentials', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-gsc-client-secret"><?php esc_html_e( 'Client Secret', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="password" id="sf-cfg-gsc-client-secret" class="regular-text" placeholder="<?php esc_attr_e( 'Enter new secret to update', 'searchforge-wordpress-plugin' ); ?>" />
							<span id="sf-cfg-gsc-secret-status"></span>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Authorized Redirect URI', 'searchforge-wordpress-plugin' ); ?></th>
						<td>
							<code id="sf-cfg-gsc-redirect-uri"><?php echo esc_html( \SearchForge\Integrations\GSC\OAuth::get_redirect_uri() ); ?></code>
							<button type="button" class="button button-small sf-copy-btn" data-copy-target="sf-cfg-gsc-redirect-uri" title="<?php esc_attr_e( 'Copy to clipboard', 'searchforge-wordpress-plugin' ); ?>">
								<?php esc_html_e( 'Copy', 'searchforge-wordpress-plugin' ); ?>
							</button>
							<p class="description">
								<?php
								printf(
									/* translators: %s: link to Google Cloud Console credentials page */
									esc_html__( 'Add this URL as an "Authorized redirect URI" in your %s when creating or editing your OAuth 2.0 Client ID.', 'searchforge-wordpress-plugin' ),
									'<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Google Cloud Console Credentials', 'searchforge-wordpress-plugin' ) . '</a>'
								);
								?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Connection', 'searchforge-wordpress-plugin' ); ?></th>
						<td id="sf-cfg-gsc-status-cell"></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-gsc-property"><?php esc_html_e( 'GSC Property URL', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-gsc-property" class="regular-text" placeholder="https://example.com/" />
							<p class="description">
								<?php esc_html_e( 'The verified site URL in Google Search Console (e.g. https://example.com/ or sc-domain:example.com).', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Bing Panel -->
			<div class="sf-cfg-panel" id="sf-cfg-bing">
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable', 'searchforge-wordpress-plugin' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="sf-cfg-bing-enabled" />
								<?php esc_html_e( 'Enable Bing Webmaster Tools integration', 'searchforge-wordpress-plugin' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-bing-api-key"><?php esc_html_e( 'API Key', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="password" id="sf-cfg-bing-api-key" class="regular-text" placeholder="<?php esc_attr_e( 'Enter new key to update', 'searchforge-wordpress-plugin' ); ?>" />
							<span id="sf-cfg-bing-key-status"></span>
							<p class="description">
								<?php esc_html_e( 'From Bing Webmaster Tools > Settings > API Access', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-bing-site-url"><?php esc_html_e( 'Site URL', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="url" id="sf-cfg-bing-site-url" class="regular-text" placeholder="https://example.com" />
						</td>
					</tr>
				</table>
			</div>

			<!-- GA4 Panel -->
			<div class="sf-cfg-panel" id="sf-cfg-ga4">
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable', 'searchforge-wordpress-plugin' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="sf-cfg-ga4-enabled" />
								<?php esc_html_e( 'Enable GA4 integration (bounce rate, engagement, conversions)', 'searchforge-wordpress-plugin' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-ga4-property-id"><?php esc_html_e( 'Property ID', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-ga4-property-id" class="regular-text" placeholder="123456789" />
							<p class="description">
								<?php esc_html_e( 'GA4 Property ID (numeric). Uses the same Google OAuth connection as GSC.', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<!-- Adobe Panel -->
			<div class="sf-cfg-panel" id="sf-cfg-adobe">
				<p class="description" style="margin: 12px 0;">
					<?php esc_html_e( 'Connect Adobe Analytics for sites using non-WordPress backends (Drupal, custom CMS). Uses OAuth Server-to-Server authentication.', 'searchforge-wordpress-plugin' ); ?>
				</p>
				<table class="form-table">
					<tr>
						<th scope="row"><?php esc_html_e( 'Enable', 'searchforge-wordpress-plugin' ); ?></th>
						<td>
							<label>
								<input type="checkbox" id="sf-cfg-adobe-enabled" />
								<?php esc_html_e( 'Enable Adobe Analytics (visits, pageviews, bounce rate, conversions, revenue)', 'searchforge-wordpress-plugin' ); ?>
							</label>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-adobe-org-id"><?php esc_html_e( 'Organization ID', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-adobe-org-id" class="regular-text" placeholder="XXXXXXXXXXXXX@AdobeOrg" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-adobe-client-id"><?php esc_html_e( 'Client ID', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-adobe-client-id" class="regular-text" />
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-adobe-client-secret"><?php esc_html_e( 'Client Secret', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="password" id="sf-cfg-adobe-client-secret" class="regular-text" placeholder="<?php esc_attr_e( 'Enter new secret to update', 'searchforge-wordpress-plugin' ); ?>" />
							<span id="sf-cfg-adobe-secret-status"></span>
							<p class="description">
								<?php esc_html_e( 'Encrypted at rest with AES-256-CBC.', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="sf-cfg-adobe-report-suite-id"><?php esc_html_e( 'Report Suite ID', 'searchforge-wordpress-plugin' ); ?></label>
						</th>
						<td>
							<input type="text" id="sf-cfg-adobe-report-suite-id" class="regular-text" placeholder="myreportsuiteid" />
							<p class="description">
								<?php esc_html_e( 'Find this in Adobe Analytics under Admin > Report Suites.', 'searchforge-wordpress-plugin' ); ?>
							</p>
						</td>
					</tr>
				</table>
			</div>

			<div class="sf-config-footer">
				<button type="button" class="button button-primary" id="sf-save-property-config">
					<?php esc_html_e( 'Save Configuration', 'searchforge-wordpress-plugin' ); ?>
				</button>
				<button type="button" class="button sf-modal-close">
					<?php esc_html_e( 'Cancel', 'searchforge-wordpress-plugin' ); ?>
				</button>
				<span id="sf-prop-config-status"></span>
			</div>
		</div>
	</div>

	<script>var sfPropertyData = <?php echo wp_json_encode( $properties_js ); ?>;</script>
</div>
<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
