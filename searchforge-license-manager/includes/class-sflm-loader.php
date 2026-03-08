<?php
/**
 * Hook/Filter Loader.
 *
 * @package SearchForge_License_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class SFLM_Loader {

    /** @var array<array{hook: string, component: object, callback: string, priority: int, args: int}> */
    protected array $actions = [];

    /** @var array<array{hook: string, component: object, callback: string, priority: int, args: int}> */
    protected array $filters = [];

    public function add_action( string $hook, object $component, string $callback, int $priority = 10, int $args = 1 ): void {
        $this->actions[] = compact( 'hook', 'component', 'callback', 'priority', 'args' );
    }

    public function add_filter( string $hook, object $component, string $callback, int $priority = 10, int $args = 1 ): void {
        $this->filters[] = compact( 'hook', 'component', 'callback', 'priority', 'args' );
    }

    public function run(): void {
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['args'] );
        }
        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['args'] );
        }
    }
}
