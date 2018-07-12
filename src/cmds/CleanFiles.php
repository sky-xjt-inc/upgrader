<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command as SymCommand;

class CleanFiles extends Command {

    const DS = DIRECTORY_SEPARATOR;

    public function configure()
    {
        $this->setName('clean-files')
             ->setDescription('Clean all original and temporary files')
             ->setHelp('This command allows you to clean residue files...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to split');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->argument = $this->input->getArgument('path');
        $this->path = realpath($this->argument); 

        $this->CleanFiles($this->path);
        $this->deleteOriginals($this->path);
    }

    private function CleanFiles($path)
    {
        if(!$this->path){
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
        if(stripos($file, '_originals_') !== false){
            $this->output->writeLn(["Deleting $file "]);
            unlink($file);            
        } elseif(basename(dirname($file)) == 'code'){
            $this->output->writeLn(["Deleting $file "]);
            unlink($file);            
        } else {

        }
        return $this->output;        
    }

    protected function deleteOriginals($path)
    {
        $path = realpath($path).($UNK = static::DS."*");

        foreach (glob($path, GLOB_ONLYDIR) as $dir) {

           $name = basename($dir);

           if($name != '_originals_'){
               $this->output = $this->deleteOriginals($dir);

           } else {
               $this->output->writeLn("Deleting $dir");
               rmdir($dir);
           }         
        }
        return $this->output;
    }
}
