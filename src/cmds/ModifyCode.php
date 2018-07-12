<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command as SymCommand;

class ModifyCode extends Command {

    const DS = DIRECTORY_SEPARATOR;

    public function configure()
    {
        $this->setName('modify-code')
             ->setDescription('Modifies current code to be compatible with latest framework')
             ->setHelp('This command allows you to upgrade legacy code to match new code...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to modify');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->argument = $this->input->getArgument('path');
        $this->path = realpath($this->argument);
        
        $this->ModifyCode($this->path);
    }

    private function ModifyCode($path)
    {

        if(!($path = realpath($path))){
            return  $this->output->writeLn("'$in' is not a valid path");
        }
        $this->output = $this->workOnPath($path, $this->output);
        $this->output->writeLn([
            "DONE!"
        ]);
        return $this->output;
    }

    protected function workOnFile($file)
    {
        if(strpos($file, '_originals_') !== false) return $this->output;

        $data = file_get_contents($file);

        $count = 0; $matches = [];

        $search_n_replace = [
            '/(\b(.*? )([a-z]+ )|static\s+)\$/si' => [
                'modifying static variables to private', 'private static $'
            ],
            '/can(.+?)\(\$member\s+=\s+(null|NULL)\s*\)/msi' =>
            [
               'adding $context = [] to "can*" functions', 'can$1($member=NULL, $context = [])'
            ],
           '/\barray\s*\((\s*(.*?)\s*)\)/msi' => [
               "ShortHand for arrays", '[$1]'
            ]
        ];

        foreach ($search_n_replace as $pattern => $replacer) {
            $count += preg_match_all($pattern, $data, $matches);
        }

        if($count === 0) return $this->output;
        $this->output->writeLn(["$file"]);
        foreach($search_n_replace as $search => $replacements){
            list($why, $replace) = $replacements;
            if($why !== NULL){
                $this->output->writeLn([" -- $why..."]);
            }            
            $data = preg_replace($search, $replace, $data);
        }
        $data = file_put_contents($file, $data);
        return $this->output;
    }


}
