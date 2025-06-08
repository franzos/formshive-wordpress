# WordPress.org Plugin Submission Guide

This document outlines the complete process for submitting the Formshive WordPress plugin to WordPress.org.

## Pre-Submission Checklist

Before submitting, ensure all requirements are met:

- [ ] Plugin follows WordPress coding standards
- [ ] All files are properly sanitized and escaped
- [ ] Plugin header information is complete and accurate
- [ ] readme.txt file is properly formatted
- [ ] Translation files (POT) are included
- [ ] Plugin is tested on latest WordPress version
- [ ] All security best practices implemented
- [ ] Proper licensing (GPL v2 or later)

## Version Management

### 1. Increment Version Number

Before each release, update version numbers in these files:

#### Main Plugin File (`formshive.php`)
```php
/**
 * Version: 1.0.1  // Update this
 */
```

#### readme.txt
```
Stable tag: 1.0.1  // Update this
```

#### Translation Files
Update version in `languages/formshive.pot`:
```
"Project-Id-Version: Formshive 1.0.1\n"
```

### Version Numbering Convention
- **Major**: 1.0.0, 2.0.0 (breaking changes)
- **Minor**: 1.1.0, 1.2.0 (new features)
- **Patch**: 1.0.1, 1.0.2 (bug fixes)

## Git Management

### 2. Commit Changes and Create Tags

```bash
# Stage all changes
git add .

# Commit with descriptive message
git commit -m "Release v1.0.1: Add new framework support and bug fixes"

# Create and push tag
git tag v1.0.1
git push origin main
git push origin v1.0.1

# Alternative: Create annotated tag with changelog
git tag -a v1.0.1 -m "Release v1.0.1

- Added Formshive framework support
- Updated German translations
- Fixed URL escaping issues
- Improved security practices"

git push origin v1.0.1
```

### Branch Strategy
- `main` - Stable releases
- `develop` - Development work
- `release/x.x.x` - Release preparation

## Plugin Packaging

### 3. Create Distribution ZIP

Create a clean ZIP file for submission (excluding development files):

```bash
# Method 1: Using git archive (recommended)
cd /path/to/your/repo
git archive --format=zip --output=formshive-1.0.1.zip HEAD:wp-content/plugins/formshive/ \
  --exclude='.git*' \
  --exclude='*.md' \
  --exclude='.*'

# Method 2: Manual ZIP creation
cd /path/to/wordpress/wp-content/plugins/
zip -r formshive-1.0.1.zip formshive/ \
  -x "formshive/.git*" \
  -x "formshive/*.md" \
  -x "formshive/.*"

## Files to Include in Submission

### Required Files
- `formshive.php` (main plugin file)
- `readme.txt` (WordPress.org readme)
- `uninstall.php` (cleanup script)
- `includes/` (all PHP class files)
- `templates/` (admin templates)
- `assets/` (CSS/JS files)
- `languages/` (translation files)

### Optional but Recommended
- `LICENSE` or `LICENSE.txt`
- `CHANGELOG.md` (if not using readme.txt)