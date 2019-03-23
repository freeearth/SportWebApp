<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Events;
use AppBundle\Entity\User;
use AppBundle\Form\EventsType;
use AppBundle\Repository\UserRepository;
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
 * @Route("/")
 */
class EventsController extends Controller
{
    /**
     * Lists all event entities.
     *
     * @Route("", name="events_index", methods={"GET","HEAD"})
     */
    public function indexAction(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $user_role = $user_new -> getRoles();
            //case "ROLE_PUBLISHER":$user_role = "Publisher";
            //case "ROLE_USER"::$user_role = "Publisher";
            if ($user_role == 'ROLE_PUBLISHER') {
                $events = $em->getRepository('AppBundle:Events')->findBy(array('user_id' => $user_new->getId()));
                // set and get session attributes
                return $this->render('events/index.html.twig', array(
                    'events' => $events,
                    'username' => $user_new->getUsername(),
                    'user_roles' => $user_new->getRoles(),
                    'show_route_path' => "events/",
                    'delete_route_path' => "events/delete",//hardcode
                    'edit_route_path' => "events/update"//hardcode
                ));
            }
            if (!empty($user_new) && $user_role== 'ROLE_USER' || $user_role == 'ROLE_ADMIN') {
                $events = $em->getRepository('AppBundle:Events')->findAll();
                // set and get session attributes
                return $this->render('events/index.html.twig', array(
                    'events' => $events,
                    'username' => $user_new->getUsername(),
                    'user_roles' => $user_new->getRoles(),
                    'show_route_path' => "events/",
                    'delete_route_path' => "events/delete",//hardcode
                    'edit_route_path' => "events/update"//hardcode
                ));
            }
            else {
                return $this->render('events/index.html.twig', array(
                    'events' => "",
                    'user_roles' => "USER_GUEST",
                    'show_route_path' => "",
                    'delete_route_path' => "",//hardcode
                    'edit_route_path' => ""//hardcode
                ));
            }
        }
        else {
            return $this->render('events/index.html.twig', array(
                    'events' => "",
                    'user_roles' => "USER_GUEST",
                    'show_route_path' => "",
                    'delete_route_path' => "",//hardcode
                    'edit_route_path' => ""//hardcode
                ));
        }

        
    }

    /**
     * Finds and displays a event entity.
     *
     * @Route("/events/{id}", name="events_show", methods={"GET","HEAD"})
     */
    public function showAction(Events $event, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();
            if (!empty($user_new) && $roles == 'ROLE_PUBLISHER' || $roles == 'ROLE_USER' || $roles == 'ROLE_ADMIN') {
                return $this->render('events/show.html.twig', array(
                    'event' => $event,
                ));
            }
            else {
                return $this->redirectToRoute('login');
            }
        }
        else {
            return $this->redirectToRoute('login');
        }
    }
    
    
    /**
     * Create new event.
     *
     * @Route("/events/create/", name="events_creation")
     */
    public function createAction(Request $request) {
        
        $em = $this->getDoctrine()->getManager();
            
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            //for publishers
            if (!empty($user_new) && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
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
        else {
             return $this->redirectToRoute('events_index');
        }
    }
    
    /**
     * Edit event.
     *
     * @Route("/events/update/{id}", name="events_update", methods={"GET","HEAD", "POST"})
     */
    public function editAction(Events $events, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
            
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            if (!empty($user_new) && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
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
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    
    /**
     * Delete selected event
     *
     * @Route("/events/delete/{id}", name="events_delete", methods={"GET","HEAD"})
     */
    public function deleteAction(Events $events, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
            
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            if (!empty($user_new) && $roles == 'ROLE_PUBLISHER'|| $roles == 'ROLE_ADMIN') {
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
                        throw  \Symfony\Component\Config\Definition\Exception\Exception('Any error occured while event deleting!');
                    }
            }
            else {
                return $this->redirectToRoute('events_index');
            }
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    
    /**
     * Finds events by name comparing.
     *
     * @Route("/events/find_by_name/", name="events_find_names", methods={"GET","HEAD", "POST"})
     */
    public function findNamesByEvNameAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user)) {
            $user = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user )) {
            if (isset($_POST['ev_name'])) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $params_name=json_decode($_POST['ev_name']);
                    $roles = $user->getRoles();
                    if ($roles == 'ROLE_PUBLISHER') {
                        $query = $em->createQuery(
                            "SELECT DISTINCT un.evName
                            FROM AppBundle:Events un
                            WHERE un.evName LIKE
                            :name AND un.user_id = :user_id" 
                        )->setParameters(array(
                                        'name' => "%".trim($params_name)."%",
                                        'user_id' => $user->getId()
                                        ));
                    }
                    if ($roles == 'ROLE_ADMIN' || $roles == 'ROLE_USER') {
                        $query = $em->createQuery(
                                "SELECT DISTINCT un.evName
                                FROM AppBundle:Events un
                                WHERE un.evName LIKE
                                :name"
                        )->setParameters(array(
                                            'name' => "%".trim($params_name)."%"
                                            ));
                    }
                    $result[]=$query->getArrayResult();
                    $resulted_keywords = array();
                    $cnt = 0;
                    foreach ($result as $k=>$val) {
                        foreach ($val as $k_0=>$val_0) {
                            if (!empty($val_0)) {
                                $keyword = explode(" ",trim($val_0["evName"]));
                                foreach ($keyword as $k_1=>$kw) {
                                    $pos = true;
                                    if (!empty(trim($params_name))) {
                                        $pos  = strpos(strtolower($kw),strtolower($params_name));
                                    }
                                    if ($pos !== false | $pos == true) {
                                        $resulted_keywords[$cnt] = trim($kw);
                                        $cnt++;
                                    }
                                }
                            }
                        }
                    }
                    //make unique keywords
                    //var_dump($resulted_keywords);
                    $resulted_keywords = array_unique($resulted_keywords, SORT_REGULAR);
                    //var_dump($resulted_keywords);
                    return new Response(json_encode($resulted_keywords));
                }
                catch (Exception $e){
                    return new Response(json_encode($e));
                }
            }
        }
        return new Response("0");
    }
    
    
    
     /**
     * Finds events by name comparing.
     *
     * @Route("/events/find_by_location/", name="events_find_locations", methods={"GET","HEAD", "POST"})
     */
    public function findLocationsByEvLocationAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user)) {
            $user = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user )) {
            if (isset($_POST['ev_location'])) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $params_name=json_decode($_POST['ev_location']);
                    $roles = $user->getRoles();
                    if ($roles == 'ROLE_PUBLISHER') {
                        $query = $em->createQuery(
                            "SELECT DISTINCT un.evLocation
                            FROM AppBundle:Events un
                            WHERE un.evLocation LIKE
                            :name AND un.user_id = :user_id" 
                        )->setParameters(array(
                                        'name' => "%".trim($params_name)."%",
                                        'user_id' => $user->getId()
                                        ));
                    }
                    if ($roles == 'ROLE_ADMIN' || $roles == 'ROLE_USER') {
                        $query = $em->createQuery(
                                "SELECT DISTINCT un.evLocation
                                FROM AppBundle:Events un
                                WHERE un.evLocation LIKE
                                :name"
                        )->setParameters(array(
                                            'name' => "%".trim($params_name)."%"
                                            ));
                    }
                    $result[]=$query->getArrayResult();
                    $resulted_keywords = array();
                    $cnt = 0;
                    foreach ($result as $k=>$val) {
                        foreach ($val as $k_0=>$val_0) {
                            if (!empty($val_0)) {
                                $keyword = explode(" ",trim($val_0["evLocation"]));
                                foreach ($keyword as $k_1=>$kw) {
                                    $pos = true;
                                    if (!empty(trim($params_name))) {
                                        $pos  = strpos(strtolower($kw),strtolower($params_name));
                                    }
                                    if ($pos !== false | $pos == true) {
                                        $resulted_keywords[$cnt] = trim($kw);
                                        $cnt++;
                                    }
                                }
                            }
                        }
                    }
                    //make unique keywords
                    //var_dump($resulted_keywords);
                    $resulted_keywords = array_unique($resulted_keywords, SORT_REGULAR);
                    //var_dump($resulted_keywords);
                    return new Response(json_encode($resulted_keywords));
                }
                catch (Exception $e){
                    return new Response(json_encode($e));
                }
            }
        }
        return new Response("0");
    }
    
    
    /**
     * Finds events by name comparing.
     *
     * @Route("/events/find_all_by_name/", name="events_find_all_names", methods={"GET","HEAD", "POST"})
     */
    public function findAllByEvNameAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user)) {
            $user = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user )) {
            if (isset($_POST['ev_name'])||isset($_POST['ev_location'])) {
                try {
                    $em = $this->getDoctrine()->getManager();
                    $params_name=json_decode($_POST['ev_name']);
                    $params_location=json_decode($_POST['ev_location']);
                    $roles = $user->getRoles();
                    if ($roles == 'ROLE_PUBLISHER') {
                        $query = $em->createQuery(
                            "SELECT un
                            FROM AppBundle:Events un
                            WHERE un.evName LIKE
                            :name AND un.evLocation LIKE :location  AND un.user_id = :user_id" 
                        )->setParameters(array(
                                        'name' => "%".trim($params_name)."%",
                                        'user_id' => trim($user->getId()),
                                        'location' => "%".trim($params_location)."%"
                                        ));
                    }
                    if ($roles == 'ROLE_ADMIN' || $roles == 'ROLE_USER') {
                        $query = $em->createQuery(
                                "SELECT un
                                FROM AppBundle:Events un
                                WHERE un.evName LIKE
                                :name AND un.evLocation LIKE :location"
                        )->setParameters(array(
                                            'name' => "%".trim($params_name)."%",
                                            'location' => "%".trim($params_location)."%"
                                            ));
                    }
                    $result[]=$query->getArrayResult();
                    return new Response(json_encode($result));
                }
                catch (Exception $e){
                    return new Response(json_encode($e));
                }
            }
        }
        return new Response("0");
    }
}
