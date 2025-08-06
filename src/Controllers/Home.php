<?php

namespace HubletoApp\External\Rindo789\WorksheetDashboard\Controllers;

class Home extends \HubletoMain\Controller
{
  public function getBreadcrumbs(): array
  {
    return array_merge(parent::getBreadcrumbs(), [
      [ 'url' => 'worksheet-dashboards', 'content' => $this->translate('Hello World') ],
    ]);
  }

  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@HubletoApp:External:Rindo789:WorksheetDashboard/Home.twig');
  }

}
