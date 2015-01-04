<?php

namespace Main\Controller;

use Common\Controller\BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Post\PostBodyInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function index(Application $app)
    {
        return $app['twig']->render('Main\View\index.twig', ['html' => '']);
    }

    public function getAnswer(Application $app, Request $req)
    {
        $startTime = microtime(true);
        $answer['OK'] = 0;

        $url = $req->get('url', '');
        $reqType = $req->get('method', 'GET');

        $data = $req->get('data', []);
        $cookies = $req->get('cookies', []);
        $headers = $req->get('headers', []);
        $redirectType = +$req->get('redirectType', 1);

        $baseLogin = $req->get('baseLogin', '');
        $basePass = $req->get('basePass', '');

        $options = [
            'exceptions' => false,
            'verify' => false
        ];
        if (!empty($cookies)) {
            $options['cookies'] = $cookies;
        }

        if ($redirectType === 1) {
            $options['allow_redirects'] = false;
        }

        if ($baseLogin) {
            $options['config']['curl'] = [
                CURLOPT_USERPWD  => $baseLogin.':'.$basePass
            ];
        }
        $client = new Client();
        $url = $this->getUrlHelper()->getFullUrl($url);

        $request = $client->createRequest($reqType, $url, $options);
        $request->setHeaders($headers);
        if ($reqType == 'POST') {
            /** @var PostBodyInterface $requestBody */
            $requestBody = $request->getBody();
            foreach ($data as $name => $val) {
                $requestBody->setField($name, $val);
            }
        }
        if ($reqType == 'GET') {
            $requestQuery = $request->getQuery();
            foreach ($data as $name => $val) {
                $requestQuery->add($name, $val);
            }
        }
        $response = $client->send($request);
        $res = $response->getBody();
        $content = $res->getContents();
        $setCookie = $response->getHeader('Set-Cookie');
        $cookies = [];
        if ($setCookie) {
            foreach (explode(', ', $setCookie) as $cookie) {
                $splitOptions = explode('; ', $cookie);
                $split = explode('=', $splitOptions[0]);
                if (isset($split[1])) {
                    $cookies[$split[0]] = urldecode($split[1]);
                }
            }
        }
        $nextPage = $response->getHeader('Location');
        $nextPage = $nextPage ? $this->getUrlHelper()->getFullUrl($nextPage, $url) : '';

        if (preg_match("/image/i", $response->getHeader('Content-Type'))) {
            $content = base64_encode($content);
            $answer['isImage'] = true;
        } else {
            $answer['isImage'] = false;
        }

        if ($res) {
            $answer['OK'] = 1;
            $answer['htmlEscaped'] = htmlentities($content);
            $answer['html'] = $content;
            $answer['headers'] = $response->getHeaders();
            $answer['reqHeaders'] = $request->getHeaders();
            $answer['setCookies'] = $cookies;
            $answer['nextPage'] = $nextPage;
            $answer['requestTime'] = microtime(true) - $startTime;
            $answer['requestUrl'] = $url;
        }
        return new JsonResponse($answer);
    }
}
