<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileController
{

    private ContainerInterface $container;
    private const FORM_ERROR = 'You have to upload a profile picture or a mobile number.';
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
        } else {
            $uploadedFile = $request->getUploadedFiles();
            $file = $uploadedFile['files'];
            $data = $request->getParsedBody();
            $user_id = $_SESSION['user_id'];
            $form_errors = [];
            $phone_error = [];
            $user = $this->container->get('user_repository')->getUserInformationById($user_id);
            //If a file and a mobile phone is provided
            if ($file->getError() === UPLOAD_ERR_OK || !empty($data['phone'])) {
                //Check if the provided mobile phone is valid
                if (!empty($data['phone'])) {
                    $phone_error = $this->container->get('validator')->validateProfile($data);
                    if (count($phone_error) == 0) {
                        $this->container->get('user_repository')->updatePhone($data['phone'], $user_id);
                    }
                }
                //Check if image is valid
                if ($file->getError() === UPLOAD_ERR_OK) {
                    $image_response = $this->container->get('image_handler')->manageImage($uploadedFile, $user_id);
                    //If it returns the filename
                    if (!is_array($image_response)) {
                        $this->container->get('user_repository')->insertImage($image_response, $user_id);
                        $info = self::CHANGES_OK;
                        $updatedUser = $this->container->get('user_repository')->getUserInformationById($user_id);
                        return $this->container->get('view')->render($response, 'profile.twig', [
                            'session' => $_SESSION['user_id'],
                            'profile_pic' => $image_response,
                            'info' => $info,
                            'user' => $updatedUser
                        ]);
                    } else {
                        //If it returns an array there are errors
                        return $this->container->get('view')->render($response, 'profile.twig', [
                            'session' => $_SESSION['user_id'],
                            'image_errors' => $image_response,
                            'profile_pic' => $user['profile_picture'],
                            'user' => $user
                        ]);
                    }
                }
                $info = self::CHANGES_OK;
                // Get updated information
                $updatedUser = $this->container->get('user_repository')->getUserInformationById($user_id);
                return $this->container->get('view')->render($response, 'profile.twig', [
                    'session' => $_SESSION['user_id'],
                    'profile_pic' => $user['profile_picture'],
                    'info' => $info,
                    'user' => $updatedUser
                ]);
            }
            //If it there's no picture uploaded 
            if ($file->getError() !== UPLOAD_ERR_OK || empty($data['phone'])) {
                $error['form_error'] = self::FORM_ERROR;
                return $this->container->get('view')->render($response, 'profile.twig', [
                    'session' => $_SESSION['user_id'],
                    'form_error' => $error['form_error'],
                    'user' => $user,
                    'phone_error' => $phone_error,
                    'profile_pic' => $user['profile_picture']
                ]);
            }
        }
        return $this->container->get('view')->render($response, 'profile.twig', []);
    }
}
