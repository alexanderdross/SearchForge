/* global jQuery, searchforge */
(function ($) {
	'use strict';

	// GSC Sync button.
	$(document).on('click', '#sf-sync-btn', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Syncing...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_sync_gsc',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success) {
				var d = response.data;
				alert('Sync complete: ' + d.pages_synced + ' pages, ' + d.keywords_synced + ' keywords.');
				location.reload();
			} else {
				alert('Sync failed: ' + (response.data && response.data.message || 'Unknown error'));
				$btn.prop('disabled', false).text('Sync Now');
			}
		}).fail(function () {
			alert('Network error. Please try again.');
			$btn.prop('disabled', false).text('Sync Now');
		});
	});

	// GSC Disconnect button.
	$(document).on('click', '#sf-disconnect-gsc', function () {
		if (!confirm('Disconnect Google Search Console?')) return;

		$.post(searchforge.ajax_url, {
			action: 'searchforge_disconnect_gsc',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success) {
				location.reload();
			} else {
				alert('Error: ' + (response.data && response.data.message || 'Unknown error'));
			}
		});
	});

	// Export brief for a page.
	$(document).on('click', '.sf-export-btn', function () {
		var $btn = $(this);
		var pagePath = $btn.data('page');
		$btn.prop('disabled', true);

		$.post(searchforge.ajax_url, {
			action: 'searchforge_export_brief',
			nonce: searchforge.nonce,
			page_path: pagePath,
			brief_type: 'page'
		}, function (response) {
			if (response.success) {
				showModal('Brief: ' + pagePath, response.data.markdown, response.data.filename);
			} else {
				alert('Export failed: ' + (response.data && response.data.message || 'Unknown error'));
			}
			$btn.prop('disabled', false);
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false);
		});
	});

	// Export site brief.
	$(document).on('click', '#sf-export-site', function () {
		var $btn = $(this);
		$btn.prop('disabled', true).text('Generating...');

		$.get(searchforge.rest_url + 'export/site', {}, function (response) {
			if (response.markdown) {
				showModal('Site Brief', response.markdown, 'searchforge-site-brief.md');
			} else {
				alert('Export failed: ' + (response.error || 'Unknown error'));
			}
			$btn.prop('disabled', false).text('Export Site Brief (.md)');
		}).fail(function (xhr) {
			var msg = xhr.responseJSON && xhr.responseJSON.error || 'Network error';
			alert('Export failed: ' + msg);
			$btn.prop('disabled', false).text('Export Site Brief (.md)');
		});
	});

	// Modal.
	var sfModalTrigger = null;

	function showModal(title, content, filename) {
		sfModalTrigger = document.activeElement;
		$('#sf-modal-title').text(title);
		$('#sf-modal-body').text(content);
		$('#sf-modal-download').data('content', content).data('filename', filename);
		$('#sf-export-modal').show();
		// Focus the first focusable element in the modal.
		var $modal = $('#sf-export-modal');
		var $focusable = $modal.find('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])').filter(':visible');
		if ($focusable.length) {
			$focusable.first().trigger('focus');
		}
	}

	function closeModal($modal) {
		$modal.hide();
		// Return focus to the element that triggered the modal.
		if (sfModalTrigger) {
			sfModalTrigger.focus();
			sfModalTrigger = null;
		}
	}

	$(document).on('click', '.sf-modal-close', function () {
		closeModal($(this).closest('.sf-modal'));
	});

	// Close modals with Escape key.
	$(document).on('keydown', function (e) {
		if (e.key === 'Escape' || e.keyCode === 27) {
			var $visibleModal = $('.sf-modal:visible');
			if ($visibleModal.length) {
				closeModal($visibleModal);
			}
		}
	});

	$(document).on('click', '#sf-modal-download', function () {
		var content = $(this).data('content');
		var filename = $(this).data('filename');
		var blob = new Blob([content], { type: 'text/markdown;charset=utf-8' });
		var url = URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
	});

	// AI Content Brief button.
	$(document).on('click', '.sf-ai-brief-btn', function () {
		var $btn = $(this);
		var pagePath = $btn.data('page');
		$btn.prop('disabled', true).text('Generating...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_generate_content_brief',
			nonce: searchforge.nonce,
			page_path: pagePath
		}, function (response) {
			if (response.success) {
				var title = 'AI Content Brief: ' + pagePath;
				if (response.data.method === 'heuristic') {
					title += ' (Heuristic)';
				}
				showModal(title, response.data.brief, response.data.filename);
			} else {
				alert('Brief generation failed: ' + (response.data && response.data.message || 'Unknown error'));
			}
			$btn.prop('disabled', false).text('AI Brief');
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false).text('AI Brief');
		});
	});

	// Dismiss alert.
	$(document).on('click', '.sf-dismiss-alert', function () {
		var $btn = $(this);
		var alertId = $btn.data('alert-id');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_dismiss_alert',
			nonce: searchforge.nonce,
			alert_id: alertId
		}, function (response) {
			if (response.success) {
				$btn.closest('.sf-alert').fadeOut();
			}
		});
	});

	// Data export (CSV/JSON).
	$(document).on('click', '.sf-data-export-btn', function () {
		var $btn = $(this);
		var type = $btn.data('type');
		var format = $btn.data('format');
		$btn.prop('disabled', true).text('Exporting...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_export_data',
			nonce: searchforge.nonce,
			export_type: type,
			export_format: format
		}, function (response) {
			if (response.success) {
				var blob = new Blob([response.data.data], { type: response.data.mime + ';charset=utf-8' });
				var url = URL.createObjectURL(blob);
				var a = document.createElement('a');
				a.href = url;
				a.download = response.data.filename;
				document.body.appendChild(a);
				a.click();
				document.body.removeChild(a);
				URL.revokeObjectURL(url);
			} else {
				alert('Export failed: ' + (response.data && response.data.message || 'Unknown error'));
			}
			$btn.prop('disabled', false).text('Export ' + format.toUpperCase());
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false).text('Export ' + format.toUpperCase());
		});
	});

	// Sitemap discovery.
	$(document).on('click', '#sf-discover-sitemaps', function () {
		var $btn = $(this);
		var $results = $('#sf-sitemap-results');
		$btn.prop('disabled', true).text('Discovering...');
		$results.html('<span class="sf-spinner"></span>');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_discover_sitemaps',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success && response.data.sitemaps.length) {
				var html = '<ul>';
				response.data.sitemaps.forEach(function (s) {
					html += '<li><code>' + s.url + '</code> — ' + s.url_count + ' URLs</li>';
				});
				html += '</ul>';
				$results.html(html);
			} else {
				$results.html('<p>No sitemaps found.</p>');
			}
			$btn.prop('disabled', false).text('Discover Sitemaps');
		}).fail(function () {
			$results.html('<p class="sf-error">Network error.</p>');
			$btn.prop('disabled', false).text('Discover Sitemaps');
		});
	});

	// Broken link scan trigger.
	$(document).on('click', '#sf-scan-broken-links', function () {
		var $btn = $(this);
		var $results = $('#sf-broken-link-results');
		$btn.prop('disabled', true).text('Scanning...');
		$results.html('<span class="sf-spinner"></span> Scanning pages for broken links...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_scan_broken_links',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success) {
				if (response.data.count === 0) {
					$results.html('<p style="color:#155724;font-weight:500;">No broken links found.</p>');
				} else {
					$results.html('<p class="sf-error">' + response.data.count + ' broken link(s) found. Reload the page to see details.</p>');
				}
			} else {
				$results.html('<p class="sf-error">' + (response.data && response.data.message || 'Scan failed.') + '</p>');
			}
			$btn.prop('disabled', false).text('Scan Now');
		}).fail(function () {
			$results.html('<p class="sf-error">Network error.</p>');
			$btn.prop('disabled', false).text('Scan Now');
		});
	});

	// API key management.
	$(document).on('click', '#sf-generate-api-key, #sf-regenerate-api-key', function () {
		var $btn = $(this);
		var isRegen = $btn.attr('id') === 'sf-regenerate-api-key';
		if (isRegen && !confirm('This will invalidate the existing API key. Continue?')) {
			return;
		}
		$btn.prop('disabled', true);

		$.post(searchforge.ajax_url, {
			action: 'searchforge_generate_api_key',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success) {
				$('#sf-api-key-value').text(response.data.key);
				$('#sf-api-key-display').show();
			} else {
				alert(response.data && response.data.message || 'Failed to generate key.');
			}
			$btn.prop('disabled', false);
		});
	});

	$(document).on('click', '#sf-revoke-api-key', function () {
		if (!confirm('Revoke the API key? External tools will lose access.')) {
			return;
		}
		$.post(searchforge.ajax_url, {
			action: 'searchforge_revoke_api_key',
			nonce: searchforge.nonce
		}, function (response) {
			if (response.success) {
				location.reload();
			}
		});
	});

	// Close modal on outside click.
	$(document).on('click', '#sf-export-modal, #sf-bulk-modal', function (e) {
		if (e.target === this) {
			closeModal($(this));
		}
	});

	// Bulk select: checkboxes.
	function updateBulkButtons() {
		var count = $('.sf-page-check:checked').length;
		$('#sf-bulk-export, #sf-bulk-ai-brief').prop('disabled', count === 0);
		$('#sf-bulk-count').text(count > 0 ? count + ' selected' : '');
	}

	$(document).on('change', '#sf-select-all, .sf-select-all-th', function () {
		$('.sf-page-check').prop('checked', this.checked);
		updateBulkButtons();
	});

	$(document).on('change', '.sf-page-check', function () {
		var total = $('.sf-page-check').length;
		var checked = $('.sf-page-check:checked').length;
		$('#sf-select-all, .sf-select-all-th').prop('checked', checked === total);
		updateBulkButtons();
	});

	// Bulk export briefs.
	$(document).on('click', '#sf-bulk-export', function () {
		var paths = $('.sf-page-check:checked').map(function () { return $(this).val(); }).get();
		if (!paths.length) return;

		$('#sf-bulk-modal-title').text('Exporting ' + paths.length + ' briefs...');
		$('#sf-bulk-progress').show();
		$('#sf-bulk-output, #sf-bulk-download').hide();
		$('#sf-bulk-fill').css('width', '0%');
		$('#sf-bulk-status').text('Starting...');
		$('#sf-bulk-modal').show();

		var results = [];
		var completed = 0;

		function exportNext() {
			if (completed >= paths.length) {
				// All done — combine and offer download.
				var combined = results.join('\n\n---\n\n');
				$('#sf-bulk-status').text('Done! ' + paths.length + ' briefs exported.');
				$('#sf-bulk-output').text(combined).show();
				$('#sf-bulk-download').data('content', combined).data('filename', 'searchforge-bulk-briefs.md').show();
				return;
			}

			var path = paths[completed];
			$('#sf-bulk-status').text('Exporting: ' + path + ' (' + (completed + 1) + '/' + paths.length + ')');

			$.post(searchforge.ajax_url, {
				action: 'searchforge_export_brief',
				nonce: searchforge.nonce,
				page_path: path,
				brief_type: 'page'
			}, function (response) {
				if (response.success) {
					results.push(response.data.markdown);
				} else {
					results.push('# Error: ' + path + '\n\n' + (response.data && response.data.message || 'Failed'));
				}
				completed++;
				$('#sf-bulk-fill').css('width', (completed / paths.length * 100) + '%');
				exportNext();
			}).fail(function () {
				results.push('# Error: ' + path + '\n\nNetwork error');
				completed++;
				$('#sf-bulk-fill').css('width', (completed / paths.length * 100) + '%');
				exportNext();
			});
		}

		exportNext();
	});

	// Bulk AI brief.
	$(document).on('click', '#sf-bulk-ai-brief', function () {
		var paths = $('.sf-page-check:checked').map(function () { return $(this).val(); }).get();
		if (!paths.length) return;
		if (!confirm('Generate AI briefs for ' + paths.length + ' pages? This may take a while.')) return;

		$('#sf-bulk-modal-title').text('Generating ' + paths.length + ' AI briefs...');
		$('#sf-bulk-progress').show();
		$('#sf-bulk-output, #sf-bulk-download').hide();
		$('#sf-bulk-fill').css('width', '0%');
		$('#sf-bulk-status').text('Starting...');
		$('#sf-bulk-modal').show();

		var results = [];
		var completed = 0;

		function generateNext() {
			if (completed >= paths.length) {
				var combined = results.join('\n\n---\n\n');
				$('#sf-bulk-status').text('Done! ' + paths.length + ' AI briefs generated.');
				$('#sf-bulk-output').text(combined).show();
				$('#sf-bulk-download').data('content', combined).data('filename', 'searchforge-bulk-ai-briefs.md').show();
				return;
			}

			var path = paths[completed];
			$('#sf-bulk-status').text('Generating: ' + path + ' (' + (completed + 1) + '/' + paths.length + ')');

			$.post(searchforge.ajax_url, {
				action: 'searchforge_generate_content_brief',
				nonce: searchforge.nonce,
				page_path: path
			}, function (response) {
				if (response.success) {
					results.push(response.data.brief);
				} else {
					results.push('# Error: ' + path + '\n\n' + (response.data && response.data.message || 'Failed'));
				}
				completed++;
				$('#sf-bulk-fill').css('width', (completed / paths.length * 100) + '%');
				generateNext();
			}).fail(function () {
				results.push('# Error: ' + path + '\n\nNetwork error');
				completed++;
				$('#sf-bulk-fill').css('width', (completed / paths.length * 100) + '%');
				generateNext();
			});
		}

		generateNext();
	});

	// Bulk download button.
	$(document).on('click', '#sf-bulk-download', function () {
		var content = $(this).data('content');
		var filename = $(this).data('filename');
		var blob = new Blob([content], { type: 'text/markdown;charset=utf-8' });
		var url = URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = filename;
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		URL.revokeObjectURL(url);
	});

	// Competitor tab navigation.
	$(document).on('click', '.searchforge-wrap .nav-tab', function (e) {
		var tab = $(this).data('tab');
		if (!tab) return;
		e.preventDefault();
		$(this).closest('.nav-tab-wrapper').find('.nav-tab').removeClass('nav-tab-active').attr('aria-selected', 'false');
		$(this).addClass('nav-tab-active').attr('aria-selected', 'true');
		$('.sf-tab-panel').removeClass('sf-tab-active');
		$('#' + tab).addClass('sf-tab-active');
	});

	// Add competitor.
	$(document).on('click', '#sf-add-competitor', function () {
		var domain = $('#sf-competitor-domain').val().trim();
		var label = $('#sf-competitor-label').val().trim();
		if (!domain) {
			alert('Please enter a competitor domain.');
			return;
		}
		var $btn = $(this);
		$btn.prop('disabled', true);

		$.post(searchforge.ajax_url, {
			action: 'searchforge_add_competitor',
			nonce: searchforge.nonce,
			domain: domain,
			label: label
		}, function (response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data && response.data.message || 'Failed to add competitor.');
				$btn.prop('disabled', false);
			}
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false);
		});
	});

	// Remove competitor.
	$(document).on('click', '.sf-remove-competitor', function () {
		if (!confirm('Remove this competitor and all its keyword data?')) return;
		var $btn = $(this);
		var id = $btn.data('id');
		$btn.prop('disabled', true);

		$.post(searchforge.ajax_url, {
			action: 'searchforge_remove_competitor',
			nonce: searchforge.nonce,
			competitor_id: id
		}, function (response) {
			if (response.success) {
				location.reload();
			} else {
				alert(response.data && response.data.message || 'Failed.');
				$btn.prop('disabled', false);
			}
		});
	});

	// Sync competitor keywords.
	$(document).on('click', '.sf-sync-competitor', function () {
		var $btn = $(this);
		var id = $btn.data('id');
		$btn.prop('disabled', true).text('Syncing...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_sync_competitor',
			nonce: searchforge.nonce,
			competitor_id: id
		}, function (response) {
			if (response.success) {
				alert(response.data.message);
				location.reload();
			} else {
				alert(response.data && response.data.message || 'Sync failed.');
				$btn.prop('disabled', false).text('Sync Keywords');
			}
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false).text('Sync Keywords');
		});
	});
	// --- Property Configuration Modal ---

	// Open property config modal.
	$(document).on('click', '.sf-configure-property', function () {
		var id = $(this).data('id');
		var prop = (typeof sfPropertyData !== 'undefined') ? sfPropertyData[id] : null;
		if (!prop) return;

		$('#sf-prop-config-id').val(id);
		$('#sf-prop-config-title').text('Configure: ' + prop.label + ' (' + prop.domain + ')');

		// Populate GSC fields.
		$('#sf-cfg-gsc-client-id').val(prop.gsc_client_id || '');
		$('#sf-cfg-gsc-client-secret').val('');
		if (prop.gsc_client_secret_set) {
			$('#sf-cfg-gsc-secret-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
		} else {
			$('#sf-cfg-gsc-secret-status').html('');
		}
		$('#sf-cfg-gsc-property').val(prop.gsc_property || '');
		renderGscStatus(prop);

		// Populate Bing fields.
		$('#sf-cfg-bing-enabled').prop('checked', !!prop.bing_enabled);
		$('#sf-cfg-bing-api-key').val('');
		if (prop.bing_api_key_set) {
			$('#sf-cfg-bing-key-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
		} else {
			$('#sf-cfg-bing-key-status').html('');
		}
		$('#sf-cfg-bing-site-url').val(prop.bing_site_url || '');

		// Populate GA4 fields.
		$('#sf-cfg-ga4-enabled').prop('checked', !!prop.ga4_enabled);
		$('#sf-cfg-ga4-property-id').val(prop.ga4_property_id || '');

		// Populate Adobe fields.
		$('#sf-cfg-adobe-enabled').prop('checked', !!prop.adobe_enabled);
		$('#sf-cfg-adobe-org-id').val(prop.adobe_org_id || '');
		$('#sf-cfg-adobe-client-id').val(prop.adobe_client_id || '');
		$('#sf-cfg-adobe-client-secret').val('');
		if (prop.adobe_client_secret_set) {
			$('#sf-cfg-adobe-secret-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
		} else {
			$('#sf-cfg-adobe-secret-status').html('');
		}
		$('#sf-cfg-adobe-report-suite-id').val(prop.adobe_report_suite_id || '');

		// Update tab indicators.
		updateConfigTabDots(prop);

		// Reset to GSC tab.
		$('.sf-config-nav .nav-tab').removeClass('nav-tab-active').first().addClass('nav-tab-active');
		$('.sf-cfg-panel').removeClass('sf-cfg-panel-active').first().addClass('sf-cfg-panel-active');

		$('#sf-prop-config-status').text('');
		$('#sf-property-config-modal').show();
	});

	function renderGscStatus(prop) {
		var $cell = $('#sf-cfg-gsc-status-cell');
		if (prop.gsc_connected) {
			$cell.html(
				'<span class="sf-status sf-status-connected">Connected</span>' +
				(prop.gsc_property ? '<br><strong>Property:</strong> ' + prop.gsc_property : '') +
				'<br><button type="button" class="button button-small sf-cfg-disconnect-gsc" style="margin-top:6px;">Disconnect</button>'
			);
		} else if (prop.gsc_auth_url) {
			$cell.html('<a href="' + prop.gsc_auth_url + '" class="button button-primary">Connect to Google Search Console</a>');
		} else {
			$cell.html('<span class="sf-status sf-status-disconnected">Save Client ID &amp; Secret first, then connect.</span>');
		}
	}

	function updateConfigTabDots(prop) {
		$('#sf-cfg-tab-gsc-dot').attr('class', 'sf-cfg-tab-indicator ' + (prop.gsc_connected ? 'sf-cfg-dot-on' : ''));
		$('#sf-cfg-tab-bing-dot').attr('class', 'sf-cfg-tab-indicator ' + (prop.bing_enabled && prop.bing_api_key_set ? 'sf-cfg-dot-on' : ''));
		$('#sf-cfg-tab-ga4-dot').attr('class', 'sf-cfg-tab-indicator ' + (prop.ga4_enabled && prop.ga4_property_id ? 'sf-cfg-dot-on' : ''));
		$('#sf-cfg-tab-adobe-dot').attr('class', 'sf-cfg-tab-indicator ' + (prop.adobe_enabled && prop.adobe_client_id ? 'sf-cfg-dot-on' : ''));
	}

	// Config tab switching.
	$(document).on('click', '.sf-config-nav .nav-tab', function (e) {
		e.preventDefault();
		var tabId = $(this).data('config-tab');
		$('.sf-config-nav .nav-tab').removeClass('nav-tab-active');
		$(this).addClass('nav-tab-active');
		$('.sf-cfg-panel').removeClass('sf-cfg-panel-active');
		$('#' + tabId).addClass('sf-cfg-panel-active');
	});

	// Save property config.
	$(document).on('click', '#sf-save-property-config', function () {
		var $btn = $(this);
		var $status = $('#sf-prop-config-status');
		var propertyId = $('#sf-prop-config-id').val();

		$btn.prop('disabled', true);
		$status.text('Saving...').css('color', '');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_update_property_config',
			nonce: searchforge.nonce,
			property_id: propertyId,
			gsc_client_id: $('#sf-cfg-gsc-client-id').val(),
			gsc_client_secret: $('#sf-cfg-gsc-client-secret').val(),
			gsc_property: $('#sf-cfg-gsc-property').val(),
			bing_enabled: $('#sf-cfg-bing-enabled').is(':checked') ? 1 : 0,
			bing_api_key: $('#sf-cfg-bing-api-key').val(),
			bing_site_url: $('#sf-cfg-bing-site-url').val(),
			ga4_enabled: $('#sf-cfg-ga4-enabled').is(':checked') ? 1 : 0,
			ga4_property_id: $('#sf-cfg-ga4-property-id').val(),
			adobe_enabled: $('#sf-cfg-adobe-enabled').is(':checked') ? 1 : 0,
			adobe_org_id: $('#sf-cfg-adobe-org-id').val(),
			adobe_client_id: $('#sf-cfg-adobe-client-id').val(),
			adobe_client_secret: $('#sf-cfg-adobe-client-secret').val(),
			adobe_report_suite_id: $('#sf-cfg-adobe-report-suite-id').val()
		}, function (response) {
			$btn.prop('disabled', false);
			if (response.success) {
				$status.text(response.data.message).css('color', '#155724');
				// Update local data and refresh UI.
				var p = response.data.property;
				var localProp = sfPropertyData[propertyId];
				if (localProp) {
					localProp.gsc_client_id = $('#sf-cfg-gsc-client-id').val();
					localProp.gsc_connected = p.gsc_connected;
					localProp.gsc_property = p.gsc_property;
					localProp.gsc_auth_url = p.gsc_auth_url || '';
					localProp.gsc_client_secret_set = localProp.gsc_client_secret_set || !!$('#sf-cfg-gsc-client-secret').val();
					localProp.bing_enabled = $('#sf-cfg-bing-enabled').is(':checked');
					localProp.bing_api_key_set = localProp.bing_api_key_set || !!$('#sf-cfg-bing-api-key').val();
					localProp.bing_site_url = $('#sf-cfg-bing-site-url').val();
					localProp.ga4_enabled = $('#sf-cfg-ga4-enabled').is(':checked');
					localProp.ga4_property_id = $('#sf-cfg-ga4-property-id').val();
					localProp.adobe_enabled = $('#sf-cfg-adobe-enabled').is(':checked');
					localProp.adobe_org_id = $('#sf-cfg-adobe-org-id').val();
					localProp.adobe_client_id = $('#sf-cfg-adobe-client-id').val();
					localProp.adobe_client_secret_set = localProp.adobe_client_secret_set || !!$('#sf-cfg-adobe-client-secret').val();
					localProp.adobe_report_suite_id = $('#sf-cfg-adobe-report-suite-id').val();
					renderGscStatus(localProp);
					updateConfigTabDots(localProp);
				}
				// Update table row status badges.
				var $row = $('tr[data-property-id="' + propertyId + '"]');
				var cols = $row.find('td');
				cols.eq(2).html(p.gsc_connected ? '<span class="sf-status sf-status-connected">Connected</span>' : '<span class="sf-status sf-status-disconnected">&mdash;</span>');
				cols.eq(3).html(p.bing_connected ? '<span class="sf-status sf-status-connected">Connected</span>' : '<span class="sf-status sf-status-disconnected">&mdash;</span>');
				cols.eq(4).html(p.ga4_connected ? '<span class="sf-status sf-status-connected">Connected</span>' : '<span class="sf-status sf-status-disconnected">&mdash;</span>');
				cols.eq(5).html(p.adobe_connected ? '<span class="sf-status sf-status-connected">Connected</span>' : '<span class="sf-status sf-status-disconnected">&mdash;</span>');
				// Clear secret fields after save.
				$('#sf-cfg-gsc-client-secret, #sf-cfg-bing-api-key, #sf-cfg-adobe-client-secret').val('');
				if (localProp && localProp.gsc_client_secret_set) {
					$('#sf-cfg-gsc-secret-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
				}
				if (localProp && localProp.bing_api_key_set) {
					$('#sf-cfg-bing-key-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
				}
				if (localProp && localProp.adobe_client_secret_set) {
					$('#sf-cfg-adobe-secret-status').html('<span class="sf-status sf-status-connected" style="font-size:11px;">Set</span>');
				}
			} else {
				$status.text(response.data && response.data.message || 'Save failed.').css('color', '#d63638');
			}
		}).fail(function () {
			$btn.prop('disabled', false);
			$status.text('Network error.').css('color', '#d63638');
		});
	});

	// Disconnect GSC from within the config modal.
	$(document).on('click', '.sf-cfg-disconnect-gsc', function () {
		if (!confirm('Disconnect Google Search Console for this property?')) return;
		var propertyId = $('#sf-prop-config-id').val();

		$.post(searchforge.ajax_url, {
			action: 'searchforge_disconnect_gsc',
			nonce: searchforge.nonce,
			property_id: propertyId
		}, function (response) {
			if (response.success) {
				var localProp = sfPropertyData[propertyId];
				if (localProp) {
					localProp.gsc_connected = false;
					localProp.gsc_property = '';
					localProp.gsc_auth_url = '';
					renderGscStatus(localProp);
					updateConfigTabDots(localProp);
				}
				$('tr[data-property-id="' + propertyId + '"] td').eq(2).html('<span class="sf-status sf-status-disconnected">&mdash;</span>');
			} else {
				alert(response.data && response.data.message || 'Disconnect failed.');
			}
		});
	});

	// Sync property from table row.
	$(document).on('click', '.sf-sync-property-btn', function () {
		var $btn = $(this);
		var id = $btn.data('id');
		$btn.prop('disabled', true).text('Syncing...');

		$.post(searchforge.ajax_url, {
			action: 'searchforge_sync_property',
			nonce: searchforge.nonce,
			property_id: id
		}, function (response) {
			if (response.success) {
				var d = response.data;
				alert('Sync complete: ' + d.pages_synced + ' pages, ' + d.keywords_synced + ' keywords.');
			} else {
				alert('Sync failed: ' + (response.data && response.data.message || 'Unknown error'));
			}
			$btn.prop('disabled', false).text('Sync');
		}).fail(function () {
			alert('Network error.');
			$btn.prop('disabled', false).text('Sync');
		});
	});

	// Close property config modal on outside click.
	$(document).on('click', '#sf-property-config-modal', function (e) {
		if (e.target === this) {
			closeModal($(this));
		}
	});

	// --- Merger Analysis ---

	// Enable/disable generate button based on property selection.
	$(document).on('change', '.sf-merger-property', function () {
		var checked = $('.sf-merger-property:checked').length;
		$('#sf-generate-merger-brief').prop('disabled', checked < 2);
	});

	// Add another nav CSV upload row.
	$(document).on('click', '#sf-add-nav-upload', function () {
		var $row = $('.sf-nav-upload-row').first().clone();
		$row.find('input').val('');
		$('#sf-nav-uploads').append($row);
	});

	// Remove a nav CSV upload row (keep at least one).
	$(document).on('click', '.sf-nav-upload-remove', function () {
		if ($('.sf-nav-upload-row').length > 1) {
			$(this).closest('.sf-nav-upload-row').remove();
		} else {
			$(this).closest('.sf-nav-upload-row').find('input').val('');
		}
	});

	// Generate merger brief (with optional CSV uploads).
	$(document).on('click', '#sf-generate-merger-brief', function () {
		var $btn = $(this);
		var $status = $('#sf-merger-status');
		var ids = [];
		$('.sf-merger-property:checked').each(function () {
			ids.push($(this).val());
		});

		if (ids.length < 2) {
			$status.text('Select at least 2 properties.');
			return;
		}

		$btn.prop('disabled', true);
		$status.text('Generating...');
		$('#sf-merger-output').hide();

		var formData = new FormData();
		formData.append('action', 'searchforge_generate_merger_brief');
		formData.append('nonce', searchforge.nonce);
		ids.forEach(function (id) {
			formData.append('property_ids[]', id);
		});

		// Attach CSV files.
		$('.sf-nav-upload-row').each(function (i) {
			var fileInput = $(this).find('.sf-nav-csv-file')[0];
			var label = $(this).find('.sf-nav-csv-label').val();
			if (fileInput && fileInput.files.length > 0) {
				formData.append('nav_csv_files[' + i + ']', fileInput.files[0]);
				formData.append('nav_csv_labels[' + i + ']', label || fileInput.files[0].name);
			}
		});

		$.ajax({
			url: searchforge.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function (response) {
				if (response.success) {
					$('#sf-merger-content').text(response.data.markdown);
					$('#sf-merger-output').show();
					$status.text('Done.');

					// Wire up download.
					$('#sf-merger-download').off('click').on('click', function () {
						var blob = new Blob([response.data.markdown], { type: 'text/markdown' });
						var url = URL.createObjectURL(blob);
						var a = document.createElement('a');
						a.href = url;
						a.download = response.data.filename || 'merger-analysis.md';
						a.click();
						URL.revokeObjectURL(url);
					});
				} else {
					$status.text(response.data && response.data.message || 'Generation failed.');
				}
				$btn.prop('disabled', false);
			},
			error: function () {
				$status.text('Network error.');
				$btn.prop('disabled', false);
			}
		});
	});

	// Copy-to-clipboard buttons.
	$(document).on('click', '.sf-copy-btn', function () {
		var targetId = $(this).data('copy-target');
		var text = $('#' + targetId).text().trim();
		var $btn = $(this);
		if (navigator.clipboard) {
			navigator.clipboard.writeText(text).then(function () {
				$btn.text('Copied!');
				setTimeout(function () { $btn.text('Copy'); }, 2000);
			});
		} else {
			var $temp = $('<input>').val(text).appendTo('body').select();
			document.execCommand('copy');
			$temp.remove();
			$btn.text('Copied!');
			setTimeout(function () { $btn.text('Copy'); }, 2000);
		}
	});

})(jQuery);
