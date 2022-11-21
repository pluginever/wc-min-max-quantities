<?php
/**
 * WC_Min_Max_Quantities Background Updater Handler
 *
 * @version  1.1.0
 * @since    1.1.0
 * @package  WC_Min_Max_Quantities\Utilities
 */

namespace WC_Min_Max_Quantities\Utilities;

defined( 'ABSPATH' ) || exit();

/**
 * Class Background_Updater.
 */
class Background_Updater extends Background_Process {

	/**
	 * Background_Updater Constructor.
	 *
	 * @param string $id The identifier for this background process.
	 *
	 * @since 1.1.0
	 */
	public function __construct( $id = '' ) {
		// Uses unique prefix per blog so each blog has separate queue.
		$this->prefix = 'wp_' . get_current_blog_id();
		$this->action = $id . '_background_updater';

		parent::__construct();
	}

	/**
	 * Handle cron healthcare
	 *
	 * Restart the background process if not already running
	 * and data exists in the queue.
	 *
	 * @since 1.1.0
	 */
	public function handle_cron_healthcheck() {
		if ( $this->is_process_running() ) {
			// Background process already running.
			return;
		}

		if ( $this->is_queue_empty() ) {
			// No data to process.
			$this->clear_scheduled_event();

			return;
		}

		$this->handle();
	}

	/**
	 * Schedule fallback event.
	 *
	 * @since 1.1.0
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}

	/**
	 * Is the updater running?
	 *
	 * @since 1.1.0
	 * @return boolean
	 */
	public function is_updating() {
		return false === $this->is_queue_empty();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param string $callback Update callback function.
	 *
	 * @since 1.1.0
	 * @return string|bool
	 */
	protected function task( $callback ) {
		$result = false;
		if ( is_callable( $callback ) ) {
			$result = (bool) call_user_func( $callback );
		}

		return $result ? $callback : false;
	}

	/**
	 * See if the batch limit has been exceeded.
	 *
	 * @since 1.1.0
	 * @return bool
	 */
	public function is_memory_exceeded() {
		return $this->memory_exceeded();
	}
}
