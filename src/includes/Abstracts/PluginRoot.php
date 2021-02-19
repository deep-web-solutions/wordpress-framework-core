<?php

namespace DeepWebSolutions\Framework\Core\Abstracts;

use DeepWebSolutions\Framework\Core\Abstracts\Exceptions\Initialization\FunctionalityInitializationFailure;
use DeepWebSolutions\Framework\Core\Abstracts\Exceptions\Initialization\PluginInitializationFailure;
use DeepWebSolutions\Framework\Core\Actions\Installation;
use DeepWebSolutions\Framework\Core\Actions\Internationalization;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable\InstallFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Exceptions\Installable\UninstallFailure;
use DeepWebSolutions\Framework\Core\Interfaces\Actions\Traits\Initializable\InitializeLocal;
use DeepWebSolutions\Framework\Core\Traits\Integrations\RunnablesOnSetup;
use DeepWebSolutions\Framework\Helpers\WordPress\Traits\Filesystem;
use DeepWebSolutions\Framework\Utilities\Interfaces\Resources\Pluginable;
use DeepWebSolutions\Framework\Utilities\Interfaces\Resources\Traits\Plugin;
use Exception;
use Psr\Log\LogLevel;
use function DeepWebSolutions\Framework\dws_wp_framework_output_initialization_error;
use const DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT;

defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating the most often required abilities of a main plugin class.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Core\Abstracts
 *
 * @see     PluginFunctionality
 */
abstract class PluginRoot extends PluginFunctionality implements Pluginable {
	// region TRAITS

	use Plugin;
	use Filesystem;

	use InitializeLocal;
	use RunnablesOnSetup;

	// endregion

	// region FIELDS AND CONSTANTS

	/**
	 * The system path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @var     string|null
	 */
	protected ?string $plugin_file_path = null;

	// endregion

	// region INHERITED METHODS

	/**
	 * The starting point of the whole plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     PluginFunctionality::initialize()
	 *
	 * @throws  Exception   Thrown if any of the children nodes do NOT belong to a plugin tree.
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	public function initialize(): ?FunctionalityInitializationFailure {
		if ( ! defined( 'DeepWebSolutions\Framework\DWS_WP_FRAMEWORK_CORE_INIT' ) || ! DWS_WP_FRAMEWORK_CORE_INIT ) {
			return new FunctionalityInitializationFailure(); // The framework will display an error message when this is false.
		}

		$result = parent::initialize();
		if ( ! is_null( $result ) ) {
			dws_wp_framework_output_initialization_error( $result, $this );
			return $result;
		}

		return null;
	}

	/**
	 * Initialize local non-functionality fields.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     InitializeLocal::initialize_local()
	 *
	 * @return  FunctionalityInitializationFailure|null
	 */
	protected function initialize_local(): ?PluginInitializationFailure {
		$this->set_plugin_file_path();
		if ( is_null( $this->plugin_file_path ) || ! $this->get_wp_filesystem()->is_file( $this->plugin_file_path ) ) {
			/* @noinspection PhpIncompatibleReturnTypeInspection */
			return $this->get_logging_service()->log_event_and_doing_it_wrong_and_return_exception(
				__FUNCTION__,
				'The plugin file path was not set!',
				'1.0.0',
				PluginInitializationFailure::class,
				null,
				LogLevel::ERROR,
				'framework'
			);
		}

		$this->set_plugin_data();

		return null;
	}

	/**
	 * Define some plugin-level, overarching functionalities.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  array
	 */
	protected function define_children(): array {
		return array( Internationalization::class, Installation::class );
	}

	// endregion

	// region WP-SPECIFIC METHODS

	/**
	 * On first activation, run the installation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return  null|InstallFailure
	 */
	public function activate(): ?InstallFailure {
		/* @noinspection PhpUnhandledExceptionInspection */
		$installer = $this->get_container()->get( Installation::class );
		return ( is_null( $installer->get_original_version() ) )
			? $installer->install_or_update()
			: null;
	}

	/**
	 * On uninstall, run the uninstallation routine.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 *
	 * @return  null|UninstallFailure
	 */
	public function uninstall(): ?UninstallFailure {
		/* @noinspection PhpUnhandledExceptionInspection */
		$installer = $this->get_container()->get( Installation::class );
		return $installer->uninstall();
	}

	// endregion

	// region GETTERS

	/**
	 * Returns the current plugin instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Identity::get_plugin()
	 */
	public function get_plugin(): Pluginable {
		return $this;
	}

	/**
	 * Gets the path to the main WP plugin file.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Plugin::get_plugin_file_path()
	 *
	 * @return  string
	 */
	public function get_plugin_file_path(): string {
		if ( is_null( $this->plugin_file_path ) ) {
			if ( ! did_action( 'plugins_loaded' ) ) {
				$this->get_logging_service()->log_event_and_doing_it_wrong(
					__FUNCTION__,
					sprintf(
						'The %1$s cannot be retrieved before the %2$s action.',
						'plugin file path',
						'plugins_loaded'
					),
					'1.0.0',
					LogLevel::DEBUG,
					'framework'
				);
			}

			return '';
		}

		return $this->plugin_file_path;
	}

	// endregion

	// region SETTERS

	/**
	 * It is the responsibility of each plugin using this framework to set the plugin file path.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	abstract protected function set_plugin_file_path(): void;

	// region INHERITED SETTERS

	/**
	 * Sets the plugin data.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @see     Plugin::set_plugin_data()
	 */
	protected function set_plugin_data(): void {
		$plugin_data                  = \get_plugin_data( $this->get_plugin_file_path() );
		$this->plugin_name            = $plugin_data['Name'];
		$this->plugin_version         = $plugin_data['Version'];
		$this->plugin_author_name     = $plugin_data['Author'];
		$this->plugin_author_uri      = $plugin_data['AuthorURI'];
		$this->plugin_description     = $plugin_data['Description'];
		$this->plugin_language_domain = $plugin_data['TextDomain'];
		$this->plugin_slug            = basename( dirname( $this->plugin_file_path ) );
	}

	// endregion

	// endregion

	// region HELPERS

	/**
	 * Returns the path to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_assets_base_path(): string {
		return str_replace( 'includes/', '', self::get_custom_base_path( 'assets' ) );
	}

	/**
	 * Returns the relative URL to the assets folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_assets_base_relative_url(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'assets' ) );
	}

	/**
	 * Returns the path to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_templates_base_path(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_path( 'templates' ) );
	}

	/**
	 * Returns the relative URL to the templates folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_templates_base_relative_url(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'templates' ) );
	}

	/**
	 * Returns the path to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_languages_base_path(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_path( 'languages' ) );
	}

	/**
	 * Returns the relative URL to the languages folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_languages_base_relative_url(): string {
		return str_replace( 'includes' . DIRECTORY_SEPARATOR, '', self::get_custom_base_relative_url( 'languages' ) );
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_includes_base_path(): string {
		return self::get_base_path();
	}

	/**
	 * Returns the path to the classes folder of the current plugin.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	public static function get_includes_base_relative_url(): string {
		return self::get_base_relative_url();
	}

	// endregion
}
