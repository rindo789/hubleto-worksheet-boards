<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Worksheets\Models\Activity;

class Quota extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();
    $quota = $this->main->urlParamAsFloat("quota") > 0 ? $this->main->urlParamAsFloat("quota") : 8;

    $workedHours = 0.00;

    $mTasks = new Activity($this->main);
    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select("duration")
      ->where("date_worked", "=", date("Y-m-d"))
      ->where("id_worker", $this->main->auth->getUserId())
      ->get()
      ->toArray()
    ;

    foreach ($usersWorktimes as $worktime) {
      $workedHours += (float) $worktime["duration"];
    }

    $this->viewParams["quota"] = $quota;
    $this->viewParams["workedHours"] = $workedHours;

    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/Quota.twig');
  }

}
