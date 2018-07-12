<?php

namespace SKYW\Upgrader\Apps;

use SKYW\Upgrader\Cmds\SplitCode;
use Symfony\Component\Console\Application as SymApp;

class Application extends SymApp
{

public function getAllCommands()
  {
    return [
        SplitCode::class
    ];
  }
}
