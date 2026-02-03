<?php

namespace NewfoldLabs\WP\Module\Installer;

use NewfoldLabs\WP\Module\Installer\Models\PriorityQueue;

/**
 * PriorityQueue wpunit tests.
 *
 * @coversDefaultClass \NewfoldLabs\WP\Module\Installer\Models\PriorityQueue
 */
class PriorityQueueWPUnitTest extends \lucatume\WPBrowser\TestCase\WPTestCase {

	/**
	 * To_array returns items in priority order (highest first).
	 *
	 * @return void
	 */
	public function test_to_array_returns_items_in_priority_order() {
		$queue = new PriorityQueue();
		$queue->insert( 'low', 1 );
		$queue->insert( 'high', 3 );
		$queue->insert( 'mid', 2 );
		$result = $queue->to_array();
		$this->assertSame( array( 'high', 'mid', 'low' ), $result );
	}

	/**
	 * To_array returns empty array when queue is empty.
	 *
	 * @return void
	 */
	public function test_to_array_returns_empty_when_empty() {
		$queue  = new PriorityQueue();
		$result = $queue->to_array();
		$this->assertSame( array(), $result );
	}
}
