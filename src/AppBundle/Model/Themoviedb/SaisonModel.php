<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 28/07/2019
 * Time: 15:39
 */

namespace AppBundle\Model\Themoviedb;




class SaisonModel
{

    /**
     * @var integer
     */
    private $numeroSaison;

    /**
     * @var integer
     */
    private $nombreEpisode;


    public function __construct($theMovieDB)
    {
        if(!isset($theMovieDB->episode_count) || !is_numeric($theMovieDB->episode_count))
            throw new \Exception("error SaisonModel => episode_count de theMovieDB et vide");
        if(!isset($theMovieDB->season_number) || !is_numeric($theMovieDB->season_number))
            throw new \Exception("error SaisonModel => season_number de theMovieDB et vide");

        $this
            ->setNombreEpisode($theMovieDB->episode_count)
            ->setNumeroSaison($theMovieDB->season_number)
        ;
    }

    /**
     * @return mixed
     */
    public function getNumeroSaison()
    {
        return $this->numeroSaison;
    }

    /**
     * @param mixed $numeroSaison
     * @return $this
     */
    public function setNumeroSaison($numeroSaison)
    {
        $this->numeroSaison = $numeroSaison;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNombreEpisode()
    {
        return $this->nombreEpisode;
    }

    /**
     * @param mixed $nombreEpisode
     * @return $this
     */
    public function setNombreEpisode($nombreEpisode)
    {
        $this->nombreEpisode = $nombreEpisode;

        return $this;
    }



}