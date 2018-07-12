<?php

namespace SKYW\Upgrader\Cmds;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

abstract class Command extends SymCommand
{
    
    protected $input, $output, $argument, $path, $options = [];

    abstract protected function workOnFile($path);

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    protected function workOnPath($path)
    {
        if (realpath(getcwd()) == $path) {
            die('WHAAAAAAT!');
        }
        $DS = static::DS;
        if(is_dir($path)){
            $files = glob(trim($path, $DS).$DS."*");
            array_walk($files, [$this, 'workOnPath']);
        } else {
            $this->output->write("***********");
            $this->workOnFile($path, $this->output);
            $dir  = pathinfo($path, PATHINFO_DIRNAME);
        }
        return $this->output;
    }

    

    public function addOptions($options)
    {
        // $this->options = $options;
        foreach ($options as $option) {
            list($name, $short, $required) =array_pad($option, 3, FALSE);
            $OPT_OR_REQ = $required? InputOption::VALUE_REQUIRED: InputOption::VALUE_OPTIONAL;
            $this->options[] = new InputOption($name, $short, $OPT_OR_REQ);
        }
        /*array(
                    new InputOption('foo', 'f'),
                    new InputOption('bar', 'b', InputOption::VALUE_REQUIRED),
                    new InputOption('cat', 'c', InputOption::VALUE_OPTIONAL),
                    */
        return $this->setDefinition(
            new InputDefinition($this->options)
        );
    }
}


