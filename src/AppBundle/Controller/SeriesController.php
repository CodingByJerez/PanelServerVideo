<?php

namespace AppBundle\Controller;

/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 */

use AppBundle\Entity\Serie;
use AppBundle\Service\Themoviedb\SerieService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Medium controller.
 *
 * @Route("serie")
 */
class SeriesController extends Controller
{
    /**
     * @Route("/", name="serieIndex")
     */
    public function indexAction()
    {

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    /**
     * @Route("/list", name="serieList")
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        $serie_dir = $this->getParameter('serie_dir');
        if(!($discSizeTotal = disk_total_space($serie_dir)) || !($discSizeFree = disk_free_space($serie_dir)))
            throw $this->createNotFoundException($this->get('translator')->trans('serie.error.dir_drive'));



        $series = $em->getRepository('AppBundle:Serie')->findBy([], ['id' => 'DESC']);

        return $this->render('@App/Serie/layout/list.html.twig', ["series" => $series, "discSizeTotal" => $discSizeTotal, "discSizeFree" => $discSizeFree]);
    }


    /**
     * @Route("/search", name="serieSearch")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {

        $result = [];

        if ($titre = $request->query->get('titre')) {

            $page = !empty($request->query->get('page')) ? intval($request->query->get('page')) : 1;

            try {
                /** @var \AppBundle\Service\Themoviedb\SerieService $themoviedb */
                $themoviedb = $this->container->get(SerieService::class)->search($titre, $page);

            } catch (\Exception $exception) {
                throw $this->createNotFoundException($exception->getMessage());
            }

            $result = ['titre' => $titre, 'page' => $page, 'themoviedb' =>  $themoviedb ];

        }

        return $this->render('@App/Serie/layout/search.html.twig', ["result" => $result]);

    }


    /**
     * @Route("/add/serie-{themoviedb_id}", name="serieAdd", requirements={"themoviedb_id"="\d+"})
     * @param Request $request
     * @param $themoviedb_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function serieAction(Request $request, $themoviedb_id)
    {


        try {
            $themoviedb = $this->container->get(SerieService::class)->serie($themoviedb_id);
        } catch (\Exception $exception) {
            throw $this->createNotFoundException($exception->getMessage());
        }

        if($saison = $request->query->get('saison')){

            $em = $this->getDoctrine()->getManager();
            $episodes = [];
            foreach($em->getRepository('AppBundle:Serie')->findBy(['themoviedb' => $themoviedb_id, 'saison' => $saison]) as $episode)
                $episodes[$episode->getEpisode()] = $episode;

            if(($url = $request->request->get('url')) && ($episode = $request->request->get('episode'))){

                $serie = new Serie();
                $serie->setThemoviedb($themoviedb->getId());
                $serie->setTitre($themoviedb->getName());
                $serie->setTitreOriginal($themoviedb->getNameOriginal());
                $serie->setProductionDate(\DateTime::createFromFormat('Y-m-d', $themoviedb->getFirstAirDate()));
                $serie->setSaison($saison);
                $serie->setEpisode($episode);
                $serie->setUrl($url);
                $em->persist($serie);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('serie.notification.adding'));

                return $this->redirect($request->getUri());

            }

        }

        return $this->render('@App/Serie/layout/add.html.twig', ["saisons" => $themoviedb->getSaison(), "episodes" => (isset($episodes) ? $episodes : [])]);

    }




    /**
     * @Route("/change-link-{id}", name="serieChangeLink", requirements={"id"="\d+"})
     * @ParamConverter("serie", class="AppBundle:Serie", options={"id" = "id"})
     * @param Request $request
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLink(Request $request, Serie $serie)
    {
        if($url = $request->request->get('url')){
            $em = $this->getDoctrine()->getManager();
            $serie->setUrl($url);
            $serie->setDownload(false);
            $serie->setError(null);
            $serie->setExecution(false);
            $em->persist($serie);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('serie.notification.change_link_sucsses'));
        } else
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('serie.notification.change_link_empty'));



        return $this->redirect($this->generateUrl('serieList'));

    }


    /**
     * @Route("/delete-{id}", name="serieDelete", requirements={"id"="\d+"})
     * @ParamConverter("serie", class="AppBundle:Serie", options={"id" = "id"})
     * @param Serie $serie
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @internal param Request $request
     */
    public function deleteAction(Serie $serie)
    {
        if($serie->getDownload()){
            $filesystem = new Filesystem();
            try {
                $filesystem->remove([$serie->getPath()]);
            } catch (IOExceptionInterface $exception) {
                throw $this->createNotFoundException("An error occurred while creating your directory at ".$exception->getPath());
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($serie);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('serie.notification.delete'));

        return $this->redirectToRoute('serieList');
    }


}
