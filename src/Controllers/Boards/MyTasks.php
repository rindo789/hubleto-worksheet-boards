<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use Hubleto\App\Community\Tasks\Models\Task;
use Hubleto\App\Community\Worksheets\Models\Activity;
use Hubleto\App\Community\Pipeline\Models\PipelineStep;
use Hubleto\App\Community\Settings\Models\User;

class MyTasks extends \Hubleto\Erp\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $mTasks = $this->getService(Task::class);
    $mPipelineSteps = $this->getService(PipelineStep::class);
    $mActivity = $this->getService(Activity::class);
    $mUser = $this->getService(User::class);

    $employeeEmail = $this->getRouter()->urlParamAsString("employeeEmail") != "" ? $this->getRouter()->urlParamAsString("employeeEmail") : null;

    $myTasks = $mTasks->record->prepareReadQuery()
      ->select(
        $mTasks->getFullTableSqlName() . ".id",
        $mTasks->getFullTableSqlName() . ".identifier",
        $mTasks->getFullTableSqlName() . ".title",
        $mPipelineSteps->getFullTableSqlName() . ".name as step",
        $mPipelineSteps->getFullTableSqlName() . ".color as color",
      )
      ->selectRAW(
        "(
          select sum(ifnull(worked_hours, 0))
          from `". $mActivity->getFullTableSqlName(). "`
          where id_task = tasks.id
        ) as worked"
      )
      ->where("is_closed", false)
      ->join(
        $mPipelineSteps->getFullTableSqlName(),
        $mPipelineSteps->getFullTableSqlName() . ".id",
        "=",
        $mTasks->getFullTableSqlName() . ".id_pipeline_step"
      );

    if (!empty($employeeEmail) && (
      $this->getAuthProvider()->userHasRole(User::TYPE_ADMINISTRATOR) ||
      $this->getAuthProvider()->userHasRole(User::TYPE_CHIEF_OFFICER) ||
      $this->getAuthProvider()->userHasRole(User::TYPE_MANAGER)
    )) {
      $employee = $mUser->record->prepareReadQuery()
        ->select($mUser->getFullTableSqlName() . ".id", "first_name", "last_name")
        ->where("email", $employeeEmail)
        ->first()
        ?->toArray();

      if ($employee) {
        $myTasks->whereRaw(
          "(
            id_developer = ?
            OR " . "id_tester = ?
          )",
          [$employee["id"], $employee["id"]]
        );
        $this->viewParams["employee"] = $employee["first_name"] . " " . $employee["last_name"];
      } else {
        $this->viewParams["employee"] = "N/A";
        $myTasks->whereRaw(
          "(
            id_developer = ?
            OR " . "id_tester = ?
          )",
          [$this->getAuthProvider()->getUserId(), $this->getAuthProvider()->getUserId()]
        );
      }
    } else {
      $myTasks->whereRaw(
        "(
          id_developer = ?
          OR " . "id_tester = ?
        )",
        [$this->getAuthProvider()->getUserId(), $this->getAuthProvider()->getUserId()]
      );
    }

    $myTasks = $myTasks->get()->toArray();

    $this->viewParams["myTasks"] = $myTasks;
    $this->setView('@Hubleto:App:External:Rindo789:WorksheetDashboard/Boards/MyTasks.twig');
  }
}
