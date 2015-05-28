<?php
use Silex\Application;

$app = new Application();
$app['debug'] = true;
require __DIR__.'/Config/route.php';
$app->register(new Silex\Provider\FormServiceProvider());
// Twig config  $twig
$app->register(new Silex\Provider\TwigServiceProvider(), [
        'twig.path' => __DIR__.'/../views',
        'twig.templates' => ['form' => __DIR__.'/../views/form_div_layout.html.twig'],
    ]
);
$app->register(new Silex\Provider\TranslationServiceProvider(), [
        'locale_fallback' => 'es',
        'translator.messages' => [],
    ]
);

$app['twig']->addExtension(new \Entea\Twig\Extension\AssetExtension($app));
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
// https://github.com/silexphp/Silex/wiki/Third-Party-ServiceProviders#database
$app->register(
    new \Arseniew\Silex\Provider\IdiormServiceProvider(),
    [
        'idiorm.db.options' => [
            'connection_string' => 'mysql:host=localhost;dbname=Prueba',
            'username' => 'root',
            'password' => '1234',
        ]
    ]
);

/*$app->register(
    new Silex\Provider\DoctrineServiceProvider(),
    [
        'dbs.options' => [
            'default' => [
                'driver' => 'pdo_mysql',
                'host' => 'localhost',
                'dbname' => 'Prueba',
                'user' => 'root',
                'password' => '1234',
                'charset' => 'utf8',
            ]
        ],
    ]
);*/

return $app;