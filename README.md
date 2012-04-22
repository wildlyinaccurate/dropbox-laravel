This bundle allows your Laravel application to access the Dropbox API via a Dropbox App. If you don't have a Dropbox App, you can create one at https://www.dropbox.com/developers

## Installation
### Artisan
    php artisan bundle:install dropbox

### Bundle Registration
Add the following to your **application/bundles.php** file:

    'dropbox' => array(
        'auto'    => true,
    ),

### Configuration
Configuration is done in the bundle's "start" event. A good place to put this is in your `application/start.php`. Here's an example:

    Event::listen('laravel.started: dropbox', function()
    {
        Config::set('dropbox::config.app_key', 'your-app-key');
        Config::set('dropbox::config.app_secret', 'your-app-secret');
        Config::set('dropbox::config.encryption_key', 'This should be a unique 32-character string');
        Config::set('dropbox::config.root', 'sandbox'); // Either 'sandbox' or 'dropbox'
    });

You can also set your OAuth access token in this event (see the "Storing OAuth Tokens" section below).

    Config::set('dropbox::config.access_token', array(
        'oauth_token_secret' => 'your-oauth-token-secret',
        'oauth_token' => 'your-oauth-token',
        'uid' => 'your-uid',
    ));
### Usage
Use the Laravel IoC container to retrieve the Dropbox API object. [See here](https://github.com/BenTheDesigner/Dropbox/tree/master/examples) for more usage examples.

    $dropbox = IoC::resolve('dropbox::api');
    $metadata = $dropbox->metadata();

[Usage examples](https://github.com/BenTheDesigner/Dropbox/tree/master/examples)

### Storing OAuth Tokens
This bundle stores OAuth tokens using PHP sessions. It is recommended that you store OAuth tokens somewhere more permanent so that your users don't have to authorise your Dropbox app every time their session expires.

You can retrieve the Dropbox OAuth tokens by doing the following:

    $dropbox_session = IoC::resolve('dropbox::session');
    $access_token = $dropbox_session->get('access_token');

`$access_token` will look something like:

    stdClass Object
    (
        [oauth_token_secret] => xxxxxxxxxxxxxxx
        [oauth_token] => xxxxxxxxxxxxxxx
        [uid] => 10252479
    )

You can then store and re-use this access token. The access token should be set in the bundle's "start" event (see "Configuration" section above) but it can also be set after the bundle has started:

    $dropbox_session = IoC::resolve('dropbox::session');
    $dropbox_session->set($access_token, 'access_token');

This bundle is a port [BenTheDesigner's Dropbox SDK](https://github.com/BenTheDesigner/Dropbox).
