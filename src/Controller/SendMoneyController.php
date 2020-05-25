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
    private const NO_MONEY = "You don't have enough money. Current balance: %s €.";
    private const SAME_EMAIL = "You can't send money to your own self.";
    private const INACTIVE_EMAIL = "You're trying to send money to an inactive account.";
    private const NO_ACCOUNT = "The email you introduced doesn't have an account.";
    private const SEND_OK = "You have successfully sent money to %s.";

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
            $email = filter_var($data['email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            //Check if the user has enough balance
            $user = $this->container->get('user_repository')->getUserInformationById($userId);
            if ($user['balance'] >= $data['amount'] && $user['email'] != $email) {
                $result = $this->container->get('user_repository')->getUserByEmail($email);
                if ($result > 0 && $result['status'] == 'active') {
                    $destId = $result['user_id'];
                    $amount = $data['amount'];
                    $this->container->get('user_repository')->createRequest($userId, $destId, $amount, self::SENT);
                    $this->container->get('user_repository')->updateAccountBalance($destId, $amount, "add");
                    //update user new balance & create transaction
                    $this->container->get('user_repository')->updateAccountBalance($userId, $amount, "sub");
                    $this->container->get('user_repository')->createTransaction($userId, 'Send Money', $amount, 'send');
                    $this->container->get('user_repository')->createTransaction($destId, 'Receive Money', $amount, 'receive');
                    //redirect to dashboard with Flash message
                    $this->container->get('flash')->addMessage('notifications', sprintf(self::SEND_OK, $result['email']));
                    return $response->withHeader('Location', '/account/summary')->withStatus(302);
                }
                if ($result != null && $result['status'] != 'active') {
                    $errors['inactive_email'] = self::INACTIVE_EMAIL;
                }
                if ($result == null) {
                    $errors['no_account'] = self::NO_ACCOUNT;
                }
            } else {
                $errors['no_money'] = sprintf(self::NO_MONEY, $user['balance']);
                if ($user['email'] == $email) {
                    $errors['same_email'] = self::SAME_EMAIL;
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
