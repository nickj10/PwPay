<?php

declare(strict_types=1);

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

final class SendMoneyController
{
    private ContainerInterface $container;
    private const SENT = 'SENT';

    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showSendMoneyForm(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        return $this->container->get('view')->render(
            $response,
            'send_money.twig',
            [
                'session' => $_SESSION['user_id']
            ]
        );
    }

    public function sendMoneyAction(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/sign-in')->withStatus(403);
        }
        $data = $request->getParsedBody();
        $userId = $_SESSION['user_id'];
        $errors = $this->container->get('validator')->validateTransaction($data);
        if (count($errors) == 0) {
            $email = filter_var($data['email'],FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            //Check if the user has enough balance
            $user = $this->container->get('user_repository')->getUserInformationById($userId);
            if ($user['balance'] >= $data['amount'] && $user['email'] != $email) {
                $result = $this->container->get('user_repository')->getUserByEmail($email);
                if ($result > 0 && $result['status'] == 'active') {
                    $destId = $result['user_id'];
                    $amount = $data['amount'];
                    $this->container->get('user_repository')->createRequest($userId,$destId,$amount,self::SENT);
                    $this->container->get('user_repository')->updateAccountBalance($destId,$amount,"add");
                    //update user new balance & create transaction
                    $this->container->get('user_repository')->updateAccountBalance($userId, $amount, "sub");
                    //$this->container->get('user_repository')->createTransaction($userId, $accountId, 'Send Money', $amount, 'send');
                    //redirect to dashboard with Flash message
                }
            }
        }
        return $this->container->get('view')->render(
            $response,
            'send_money.twig',
            [
                'session' => $_SESSION['user_id'],
                'errors' => $errors,
                'data' => $data
            ]
        );

    }


}