<?php

namespace SKYW\Upgrader\Apps;

use SKYW\Upgrader\Cmds\CleanFiles;
use SKYW\Upgrader\Cmds\ModifyCode;
use SKYW\Upgrader\Cmds\SplitClasses;
use Symfony\Component\Console\Application as SymApp;

class Application extends SymApp
{

public function getAllCommands()
  {
    return [
        CleanFiles::class,
        ModifyCode::class,
        SplitClasses::class
    ];
  }
}
