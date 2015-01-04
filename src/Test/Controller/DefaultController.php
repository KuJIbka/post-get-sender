<?php

namespace Test\Controller;

use Common\Controller\BaseController;
use Silex\Application;

class DefaultController extends BaseController
{
    public function index()
    {
        echo "PAGE1<br /><br />";
        $this->getAllData();
        setcookie('SETTING_FROM_PHP', 'there is val');
        setcookie('SETTING_FROM_PHP_2', 'какой-то русский текст = 20');
        return '';
    }

    public function index2(Application $app)
    {
        echo "PAGE2<br /><br />";
        $this->getAllData();

        return $app->redirect($app['url_generator']->generate('testHeaders1'));
    }

    public function index3(Application $app)
    {
        session_start();

        if (!isset($_SESSION['counter'])) {
            $_SESSION['counter'] = 0;
        }

        echo "PHPSESSID: ".session_id();
        echo "<br />";
        echo "COUNTER: ".(++$_SESSION['counter']);

        echo "<br /><br />";
        $this->getAllData();
        return '';
    }

    private function getAllData()
    {
        $headers = getallheaders();
        echo "Headers: <br />";
        foreach ($headers as $headName => $headValue) {
            echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$headName.' = '.$headValue.'<br />';
        }
        echo '<br />';
        echo 'GET: <br />';
        if (isset($_GET)) {
            foreach ($_GET as $k => $v) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$k.' = '.$v.'<br />';
            }
        }
        echo '<br />';
        echo 'POST: <br />';
        if (isset($_POST)) {
            foreach ($_POST as $k => $v) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$k.' = '.$v.'<br />';
            }
        }
        echo '<br />';

        echo 'COOKIES: <br />';
        if (isset($_COOKIE)) {
            foreach ($_COOKIE as $k => $v) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;'.$k.' = '.$v.'<br />';
            }
        }
        echo '<br />';
    }
}
