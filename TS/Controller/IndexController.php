<?php
namespace TS\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController implements ControllerProviderInterface
{
    public function index(Application $app, Request $request)
    {
        $datos = $app['idiorm.db']->for_table('Simple')->findMany();

        $data = [
            'descripcion' => 'Descripción',
            'precio' => '200',
            'fecha' => new \DateTime('today'),
        ];

        $formBuilder = $app['form.factory']->createBuilder('form', $data)
            ->add('descripcion')
            ->add('precio', 'money')
        ;

        $form = $formBuilder->getForm();

        return $app['twig']->render(
            'home.twig',
            [
                'form' => $form->createView(),
                'datos' => $datos
            ]
        );
    }

    public function update(Application $app, Request $request)
    {
        $formBuilder = $app['form.factory']->createBuilder('form', [])
            ->add('descripcion')
            ->add('precio', 'money')
        ;

        $form = $formBuilder->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $simple = $app['idiorm.db']->for_table('Simple')->create();

            $simple->descripcion = $data['descripcion'];
            $simple->precio = $data['precio'];

            $simple->save();

            return new Response('Se guardo correctamente');
        }

        return new Response('Woops!! algo fue mal!');
    }

    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $app->get('/', 'TS\Controller\IndexController::index');
        $app->post('/', 'TS\Controller\IndexController::update')
            ->bind('guardarDatos');

        return $controllers;
    }

}