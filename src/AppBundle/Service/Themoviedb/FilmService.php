<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 27/03/2019
 * Time: 08:25
 */

namespace AppBundle\Service\Themoviedb;





use Symfony\Component\HttpFoundation\RequestStack;

class FilmService extends AppService
{

    private $key;
    private $lang;

    /**
     * @param RequestStack $requestStack
     * @param $themoviedb_key
     * @internal param $lang
     */
    public function __construct(RequestStack $requestStack, $themoviedb_key)
    {
        $this->key = $themoviedb_key;
        $this->lang = $requestStack->getCurrentRequest()->getLocale();

        return $this;
    }


    /**
     * @param string $name
     * @param int $year
     * @param int $page
     * @return bool|mixed
     */
    public function search(string $name, int $year, int $page)
    {

      //  dump("https://api.themoviedb.org/3/search/movie?api_key={$this->key}&language={$this->lang}&page={$page}&query=$name");
      //  exit();

        $response = $this->curlConnect("https://api.themoviedb.org/3/search/movie?api_key={$this->key}&language={$this->lang}&page={$page}&query=" . urlencode($name) . ($year > 0 ? "&year=$year" : ''));

        if(!isset($response->results))
            return false;

        foreach ($response->results as &$movies){
            $movies->poster_path = $this->recoverImgBase64($movies->poster_path);
        }

        return $response;

    }


    public function movie(int $id)
    {
        $response = $this->curlConnect("https://api.themoviedb.org/3/movie/{$id}?language={$this->lang}&api_key={$this->key}");

        return isset($response->id) ? $response : false;

    }



}