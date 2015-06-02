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

        $form = $this->getForm($app['form.factory'], $data);

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
        $form = $this->getForm($app['form.factory']);
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function actualizar(Application $app, Request $request, $id)
    {
        $tabla = $app['idiorm.db']
            ->for_table('Simple')
            ->where('id', $id)
            ->find_one()
        ;

        $registro = [
            'descripcion' => $tabla->descripcion,
            'precio' => $tabla->precio,
        ];

        $form = $this->getForm($app['form.factory'], $registro);

        $form->handleRequest($request);
        if ($form->isValid()) {
            $data = $form->getData();

            $tabla->descripcion = $data['descripcion'];
            $tabla->precio = $data['precio'];
            $tabla->save();

            return $app->redirect('/');
        }

        return $app['twig']->render(
            'editarRegistro.twig',
            [
                'id' => $id,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @param Application $app
     * @param $id
     * @return Response
     */
    public function elimiar(Application $app, $id)
    {
        try {
            $tabla = $app['idiorm.db']
                ->for_table('Simple')
                ->where('id', $id)
                ->find_one()
            ;

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
        $app->get('/', 'TS\Controller\IndexController::index')->bind('index');
        $app->post('/', 'TS\Controller\IndexController::nuevo')
            ->bind('nuevo')
        ;

        $app->post('/{id}', 'TS\Controller\IndexController::actualizar')
            ->bind('actualizar')
        ;
        $app->get('/{id}', 'TS\Controller\IndexController::actualizar')
            ->bind('editar')
        ;

        $app->delete('/{id}', 'TS\Controller\IndexController::elimiar')
            ->bind('elimiar')
        ;

        return $controllers;
    }

    /**
     * @param $formFactory
     * @param array $registro
     * @return mixed
     */
    private function getForm($formFactory, $registro = [])
    {
        $formBuilder = $formFactory->createBuilder('form', $registro)
            ->add('descripcion')
            ->add('precio', 'money')
        ;

        return $formBuilder->getForm();
    }
}