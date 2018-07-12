<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command as SymCommand;

class UpgradeSS extends Command {

    const DS = DIRECTORY_SEPARATOR;
    private $namespace = 'SkyWest';

    public function configure()
    {
        $this->setName('ss')
             ->setDescription('Upgrade silverstripe code using silverstripe upgrader')
             ->setHelp('This command allows you to clean residue files...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to split')
             ->addArgument('namespace', InputArgument::OPTIONAL, 'The new namespace to add');
            //  ->addOptions([
            //      ['--w' => 'hishsihsi:R']
            //      ]);
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->argument = $this->input->getArgument('path');
        $this->namespace = $this->input->getArgument('namespace');
        $this->path = realpath($this->argument); 


        $this->UpgradeSS($this->path);
    }

    private function UpgradeSS($path)
    {
        if(!$this->path){
            return  $this->output->writeLn("'$this->argument' is not a valid path");
        }
        $this->output->writeLn([
            "==========================================",
            "              Silverstripe Update         ",
            "------------------------------------------"
        ]);

        $this->workOnFile($path);

        $this->output->writeLn(['Upgrading code to match SS latest framework...']);
        $this->output->writeLn(['--- '.exec("upgrade-code upgrade $this->path -w")]);
        $this->output->writeLn(['--- '.exec("upgrade-code inspect $this->path -w")]);

        $this->output->writeLn(['--- '.exec("composer update")]);

        $this->output->writeLn([
            "DONE!", "=========================================="
        ]);
        return $this->output;
    }

    protected function workOnFile($path)
    {
        $path .= ($UNK = static::DS."*");

        $this->output->writeLn(['Adding namespace to files...']);

        foreach (glob($path, GLOB_ONLYDIR) as $dir) {

           $name = str_ireplace($this->path, '', $dir);
           $space = ucfirst(trim($name,'/\\'));

           $namespace = $this->namespace."\\".$space;
           $arguments = "add-namespace $namespace $dir";

           $execute = exec("upgrade-code $arguments -w");           
           $this->output->writeLn(['--- ' . $execute]);
        }
        return $this->output;        
    }
}
