<?php

trait CustomAssertTrait {
	public function assertArrayHasObjectOfType( $type, $array, $message = '' ) {

		$found = false;

		foreach ( $array as $obj ) {
			if ( get_class( $obj ) === $type ) {
				$found = true;
				break;
			}
		}

		$this->assertTrue( $found, $message );

	}
}
