<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard;

class Loader extends \Hubleto\Framework\App
{
  public bool $permittedForAllUsers = true;

  public function init(): void
  {
    parent::init();
    $this->router()->get([
      '/^worksheet-dashboards\/?$/' => Controllers\Home::class,
      '/^worksheets\/boards\/quota\/?$/' => Controllers\Boards\Quota::class,
      '/^worksheets\/boards\/work-table\/?$/' => Controllers\Boards\WorkTable::class,
      '/^worksheets\/boards\/hours-by-month\/?$/' => Controllers\Boards\HoursByMonth::class,
      '/^worksheets\/boards\/my-tasks\/?$/' => Controllers\Boards\MyTasks::class,
    ]);
    /** @var \Hubleto\App\Community\Dashboards\Manager $dashboardsApp */
    $dashboardsApp = $this->getService(\Hubleto\App\Community\Dashboards\Manager::class);
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
