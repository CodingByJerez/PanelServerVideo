<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 03/08/2019
 * Time: 12:35
 */

namespace AppBundle\Twig\Extension;



class BytesInGoExtension extends \Twig_Extension
{


    public function getFilters()
    {
        return [new \Twig_SimpleFilter('bytesInGo', [$this, 'bytesInGo'])];
    }




    public function bytesInGo(int $bytes): int
    {
        return intval($bytes / pow(1024,3));
    }


    public function getName()
    {
        return 'bytes_in_go_extension';

    }

}