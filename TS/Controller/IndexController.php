<?php
namespace TS\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO: usar doctrine en lugar de idiorm (para no tener dos conexiones)
 * Class IndexController
 * @package TS\Controller
 */
class IndexController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     * @param Request $request
     * @return mixed
     */
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

    /**
     * @param Application $app
     * @param Request $request
     * @return Response
     */
    public function nuevo(Application $app, Request $request)
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

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     */
    public function actualizar(Application $app, Request $request, $id)
    {

    }

    /**
     * @param Application $app
     * @param $id
     * @return Response
     */
    public function elimiar(Application $app, $id)
    {
        try {
            $tabla = $app['idiorm.db']->for_table('Simple')->where('id', $id)->find_one();
            $tabla->delete();
        } catch (\Exception $e) {
            return new Response('Excepción capturada: ' + $e->getMessage());
        }

        return new Response('El registro se elimino correctamente');
    }

    /**
     * @param Application $app
     * @return mixed
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $app->get('/', 'TS\Controller\IndexController::index');
        $app->post('/', 'TS\Controller\IndexController::nuevo')
            ->bind('nuevo');
        $app->put('/{id}', 'TS\Controller\IndexController::actualizar')
            ->bind('actualizar');
        $app->delete('/{id}', 'TS\Controller\IndexController::elimiar')
            ->bind('elimiar');

        return $controllers;
    }

}