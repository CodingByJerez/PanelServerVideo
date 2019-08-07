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
use CronBundle\Entity\CronShift;

use CronBundle\Service\UnFichier\DownloadService;
use CronBundle\Utils\Slugger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DownloadCommand
 * @package CronBundle\Command
 */
class DownloadCommand extends ContainerAwareCommand
{
    // the name of the command (the part after "bin/console")
    /**
     * @var string
     */
    protected static $defaultName = 'app:download';

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var Slugger
     */
    private $slugger;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var \CronBundle\Service\UnFichier\DownloadService
     */
    private $downloadService; // after v1.1


    /**
     * DownloadCommand constructor.
     * @param EntityManagerInterface $em
     * @param Slugger $slugger
     * @param ContainerInterface $container
     */
    public function __construct(EntityManagerInterface $em, Slugger $slugger, ContainerInterface $container)
    {
        parent::__construct();
        $this->em = $em;
        $this->slugger = $slugger;
        $this->container = $container;
    }


    /**
     *
     */
    protected function configure(): void
    {
        $this
            // the short description shown while running "php bin/console list"
            ->setDescription('Download Video')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('execute Download Video dans le serveur')
            ->addArgument('type_video', InputArgument::OPTIONAL, 'option "film" ou "serie"')
        ;
    }


    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        // isset argument test if valid: film ou serie
        if(($typeVideo = $input->getArgument('type_video')) && !in_array($input->getArgument('type_video'), ['film', 'serie'])){
            $output->writeln("<error>Argument valide: [film|serie]</error>");
            exit;
        }

        $typeVideo = $typeVideo ?: $this->getShift()->getName();
        /** @var Film::class || Serie::class $entityName */
        $entityName = "AppBundle:" . ucfirst($typeVideo);
        // test limit download simultané
        if($this->em->getRepository($entityName)->countExecution() == $this->container->getParameter("{$typeVideo}_limit_download"))
            exit;

        // if not has video for download
        /** @var \AppBundle\Entity\Film | \AppBundle\Entity\Serie $film */
        if(!$film = $this->em->getRepository($entityName)->getOneExecution())
            exit;

        // up db status ececution
        $film->setExecution(true);
        $this->em->persist($film);
        $this->em->flush();

        try {
            $path = $this->container->get(DownloadService::class)
                ->download($this->getUrl($film->getUrl()))
                ->save($this->getPath($film, $typeVideo))
                ->getPath();
            ;
        } catch (\Exception $exception) {
            $film->setExecution(false)->setError($exception->getMessage(), $exception->getCode());
            $this->em->persist($film);
            $this->em->flush();
            exit;
        }

        $film->setDownload(true)->setPath($path)->setExecution(false);
        $this->em->persist($film);
        $this->em->flush();
    }


    /**
     * @param string $url
     * @return string
     * @throws \Exception
     */
    private function getUrl($url): string
    {
        if(!($url = parse_url($url)) || !isset($url["query"]))
            throw new \Exception("URL invalide");

        if($url["host"] != "1fichier.com")
            throw new \Exception("URL uplodeur non Reconnu. (lien valide uniquement 1fichier.com)");

       // $this->downloadService = \CronBundle\Service\UnFichier\DownloadService::class;
        parse_str($url["query"], $output);
        return "https://1fichier.com/?" . array_key_first($output);
    }


    /**
     * @param Film|Serie $videoEntity
     * @param string $typeVideo
     * @return string
     */
    private function getPath($videoEntity, string $typeVideo): string
    {
        $dir = $this->container->getParameter("{$typeVideo}_dir");
        $fileName = $this->slugger->cleanTitre($videoEntity->getTitreOriginal());

        if($typeVideo == "serie"){
            $dir .= $this->slugger->cleanTitre($videoEntity->getTitreOriginal()) . '-' . $videoEntity->getProductionDate()->format('Y') . '/';
            $fileName .= "-s{$videoEntity->getSaison()}e{$videoEntity->getEpisode()}";
        }
        else
            $fileName .= '-' . $videoEntity->getProductionDate()->format('Y');

        return $dir . $fileName;
    }


    /**
     * @return CronShift
     */
    private function getShift(): CronShift
    {
        /** @var \CronBundle\Entity\CronShift $shift */
        if(!$shift = $this->em->getRepository('CronBundle:CronShift')->getOneByDate())
            return $this->initDefaultFixtures();

        // up datetime
        $this->em->persist($shift);
        $this->em->flush();

        return $shift;
    }


    /**
     * @return CronShift
     */
    private function initDefaultFixtures(): CronShift
    {
        foreach (['film', 'serie'] as $value){
            $this->em->persist((new CronShift())->setName($value));
            $this->em->flush();
        }

        return $this->getShift();
    }

}