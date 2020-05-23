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
    private const CHANGES_OK = 'Your user account has been updated.';

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
                'profile_pic' => $user['profile_picture'],
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
            $user_id = $_SESSION['user_id'];
            $form_errors = [];
            $user = $this->container->get('user_repository')->getUserInformationById($user_id);
            //If a file and a mobile phone is provided
            if ($file->getError() === UPLOAD_ERR_OK && !empty($data['phone'])) {
                //Check if the provided mobile phone is valid
                $form_errors = $this->container->get('validator')->validateProfile($data);
                if (count($form_errors) == 0) {
                    $this->container->get('user_repository')->updatePhone($data['phone'], $user_id);
                    $image_response = $this->container->get('image_handler')->manageImage($uploadedFile, $user_id);
                    if (!is_array($image_response)) {
                        $this->container->get('user_repository')->insertImage($image_response, $user_id);
                        $info = self::CHANGES_OK;
                    }
                    $updatedUser = $this->container->get('user_repository')->getUserInformationById($user_id);
                    return $this->container->get('view')->render($response, 'profile.twig', [
                        'session' => $_SESSION['user_id'],
                        'profile_pic' => $image_response,
                        'image_errors' => $image_response,
                        'form_errors' => $form_errors,
                        'info' => $info,
                        'user' => $updatedUser
                    ]);
                }
                return $this->container->get('view')->render($response, 'profile.twig', [
                    'session' => $_SESSION['user_id'],
                    'form_errors' => $form_errors,
                    'user' => $user
                ]);
            }
            $error['form_error'] = self::FORM_ERROR;
            return $this->container->get('view')->render($response, 'profile.twig', [
                'session' => $_SESSION['user_id'],
                'form_error' => $error['form_error'],
                'user' => $user
            ]);
        }
    }
}
