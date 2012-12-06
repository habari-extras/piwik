Piwik is an Open Source Web analytics package.

Piwik is self-hosted software. You need to install and configure Piwik
separately. Piwik needs PHP and a MySQL database to store data on
site visits.

For more details, see

    http://piwik.org/

This plugin embeds the Piwik (Javascript) tracking code in the theme footer.

To install the plugin, unpack under the '/user/plugins' directory in
your Habari installation.

Then activate and configure the plugin from the dashboard (Admin-Plugins).

The configuration options are:
    - Piwik site URL: This is the full URL of the Piwik site
      (e.g. 'http://www.example.com/piwki') - the trailing slash '/'
      is required
    - Piwik site number: Piwik can track multiple Web sites. The site
      number is displayed in the Piwik-Settings administration screen
      under the 'Site' tab in the 'ID' field.
    - Tracked logged-in users: Visits by logged in users can
      optionally be ignored.
    - Piwik Auth Token: The auth token of your piwik install. Found in the
      "API" screen, link at the top.
    - Include PiWik Click Heat Plugin JS: If you have the Click Heat plugin installed on your Piwik instance,
      you can include the Javascript needed by clicking this checkbox.
