<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard;

class Loader extends \Hubleto\Framework\App
{
  public bool $permittedForAllUsers = true;

  public function init(): void
  {
    parent::init();
    $this->main->router->httpGet([ '/^worksheet-dashboards\/?$/' => Controllers\Home::class ]);
  }

}
