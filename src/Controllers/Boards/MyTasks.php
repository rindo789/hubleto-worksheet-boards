<?php

namespace Hubleto\App\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use Hubleto\App\Community\Tasks\Models\Task;
use Hubleto\App\Community\Worksheets\Models\Activity;
use Hubleto\App\Community\Workflow\Models\WorkflowStep;
use Hubleto\App\Community\Settings\Models\User;

class MyTasks extends \Hubleto\Erp\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();

    $authProvider = $this->authProvider();

    $mTasks = $this->getModel(Task::class);
    $mWorkflowSteps = $this->getModel(WorkflowStep::class);
    $mActivity = $this->getModel(Activity::class);
    $mUser = $this->getModel(User::class);

    $employeeEmail = $this->router()->urlParamAsString("employeeEmail") != "" ? $this->router()->urlParamAsString("employeeEmail") : null;

    $myTasks = $mTasks->record->prepareReadQuery()
      ->select(
        $mTasks->getFullTableSqlName() . ".id",
        $mTasks->getFullTableSqlName() . ".identifier",
        $mTasks->getFullTableSqlName() . ".title",
        $mWorkflowSteps->getFullTableSqlName() . ".name as step",
        $mWorkflowSteps->getFullTableSqlName() . ".color as color",
      )
      ->selectRAW("
        (
          select sum(ifnull(worked_hours, 0))
          from `". $mActivity->getFullTableSqlName(). "`
          where id_task = tasks.id
        ) as worked,
        (
          select
            concat(
              ifnull(group_concat(concat('D:', deals.identifier) separator ', '), ''),
              ifnull(group_concat(concat('P:', projects.identifier) separator ', '), '')
            )
          from tasks t2
          left join deals_tasks on deals_tasks.id_task = t2.id
          left join projects_tasks on projects_tasks.id_task = t2.id
          left join deals on deals.id = deals_tasks.id_deal
          left join projects on projects.id = projects_tasks.id_project
          where
            t2.id = tasks.id
            and (
              deals_tasks.id_task = tasks.id
              or projects_tasks.id_task = tasks.id
            )
        ) as related_to
      ")
      ->where("is_closed", false)
      ->join(
        $mWorkflowSteps->getFullTableSqlName(),
        $mWorkflowSteps->getFullTableSqlName() . ".id",
        "=",
        $mTasks->getFullTableSqlName() . ".id_workflow_step"
      );

    if (!empty($employeeEmail) && (
      $authProvider->userHasRole(User::TYPE_ADMINISTRATOR) ||
      $authProvider->userHasRole(User::TYPE_CHIEF_OFFICER) ||
      $authProvider->userHasRole(User::TYPE_MANAGER)
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
          [$authProvider->getUserId(), $authProvider->getUserId()]
        );
      }
    } else {
      $myTasks->whereRaw(
        "(
          id_developer = ?
          OR " . "id_tester = ?
        )",
        [$authProvider->getUserId(), $authProvider->getUserId()]
      );
    }

    $myTasks = $myTasks->get()->toArray();

    $this->viewParams["myTasks"] = $myTasks;
    $this->setView('@Hubleto:App:External:Rindo789:WorksheetDashboard/Boards/MyTasks.twig');
  }
}
