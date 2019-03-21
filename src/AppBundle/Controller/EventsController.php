<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Events;
use AppBundle\Entity\User;
use AppBundle\Form\EventsType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Form\FormError;
use RecursiveArrayIterator;
use Symfony\Component\HttpFoundation\Response;






/**
 * Event controller.
 *
 * @Route("events")
 */
class EventsController extends Controller
{
    /**
     * Lists all event entities.
     *
     * @Route("/", name="events_index", methods={"GET","HEAD"})
     */
    public function indexAction(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        
        
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (empty($serialized)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $user_old = new User();
        $user_old->unserialize($serialized);
        
        $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
        if (empty($user_new)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $password_old = $user_old->getPassword();
        $password_new = $user_new->getPassword();
        $user_role = $user_new -> getRoles();
        //case "ROLE_PUBLISHER":$user_role = "Publisher";
        //case "ROLE_USER"::$user_role = "Publisher";
        if ($password_old == $password_new && $user_role == 'ROLE_PUBLISHER') {
            $events = $em->getRepository('AppBundle:Events')->findBy(array('user_id' => $user_new->getId()));
            // set and get session attributes
            return $this->render('events/index.html.twig', array(
                'events' => $events,
                'username' => $user_new->getUsername(),
                'user_roles' => $user_new->getRoles()
            ));
        }
        if ($password_old == $password_new && $user_role== 'ROLE_USER' || $user_role == 'ROLE_ADMIN') {
            $events = $em->getRepository('AppBundle:Events')->findAll();
            // set and get session attributes
            return $this->render('events/index.html.twig', array(
                'events' => $events,
                'username' => $user_new->getUsername(),
                'user_roles' => $user_new->getRoles()
            ));
        }
        else {
            $session->clear();
            return $this->redirectToRoute('login');
        }

        
    }

    /**
     * Finds and displays a event entity.
     *
     * @Route("/{id}", name="events_show", methods={"GET","HEAD"})
     */
    public function showAction(Events $event, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (empty($serialized)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $user_old = new User();
        $user_old->unserialize($serialized);

        $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
        if (empty($user_new)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $password_old = $user_old->getPassword();
        $password_new = $user_new->getPassword();
        $roles = $user_new->getRoles();
        if ($password_old == $password_new && $roles == 'ROLE_PUBLISHER' || $roles == 'ROLE_USER' || $roles == 'ROLE_ADMIN') {
            return $this->render('events/show.html.twig', array(
                'event' => $event,
            ));
        }
    }
    
    
    /**
     * Create new event.
     *
     * @Route("/create/", name="events_creation")
     */
    public function createAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
            
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (empty($serialized)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $user_old = new User();
        $user_old->unserialize($serialized);

        $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
        if (empty($user_new)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $password_old = $user_old->getPassword();
        $password_new = $user_new->getPassword();
        $roles = $user_new->getRoles();

        //for publishers
        if ($password_old == $password_new && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
            $events = new Events();
            $form = $this->createForm(EventsType::class, $events);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                try {  
                    $events->setUser_id($user_new->getId());
                    // 4) save Event!
                    $em->persist($events);
                    $em->flush();
                    //return new RedirectResponse($request->getHttpHost()."/events/");
                    return $this->redirectToRoute('events_index',array('success_creation' => 1));
                }
                catch( \Doctrine\DBAL\DBALException $e ) {
                    //echo $e->getMessage();
                    $error = new FormError("Any error occured!");
                    $form->addError($error);
                    return $this->render('events/edit.html.twig',
                    [
                        'form' => $form->createView(),
                        'title_l' => "Create new event",
                        'submit_name' => "Create"
                        ]);
                }
            }
            return $this->render('events/edit.html.twig',
                    [
                        'form' => $form->createView(),
                        'title_l' => "Create new event",
                        'submit_name' => "Create"
                        ]);

        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    /**
     * Edit event.
     *
     * @Route("/update/{id}", name="events_update", methods={"GET","HEAD", "POST"})
     */
    public function editAction(Events $events, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
            
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (empty($serialized)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $user_old = new User();
        $user_old->unserialize($serialized);

        $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
        if (empty($user_new)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $password_old = $user_old->getPassword();
        $password_new = $user_new->getPassword();
        $roles = $user_new->getRoles();

        if ($password_old == $password_new && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
            $form = $this->createForm(EventsType::class, $events);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                if (!$events) {
                    throw $this->createNotFoundException(
                    'There is no event with the following id: ' . $events->getId()
                    );
                }
                try {  
                    // 4) save Event!
                    $em->persist($events);
                    $em->flush();
                    //return new RedirectResponse($request->getHttpHost()."/events/");
                    return $this->redirectToRoute('events_index',array('success_updation' => 1));
                }
                catch( \Doctrine\DBAL\DBALException $e ) {
                    //echo $e->getMessage();
                    $error = new FormError("Any error occured!");
                    $form->addError($error);
                    return $this->render('events/edit.html.twig',
                    [
                        'form' => $form->createView(),
                        'title_l' => "Edit existing event",
                        'submit_name' => "Update"
                        ]);
                }
            }
            return $this->render('events/edit.html.twig',
                    [
                        'form' => $form->createView(),
                        'title_l' => "Edit existing event",
                        'submit_name' => "Update"
                        ]);

        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    
    /**
     * Delete selected event
     *
     * @Route("delete/{id}", name="events_delete", methods={"GET","HEAD"})
     */
    public function deleteAction(Events $events, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
            
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (empty($serialized)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $user_old = new User();
        $user_old->unserialize($serialized);

        $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
        if (empty($user_new)) {
            $session->clear();
            return $this->redirectToRoute('login');
        }
        $password_old = $user_old->getPassword();
        $password_new = $user_new->getPassword();
        $roles = $user_new->getRoles();

        if ($password_old == $password_new && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
                if (!$events) {
                    throw $this->createNotFoundException(
                    'There is no event with the following id: ' . $events->getId()
                    );
                }
                try {  
                    //delete Event
                    $em->remove($events);
                    $em->flush();
                    //return new RedirectResponse($request->getHttpHost()."/events/");
                    return $this->redirectToRoute('events_index',array('success_deletion' => 1));
                }
                catch( \Doctrine\DBAL\DBALException $e ) {
                    throw  $this->createException('Any error occured while event deleting!');
                }
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    
    /**
     * Finds events by name comparing.
     *
     * @Route("find_by_name/", name="events_find_names", methods={"GET","HEAD", "POST"})
     */
    public function findNamesByEvNameAction()
    {
        if (isset($_POST['ev_name'])) {
            try {
                $em = $this->getDoctrine()->getManager();
                $params_name=json_decode($_POST['ev_name']);
                $query = $em->createQuery(
                        "SELECT DISTINCT un.evName
                        FROM AppBundle:Events un
                        WHERE un.evName LIKE
                        :name"
                )->setParameters(array(
                                    'name' => "%".trim($params_name)."%"
                                    ));
                $result[]=$query->getArrayResult();
                $resulted_keywords = array();
                foreach ($result as $k=>$val) {
                    foreach ($val as $k_0=>$val_0)
                        if (!empty($val_0)) {
                        $keyword = explode(" ",trim($val_0["evName"]));
                        foreach ($keyword as $k_1=>$kw) {
                            $pos = true;
                            if (!empty(trim($params_name))) {
                                $pos  = strpos(strtolower($kw),trim(strtolower($params_name)));
                            }
                            if ($pos !== false) {
                                $resulted_keywords[$k][$k_0]["evName"] = trim($kw);
                            }
                        }
                    }
                }
                //make unique keywords
                $resulted_keywords = array_unique($resulted_keywords);
                //var_dump($resulted_keywords);
                return new Response(json_encode($resulted_keywords));
            }
            catch (Exception $e){
                return new Response(json_encode($e));
            }
        }
        return new Response("0");
    }
    
    /**
     * Finds events by name comparing.
     *
     * @Route("find_all_by_name/", name="events_find_all_names", methods={"GET","HEAD", "POST"})
     */
    public function findAllByEvNameAction()
    {
        if (isset($_POST['ev_name'])) {
            try {
                $em = $this->getDoctrine()->getManager();
                $params_name=json_decode($_POST['ev_name']);
                $query = $em->createQuery(
                        "SELECT un
                        FROM AppBundle:Events un
                        WHERE un.evName LIKE
                        :name"
                )->setParameters(array(
                                    'name' => "%".trim($params_name)."%"
                                    ));
                $result[]=$query->getArrayResult();
                return new Response(json_encode($result));
            }
            catch (Exception $e){
                return new Response(json_encode($e));
            }
        }
        return new Response("0");
    }
}
