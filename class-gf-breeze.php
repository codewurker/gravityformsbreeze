<?php

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die();
}

GFForms::include_feed_addon_framework();

/**
 * Gravity Forms Breeze Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2016, Rocketgenius
 */
class GF_Breeze extends GFFeedAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Breeze Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from breeze.php
	 */
	protected $_version = GF_BREEZE_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.14.26';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformsbreeze';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformsbreeze/breeze.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Breeze Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Breeze';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_breeze';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_breeze';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_breeze_uninstall';

	/**
	 * Contains an instance of the Breeze API library, if available.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    object $api If available, contains an instance of the Breeze API library.
	 */
	protected $api = null;

	/**
	 * Defines the capabilities needed for the Breeze Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_breeze', 'gravityforms_breeze_uninstall' );

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 * @return object $_instance An instance of the GF_Breeze class.
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Plugin starting point. Adds PayPal delayed payment support.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init() {

		parent::init();

		$this->add_delayed_payment_support(
			array(
				'option_label' => esc_html__( 'Create Breeze card only when payment is received.', 'gravityformsbreeze' ),
			)
		);

	}

	/**
	 * Define plugin settings fields.
	 *
	 * @since  1.0
	 * @access public
	 * @return array The settings fields for the Breeze Add-On.
	 */
	public function plugin_settings_fields() {

		return array(
			array(
				'title'       => '',
				'description' => '',
				'fields'      => array(
					array(
						'name'              => 'apiToken',
						'label'             => esc_html__( 'API Token', 'gravityformsbreeze' ),
						'type'              => 'text',
						'class'             => 'small',
						'feedback_callback' => array( $this, 'initialize_api' ),
					),
					array(
						'type'              => 'save',
						'messages'          => array(
							'success' => esc_html__( 'Breeze settings have been updated.', 'gravityformsbreeze' ),
						),
					),
				),
			),
		);

	}

	/**
	 * Define feed settings fields.
	 *
	 * @since  1.0
	 * @access public
	 * @return array The settings fields associated with feeds for the Breeze Add-On
	 */
	public function feed_settings_fields() {

		$projects = $this->get_projects_for_feed_setting();
		$lists    = $this->get_lists_for_feed_setting();

		// Build base fields.
		$base_fields = array(
			'title'  => '',
			'fields' => array(
				array(
					'name'           => 'feedName',
					'label'          => esc_html__( 'Feed Name', 'gravityformsbreeze' ),
					'type'           => 'text',
					'required'       => true,
					'class'          => 'medium',
					'default_value'  => $this->get_default_feed_name(),
					'tooltip'        => '<h6>'. esc_html__( 'Name', 'gravityformsbreeze' ) .'</h6>' . esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformsbreeze' ),
				),
				array(
					'name'           => 'project',
					'label'          => esc_html__( 'Breeze Project', 'gravityformsbreeze' ),
					'type'           => 'select',
					'required'       => true,
					'choices'        => $projects,
					'no_choices'     => empty( $projects ) ? esc_html__( 'Unable to retrieve Projects from Breeze.', 'gravityformsbreeze' ) : '',
					'onchange'       => "jQuery('select[name=\"_gaddon_setting_list\"]').val('');jQuery(this).parents('form').submit();",
				),
				array(
					'name'           => 'list',
					'label'          => esc_html__( 'Breeze List', 'gravityformsbreeze' ),
					'type'           => 'select',
					'required'       => true,
					'choices'        => $lists,
					'no_choices'     => empty( $lists ) ? esc_html__( 'Unable to retrieve Project Lists from Breeze.', 'gravityformsbreeze' ) : '',
					'dependency'     => 'project',
					'onchange'       => "jQuery(this).parents('form').submit();",
				),
			),
		);

		// Build card settings fields.
		$card_fields = array(
			'title'      => esc_html__( 'Card Settings', 'gravityformstrello' ),
			'dependency' => 'list',
			'fields'     => array(
				array(
					'name'          => 'cardName',
					'type'          => 'text',
					'required'      => true,
					'class'         => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
					'label'         => esc_html__( 'Name', 'gravityformsbreeze' ),
					'default_value' => 'New submission from {form_title}',
				),
				array(
					'name'          => 'cardDescription',
					'type'          => 'textarea',
					'required'      => false,
					'class'         => 'medium merge-tag-support mt-position-right mt-hide_all_fields',
					'label'         => esc_html__( 'Description', 'gravityformsbreeze' ),
				),
				array(
					'name'          => 'cardDueDate',
					'type'          => 'select_custom',
					'label'         => esc_html__( 'Due Date', 'gravityformsbreeze' ),
					'after_input'   => ' ' . esc_html__( 'days after today', 'gravityformsbreeze' ),
					'choices'       => $this->get_date_fields_for_feed_setting(),
				),
				array(
					'name'          => 'cardInvitees',
					'type'          => 'checkbox',
					'label'         => esc_html__( 'Assign Users to Card', 'gravityformsbreeze' ),
					'choices'       => $this->get_users_for_feed_setting(),
					'no_choices'    => esc_html__( 'Unable to retrieve Project Users from Breeze.', 'gravityformsbreeze' ),
				),
			),
		);

		// Build conditional logic fields array.
		$conditional_fields = array(
			'title'      => esc_html__( 'Feed Conditional Logic', 'gravityformsbreeze' ),
			'dependency' => 'list',
			'fields'     => array(
				array(
					'name'           => 'feedCondition',
					'type'           => 'feed_condition',
					'label'          => esc_html__( 'Conditional Logic', 'gravityformsbreeze' ),
					'checkbox_label' => esc_html__( 'Enable', 'gravityformsbreeze' ),
					'instructions'   => esc_html__( 'Export to Breeze if', 'gravityformsbreeze' ),
					'tooltip'        => '<h6>' . esc_html__( 'Conditional Logic', 'gravityformsbreeze' ) . '</h6>' . esc_html__( 'When conditional logic is enabled, form submissions will only be exported to Breeze when the condition is met. When disabled, all form submissions will be posted.', 'gravityformsbreeze' ),
				),
			),
		);

		return array( $base_fields, $card_fields, $conditional_fields );

	}

	/**
	 * Prepare Breeze projects for feed settings field.
	 *
	 * @since  1.0
	 * @access public
	 * @return array $choices The projects available to choose from in the feed settings.
	 */
	public function get_projects_for_feed_setting() {

		// Initialize choices array.
		$choices = array();

		// If API is not initialized, return choices.
		if ( ! $this->initialize_api() ) {
			return $choices;
		}

		// Get the Breeze projects.
		$projects = $this->api->get_projects();

		if ( is_wp_error( $projects ) || empty( $projects ) ) {
			return $choices;
		}

		$choices[] = array(
			'label' => esc_html__( 'Select a Breeze Project', 'gravityformsbreeze' ),
			'value' => '',
		);

		// Add Breeze projects to choices.
		foreach ( $projects as $project ) {
			$choices[] = array(
				'label' => $project['name'],
				'value' => $project['id'],
			);
		}

		return $choices;

	}

	/**
	 * Prepare Breeze lists for feed setting.
	 *
	 * @since  1.0
	 * @access public
	 * @return array $choices The lists available to choose from in the feed settings.
	 */
	public function get_lists_for_feed_setting() {

		// Initialize choices array.
		$choices = array();

		// If API is not initialized, return choices.
		if ( ! $this->initialize_api() ) {
			return $choices;
		}

		// Get selected project ID.
		$project_id = $this->get_setting( 'project' );

		// Get the lists for Breeze project.
		$lists = empty( $project_id ) ? array() : $this->api->get_project_lists( $project_id );

		if ( is_wp_error( $lists ) || empty( $lists ) ) {
			return $choices;
		}

		$choices[] = array(
			'label' => esc_html__( 'Select a Breeze List', 'gravityformsbreeze' ),
			'value' => '',
		);

		// Add Breeze lists to choices.
		foreach ( $lists as $list ) {
			$choices[] = array(
				'label' => $list['name'],
				'value' => $list['id'],
			);
		}

		return $choices;

	}

	/**
	 * Prepare Breeze project users for feed setting.
	 *
	 * @since  1.0
	 * @access public
	 * @return array $choices
	 */
	public function get_users_for_feed_setting() {

		// Initialize choices array.
		$choices = array();

		// If API is not initialized, return choices.
		if ( ! $this->initialize_api() ) {
			return $choices;
		}

		// Get selected project ID.
		$project_id = $this->get_setting( 'project' );

		// Get the Breeze project.
		$project = empty( $project_id ) ? array() : $this->api->get_project( $project_id );

		if ( is_wp_error( $project ) || empty( $project['users'] ) ) {
			return $choices;
		}

		// Add Breeze users to choices.
		foreach ( $project['users'] as $user ) {
			$choices[] = array(
				'label' => rgar( $user, 'name' ) ? $user['name'] : $user['email'],
				'name'  => 'cardInvitees[' . $user['email'] . ']',
			);
		}

		return $choices;

	}

	/**
	 * Prepare date fields for feed setting.
	 *
	 * @since  1.0
	 * @access public
	 * @return array $choices
	 */
	public function get_date_fields_for_feed_setting() {

		// Initialize choices array.
		$choices = array(
			array(
				'label' => esc_html__( 'No Due Date', 'gravityformsbreeze' ),
				'value' => '',
			),
		);

		// Get the form.
		$form = GFAPI::get_form( rgget( 'id' ) );

		// Get date fields for the form.
		$date_fields = GFCommon::get_fields_by_type( $form, array( 'date' ) );

		// Add date fields to choices.
		if ( ! empty( $date_fields ) ) {
			foreach ( $date_fields as $field ) {
				$choices[] = array(
					'label' => $field->label,
					'value' => $field->id,
				);
			}
		}

		return $choices;

	}

	/**
	 * Set feed creation control.
	 *
	 * @since  1.0
	 * @access public
	 * @return bool
	 */
	public function can_create_feed() {

		return $this->initialize_api();

	}

	/**
	 * Allow the feed to be duplicated.
	 *
	 * @since 1.4
	 *
	 * @param array|int $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 *
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {

		return true;

	}

	/**
	 * Setup columns for feed list table.
	 *
	 * @since  1.0
	 * @access public
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName' => esc_html__( 'Name', 'gravityformsbreeze' ),
			'project'  => esc_html__( 'Breeze Project', 'gravityformsbreeze' ),
			'list'     => esc_html__( 'Breeze List', 'gravityformsbreeze' ),
		);

	}

	/**
	 * Return the plugin's icon for the plugin/form settings menu.
	 *
	 * @since 1.4
	 *
	 * @return string
	 */
	public function get_menu_icon() {

		return file_get_contents( $this->get_base_path() . '/images/menu-icon.svg' );

	}

	/**
	 * Get Breeze project name for feed list table.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $feed Feed object.
	 *
	 * @return string
	 */
	public function get_column_value_project( $feed ) {

		// Get the project ID.
		$project_id = $feed['meta']['project'];

		// If API is not initialized, return project ID.
		if ( ! $this->initialize_api() ) {
			return $project_id;
		}

		// Get the Breeze project.
		$project = $this->api->get_project( $project_id );

		if ( is_wp_error( $project ) ) {
			$this->log_error( __METHOD__ . '(): Unable to get Breeze project; ' . $project->get_error_message() );

			return $project_id;
		}

		return rgar( $project, 'name', $project_id );

	}

	/**
	 * Get Breeze list name for feed list table.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $feed Feed object.
	 *
	 * @return string
	 */
	public function get_column_value_list( $feed ) {

		// Get the project and list ID.
		$project_id = $feed['meta']['project'];
		$list_id    = $feed['meta']['list'];

		// If API is not initialized, return list ID.
		if ( ! $this->initialize_api() ) {
			return $list_id;
		}

		// Get the lists for Breeze project.
		$lists = $this->api->get_project_lists( $project_id );

		if ( is_wp_error( $lists ) ) {
			// Log the request error.
			$this->log_error( __METHOD__ . '(): Unable to get Breeze project; ' . $lists->get_error_message() );
		} elseif ( ! empty( $lists ) ) {
			// If list is found, return list name.
			foreach ( $lists as $list ) {
				if ( intval( $list_id ) === $list['id'] ) {
					return $list['name'];
				}
			}
		}

		return $list_id;

	}

	/**
	 * Create Breeze card upon form submission.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param  array $feed Feed object.
	 * @param  array $entry Entry object.
	 * @param  array $form Form object.
	 */
	public function process_feed( $feed, $entry, $form ) {

		// If API is not initialized, add feed error and exit.
		if ( ! $this->initialize_api() ) {
			$this->add_feed_error( esc_html__( 'Feed was not processed because API was not initialized.', 'gravityformsbreeze' ), $feed, $entry, $form );
			return;
		}

		// Prepare card object.
		$card = array(
			'name'        => GFCommon::replace_variables( $feed['meta']['cardName'], $form, $entry, false, true, false, 'text' ),
			'description' => GFCommon::replace_variables( $feed['meta']['cardDescription'], $form, $entry, false, true, false, 'text' ),
			'stage_id'    => intval( $feed['meta']['list'] ),
		);

		// Invite users to card.
		if ( rgars( $feed, 'meta/cardInvitees' ) ) {
			foreach ( rgars( $feed, 'meta/cardInvitees' ) as $email => $enabled ) {
				if ( '1' === $enabled ) {
					$card['invitees'][] = $email;
				}
			}
		}

		// Add due date to card.
		if ( rgars( $feed, 'meta/cardDueDate' ) ) {

			if ( 'gf_custom' === $feed['meta']['cardDueDate'] && rgars( $feed, 'meta/cardDueDate_custom' ) ) {

				// Convert custom date.
				$card['duedate'] = date( 'Y-m-d', strtotime( 'midnight +' . $feed['meta']['cardDueDate_custom'] . ' days' ) );

			} else {

				// Get date field value.
				$date_value = $this->get_field_value( $form, $entry, $feed['meta']['cardDueDate'] );

				// If date field value is set, add it to the card.
				if ( $date_value ) {
					$card['duedate'] = date( 'Y-m-d', strtotime( $date_value ) );
				}

			}

		}

		/**
		 * Filters the card to be created.
		 *
		 * @since 1.0
		 *
		 * @param array $card  The card being filtered.
		 * @param array $feed  The feed object.
		 * @param array $entry The entry object.
		 * @param array $form  The form object
		 */
		$card = gf_apply_filters( array( 'gform_breeze_card', $form['id'] ), $card, $feed, $entry, $form );
		$this->log_debug( __METHOD__ . '(): Card to be created => ' . print_r( $card, 1 ) );

		// Add feed error if no card name is set.
		if ( rgblank( $card['name'] ) ) {
			$this->add_feed_error( esc_html__( 'Card could not be created because no name was provided.', 'gravityformsbreeze' ), $feed, $entry, $form );
			return;
		}

		// Create card.
		$card = $this->api->create_card( $card, $feed['meta']['project'] );

		if ( is_wp_error( $card ) ) {
			// Add feed error.
			$this->add_feed_error( esc_html__( 'Card could not be created: ', 'gravityformsbreeze' ) . $card->get_error_message(), $feed, $entry, $form );
			return;
		}

		// Log that card was created.
		$this->log_debug( __METHOD__ . '(): Card #' . $card['id'] . ' created.' );

		return;

	}

	/**
	 * Initializes Breeze API if credentials are valid.
	 *
	 * @since  1.0
	 * @access public
	 * @return bool
	 */
	public function initialize_api() {

		// If the Breeze API library has already been initialize, return true.
		if ( is_object( $this->api ) ) {
			return true;
		}

		// Load the API library.
		if ( ! class_exists( 'GF_Breeze_API' ) ) {
			require_once( 'includes/class-gf-breeze-api.php' );
		}

		// Get the API token.
		$api_token = $this->get_plugin_setting( 'apiToken' );

		// If the API token is not defined, do not run a validation check.
		if ( rgblank( $api_token ) ) {
			return null;
		}

		$this->log_debug( __METHOD__ . '(): Validating API Info.' );

		// Setup a new Breeze API object with the API credentials.
		$breeze = new GF_Breeze_API( $api_token );

		// Run an authentication test.
		$auth_test = $breeze->get_projects();

		if ( is_wp_error( $auth_test ) ) {
			// Log that test failed.
			$this->log_error( __METHOD__ . '(): API credentials are invalid; ' . $auth_test->get_error_message() );

			return false;
		}

		// Assign Breeze API object to class.
		$this->api = $breeze;

		// Log that test passed.
		$this->log_debug( __METHOD__ . '(): API credentials are valid.' );

		return true;

	}

}
