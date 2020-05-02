<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use SallePW\SlimApp\model\User;
use SallePW\SlimApp\model\Mailer;


final class RegisterController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showRegisterFormAction(Request $request, Response $response): Response
    {
        return $this->container->get('view')->render($response, 'register.twig', []);
    }
    public function registerAction(Request $request, Response $response): Response
    {        
        $data = $request->getParsedBody();
        $errors = [];
        $errors = $this->container->get('validator')->validateRegister($data);
        try {
            //Check if user data already exists
            if ($this->container->get('user_repository')->isEmailTaken($data['email'])) {
                $errors['emailTaken'] = 'This email is already taken';
            }
            if (count($errors) == 0) {
                //$birthdate = date_create($data['birthday']);
                $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $password = filter_var($data['password'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
                $user = new User ($email, $password, $data['birthday'], intval($data['phone']));
                $this->container->get('user_repository')->save($user);
                //We have to retreive its id for the activation link
                $user = $this->container->get('user_repository')->getUserByEmail($email);
                $uuid = $this->container->get('user_repository')->generateUuid($user['user_id']);
                $emailSent = $this->container->get('mailer')->sendEmail($user['user_id'], $user['email'], $uuid);
                if ($emailSent) {
                    $errors['activation_link'] = "Great! Don't forget to check your email to validate your account.";
                }
                else {
                    $errors['activation_link'] = "Something wrong happened. Please try again.";
                }
            }
            
            return $this->container->get('view')->render (
                $response,
                'register.twig',
                [
                    'form_errors' => $errors,
                    'data' => $data
                ]
            );
        } catch (Exception $e) {
            $response->getBody()->write('Unexpected error: ' . $e->getMessage());
            return $response->withStatus(500);
        }
        return $response->withStatus(201);
    }
}