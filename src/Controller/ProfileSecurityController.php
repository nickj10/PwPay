<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class ProfileSecurityController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showProfileSecurity(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'profile_security.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }

    public function profileSecurityAction (Request $request, Response $response): Response 
    {
        $data = $request->getParsedBody();
        if (!empty($data['save_button'])) {
            $errors = [];
            $errors = $this->container->get('validator')->validateSecurityPassword($data);
            if (count($errors) == 0) {
                $password = filter_var($data['old_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $new_password = filter_var($data['new_password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $user = $this->container->get('user_repository')->getUserInformationById($_SESSION['user_id']);
                //If users current password doesn't match
                if (md5($password) != $user['password']) {
                    $errors['old_pass'] = "Your old password is wrong.";
                }
                else {
                    //Check if the new password is the same
                    if (md5($new_password) == $user['password']) {
                        $errors['same_pass'] = "Your new password can't be same as the old one.";
                    }
                    //Update user password
                    else {
                        $this->container->get('user_repository')->updatePassword(md5($new_password), $_SESSION['user_id']);
                        $info['success'] = "Your password has been udpated.";
                        return $this->container->get('view')->render($response, 
                        'profile_security.twig', 
                        [
                            'session' => $_SESSION['user_id'],
                            'info' => $info,
                            'data' => $data
                        ]);
                    }
                }
            }            
            return $this->container->get('view')->render($response, 
                'profile_security.twig', 
                [
                    'session' => $_SESSION['user_id'],
                    'errors' => $errors,
                    'data' => $data
                ]);
        }
        if (!empty($data['cancel_button'])) {
            return $response->withHeader('Location', '/profile')->withStatus(403);
        }
    }
}
