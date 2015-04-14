Contao change log
=================

Version 4.0.0-beta1 (2014-04-13)
---------------------------------

### Removed
Removed the functions `show_help_message()` and `die_nicely()` throw meaningful exceptions instead.

### Removed
The `coreOnlyMode` setting has been removed (see #145).

### Removed
The change log viewer has been removed from the back end (see #152).

### Changed
The rich text and code editor configuration files are now real templates, which
can be customized in the template editor.

### Changed
The `debugMode` setting has been removed, since the debug mode is automatically
enabled if the application is called via the `app_dev.php` script.

### Improved
The `rewriteUrl` setting has been removed, because the application now adds or
removes the script fragment automatically.

### Changed
Protect the `DcaExtractor` constructor (use `getInstance()` instead).

### Changed
Return `null` if a widget is empty and the DB field is nullable (see #17).

### Changed
Remove the JS library dependencies from the library agnostic scripts (see #23).

### Changed
Replace the syntax highlighter component with highlight.js.

### Removed
Removed the "default" theme in favor of the "flexible" theme.

### Changed
Load the third-party components via `contao-components`.

### Removed
Removed the MooTools "slimbox" plugin.

### Removed
Removed the CSS3PIE plugin.

### Changed
Make the public extension folders configurable (see #8).

### Fixed
Correctly symlink the upload folder.

### Changed
Do not use a constant for the website path (see contao/core#5347).

### Changed
Support scopes in the `Message` class (see contao/core#6558).

### Changed
Use `<fieldset>` and `<legend>` in the newsletter channel selection menu.

### Changed
Always pass the DC object as first argument in the ondelete_callback.

### Changed
Do not auto-generate article IDs from their alias names (see contao/core#4837).

### Fixed
Correctly assign the CSS classes "odd" and "even" to the table element.

### Changed
Use a `<strong>` tag to highlight keywords in search results.

### Changed
Use a `<strong>` tag instead of a `<span>` tag for active menu items.

### Changed
Use the CSS class `active` instead of `current` in the pagination menu.

### Changed
Use the CSS class `previous` instead of `prev` in the book navigation module.

### Fixed
Correctly set the folder protection status when loading subfolders (see #4).

### Changed
Adjust the logic of the `File` class (see contao/core#5341).

### Removed
Remove the Safe Mode Hack, the XHTML resources and the IE6 warning.

### Changed
Move all public resources to the `web/` subdirectory.
