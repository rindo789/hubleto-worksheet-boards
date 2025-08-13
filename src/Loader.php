<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard;

class Loader extends \HubletoMain\App
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
      '/^worksheets\/boards\/my-tasks\/?$/' => Controllers\Boards\MyTasks::class,
    ]);

    $dashboardsApp = $this->main->load(\HubletoApp\Community\Dashboards\Manager::class);
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
      $dashboardsApp->addBoard(
        $this,
        $this->translate('My Tasks'),
        'worksheets/boards/my-tasks'
      );
    }
  }

}
