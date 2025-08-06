<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Worksheets\Models\Activity;

class Quota extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $workedHours = 0.00;

    $mTasks = new Activity($this->main);
    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->where("datetime_created", date("Y-m-d"))
      ->where("id_worker", $this->main->auth->getUserId())
      ->get("duration")
      ->toArray()
    ;

    foreach ($usersWorktimes as $worktime) {
      $workedHours += (float) $worktime["duration"];
    }

    $this->viewParams["quota"] = 8;
    $this->viewParams["workedHours"] = $workedHours;

    $this->setView('@HubletoApp:Community:Worksheets/Boards/Quota.twig');
  }

}
