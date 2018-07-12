<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

class ModifyCode extends SymCommand {

    const DS = DIRECTORY_SEPARATOR;
    private $input, $output, $argument, $options;

    public function configure()
    {
        $this->setName('modify-code')
             ->setDescription('Modifies current code to be compatible with latest framework')
             ->setHelp('This command allows you to upgrade legacy code to match new code...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to modify');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->argument = $this->input->getArgument('path');
        $this->ModifyCode();
    }

    private function ModifyCode()
    {
        $path = getcwd() .static::DS. ($in = $this->argument);

        if(!($path = realpath($path))){
            return  $this->output->writeLn("'$in' is not a valid path");
        }
        $this->output = $this->workOnPath($path, $this->output);
        $this->output->writeLn([
            "DONE!"
        ]);
        return $this->output;
    }

    private function workOnPath($path)
    {
        $DS = static::DS;
        if(is_dir($path)){
            $files = glob(trim($path, $DS).$DS."*");
            array_walk($files, [$this, 'workOnPath']);
        } else {
            $this->output->writeLn([$path]);
            $this->workOnFile($path, $this->output);
        }
        return $this->output;
    }

    private function workOnFile($file)
    {
        $data = file_get_contents($file);

        $search_n_replace = [
           '/static \$/msi' => [
               'forcing static variables to private', 'private static $'
           ],
           '/([a-z]+)\s+private\s+static\s+\$/msi' => [
               'forcing static variables to private', 'private static $'
           ],
           '/can(.+?)\(\$member\s+=\s+(null|NULL)\s*\)/msi' =>
           [
               'adding $context = [] to "can do" functions', 'can$1($member=NULL, $context = [])'
           ]
        ];

        foreach($search_n_replace as $search => $replacements){
            list($why, $replace) = $replacements;
            $this->output->writeLn([" -- $file: $why..."]);
            $data = preg_replace($search, $replace, $data);
        }
        $data = file_put_contents($file, $data);
        return $this->output;
    }


}
