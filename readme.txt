=== Formshive ===
Contributors: formshive
Tags: forms, contact-forms, form-builder, embed, gutenberg-blocks
Requires at least: 5.0
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 0.1.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily embed Formshive forms on your WordPress website. Connect to your Formshive account to display forms with WordPress integration.

== Description ==

A WordPress integration plugin that allows you to embed Formshive forms on your WordPress website. Connects to the Formshive API to display your existing forms with WordPress shortcodes and Gutenberg blocks.

= Features =

**🚀 Formshive Integration**
* **Embed Forms**: Connect and display forms directly from your Formshive account
* **WordPress Integration**: Seamless integration with WordPress content
* **Responsive Design**: Forms look great on all devices
* **Form Management**: Admin interface to manage your embedded forms

**🎨 Framework Support**
* **Formshive**: Native Formshive styling (default)
* **Bootstrap**: Bootstrap compatibility for styling
* **Bulma**: Bulma CSS framework support

**🔧 WordPress Integration**
* **Shortcodes**: Use `[formshive id="123"]` anywhere
* **Gutenberg Blocks**: Visual form embedding in the block editor
* **Widget Support**: Add forms to widget areas
* **PHP Integration**: Embed forms directly in theme files

= Usage =

**Create Your First Form**

**Add a Formshive Form**
1. Go to **Forms → Add New Form**
2. Enter a form name
3. Enter your complete Formshive form URL
4. Select your preferred CSS framework
5. Save the form

**Display Your Form**

*Using Shortcode*
`[formshive id="123"]`

*Using Gutenberg Block*
1. Add a new **Formshive Form** block
2. Select your form from the dropdown
3. Configure display settings

*Using PHP*
`<?php echo do_shortcode('[formshive id="123"]'); ?>`

== Installation ==

= Automatic Installation (Recommended) =

1. Go to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "Formshive"
4. Click **Install Now** and then **Activate**

= Manual Installation =

1. Download the plugin from WordPress.org
2. Upload the `formshive` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

= From GitHub =

1. Download or clone from GitHub
2. Upload to `/wp-content/plugins/formshive/`
3. Activate through WordPress admin

== Frequently Asked Questions ==

= How do I get a Formshive account? =

Visit [Formshive.com](https://formshive.com) to create your account and start building forms.

= Can I use this plugin without a Formshive account? =

No, this plugin requires a Formshive account to create and manage forms. The plugin embeds forms from the Formshive service.

= Which CSS frameworks are supported? =

The plugin supports three CSS frameworks for form styling:
* Formshive (default) - Native Formshive styling
* Bootstrap - Bootstrap CSS framework compatibility  
* Bulma - Bulma CSS framework support

= Can I customize the form appearance? =

Form appearance is controlled by the Formshive service and the selected CSS framework (Formshive, Bootstrap, or Bulma). You can override styles with custom CSS in your theme.

= Is the plugin translation ready? =

Yes, the plugin is fully internationalized and ready for translation. POT file is included.

= Does it work with Gutenberg? =

Yes! The plugin includes a custom Gutenberg block for easy form embedding in the block editor.

== Changelog ==

= 0.1.0 =
* Initial release of Formshive WordPress plugin

== Additional Info ==

= Requirements =
* WordPress 5.0 or higher
* PHP 7.4 or higher
* Modern web browser with JavaScript enabled

= Privacy =
This plugin connects to the Formshive API when using embed mode. Form submissions may be sent to Formshive servers based on your configuration. Please review Formshive's privacy policy for details on data handling.