<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use Hubleto\App\Community\Settings\Models\User;
use Hubleto\App\Community\Worksheets\Models\Activity;

class Quota extends \Hubleto\Erp\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $mTasks = $this->getService(Activity::class);
    $mUser = $this->getService(User::class);

    $quota = $this->getRouter()->urlParamAsFloat("quota") > 0 ? $this->getRouter()->urlParamAsFloat("quota") : 8;
    $employeeEmail = $this->getRouter()->urlParamAsString("employeeEmail") != "" ? $this->getRouter()->urlParamAsString("employeeEmail") : null;

    $workedHours = 0.00;

    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select("worked_hours")
      ->where("date_worked", "=", date("Y-m-d"))
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
      $usersWorktimes->where("id_worker",$this->getAuthProvider()->getUserId());
    }

    $usersWorktimes = $usersWorktimes->get()->toArray();

    foreach ($usersWorktimes as $worktime) {
      $workedHours += (float) $worktime["worked_hours"];
    }

    $this->viewParams["quota"] = $quota;
    $this->viewParams["workedHours"] = $workedHours;

    $this->setView('@Hubleto:App:External:Rindo789:WorksheetDashboard/Boards/Quota.twig');
  }

}
