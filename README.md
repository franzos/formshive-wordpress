# Formshive WordPress Plugin

A comprehensive WordPress plugin that allows you to embed Formshive forms on your WordPress website. Connect to your Formshive account to display forms with seamless WordPress integration.

## Features

### 🚀 Formshive Integration
- **Embed Forms**: Connect and display forms directly from your Formshive account
- **WordPress Integration**: Seamless integration with WordPress content
- **Responsive Design**: Forms look great on all devices
- **Form Management**: Admin interface to manage your embedded forms

### 🎨 Framework Support
- **Formshive**: Native Formshive styling (default)
- **Bootstrap**: Bootstrap CSS framework compatibility
- **Bulma**: Bulma CSS framework support

### 🔧 WordPress Integration
- **Shortcodes**: Use `[formshive id="123"]` anywhere
- **Gutenberg Blocks**: Visual form embedding in the block editor
- **Widget Support**: Add forms to widget areas
- **PHP Integration**: Embed forms directly in theme files

## Installation

### Automatic Installation (Recommended)

1. Go to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Search for "Formshive"
4. Click **Install Now** and then **Activate**

### Manual Installation

1. Download the plugin from [WordPress.org](https://wordpress.org/plugins/formshive/)
2. Upload the `formshive` folder to `/wp-content/plugins/`
3. Activate the plugin through the 'Plugins' menu in WordPress

### From GitHub

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/formshive/formshive-wp.git formshive
```

## Quick Start

### 1. Configure Settings

After activation, go to **Forms → Settings** and configure:
- **API Endpoint**: Your Formshive API endpoint
- **Default Framework**: Choose Formshive, Bootstrap, or Bulma

### 2. Embed Your First Form

1. Go to **Forms → Add New Form**
2. Enter a form name
3. Enter your complete Formshive form URL
4. Select your preferred CSS framework
5. Save the form

### 3. Display Your Form

#### Using Shortcode
```php
[formshive id="123"]
```

#### Using Gutenberg Block
1. Add a new **Formshive Form** block
2. Select your form from the dropdown
3. Configure display settings

#### Using PHP
```php
<?php echo do_shortcode('[formshive id="123"]'); ?>
```

## Troubleshooting

### Common Issues

#### Forms Not Displaying
1. Check if form ID exists in **Forms → All Forms**
2. Verify shortcode syntax: `[formshive id="123"]`
3. Ensure plugin is activated

#### Styling Issues
1. Check CSS framework setting in **Forms → Settings**
2. Verify theme compatibility
3. Check for CSS conflicts in browser dev tools

#### Form Loading Issues
1. Check API endpoint configuration
2. Verify Formshive form ID is correct
3. Check error logs in **Tools → Site Health**

## License

This plugin is licensed under the [GPL v2 or later](https://www.gnu.org/licenses/gpl-2.0.html).

---

Made with ❤️ by the [Formshive](https://formshive.com) team.