<?php
namespace NewfoldLabs\WP\Module\Installer\Models;

/**
 * Max heap implementation of a Priority Queue.
 */
class PriorityQueue extends \SplPriorityQueue {

	/**
	 * Converts the max heap to an array.
	 *
	 * @return array
	 */
	public function to_array() {
		$array = array();
		while ( $this->valid() ) {
			array_push( $array, $this->extract() );
		}
		return $array;
	}
}
