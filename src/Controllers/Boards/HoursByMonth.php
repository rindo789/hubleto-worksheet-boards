<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers\Boards;

use HubletoApp\Community\Worksheets\Models\Activity;
use Illuminate\Database\Capsule\Manager as DB;

class HoursByMonth extends \HubletoMain\Controller
{
  public bool $hideDefaultDesktop = true;

  public function prepareView(): void
  {
    parent::prepareView();
    $sortedMonths = [
      1 => ["title" => "January", "value" => null],
      2 => ["title" => "February", "value" => null],
      3 => ["title" => "March", "value" => null],
      4 => ["title" => "April", "value" => null],
      5 => ["title" => "May", "value" => null],
      6 => ["title" => "June", "value" => null],
      7 => ["title" => "July", "value" => null],
      8 => ["title" => "August", "value" => null],
      9 => ["title" => "September", "value" => null],
      10 => ["title" => "October", "value" => null],
      11 => ["title" => "November", "value" => null],
      12 => ["title" => "December", "value" => null]
    ];

    $mTasks = new Activity($this->main);
    $usersWorktimes = $mTasks->record->prepareReadQuery()
      ->select(DB::raw("MONTH(date_worked) as month"), DB::raw("SUM(duration) as duration"))
      ->where(DB::raw("YEAR(date_worked)"), date("Y"))
      ->where("id_worker", $this->main->auth->getUserId())
      ->groupBy(DB::raw("MONTH(date_worked)"))
      ->get()
      ->toArray()
    ;

    foreach ($usersWorktimes as $workSummary) {
      $sortedMonths[$workSummary["month"]]["value"] = $workSummary["duration"];
    }

    $this->viewParams["sortedMonths"] = $sortedMonths;
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Boards/HoursByMonth.twig');
  }

}
