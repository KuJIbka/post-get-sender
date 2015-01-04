<?php
namespace Common\Controller;

use Common\Service\DB;
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

    /**
     * @param Application $app
     * @return DB
     */
    public function getDb(Application $app)
    {
        return DB::get($app);
    }
}
