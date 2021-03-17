<?php

namespace DeepWebSolutions\Framework\Core\Actions;

\defined( 'ABSPATH' ) || exit;

/**
 * Describes an instance that has an uninstallation routine.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Actions
 */
interface UninstallableInterface {
	/**
	 * Describes the data uninstallation logic of the implementing class.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  Installable\UninstallFailureException|null
	 */
	public function uninstall(): ?Installable\UninstallFailureException;
}
