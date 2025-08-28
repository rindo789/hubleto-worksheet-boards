<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use Hubleto\App\Community\Settings\Models\User;
use Hubleto\App\Community\Worksheets\Models\Activity;

class WorkTable extends \Hubleto\Erp\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $mTasks = $this->getService(Activity::class);
    $mUser = $this->getService(User::class);

    $quota = $this->getRouter()->urlParamAsFloat("quota") > 0 ? $this->getRouter()->urlParamAsFloat("quota") : 8;
    $employeeEmail = $this->getRouter()->urlParamAsString("employeeEmail") != "" ? $this->getRouter()->urlParamAsString("employeeEmail") : null;
    $range = $this->getRouter()->urlParamAsInteger("range") > 0 ? $this->getRouter()->urlParamAsInteger("range") : 30;
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

    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select("duration", "date_worked")
      ->where("date_worked", ">=", $dateStart . " 00:00:00")
    ;

    if (!empty($employeeEmail) && (
      $this->getAuthProvider()->userHasRole(User::TYPE_ADMINISTRATOR) ||
      $this->getAuthProvider()->userHasRole(User::TYPE_CHIEF_OFFICER) ||
      $this->getAuthProvider()->userHasRole(User::TYPE_MANAGER)
    )) {
      $employee = $mUser->record->prepareReadQuery()
        ->select($mUser->getFullTableSqlName().".id", "first_name", "last_name")
        ->where("email", $employeeEmail)
        ->first()
        ?->toArray()
      ;

      if ($employee) {
        $usersWorktimes->where("id_worker",$employee["id"]);
        $this->viewParams["employee"] = $employee["first_name"] . " " . $employee["last_name"];
      } else {
        $this->viewParams["employee"] = "N/A";
        $usersWorktimes->where("id_worker", $this->getAuthProvider()->getUserId());
      }
    } else {
      $usersWorktimes->where("id_worker", $this->getAuthProvider()->getUserId());
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
    $this->setView('@Hubleto:App:External:Rindo789:WorksheetDashboard/Boards/WorkTable.twig');
  }

}
