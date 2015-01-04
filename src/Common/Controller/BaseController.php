<?php
namespace Common\Controller;

use Common\Service\MyCodeHelper;
use Common\Service\UrlHelper;
use Silex\Application;

class BaseController
{
    /**
     * @return UrlHelper
     */
    public function getUrlHelper()
    {
        return UrlHelper::get();
    }

    /**
     * @return MyCodeHelper
     */
    public function getMyCodeHelper()
    {
        return MyCodeHelper::get();
    }
}
