<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command as SymCommand;

class SplitClasses extends SymCommand {

    const DS = DIRECTORY_SEPARATOR;
    private $input, $output, $argument, $options;

    public function configure()
    {
        $this->setName('split-code')
             ->setDescription('Split multi-classes files into their own class files')
             ->setHelp('This command allows you to split different classes into their own classes...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to split');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
        $this->argument = $this->input->getArgument('path');
        $this->splitCode();
    }

    private function splitCode()
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
            unlink($path);
        }
        return $this->output;
    }

    private function workOnFile($file)
    {
        $data = file_get_contents($file);

        $class_bodies = '/({(?>[^{}]++|(?R))*})/msi';
        $declarations = '/(class\s+(.+?)\s+extends\s+(?:.+?))\s*{/msi';

        $matchClassBody = [];
        preg_match_all($class_bodies, $data, $matchClassBody);

        $matchClassDecl = [];
        preg_match_all($declarations, $data, $matchClassDecl);


        foreach($matchClassDecl[1] as $k => $class){
            $class = "<?php\n\n".$class."\n";
            $class .= $matchClassBody[1][$k];
            $name = $matchClassDecl[2][$k];
            $this->createNewFiles($name, $class, $file);
        }
        return $this->output;
    }

    public function createNewFiles($name, $content, $file)
    {
        $this->output->writeLn(["Creating $name.php ..."]);

        $dirname = pathinfo($file, PATHINFO_DIRNAME);

        if(stripos($name, 'Controller') !== false){
            $dir = $dirname.static::DS.'controllers';
            if(!is_dir($dir)) mkdir($dir);
            $path = $dir.static::DS.$name.'.php';

        } elseif(stripos($name, 'Page') !== false){
            $dir = $dirname.static::DS.'pagetypes';
            if(!is_dir($dir)) mkdir($dir);
            $path = $dir.static::DS.$name.'.php';

        } else {
            $dir = $dirname.static::DS.'datamodels';
            if(!is_dir($dir)) mkdir($dir);
            $path = $dir.static::DS.$name.'.php';
        }
        $this->storeNewFiles($path, $content, $file);
    }

    public function storeNewFiles($path, $content, $file)
    {
        $this->output->writeLn(['Saving file ...']);
        file_put_contents($path, $content);

        $pathinfo = pathinfo($file);
        $path = $pathinfo['dirname'].static::DS;

        if(!is_dir($path .= '_originals_')) mkdir($path);

        copy($file, $path.static::DS.$pathinfo['basename']);

        return $this->output->writeLn(['Done!']);
    }
}
