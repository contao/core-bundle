# Change log

## DEV

 * Do not make the response private when saving the session (see #1388).
 * Improve the folder hashing performance (see contao/core#8856).

## 4.4.14 (2018-02-14)

 * Correctly render the article list if there are no articles (see #1351).
 * Do not log custom insert tags as "unknown" if they have been replaced (see #1295).
 * Show redirect pages with unpublished targets in the front end preview.

## 4.4.13 (2018-01-23)

 * Quote reserved words in database queries (see #1262).

## 4.4.12 (2018-01-03)

 * Do not resend activation mails for active members (see #1234).
 * Order the files by name when selecting folders in the file picker (see #1270).
 * Optimize inserting keywords into tl_search_index (see #1277).

## 4.4.11 (2017-12-28)

 * Revert 'Quote reserved words in database queries (see #1262)'.

## 4.4.10 (2017-12-27)

 * Quote reserved words in database queries (see #1262).
 * Only add _locale if prepend_locale is enabled (see #1257).

## 4.4.9 (2017-12-14)

 * Show the "invisible" field when editing a form field (see #1199).
 * Only add pages requested via GET to the search index (see #1194).
 * Fix the Encrption class not supporting PHP 7.2 (see contao/core#8820).
 * Handle single file uploads in FileUpload::getFilesFromGlobal() (see #1192).

## 4.4.8 (2017-11-15)

 * Prevent SQL injections in the back end search panel (see CVE-2017-16558).
 * Support class named services in System::import() and System::importStatic() (see #1176).
 * Only show pretty error screens on Contao routes (see #1149).

## 4.4.7 (2017-10-12)

 * Show broken images in the file manager (see #1116).
 * Copy the existing referers if a new referer ID is initialized (see #1117).
 * Stop using the TinyMCE gzip compressor (deprecated since 2014).
 * Prevent the User::authenticate() method from running twice (see #1067).

## 4.4.6 (2017-09-28)

 * Bind the lock file path to the installation root directory (see #1107).
 * Correctly select the important part in the modal dialog (see #1093).
 * Correctly handle unencoded data images in the Combiner (see contao/core#8788).
 * Do not add a suffix when copying if the "doNotCopy" flag is set (see contao/core#8610).
 * Use the module type as group header if sorted by type (see contao/core#8402).

## 4.4.5 (2017-09-18)

 * Fall back to the URL if there is no link title (see #1081).
 * Correctly calculate the intersection of the root nodes with the mounted nodes (see #1001).
 * Catch the DriverException if the database connection fails (see contao/managed-edition#27).
 * Fix the back end theme.
 * Check if the session has been started before using the flash bag.

## 4.4.4 (2017-09-05)

 * Show the form submit buttons at the end of the form instead of at the end of the page.
 * Do not add the referer ID in the Template::route() method (see #1033). 

## 4.4.3 (2017-08-16)

 * Correctly assign the form CSS ID (see #956).
 * Fix the referer management in the back end (see contao/core#6127).
 * Also check for a front end user during header replay (see #1008).
 * Encode the username when opening the front end preview as a member (see contao/core#8762).
 * Correctly assign the CSS media type in the combiner.

## 4.4.2 (2017-07-25)

 * Adjust the command scheduler listener so it does not rely on request parameters (see #955).
 * Rewrite the DCA picker (see #950).

## 4.4.1 (2017-07-12)

 * Prevent arbitrary PHP file inclusions in the back end (see CVE-2017-10993).
 * Correctly handle subpalettes in "edit multiple" mode (see #946).
 * Correctly show the DCA picker in the site structure (see #906).
 * Correctly update the style sheets if a format definition is enabled/disabled (see #893).
 * Always show the "show from" and "show until" fields (see #908).

## 4.4.0 (2017-06-15)

 * Fix the "save and go back" function (see #870).

## 4.4.0-RC2 (2017-06-12)

 * Update all Contao components to their latest version.
 * Regenerate the symlinks after importing a theme (see #867).
 * Only check "gdMaxImgWidth" and "gdMaxImgHeight" if the GDlib is used to resize images (see #826).
 * Improve the accessibility of the CAPTCHA widget (see contao/core#8709).
 * Re-add the "reset selection" buttons to the picker (see #856).
 * Trigger all the callbacks in the toggleVisibility() methods (see #756).
 * Only execute the command scheduler upon the "contao_backend" and "contao_frontend" routes (see #736).
 * Correctly set the important part in "edit multiple" mode (see #839).
 * Remove the broken alias transliteration see (#848).

## 4.4.0-RC1 (2017-05-23)

 * Tweak the back end template.
 * Add the "allowed member groups" setting (see contao/core#8528).
 * Hide the CAPTCHA field by default by adding a honeypot field (see #832).
 * Add the "href" parameter to the article list (see #694).
 * Show both paths and UUIDs in the "show" view (see #793).
 * Do not romanize file names anymore.
 * Improve the back end breadcrumb menu (see #623).
 * Correctly render the image thumbnails (see #817).
 * Use the "file" insert tag in the file picker where applicable (see contao/core#8578).
 * Update the Punycode library to version 2 (see #748).
 * Always add custom meta fields to the templates (see #717).

## 4.4.0-beta1 (2017-05-05)

 * Warn if another user has edited a record when saving it (see #809).
 * Optimize the element preview height (see #810).
 * Use the file meta data by default when adding an image (see #807).
 * Use the kernel.project_dir parameter (see #758).
 * Disable the "publish" checkbox if a parent folder is public (see #712).
 * Improve the findByIdOrAlias() method (see #729).
 * Make sure that all modules can have a custom template (see #704).
 * Remove the popupWidth and popupHeight parameters in the file manager (see #727).
 * Remove the cron.txt file (see #753).
 * Allow to disable input encoding for a whole DCA (see #708).
 * Add the DCA picker (see #755).
 * Allow to manually pass a value to any widget (see #674).
 * Only prefix an all numeric alias when standardizing (see #707).
 * Support using objects in callback arrays (see #699).
 * Support importing form field options from a CSV file (see #444).
 * Add a Doctrine DBAL field type for UUIDs (see #415).
 * Support custom back end routes (see #512).
 * Add the contao.image.target_dir parameter (see #684).
 * Match the security firewall based on the request scope (see #677).
 * Add the contao.web_dir parameter (see contao/installation-bundle#40).
 * Look up the form class and allow to choose the type (see contao/core#8527).
 * Auto-select the active page in the quick navigation/link module (see contao/core#8587).
