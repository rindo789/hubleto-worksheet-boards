<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Settings\Models\User;
use HubletoApp\Community\Worksheets\Models\Activity;

class Quota extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();
    $quota = $this->main->urlParamAsFloat("quota") > 0 ? $this->main->urlParamAsFloat("quota") : 8;
    $employeeEmail = $this->main->urlParamAsString("employeeEmail") != "" ? $this->main->urlParamAsString("employeeEmail") : null;

    $workedHours = 0.00;

    $mTasks = new Activity($this->main);
    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select("duration")
      ->where("date_worked", "=", date("Y-m-d"))
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

      $usersWorktimes->where("id_worker",$employee["id"]);
      $this->viewParams["employee"] = $employee["first_name"] . " " . $employee["last_name"];
    } else {
      $usersWorktimes->where("id_worker",$this->main->auth->getUserId());
    }

    $usersWorktimes = $usersWorktimes->get()->toArray();

    foreach ($usersWorktimes as $worktime) {
      $workedHours += (float) $worktime["duration"];
    }


    $this->viewParams["quota"] = $quota;
    $this->viewParams["workedHours"] = $workedHours;

    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/Quota.twig');
  }

}
