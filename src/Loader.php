<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard;

class Loader extends \Hubleto\Framework\App
{
  public bool $permittedForAllUsers = true;

  public function init(): void
  {
    parent::init();
    $this->main->router->httpGet([
      '/^worksheet-dashboards\/?$/' => Controllers\Home::class,
      '/^worksheets\/boards\/quota\/?$/' => Controllers\Boards\Quota::class,
    ]);
  }

  $dashboardsApp = $this->main->apps->community('Dashboards');
  if ($dashboardsApp) {
    $dashboardsApp->addBoard(
      $this,
      $this->translate('Quota'),
      'worksheets/boards/quota'
    );
  }
}
