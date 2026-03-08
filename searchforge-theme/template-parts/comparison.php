<?php
/**
 * Template part: Comparison table.
 *
 * @package SearchForge_Theme
 */

$rows = [
	[ 'capability' => 'Data collection',        'old' => 'Hours per week',                  'sf' => 'Automatic daily sync' ],
	[ 'capability' => 'Sources combined',        'old' => 'Copy-paste between tabs',         'sf' => '8 sources in one dashboard' ],
	[ 'capability' => 'LLM-ready export',        'old' => 'Reformat manually',               'sf' => 'One-click markdown brief' ],
	[ 'capability' => 'AI visibility',           'old' => 'Not tracked',                     'sf' => 'Monitored weekly' ],
	[ 'capability' => 'Competitor data',         'old' => '$1,400/yr (Semrush)',              'sf' => 'Included in Pro (&euro;99/yr)' ],
	[ 'capability' => 'Historical trends',       'old' => 'Lost when CSVs pile up',          'sf' => '12-month rolling snapshots' ],
	[ 'capability' => 'Content recommendations', 'old' => 'Guesswork',                       'sf' => 'AI-generated per page' ],
];
?>

<section class="sf-section">
	<div class="sf-container">
		<div class="sf-section__header">
			<h2>SearchForge vs. The Old Way</h2>
		</div>

		<div class="sf-comparison-table-wrapper">
			<table class="sf-comparison-table">
				<thead>
					<tr>
						<th scope="col">Capability</th>
						<th scope="col">Manual / Spreadsheets</th>
						<th scope="col">SearchForge</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $rows as $row ) : ?>
						<tr>
							<td class="sf-comparison-table__capability"><?php echo esc_html( $row['capability'] ); ?></td>
							<td class="sf-comparison-table__old"><?php echo esc_html( $row['old'] ); ?></td>
							<td class="sf-comparison-table__new"><?php echo wp_kses_post( $row['sf'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</section>
