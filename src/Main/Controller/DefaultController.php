<?php

namespace Main\Controller;

use Common\Controller\BaseController;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
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
            foreach ($cookies as $cookName => $val) {
                $cookies[$cookName] = $this->getMyCodeHelper()->parseString($val);
            }
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
        if (!empty($headers)) {
            foreach ($headers as $headName => $val) {
                $headers[$headName] = $this->getMyCodeHelper()->parseString($val);
            }
        }
        $request->setHeaders($headers);
        if ($reqType == 'POST') {
            /** @var PostBodyInterface $requestBody */
            $requestBody = $request->getBody();
            foreach ($data as $name => $val) {
                $requestBody->setField($name, $this->getMyCodeHelper()->parseString($val));
            }
        }
        if ($reqType == 'GET') {
            $requestQuery = $request->getQuery();
            foreach ($data as $name => $val) {
                $requestQuery->add($name, $this->getMyCodeHelper()->parseString($val));
            }
        }
        $respHeaders = [];
        try {
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
            $respHeaders = $response->getHeaders();
        } catch (ConnectException $e) {
            $res = true;
            $content = 'Connection error';
            $nextPage = '';
        }

        if ($res) {
            $answer['OK'] = 1;
            $answer['htmlEscaped'] = htmlentities($content);
            $answer['html'] = $content;
            $answer['headers'] = $respHeaders;
            $answer['reqHeaders'] = $request->getHeaders();
            $answer['setCookies'] = $cookies;
            $answer['nextPage'] = $nextPage;
            $answer['requestTime'] = microtime(true) - $startTime;
            $answer['requestUrl'] = $url;
        }
        return new JsonResponse($answer);
    }

    public function loadPresets(Application $app)
    {
        $answer['OK'] = 1;
        $answer['presets'] = $this->getDb($app)->getPresets();
        return new JsonResponse($answer);
    }

    public function savePreset(Application $app, Request $req)
    {
        $presetName = $req->get('presetName', '');
        $url = $req->get('url', '');
        $reqType = $req->get('reqType', 'GET');

        $data = $req->get('data', []);
        $cookies = $req->get('cookies', []);
        $headers = $req->get('headers', []);
        $redirectType = +$req->get('redirectType', 1);

        $baseLogin = $req->get('baseLogin', '');
        $basePass = $req->get('basePass', '');

        $autoPickup = $req->get('autoPickup', false);
        $throwValues = $req->get('throwValues', false);
        $timerEnable = $req->get('timerEnable', false);
        $timerVal = $req->get('timerVal', '0');

        $allData = [
            'url' => $url,
            'reqType' => $reqType,
            'data' => $data,
            'cookies' => $cookies,
            'headers' => $headers,
            'redirectType' => $redirectType,
            'baseLogin' => $baseLogin,
            'basePass' => $basePass,
            'autoPickup' => $autoPickup,
            'throwValues' => $throwValues,
            'timerEnable' => $timerEnable,
            'timerVal' => $timerVal
        ];
        $answer['OK'] = 0;
        $answer['presetId'] = $this->getDb($app)->savePreset($presetName, $allData);
        if ($answer['presetId'] > 0) {
            $answer['OK'] = 1;
        }
        return new JsonResponse($answer);
    }

    public function deletePreset(Application $app, Request $req)
    {
        $answer['OK'] = 0;
        if ($this->getDb($app)->deletePreset($req->get('presetId', 0))) {
            $answer['OK'] = 1;
        }
        return new JsonResponse($answer);
    }
}
