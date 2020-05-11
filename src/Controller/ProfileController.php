<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileController
{

    private ContainerInterface $container;
    private const FORM_ERROR = 'You have to upload a profile picture and a mobile number.';

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfile(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        $user = $this->container->get('user_repository')->getUserInformationById($_SESSION['user_id']);
        return $this->container->get('view')->render(
            $response,
            'profile.twig',
            [
                'session' => $_SESSION['user_id'],
                'user' => $user
            ]
        );
    }
    public function profileAction(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        else {
            $uploadedFile = $request->getUploadedFiles();
            $file = $uploadedFile['files'];
            $data = $request->getParsedBody();
            $image_errors = [];
            $form_errors = [];
            $user = $this->container->get('user_repository')->getUserInformationById($_SESSION['user_id']);
            if ($file->getError() === UPLOAD_ERR_OK) {
                $image_errors = $this->container->get('image_handler')->validateImage($uploadedFile);
                $form_errors = $this->container->get('validator')->validateProfile($data);
                return $this->container->get('view')->render($response, 'profile.twig', [
                    'image_errors' => $image_errors,
                    'form_errors' => $form_errors,
                    'user' => $user
                ]);
            }
            $error['form_error'] = self::FORM_ERROR;
            return $this->container->get('view')->render($response, 'profile.twig', [
                'form_error' => $error['form_error'],
                'user' => $user
            ]);
        }
    }
}
