<?php
namespace notigo\Controllers;

use Silex\Application;
use PointOut\Form\CastingType;
use PointOut\Twig\Extensions\VarsExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

class ApiController
{

    public function indexAction(Request $request, Application $app)
    {

        return new Response("test");

    }

}
