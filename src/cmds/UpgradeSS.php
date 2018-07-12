<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command as SymCommand;

class UpgradeSS extends Command {

    const DS = DIRECTORY_SEPARATOR;

    public function configure()
    {
        $this->setName('ss')
             ->setDescription('Upgrade silverstripe code using silverstripe upgrader')
             ->setHelp('This command allows you to clean residue files...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to split');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->argument = $this->input->getArgument('path');
        $this->path = getcwd() .static::DS. $this->argument;  

        $this->UpgradeSS($this->path);
    }

    private function UpgradeSS($path)
    {
        if(!($path = realpath($path))){
            return  $this->output->writeLn("'$this->path' is not a valid path");
        }
        $this->output = $this->workOnPath($path, $this->output);
        $this->output->writeLn([
            "DONE!"
        ]);
        return $this->output;
    }

    protected function workOnFile($file)
    {        
        return $this->output;        
    }
}
