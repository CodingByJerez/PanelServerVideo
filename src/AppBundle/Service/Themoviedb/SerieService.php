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




use AppBundle\Model\Themoviedb\SerieModel;
use Symfony\Component\HttpFoundation\RequestStack;

class SerieService extends AppService
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
     * @param int $page
     * @return bool|mixed
     */
    public function search(string $name, int $page)
    {
        $response = $this->curlConnect("https://api.themoviedb.org/3/search/tv?api_key={$this->key}&language={$this->lang}&page={$page}&query=" . urlencode($name));

        if(!isset($response->results))
            return false;

        foreach ($response->results as &$movies){
            $movies->poster_path = $this->recoverImgBase64($movies->poster_path);
        }

        return $response;

    }


    public function serie(int $id, int $season = null, int $episode = null)
    {
        $serie = $this->curlConnect("https://api.themoviedb.org/3/tv/{$id}?api_key={$this->key}&language={$this->lang}");


        return new SerieModel($serie);
    }

    public function saison(int $serie, int $saison)
    {
        $saison = $this->curlConnect("https://api.themoviedb.org/3/tv/{$serie}/season/{$saison}?api_key={$this->key}&language={$this->lang}");

        return $saison ?: false;

    }

    public function episode(int $serie, int $saison, int $episode)
    {
        $episode = $this->curlConnect("https://api.themoviedb.org/3/tv/{$serie}/season/{$saison}/episode/{$episode}?api_key={$this->key}&language={$this->lang}");

        return $episode ?: false;

    }





}