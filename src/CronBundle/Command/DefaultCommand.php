<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 31/07/2019
 * Time: 14:58
 */

namespace CronBundle\Command;


use AppBundle\Entity\Film;
use AppBundle\Entity\Serie;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * Class DownloadCommand
 * @package CronBundle\Command
 */
class DefaultCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    /**
     * @var string
     */
    protected static $defaultName = 'app:cron';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ContainerInterface
     */
    private $container;

    private $timeOut;


    private $curl;

    /**
     * @var OutputInterface
     */
    private $output;



    /**
     * DownloadCommand constructor.
     * @param EntityManagerInterface $em
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, ContainerInterface $container)
    {
        parent::__construct();
        $this->em = $em;
        $this->container = $container;
        $this->timeOut = $this->container->getParameter("server_time_off");
    }


    /**
     *
     */
    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('command for tache cron')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('ajouter la comande a votre cron serveur')
        ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->output = $output;

        if($this->container->getParameter("server_auto_off"))
            $this->offServer();



        //exec("/usr/bin/php /home/panel_video/bin/console app:download");
        $command = $this->getApplication()->find('app:download');

        $arguments = [
            'command' => 'app:download',
            //'name'    => 'Fabien',
           // '--yell'  => true,
        ];

        $greetInput = new ArrayInput($arguments);
        $returnCode = $command->run($greetInput, $output);





    }





    private function offServer()
    {

        // temp que le serveur est open - de 30min
        if(floatval(explode(' ',  @file_get_contents('/proc/uptime'))[0]) < $this->timeOut)
            return;

        if($this->em->getRepository(Film::class)->getOneExecution())
            return;
        if($this->em->getRepository(Serie::class)->getOneExecution())
            return;

        $this->curl = curl_init();
        curl_setopt_array($this->curl,[
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => "http://{$this->container->getParameter('emby_ip')}:{$this->container->getParameter('emby_port')}/emby/Sessions?api_key={$this->container->getParameter('emby_key')}"
        ]);

        $curlResponse = curl_exec($this->curl);

        if($err = curl_error($this->curl))
            $this->error("curl error ==> {$err}");

        if(!$result = json_decode($curlResponse))
            $this->error("Json decode error");

        foreach ($result as $user){
            if($user->Client == $this->container->getParameter('emby_name_key'))
                continue;
            //dump($user->PlayState->CanSeek);
             if($user->PlayState->CanSeek)
                return;
           // dump($user->Client);
           // dump((new \DateTime($user->LastActivityDate))->format('U'));
           // dump((new \DateTime())->format('U'));
            if(((new \DateTime())->format('U') - $this->timeOut) < (new \DateTime($user->LastActivityDate))->format('U'))
                return;

        }


        $filesystem = new Filesystem();
        try {
            $filesystem->dumpFile('/tmp/reboot.server', 'Reboot now');
        } catch (IOExceptionInterface $exception) {
            echo "An error occurred while creating your directory at ".$exception->getPath();
        }




        //system('shutdown -h now');
        //https://symfony.com/doc/3.4/components/process.html#usage
       // $process = new Process(['poweroff']);
        //$process->run();

        // executes after the command finishes
        //if (!$process->isSuccessful())
           // $this->error($process->getErrorOutput());


    }

    private function cleanTempFile()
    {

        // -- V1.1
        //$filesystem = new Filesystem();
        //$filesystem->remove(['symlink', '/path/to/directory', 'activity.log']);
    }

    private function error($msg)
    {
        $this->output->writeln("<error>{$msg}</error>");
        exit();
    }


    public function __destruct()
    {
        if (is_resource($this->curl)) curl_close($this->curl);
    }


}

