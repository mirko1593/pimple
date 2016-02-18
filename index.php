<?php 

use Acme\Pimple;

require 'vendor/autoload.php';

$container = new Pimple;

# defining parameters.
$container['cookie_name'] = 'SESSION_ID';
$container['session_storage_class'] = 'SessionStorage';


# defining services.
$container['session_storage'] = function ($c) {
	return new $c['session_storage_class']($c['cookie_name']);
};

$container['session'] = function ($c) {
	return new Session($c['session_storage']);
};

# defining shared parameters.
$container['log'] = $container->share(function ($c) {
	return new Logger($c['log_file']);
});


# protect a anonymous function as a parameter. instead of a service.
$container['random'] = $container->protect( function () { 
	return rand(0, 100); 
});


# modifying services after creation.
$container['mail'] = function ($c) {
	return new \Zend_Mail();
};


$container['mail'] = $container->extend('mail', function ($mail, $c) {
	$mail->setFrom($c['mail.default_address']);
	return $mail;
});


# modifying shared services after creation.
$container['twig'] = $container->share(function ($c) {
	return new Twig_Environment($c['twig.loader'], $c['twig_option']);
});

$container['twig'] = $container->share(
	$container->extend('twig', function ($twig, $c) {
		$twig->addExtension(new MyTwigExtension());
		return $twig;
	})
);


# fetching service creation function without execute.
$container['cache'] = $container->share(function ($c) {
	return new Cache($c['cache_option']);
});

$cacheFunction = $container->raw('cache');



echo "done boorstrap.";









