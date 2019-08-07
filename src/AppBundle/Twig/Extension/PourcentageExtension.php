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



class PourcentageExtension extends \Twig_Extension
{


    public function getFilters()
    {
        return [new \Twig_SimpleFilter('pourcentage', [$this, 'pourcentage'])];
    }




    public function pourcentage(int $level, int $total): int
    {
        $pourcentage = $level / $total * 100;
        return round($pourcentage);
    }


    public function getName()
    {
        return 'pourcentage_extension';

    }

}