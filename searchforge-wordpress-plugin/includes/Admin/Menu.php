<?php

namespace SearchForge\Admin;

defined( 'ABSPATH' ) || exit;

class Menu {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'register_menus' ] );
	}

	public function register_menus(): void {
		add_menu_page(
			__( 'SearchForge', 'searchforge-wordpress-plugin' ),
			__( 'SearchForge', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge',
			[ $this, 'render_dashboard' ],
			'dashicons-search',
			30
		);

		add_submenu_page(
			'searchforge',
			__( 'Dashboard', 'searchforge-wordpress-plugin' ),
			__( 'Dashboard', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge',
			[ $this, 'render_dashboard' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Pages', 'searchforge-wordpress-plugin' ),
			__( 'Pages', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-pages',
			[ $this, 'render_pages' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Keywords', 'searchforge-wordpress-plugin' ),
			__( 'Keywords', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-keywords',
			[ $this, 'render_keywords' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Analysis', 'searchforge-wordpress-plugin' ),
			__( 'Analysis', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-analysis',
			[ $this, 'render_analysis' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Competitors', 'searchforge-wordpress-plugin' ),
			__( 'Competitors', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-competitors',
			[ $this, 'render_competitors' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Monitoring', 'searchforge-wordpress-plugin' ),
			__( 'Monitoring', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-monitoring',
			[ $this, 'render_monitoring' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Export', 'searchforge-wordpress-plugin' ),
			__( 'Export', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-export',
			[ $this, 'render_export' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Comparison', 'searchforge-wordpress-plugin' ),
			__( 'Comparison', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-comparison',
			[ $this, 'render_comparison' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Merger Analysis', 'searchforge-wordpress-plugin' ),
			__( 'Merger Analysis', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-merger',
			[ $this, 'render_merger' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Instructions', 'searchforge-wordpress-plugin' ),
			__( 'Instructions', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-instructions',
			[ $this, 'render_instructions' ]
		);

		add_submenu_page(
			'searchforge',
			__( 'Settings', 'searchforge-wordpress-plugin' ),
			__( 'Settings', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-settings',
			[ $this, 'render_settings' ]
		);

		// Hidden page detail view (no menu entry).
		add_submenu_page(
			null,
			__( 'Page Detail', 'searchforge-wordpress-plugin' ),
			__( 'Page Detail', 'searchforge-wordpress-plugin' ),
			'manage_options',
			'searchforge-page-detail',
			[ $this, 'render_page_detail' ]
		);
	}

	private function render_template( string $template ): void {
		include SEARCHFORGE_PATH . 'templates/' . $template;
		include SEARCHFORGE_PATH . 'templates/partials/admin-footer.php';
	}

	public function render_dashboard(): void {
		$this->render_template( 'dashboard.php' );
	}

	public function render_pages(): void {
		$this->render_template( 'pages.php' );
	}

	public function render_keywords(): void {
		$this->render_template( 'keywords.php' );
	}

	public function render_analysis(): void {
		$this->render_template( 'analysis.php' );
	}

	public function render_monitoring(): void {
		$this->render_template( 'monitoring.php' );
	}

	public function render_export(): void {
		$this->render_template( 'export.php' );
	}

	public function render_settings(): void {
		$this->render_template( 'settings.php' );
	}

	public function render_competitors(): void {
		$this->render_template( 'competitors.php' );
	}

	public function render_page_detail(): void {
		$this->render_template( 'page-detail.php' );
	}

	public function render_instructions(): void {
		$this->render_template( 'instructions.php' );
	}

	public function render_comparison(): void {
		$this->render_template( 'comparison.php' );
	}

	public function render_merger(): void {
		$this->render_template( 'merger-analysis.php' );
	}
}
