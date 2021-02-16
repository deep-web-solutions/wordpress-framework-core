<?php

namespace DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract trait that other traits should use to denote that they want their own setup logic called WHEN the instance
 * is NOT active.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Interfaces\Actions\Traits\Setupable\Integrations
 */
trait SetupableInactive {
	/* empty on purpose */
}