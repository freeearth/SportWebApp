<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
  /**
     * About us page.
     *
     * @Route("about", name="about_us", methods={"GET","HEAD"})
     */
    public function aboutAction(Request $request)
    { 
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        $username = "";
        if (!empty($user_new)) {
            $username = $user_new->getUsername();
        }
        return $this->render('default/about.html.twig',
                array(
                    "page_title"=>"About Us",
                    "username"=>$username
                    ));   
    } 
    
    /**
     * Services page.
     *
     * @Route("services", name="services", methods={"GET","HEAD"})
     */
    public function servicesAction(Request $request)
    { 
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        $username = "";
        if (!empty($user_new)) {
            $username = $user_new->getUsername();
        }
        return $this->render('default/services.html.twig',
                array(
                    "page_title"=>"Services",
                    "username"=>$username
                    ));   
    } 
    
    /**
     * Contacts page.
     *
     * @Route("contacts", name="contacts", methods={"GET","HEAD"})
     */
    public function contactsAction(Request $request)
    { 
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        $username = "";
        if (!empty($user_new)) {
            $username = $user_new->getUsername();
        }
        return $this->render('default/contacts.html.twig',
                array(
                    "page_title"=>"Contacts",
                    "username"=>$username
                    ));   
    }
    
}
