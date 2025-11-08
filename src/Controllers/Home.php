<?php

namespace Hubleto\App\External\WorksheetDashboard\Controllers;

class Home extends \Hubleto\Erp\Controller
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
    $this->setView('@Hubleto:App:External:WorksheetDashboard/Home.twig');
  }

}
