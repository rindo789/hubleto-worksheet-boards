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
      '/^worksheets\/boards\/work-table\/?$/' => Controllers\Boards\WorkTable::class,
      '/^worksheets\/boards\/hours-by-month\/?$/' => Controllers\Boards\HoursByMonth::class,
    ]);

    $dashboardsApp = $this->main->apps->community('Dashboards');
    if ($dashboardsApp) {
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Quota for today'),
        'worksheets/boards/quota'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Work Table'),
        'worksheets/boards/work-table'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Hours by Month'),
        'worksheets/boards/hours-by-month'
      );
    }
  }

}
