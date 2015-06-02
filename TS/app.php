<?php
use Silex\Application;

$app = new Application();
$app['debug'] = true;
require __DIR__.'/Config/route.php';

$app->register(new Silex\Provider\UrlGeneratorServiceProvider());
$app->register(new Silex\Provider\RememberMeServiceProvider());
$app->register(new Silex\Provider\SessionServiceProvider());
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\FormServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), [
        'locale_fallback' => 'es',
        'translator.messages' => [],
    ]
);
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

$app->register(
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
);

$app['security.firewalls'] = [
    'login' => [
        'pattern' => '^/user/login$', //<- anonymous path
    ],
    'register' => [
        'pattern' => '^/user/register$' //<- anonymous path
    ],
    'secured_area' => [
        'pattern' => '^.*$',
        'anonymous' => false,
        'form' => [
            'login_path' => '/user/login',
            'check_path' => '/user/login_check',
        ],
        'logout' => [
            'logout_path' => '/user/logout',
        ],
        'users' => $app->share(
            function ($app) {
                return $app['user.manager'];
            }
        ),
    ],
];
// Twig config  $twig
$app->register(new Silex\Provider\TwigServiceProvider(), [
        'twig.path' => __DIR__.'/../views',
        'twig.templates' => ['form' => __DIR__.'/../views/form_div_layout.html.twig'],
    ]
);
$app['twig']->addExtension(new \Entea\Twig\Extension\AssetExtension($app));
$app->register(new Silex\Provider\SecurityServiceProvider());
$simpleUserProvider = new SimpleUser\UserServiceProvider();
$app->register($simpleUserProvider);
$app->mount('/user', $simpleUserProvider);
$function = new Twig_SimpleFunction('is_granted', function($role,$object = null) use ($app){
    return $app['security']->isGranted($role,$object);
});
$app['twig']->addFunction($function);
//$user = $app['user.manager']->createUser('admin@mail.com', '1234', 'Administrador', array('ROLE_ADMIN'));
//$app['user.manager']->insert($user);
return $app;