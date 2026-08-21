<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard;

use Hubleto\Erp\App;

class Loader extends App
{
  public bool $permittedForAllUsers = true;

  public function init(): void
  {
    parent::init();
    $this->router()->get([
      '/^worksheet-dashboards\/?$/' => Controllers\Home::class,
      '/^worksheets-dashboards\/boards\/quota\/?$/' => Controllers\Boards\Quota::class,
      '/^worksheets-dashboards\/boards\/work-table\/?$/' => Controllers\Boards\WorkTable::class,
      '/^worksheets-dashboards\/boards\/hours-by-month\/?$/' => Controllers\Boards\HoursByMonth::class,
      '/^worksheets-dashboards\/boards\/my-tasks\/?$/' => Controllers\Boards\MyTasks::class,
      '/^worksheets-dashboards\/boards\/pinned-tasks\/?$/' => Controllers\Boards\PinnedTask::class,
      '/^worksheets-dashboards\/boards\/latest-tasks\/?$/' => Controllers\Boards\LatestWorkedTasks::class,
    ]);
    /** @var \Hubleto\App\Community\Dashboards\Manager $dashboardsApp */
    $dashboardsApp = $this->getService(\Hubleto\App\Community\Dashboards\Manager::class);
    if ($dashboardsApp) {
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Quota for today'),
        'worksheets-dashboards/boards/quota'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Work Table'),
        'worksheets-dashboards/boards/work-table'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Hours by Month'),
        'worksheets-dashboards/boards/hours-by-month'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('My Tasks'),
        'worksheets-dashboards/boards/my-tasks'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Pinned tasks'),
        'worksheets-dashboards/boards/pinned-tasks'
      );
      $dashboardsApp->addBoard(
        $this,
        $this->translate('Latest worked on tasks'),
        'worksheets-dashboards/boards/latest-tasks'
      );
    }
  }

}
