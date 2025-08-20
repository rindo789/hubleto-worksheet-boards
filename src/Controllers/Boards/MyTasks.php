<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Tasks\Models\Task;
use HubletoApp\Community\Worksheets\Models\Activity;
use HubletoApp\Community\Pipeline\Models\PipelineStep;
use HubletoApp\Community\Settings\Models\User;

class MyTasks extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {

    $mTasks = new Task($this->main);
    $mPipelineSteps = new PipelineStep($this->main);
    $mActivity = new Activity($this->main);

    $employeeEmail = $this->main->urlParamAsString("employeeEmail") != "" ? $this->main->urlParamAsString("employeeEmail") : null;

    $myTasks = $mTasks->record->prepareReadQuery()
      ->select(
        $mTasks->getFullTableSqlName().".id",
        $mTasks->getFullTableSqlName().".identifier",
        $mTasks->getFullTableSqlName().".title",
        $mPipelineSteps->getFullTableSqlName().".name as step",
        $mPipelineSteps->getFullTableSqlName().".color as color",
      )
      ->selectRAW("(
          select sum(ifnull(duration, 0))
          from ".$mActivity->getFullTableSqlName()."
          where id_task = tasks.id
        ) as worked"
      )
      ->where("is_closed",false)
      ->join(
        $mPipelineSteps->getFullTableSqlName(),
        $mPipelineSteps->getFullTableSqlName().".id",
        "=",
        $mTasks->getFullTableSqlName().".id_pipeline_step"
      )
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

      $myTasks
        ->where("id_developer",$employee["id"])
        ->orWhere("id_tester",$employee["id"])
      ;
      $this->viewParams["employee"] = $employee["first_name"] . " " . $employee["last_name"];
    } else {
      $myTasks
        ->where("id_developer",$this->main->auth->getUserId())
        ->orWhere("id_tester",$this->main->auth->getUserId())
      ;
    }

    $myTasks = $myTasks->get()->toArray();

    $this->viewParams["myTasks"] = $myTasks;
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/MyTasks.twig');
  }

}
