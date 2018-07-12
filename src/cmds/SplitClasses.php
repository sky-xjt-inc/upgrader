<?php

namespace SKYW\Upgrader\Cmds;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
// use Symfony\Component\Console\Command\Command as SymCommand;

class SplitClasses extends Command {

    const DS = DIRECTORY_SEPARATOR;

    public function configure()
    {
        $this->setName('split-classes')
             ->setDescription('Split multi-classes files into their own class files')
             ->setHelp('This command allows you to split different classes into their own classes...')
             ->addArgument('path', InputArgument::REQUIRED, 'The path of the file/folder to split');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->argument = $this->input->getArgument('path');
        $this->path = realpath($this->argument);

        $this->splitClasses($this->path);
    }

    private function splitClasses($path)
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
        if(stripos($file, '_originals_') !== false) return $this->output;

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
            $class = str_replace('_Controller', 'Controller', $class);
            $name = $matchClassDecl[2][$k];
            $this->createNewFiles($name, $class, $file);
        }
        return $this->output;
    }

    public function createNewPaths($dir, $name)
    {
        $base = pathinfo($dir, PATHINFO_FILENAME);

        if($base != $name){
            $dir .= static::DS.$name;
        }
        if(!is_dir($dir)) mkdir($dir);

        return $dir;
    }

    public function createNewFiles($name, $content, $file)
    {
        $this->output->writeLn(["\nCreating $name.php ..."]);

        $dirname = pathinfo($file, PATHINFO_DIRNAME);

        if(stripos($name, 'Controller') !== false){
            $dir = $this->createNewPaths($dirname, 'controllers');
            $name = str_replace('_Controller', 'Controller', $name);
            $path = $dir.static::DS.$name.'.php';

        } elseif(stripos($name, 'Page') !== false){
            $dir = $this->createNewPaths($dirname, 'pagetypes');
            $path = $dir.static::DS.$name.'.php';

        } else {
            $dir = $this->createNewPaths($dirname, 'datamodels');
            $path = $dir.static::DS.$name.'.php';
        }
        $this->storeNewFiles($path, $content, $file);
    }

    public function storeOldFiles($path, $content, $file)
    {
        $this->output->write(["Saving $file"]);
        file_put_contents($path, $content);

        $path = pathinfo($file, PATHINFO_DIRNAME);

        $path = $this->createNewPaths($path, '_originals_');

        copy($file, $path.static::DS.basename($file));

        return $this->output->write([" ... Done!\n"]);
    }

    public function storeNewFiles($path, $content, $file)
    {
        $this->output->write(["Saving $file"]);
        file_put_contents($path, $content);

        $path = pathinfo($file, PATHINFO_DIRNAME);

        $path = $this->createNewPaths($path, '_originals_');

        copy($file, $path.static::DS.basename($file));

        return $this->output->write([" ... Done!\n"]);
    }
}
