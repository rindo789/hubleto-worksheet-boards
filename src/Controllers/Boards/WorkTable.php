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
    $range = $this->main->urlParamAsInteger("range") > 0 ? $this->main->urlParamAsInteger("range") : 30;
    $today = date("Y-m-d");
    $dateStart = date("Y-m-d", strtotime("-".$range." days", strtotime($today)));

    //inicialize today
    $dayStr = date("D", strtotime($today));
    $year = date("Y", strtotime($today));
    $month = date("F", strtotime($today));
    $dateCounter = date("Y-m-d", strtotime($today));

    $sortedWorkDays[$year][$month][$dateCounter]["hours"] = 0;
    if ($dayStr == "Sat" || $dayStr == "Sun") $sortedWorkDays[$year][$month][$dateCounter]["weekend"] = true;

    while ($dateStart != $dateCounter) {
      $dayStr = date("D", strtotime("-1 day", strtotime($dateCounter)));
      $year = date("Y", strtotime("-1 day", strtotime($dateCounter)));
      $month = date("F", strtotime("-1 day", strtotime($dateCounter)));
      $dateCounter = date("Y-m-d", strtotime("-1 day", strtotime($dateCounter)));

      if ($dayStr == "Sat" || $dayStr == "Sun") $sortedWorkDays[$year][$month][$dateCounter]["weekend"] = true;
      $sortedWorkDays[$year][$month][$dateCounter]["hours"] = 0;
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
      $year = date("Y", strtotime($workTime["date_worked"]));
      $month = date("F", strtotime($workTime["date_worked"]));
      $date = date("Y-m-d", strtotime($workTime["date_worked"]));

      if (isset($sortedWorkDays[$year][$month][$date]["hours"])) {
        $sortedWorkDays[$year][$month][$date]["hours"] += (float) $workTime["duration"];
      } else {
        $sortedWorkDays[$year][$month][$date]["hours"] = (float) $workTime["duration"];
      }
    }

    $this->viewParams["worksheet"] = $sortedWorkDays;
    $this->viewParams["quota"] = $quota;
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/WorkTable.twig');
  }

}
