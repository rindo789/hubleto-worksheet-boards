<?php

namespace HubletoApp\External\WaiBlue\HelloWorld;

class Loader extends \Hubleto\Framework\App
{
  public bool $permittedForAllUsers = true;

  public function init(): void
  {
    parent::init();
    $this->main->router->httpGet([ '/^hello-world\/?$/' => Controllers\Home::class ]);
  }

}
