<?php

namespace SKYW\Upgrader\Apps;

use SKYW\Upgrader\Cmds\ModifyCode;
use SKYW\Upgrader\Cmds\SplitClasses;
use Symfony\Component\Console\Application as SymApp;

class Application extends SymApp
{

public function getAllCommands()
  {
    return [
        ModifyCode::class,
        SplitClasses::class
    ];
  }
}
