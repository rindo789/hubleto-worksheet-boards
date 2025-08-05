<?php

namespace HubletoApp\External\WaiBlue\HelloWorld\Controllers;

class Home extends \HubletoMain\Controller
{
  public function getBreadcrumbs(): array
  {
    return array_merge(parent::getBreadcrumbs(), [
      [ 'url' => 'hello-world', 'content' => $this->translate('Hello World') ],
    ]);
  }

  public function prepareView(): void
  {
    parent::prepareView();
    $this->setView('@HubletoApp:External:WaiBlue:HelloWorld/Home.twig');
  }

}
