<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 30/07/2019
 * Time: 10:00
 */

namespace CronBundle\Utils;


class Slugger
{
    public function slugify(string $value): string
    {

    }

    public function cleanTitre(string $chaine){
        setlocale(LC_ALL, 'fr_FR.utf8');
        $chaine = strtolower($chaine);
        $chaine = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $chaine);
        $chaine = strtolower($chaine);
        $chaine = preg_replace('#[^a-z0-9]+#i', '.', $chaine);
        while(strpos('..', $chaine) !== false)
            $chaine = str_replace('..', '.', $chaine);

        $chaine = trim($chaine, '.');

        $titre = '';
        foreach (explode(".", $chaine) as $value)
            $titre .= '.' . ucfirst($value);

        return trim($titre, '.');
    }



}