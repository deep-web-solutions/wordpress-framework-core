<?php

namespace DeepWebSolutions\Framework\Core\Traits;

use DeepWebSolutions\Framework\Core\Traits\Abstracts\FunctionalityTrait;

/**
 * Functionality trait for children classes to run their own random setup code.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\Framework\Core\Traits
 */
trait Local {
	use FunctionalityTrait {
		setup as setup_local;
	}
}