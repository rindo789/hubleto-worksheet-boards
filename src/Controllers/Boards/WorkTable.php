<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Worksheets\Models\Activity;

class WorkTable extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $quota = $this->main->urlParamAsFloat("quota") > 0 ? $this->main->urlParamAsFloat("quota") : 8;
    $dateStart = !empty($this->main->urlParamAsString("dateStart")) ? $this->main->urlParamAsString("dateStart") : date("Y-m-d", strtotime("-1 month"));
    $today = date("Y-m-d");
    $dateCounter = date("Y-m-d", strtotime($dateStart));

    $sortedWorkDays = [];
    $sortedWorkDays[$dateCounter]["hours"] = 0;

    while ($today != $dateCounter) {
      $dateCounter = date("Y-m-d", strtotime("+1 day", strtotime($dateCounter)));
      $sortedWorkDays[$dateCounter]["hours"] = 0;
    }

    $mTasks = new Activity($this->main);
    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select("duration", "date_worked")
      ->where("date_worked", ">=", $dateStart . " 00:00:00")
      ->where("id_worker", $this->main->auth->getUserId())
      ->get()
      ->toArray()
    ;

    foreach ($usersWorktimes as $workTime) {
      $date = date("Y-m-d", strtotime($workTime["date_worked"]));
      if (isset($sortedWorkDays[$date]["hours"])) {
        $sortedWorkDays[$date]["hours"] += (float) $workTime["duration"];
      } else {
        $sortedWorkDays[$date]["hours"] = (float) $workTime["duration"];
      }
    }

    foreach ($sortedWorkDays as $date => $workDay) {
      $sortedWorkDays[$date]["fullFilled"] = ($workDay["hours"] / $quota) * 100;
    }

    $this->viewParams["workDays"] = array_reverse($sortedWorkDays);
    $this->viewParams["quota"] = $quota;
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/WorkTable.twig');
  }

}
