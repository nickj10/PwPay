<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileController
{

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }
    public function profileAction(Request $request, Response $response): Response
    {
        $uploadedFile = $request->getUploadedFiles();
        $data = $request->getParsedBody();
        $image_errors = [];
        $form_errors = [];
        //TODO: controlar si no ha puesto ninguna foto
        $image_errors = $this->container->get('image_handler')->validateImage($uploadedFile);
        $form_errors = $this->container->get('validator')->validateProfile($data);

        return $this->container->get('view')->render($response, 'profile.twig', [
            'image_errors' => $image_errors,
            'form_errors' => $form_errors
        ]);
    }
}
