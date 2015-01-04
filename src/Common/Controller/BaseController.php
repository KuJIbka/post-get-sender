<?php
namespace Common\Controller;

use Common\Service\DoctrineDebugProfiler;
use Common\Service\UrlHelper;
use Doctrine\ORM\EntityManager;
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
}
