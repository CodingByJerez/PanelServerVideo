<?php

/**
 * Created by PhpStorm.
 * User: CodingByJerez
 * Url: http://coding.by.jerez.me
 * Github: https://github.com/CodingByJerez
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Film;
use AppBundle\Service\Themoviedb\FilmService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Medium controller.
 *
 * @Route("film")
 */
class FilmController extends Controller
{

    /**
     * @Route("/", name="filmIndex")
     */
    public function indexAction()
    {
        return false;
    }

    /**
     * @Route("/list", name="filmList")
     */
    public function listAction()
    {
        $film_dir = $this->getParameter('film_dir');
        if(!($discSizeTotal = disk_total_space($film_dir)) || !($discSizeFree = disk_free_space($film_dir)))
            throw $this->createNotFoundException($this->get('translator')->trans('film.error.dir_drive'));


        $em = $this->getDoctrine()->getManager();

        $films = $em->getRepository('AppBundle:Film')->findBy([], ['id' => 'DESC']);

        return $this->render('@App/Film/layout/list.html.twig', ["films" => $films, "discSizeTotal" => $discSizeTotal, "discSizeFree" => $discSizeFree]);
    }


    /**
     * @Route("/search", name="filmSearch")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchAction(Request $request)
    {
        $result = [];

        if($titre = $request->query->get('titre')){

            $annee = !empty($request->query->get('date')) ? intval($request->query->get('date')) : 0;
            $page = !empty($request->query->get('page')) ? intval($request->query->get('page')) : 1;

            try {
                /** @var \AppBundle\Service\Themoviedb\FilmService $themoviedb */
                $themoviedb = $this->container->get(FilmService::class)->search($titre, $annee, $page);

            } catch (\Exception $exception) {
                throw $this->createNotFoundException($exception->getMessage());
            }

            $result = ['titre' => $titre, 'page' => $page, 'date' => $annee,'themoviedb' =>  $themoviedb ];

        }

        return $this->render('@App/Film/layout/search.html.twig', ["result" => $result]);
    }


    /**
     * @Route("/add-{themoviedb_id}", name="filmAdd", requirements={"themoviedb_id"="\d+"})
     * @param Request $request
     * @param $themoviedb_id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAction(Request $request, $themoviedb_id)
    {
        $em = $this->getDoctrine()->getManager();

        if($em->getRepository('AppBundle:Film')->findOneBy(['themoviedb' => $themoviedb_id])){
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('film.notification.isset'));
            return $this->redirect($this->generateUrl('filmSearch'));
        }

        if($url = $request->request->get('url')){
          //  $url = "https://1fichier.com/?yktnzqhz8pwsy3pwu0kv&af=2549450";

            try {
                /** @var \AppBundle\Service\Themoviedb\FilmService $themoviedb */
                $themoviedb = $this->container->get(FilmService::class)->movie($themoviedb_id);

            } catch (\Exception $exception) {
                throw $this->createNotFoundException($exception->getMessage());
            }

            $film = new Film();
            $film->setThemoviedb($themoviedb_id);
            $film->setUrl($url);
            $film->setTitre($themoviedb->title);
            $film->setTitreOriginal($themoviedb->original_title);
            $film->setProductionDate(\DateTime::createFromFormat('Y-m-d', $themoviedb->release_date));
            $em->persist($film);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('film.notification.adding'));
            return $this->redirect($this->generateUrl('filmSearch'));


        }

        return $this->render('@App/Film/layout/add.html.twig');
    }

    /**
     * @Route("/change-link-{id}", name="filmChangeLink", requirements={"id"="\d+"})
     * @ParamConverter("film", class="AppBundle:Film", options={"id" = "id"})
     * @param Request $request
     * @param Film $film
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function changeLink(Request $request, Film $film)
    {
        if($url = $request->request->get('url')){
            $em = $this->getDoctrine()->getManager();
            $film->setUrl($url);
            $film->setDownload(false);
            $film->setError(null);
            $film->setExecution(false);
            $em->persist($film);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('film.notification.change_link_sucsses'));
        } else
            $this->get('session')->getFlashBag()->add('danger', $this->get('translator')->trans('film.notification.change_link_empty'));



        return $this->redirect($this->generateUrl('filmList'));

    }


    /**
     * @Route("/delete-{id}", name="filmDelete", requirements={"id"="\d+"})
     * @ParamConverter("film", class="AppBundle:Film", options={"id" = "id"})
     * @param Film $film
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Film $film)
    {
        if($film->getDownload()){
            $filesystem = new Filesystem();
            try {
                $filesystem->remove([$film->getPath()]);
            } catch (IOExceptionInterface $exception) {
                throw $this->createNotFoundException("An error occurred while creating your directory at ".$exception->getPath());
            }
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($film);
        $em->flush();
        $this->get('session')->getFlashBag()->add('success', $this->get('translator')->trans('film.notification.delete'));

        return $this->redirectToRoute('filmList');
    }



}
