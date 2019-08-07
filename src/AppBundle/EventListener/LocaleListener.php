<?php
/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 * Date: 12/07/2019
 * Time: 04:18
 */

namespace AppBundle\EventListener;


use Negotiation\LanguageNegotiator;

use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocaleListener
{

    private $dir;
    private $defaultLanguage;

    public function __construct($defaultLanguage, $dir)
    {
        $this->dir = $dir;
        $this->defaultLanguage = $defaultLanguage;
    }




    public function onKernelRequest(GetResponseEvent $event)
    {

        if ($event->getRequest()->getPathInfo() !== '/') {
            return;
        }


        if (null !== ($acceptLanguage = $event->getRequest()->headers->get('Accept-Language'))) {

            $finder = new Finder();
            $finder->files()->in($this->dir . '/Resources/translations/');

            $locales = [];
            foreach ($finder as $file)
                $locales[] = explode(".", $file->getRelativePathname())[1];


            $negotiator = new LanguageNegotiator();
            $best       = $negotiator->getBest(
                $event->getRequest()->headers->get('Accept-Language'),
                $locales
            );

            $this->defaultLanguage = null !== $best ? $best->getType() : $this->defaultLanguage;

        }

        $response = new RedirectResponse('/' . $this->defaultLanguage);

        $event->setResponse($response);

    }


}