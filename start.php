<?php

// Start the bundle after the Laravel application, to allow
// OAuth tokens to be set
Laravel\Event::listen('laravel.started: dropbox', function()
{
	// Register a simple autoload function
	spl_autoload_register(function($class)
	{
		if (substr($class, 0, 7) === 'Dropbox')
		{
			$class = str_replace('\\', '/', $class);
			require_once(__DIR__ . '/' . $class . '.php');
		}
	});

	$app_key = Laravel\Config::get('dropbox::config.app_key');
	$app_secret = Laravel\Config::get('dropbox::config.app_secret');
	$encryption_key = Laravel\Config::get('dropbox::config.encryption_key');

	if (empty($app_key) || empty($app_secret))
	{
		throw new \Dropbox\Exception('Please set your Dropbox App key & secret.');
	}

	if (strlen($encryption_key) !== 32)
	{
		throw new \Dropbox\Exception('Expecting a 32 byte Dropbox encryption key, got ' . strlen($encryption_key));
	}

	// Check whether to use HTTPS and set the callback URL
	$protocol = (isset($_SERVER['HTTPS']) && ! empty($_SERVER['HTTPS'])) ? 'https' : 'http';
	$request_uri = (isset($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '/';
	$http_host = (isset($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : '127.0.0.1';
	$callback = $protocol . '://' . $http_host . $request_uri;

	// Instantiate the required Dropbox objects
	$encrypter = new \Dropbox\OAuth\Storage\Encrypter($encryption_key);
	$storage = new \Dropbox\OAuth\Storage\Session($encrypter);

	if ($access_token = Config::get('dropbox::config.access_token'))
	{
		$storage->set((object) $access_token, 'access_token');
	}

	$OAuth = new \Dropbox\OAuth\Consumer\Curl($app_key, $app_secret, $storage, $callback);
	$dropbox = new \Dropbox\API($OAuth, Laravel\Config::get('dropbox::config.root'));

	IoC::instance('dropbox::session', $storage);
	IoC::instance('dropbox::api', $dropbox);
});
