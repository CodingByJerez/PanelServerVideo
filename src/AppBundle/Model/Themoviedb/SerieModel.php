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



class SerieModel
{


    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $nameOriginal;

    /**
     * @var string
     */
    private $firstAirDate;

    /**
     * @var \AppBundle\Model\Themoviedb\SaisonModel
     */
    private $saisons;



    public function __construct($theMovieDB)
    {
        $this->saisons = [];

        if(empty($theMovieDB->id))
            throw new \Exception("error SerieModel => id de theMovieDB et vide");
        if(empty($theMovieDB->name))
            throw new \Exception("error SerieModel => name de theMovieDB et vide");
        if(empty($theMovieDB->original_name))
            throw new \Exception("error SerieModel => original_name de theMovieDB et vide");
        if(empty($theMovieDB->first_air_date))
            throw new \Exception("error SerieModel => first_air_date de theMovieDB et vide");
        if(empty($theMovieDB->seasons))
            throw new \Exception("error SerieModel => seasons de theMovieDB et vide");

        $this
            ->setId(intval($theMovieDB->id))
            ->setName($theMovieDB->name)
            ->setNameOriginal($theMovieDB->original_name)
            ->setFirstAirDate($theMovieDB->first_air_date)
        ;

        foreach ($theMovieDB->seasons as $season)
            $this->addSaison(new SaisonModel($season));

    }


    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNameOriginal()
    {
        return $this->nameOriginal;
    }

    /**
     * @param mixed $nameOriginal
     * @return $this
     */
    public function setNameOriginal($nameOriginal)
    {
        $this->nameOriginal = $nameOriginal;

        return $this;
    }

    /**
     * @return string
     */
    public function getFirstAirDate(): string
    {
        return $this->firstAirDate;
    }

    /**
     * @param string $firstAirDate
     */
    public function setFirstAirDate(string $firstAirDate)
    {
        $this->firstAirDate = $firstAirDate;
    }

    /**
     * @param int|null $saison
     * @return mixed
     */
    public function getSaison(int $saison = null)
    {
        return $saison === null ? $this->saisons : $this->saisons[$saison];
    }

    /**
     * @param mixed $saison
     * @return $this
     */
    public function addSaison(\AppBundle\Model\Themoviedb\SaisonModel $saison)
    {
        $this->saisons[$saison->getNumeroSaison()] = $saison;

        return $this;
    }




}