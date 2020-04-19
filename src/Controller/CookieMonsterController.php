<?php

namespace SallePW\SlimApp\Controller;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;

final class CookieMonsterController
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function showAdvice(Request $request, Response $response): Response
    {
        $cookie = FigRequestCookies::get($request, 'cookies_advice', false);

        $isAdviced = (bool)$cookie->getValue();

        if (!$isAdviced) {
            $response = FigResponseCookies::set(
                $response,
                $this->generateAdviceCookie()
            );
        }

        return $this->container->get('view')->render(
            $response,
            'cookies.twig',
            [
                'isAdviced' => $isAdviced,
            ]
        );
    }

    private function generateAdviceCookie(): SetCookie
    {
        return SetCookie::create('cookies_advice')
            ->withValue(1)
            ->withDomain('localhost')
            ->withHttpOnly(false)
            ->withPath('/cookies');
    }
}
