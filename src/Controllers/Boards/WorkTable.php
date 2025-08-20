<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Settings\Models\User;
use HubletoApp\Community\Worksheets\Models\Activity;

class WorkTable extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $quota = $this->main->urlParamAsFloat("quota") > 0 ? $this->main->urlParamAsFloat("quota") : 8;
    $employeeEmail = $this->main->urlParamAsString("employeeEmail") != "" ? $this->main->urlParamAsString("employeeEmail") : null;
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
    ;

    if (!empty($employeeEmail) && (
      $this->main->auth->userHasRole(User::TYPE_ADMINISTRATOR) ||
      $this->main->auth->userHasRole(User::TYPE_CHIEF_OFFICER) ||
      $this->main->auth->userHasRole(User::TYPE_MANAGER)
    )) {
      $mUser = new User($this->main);
      $employee = $mUser->record->prepareReadQuery()
        ->select($mUser->getFullTableSqlName().".id", "first_name", "last_name")
        ->where("email", $employeeEmail)
        ->first()
        ->toArray()
      ;

      $usersWorktimes->where("id_worker", $employee["id"]);
      $this->viewParams["employee"] = $employee["first_name"] . " " . $employee["last_name"];
    } else {
      $usersWorktimes->where("id_worker", $this->main->auth->getUserId());
    }

    $usersWorktimes = $usersWorktimes->get()->toArray();

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
