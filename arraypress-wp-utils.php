<?php
/**
 * Plugin Name: ArrayPress - WordPress Utility Library
 * Plugin URI: https://arraypress.com/
 * Description: Loads the WordPress Utility Library files.
 * Version: 1.0.0
 * Author: ArrayPress
 * Author URI: https://arraypress.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-utils-loader
 * Domain Path: /languages
 */

use ArrayPress\Utils\Elements\Shape;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


// Define the plugin directory path.
define( 'WP_UTILS_DIR', plugin_dir_path( __FILE__ ) . '/src/' );

// Function to load the utility files.
function wp_utils_load_files() {
	$files = array(
		'Traits/Comment/Core.php',
		'Traits/Comment/Dates.php',
		'Traits/Comment/Hierarchy.php',
		'Traits/Comment/Status.php',
		'Traits/Comments/Bulk.php',
		'Traits/Comments/Query.php',
		'Traits/Post/Comments.php',
		'Traits/Post/Conditional.php',
		'Traits/Post/Content.php',
		'Traits/Post/Core.php',
		'Traits/Post/Dates.php',
		'Traits/Post/Format.php',
		'Traits/Post/Hierarchy.php',
		'Traits/Post/Media.php',
		'Traits/Post/Password.php',
		'Traits/Post/Query.php',
		'Traits/Post/Status.php',
		'Traits/Post/Sticky.php',
		'Traits/Post/Terms.php',
		'Traits/Posts/Bulk.php',
		'Traits/Posts/Core.php',
		'Traits/Posts/Query.php',
		'Traits/Posts/Taxonomy.php',
		'Traits/Shared/Meta.php',
		'Traits/Term/Analysis.php',
		'Traits/Term/Core.php',
		'Traits/Term/Fields.php',
		'Traits/Term/Hierarchy.php',
		'Traits/Term/Relationship.php',
		'Traits/Term/Utility.php',
		'Traits/Terms/Analysis.php',
		'Traits/Terms/Core.php',
		'Traits/Terms/ObjectRelations.php',
		'Traits/Terms/Query.php',
		'Traits/Terms/Utility.php',
		'Traits/User/Authentication.php',
		'Traits/User/Avatar.php',
		'Traits/User/Capabilities.php',
		'Traits/User/Comments.php',
		'Traits/User/Core.php',
		'Traits/User/Dates.php',
		'Traits/User/Info.php',
		'Traits/User/MetaKeys.php',
		'Traits/User/Posts.php',
		'Traits/User/Roles.php',
		'Traits/User/Security.php',
		'Traits/User/Social.php',
		'Traits/User/Spam.php',
		'Traits/Users/Core.php',
		'Traits/Users/Management.php',
		'Traits/Users/Query.php',
		'Blocks/Block.php',
		'Blocks/Blocks.php',
		'Blocks/Gutenberg.php',
		'Capabilities/Capabilities.php',
		'Capabilities/Capability.php',
		'Comments/Comment.php',
		'Comments/Comments.php',
		'Comments/Search.php',
		'Common/Anonymize.php',
		'Common/Arr.php',
		'Common/Cache.php',
		'Common/Color.php',
		'Common/Compare.php',
		'Common/Constant.php',
		'Common/Convert.php',
		'Common/Cookie.php',
		'Common/Currency.php',
		'Common/Date.php',
		'Common/Email.php',
		'Common/Extract.php',
		'Common/File.php',
		'Common/Format.php',
		'Common/Generate.php',
		'Common/Hashing.php',
		'Common/IP.php',
		'Common/MIME.php',
		'Common/Math.php',
		'Common/Num.php',
		'Common/Reflector.php',
		'Common/Request.php',
		'Common/Sanitize.php',
		'Common/Server.php',
		'Common/Site.php',
		'Common/Social.php',
		'Common/Split.php',
		'Common/Str.php',
		'Common/Time.php',
		'Common/URL.php',
		'Common/Unit.php',
		'Common/Validate.php',
		'Database/Exists.php',
		'Database/Generate.php',
		'Database/Query.php',
		'Database/Query/Author.php',
		'Database/Query/Date.php',
		'Database/Query/Meta.php',
		'Database/Query/Orderby.php',
		'Database/Query/Post.php',
		'Database/Query/Relationship.php',
		'Database/Query/Tax.php',
		'Database/Table.php',
		'Debug/Debug.php',
		'Functions/HTML.php',
		'Functions/Math.php',
		'Functions/PostTypes.php',
		'Functions/Posts.php',
		'Functions/Register.php',
		'Functions/Taxonomies.php',
		'Functions/Terms.php',
		'Functions/Users.php',
		'HTML/Components/Base.php',
		'HTML/Components/Container.php',
		'HTML/Components/Media.php',
		'HTML/Components/Nav.php',
		'HTML/Components/Status.php',
		'HTML/Components/Table.php',
		'HTML/Components/Utils.php',
		'HTML/Element.php',
		'HTML/Field.php',
		'HTML/FormLayout.php',
		'HTML/Helpers/CSS.php',
		'HTML/Helpers/DOM.php',
		'HTML/Helpers/JS.php',
		'HTML/Scripts.php',
		'HTML/Shape.php',
		'I18n/Operators.php',
		'I18n/Statuses.php',
		'I18n/TimeUnits.php',
		'Math/ExpressionParser.php',
		'Media/Attachment.php',
		'Options/Option.php',
		'Options/Options.php',
		'Polyfills/Mdash.php',
		'Polyfills/Misc.php',
		'Polyfills/Time.php',
		'PostTypes/PostType.php',
		'PostTypes/PostTypes.php',
		'PostTypes/Search.php',
		'Posts/Post.php',
		'Posts/Posts.php',
		'Posts/Search.php',
		'Register/Assets.php',
		'Register/Roles.php',
		'Register/Utils/AssetCollection.php',
		'Roles/Role.php',
		'Roles/Roles.php',
		'Taxonomies/Search.php',
		'Taxonomies/Taxonomies.php',
		'Taxonomies/Taxonomy.php',
		'Terms/Search.php',
		'Terms/Term.php',
		'Terms/Terms.php',
		'Themes/Theme.php',
		'Themes/Themes.php',
		'Transients/Transient.php',
		'Transients/Transients.php',
		'Users/Search.php',
		'Users/User.php',
		'Users/Users.php',
		'Widgets/Widget.php',
		'Widgets/Widgets.php',
	);

	foreach ( $files as $file ) {
		$file_path = WP_UTILS_DIR . $file;
		if ( file_exists( $file_path ) ) {
			require_once $file_path;
		}
	}

}

// Hook to load the files.
add_action( 'plugins_loaded', 'wp_utils_load_files', 1 );

/**
 * Add the debug tools page to the WordPress admin
 */
function add_debug_tools_page(): void {
	add_management_page(
		'Debug Tools',
		'Debug Tools',
		'manage_options',
		'arraypress-debug-tools',
		'render_debug_tools_page'
	);
}

add_action( 'admin_menu', 'add_debug_tools_page' );

use ArrayPress\Utils\Elements\Element;
use ArrayPress\Utils\Elements\Field;

/**
 * Render the debug tools page
 */
function render_field_examples_page() {
	ob_start();
	?>
    <div class="wrap">
        <h1>Field Examples</h1>

        <div class="card">
            <h2>Basic Text Inputs</h2>
			<?php
			// Text input
			echo Field::section(
				Field::row(
					Field::text([
						'name' => 'text_example',
						'value' => 'Default text',
						'label' => 'Text Input',
						'desc' => 'This is a basic text input field'
					]),
					['label' => 'Text Field']
				) .
				// Email input
				Field::row(
					Field::email([
						'name' => 'email_example',
						'value' => 'test@example.com',
						'label' => 'Email Input',
						'desc' => 'Enter your email address'
					]),
					['label' => 'Email Field']
				) .
				// URL input
				Field::row(
					Field::url([
						'name' => 'url_example',
						'value' => 'https://example.com',
						'label' => 'URL Input',
						'desc' => 'Enter a valid URL'
					]),
					['label' => 'URL Field']
				),
				['title' => 'Text Input Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Number & Range Inputs</h2>
			<?php
			echo Field::section(
				Field::row(
					Field::number([
						'name' => 'number_example',
						'value' => '42',
						'min' => 0,
						'max' => 100,
						'step' => 1,
						'label' => 'Number Input',
						'desc' => 'Enter a number between 0 and 100'
					]),
					['label' => 'Number Field']
				) .
				Field::row(
					Field::range([
						'name' => 'range_example',
						'value' => '50',
						'min' => 0,
						'max' => 100,
						'step' => 1,
						'show_value' => true,
						'label' => 'Range Slider',
						'desc' => 'Slide to select a value'
					]),
					['label' => 'Range Slider']
				),
				['title' => 'Numeric Input Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Selection Fields</h2>
			<?php
			$options = [
				'option1' => 'Option 1',
				'option2' => 'Option 2',
				'option3' => 'Option 3'
			];

			echo Field::section(
				Field::row(
					Field::select([
						'name' => 'select_example',
						'options' => $options,
						'selected' => 'option2',
						'label' => 'Select Dropdown',
						'desc' => 'Choose an option'
					]),
					['label' => 'Select Field']
				) .
				Field::row(
					Field::radio_group([
						'name' => 'radio_example',
						'options' => $options,
						'selected' => 'option1',
						'label' => 'Radio Options',
						'desc' => 'Select one option'
					]),
					['label' => 'Radio Group']
				) .
				Field::row(
					Field::checkbox_group([
						'name' => 'checkbox_group_example',
						'options' => $options,
						'selected' => ['option1', 'option3'],
						'label' => 'Checkbox Options',
						'desc' => 'Select multiple options'
					]),
					['label' => 'Checkbox Group']
				),
				['title' => 'Selection Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Toggle & Checkbox</h2>
			<?php
			echo Field::section(
				Field::row(
					Field::toggle_switch([
						'name' => 'toggle_example',
						'label' => 'Toggle Switch',
						'checked' => true,
						'desc' => 'Switch this option on or off'
					]),
					['label' => 'Toggle Switch']
				) .
				Field::row(
					Field::checkbox([
						'name' => 'checkbox_example',
						'label' => 'Single Checkbox',
						'checked' => true,
						'desc' => 'Check this option'
					]),
					['label' => 'Checkbox Field']
				),
				['title' => 'Toggle & Checkbox Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Media & File Upload</h2>
			<?php
			echo Field::section(
				Field::row(
					Field::wp_media_upload([
						'name' => 'media_example',
						'value' => '',
						'label' => 'Media Upload',
						'desc' => 'Select or upload media',
						'type' => 'image'
					]),
					['label' => 'Media Upload']
				) .
				Field::row(
					Field::file([
						'name' => 'file_example',
						'label' => 'File Upload',
						'desc' => 'Select a file to upload',
						'accept' => '.pdf,.doc,.docx'
					]),
					['label' => 'File Upload']
				),
				['title' => 'Upload Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Color & Date/Time</h2>
			<?php
			echo Field::section(
				Field::row(
					Field::color([
						'name' => 'color_example',
						'value' => '#FF5733',
						'label' => 'Color Picker',
						'desc' => 'Select a color'
					]),
					['label' => 'Color Field']
				) .
				Field::row(
					Field::date([
						'name' => 'date_example',
						'value' => date('Y-m-d'),
						'label' => 'Date Picker',
						'desc' => 'Select a date'
					]),
					['label' => 'Date Field']
				) .
				Field::row(
					Field::time([
						'name' => 'time_example',
						'value' => '13:30',
						'label' => 'Time Picker',
						'desc' => 'Select a time'
					]),
					['label' => 'Time Field']
				),
				['title' => 'Color & DateTime Fields']
			);
			?>
        </div>

        <div class="card">
            <h2>Form Actions</h2>
			<?php
			echo Field::actions(
				get_submit_button('Save Changes', 'primary', 'submit', false) .
				get_submit_button('Reset', 'secondary', 'reset', false),
				['align' => 'right', 'sticky' => true]
			);
			?>
        </div>
    </div>

    <style>
        .card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
            margin: 20px 0;
            padding: 20px;
        }

        .card h2 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
    </style>
	<?php
	return ob_get_clean();
}