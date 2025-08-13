<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Tasks\Models\Task;
use HubletoApp\Community\Worksheets\Models\Activity;
use HubletoApp\Community\Pipeline\Models\PipelineStep;
class MyTasks extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {

    $mTasks = new Task($this->main);
    $mPipelineSteps = new PipelineStep($this->main);
    $mActivity = new Activity($this->main);

    $myTasks = $mTasks->record->prepareReadQuery()
      ->select(
        $mTasks->getFullTableSqlName().".id",
        $mTasks->getFullTableSqlName().".identifier",
        $mTasks->getFullTableSqlName().".title",
        $mPipelineSteps->getFullTableSqlName().".name as step",
        $mPipelineSteps->getFullTableSqlName().".color as color",
      )
      ->selectRAW("(select sum(ifnull(duration, 0)) from ".$mActivity->getFullTableSqlName()." where id_task = tasks.id) as worked")
      ->where("id_developer",$this->main->auth->getUserId())
      ->where("id_developer",$this->main->auth->getUserId())
      ->where("is_closed",false)
      ->join(
        $mPipelineSteps->getFullTableSqlName(),
        $mPipelineSteps->getFullTableSqlName().".id",
        "=",
        $mTasks->getFullTableSqlName().".id_pipeline_step"
      )
      ->get()
      ->toArray()
    ;

    $this->viewParams["myTasks"] = $myTasks;
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/MyTasks.twig');
  }

}
