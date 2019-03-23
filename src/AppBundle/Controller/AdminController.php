<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Admin controller.
 *
 * @Route("/admin")
 */
class AdminController extends Controller
{
  /**
     * Users page.
     *
     * @Route("/users/", name="admin_users_index", methods={"POST", "GET","HEAD"})
     */
    public function adminusersAction(Request $request)
    { 
    	$em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $user_role = $user_new -> getRoles();
            
            if (!empty($user_new) && $user_role == 'ROLE_ADMIN') {
                $users = $em->getRepository('AppBundle:User')->findAll();
            // set and get session attributes
                return $this->render('admin/users.html.twig', array(
                    'users' => $users,
                    'username' => $user_new->getUsername(),
                    'user_roles' => $user_new->getRoles(),
                    'show_route_path' => "/users/",
                    'delete_route_path' => "users/delete",//hardcode
                    'edit_route_path' => "users/update"//hardcode
                ));
            }
	    return $this->redirectToRoute('events_index');
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    } 
    
    
    /**
     * Finds and displays a event entity.
     *
     * @Route("/users/{id}", name="users_admin_show", methods={"POST", "GET","HEAD"})
     */
    public function showUserAction(User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();
            if (!empty($user_new) && $roles == 'ROLE_ADMIN') {
                return $this->render('admin/show_user.html.twig', array(
                    'user' => $user,
                    'username' => $user_new->getUsername(),
                    'user_roles' => $user_new->getRoles(),
                    'title_l' => "User description",
                            
                ));
            }
	    return $this->redirectToRoute('events_index');
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    /**
     * Create new user.
     *
     * @Route("/users/create/", name="user_admin_creation")
     */
    public function useradminCreationAction(Request $request, UserPasswordEncoderInterface $passwordEncoder) {
        
        $em = $this->getDoctrine()->getManager();
            
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            //for publishers
            if (!empty($user_new) && $roles == 'ROLE_ADMIN') {
                $user = new User();
                $form = $this->createForm(UserType::class, $user);
                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    try { 
                        $password = $passwordEncoder->encodePassword($user, trim($user->getPlainPassword()));
                        $user->setPassword($password);
                        // 4) save Event!
                        $em->persist($user);
                        $em->flush();
                        //return new RedirectResponse($request->getHttpHost()."/events/");
                        return $this->redirectToRoute('admin_users_index',array('success_user_creation' => 1));
                    }
                    catch( \Doctrine\DBAL\DBALException $e ) {
                        //echo $e->getMessage();
                        $error = new FormError("Any error occured!");
                        $form->addError($error);
                        return $this->render('admin/edit_user.html.twig',
                        [
                            'form' => $form->createView(),
                            'title_l' => "Create new user",
                            'submit_name' => "Create",
                            'username' => $user_new->getUsername(),
                            'user_roles' => $user_new->getRoles(),
                            ]);
                    }
                }
                return $this->render('admin/edit_user.html.twig',
                        [
                            'form' => $form->createView(),
                            'title_l' => "Create new user",
                            'submit_name' => "Create",
                            'username' => $user_new->getUsername(),
                            'user_roles' => $user_new->getRoles(),
                            ]);

            }
	    return $this->redirectToRoute('events_index');
        }
        else {
             return $this->redirectToRoute('events_index');
        }
    }
    
    
    /**
     * Edit user.
     *
     * @Route("/users/update/{id}", name="user_admin__update", methods={"GET","HEAD", "POST"})
     */
    public function edituseradminAction(User $user, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            if (!empty($user_new) && $roles == 'ROLE_ADMIN') {
                $form = $this->createForm(UserType::class, $user);
                $pp = $form->get("plainPassword");
                $form->remove("plainPassword");
                $form->handleRequest($request);
                    
                 if ($form->isSubmitted()) {
                     
                    if (!$user) {
                        throw $this->createNotFoundException(
                        'There is no user with the following id: ' . $user->getId()
                        );
                    }
                    try {  
                        // 4) save User!
                        $em->persist($user);
                        $em->flush();
                        //return new RedirectResponse($request->getHttpHost()."/events/");
                        return $this->redirectToRoute('admin_users_index',array('success_updation_user' => 1));
                    }
                    catch( \Doctrine\DBAL\DBALException $e ) {
                        //echo $e->getMessage();
                        $error = new FormError("Any error occured!");
                        $form->addError($error);
                        return $this->render('admin/edit_user.html.twig',
                        [
                            'form' => $form->createView(),
                            'title_l' => "Edit existing user",
                            'submit_name' => "Update",
                            'username' => $user_new->getUsername(),
                            'user_roles' => $user_new->getRoles(),
                            'updating' => "1"
                            ]);
                    }
                }
                return $this->render('admin/edit_user.html.twig',
                        [
                            'form' => $form->createView(),
                            'title_l' => "Edit existing event",
                            'submit_name' => "Update",
                            'username' => $user_new->getUsername(),
                            'user_roles' => $user_new->getRoles(),
                            'updating' => "1"
                            ]);

            }
	    return $this->redirectToRoute('events_index');
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
     /**
     * Delete selected user
     *
     * @Route("/users/delete/{id}", name="user_admin_delete", methods={"GET","HEAD"})
     */
    public function deleteuserAction(User $user, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
            
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            $roles = $user_new->getRoles();

            if (!empty($user_new) && $roles == 'ROLE_ADMIN') {
                    if (!$user) {
                        throw $this->createNotFoundException(
                        'There is no user with the following id: ' . $user->getId()
                        );
                    }
                    if ($user->getRoles() == 'ROLE_ADMIN') {
                        throw  new \Symfony\Component\Config\Definition\Exception\Exception("You can't delete admin users!");
                    }
                    else {
                        try {  
                            //delete Event
                            $em->remove($user);
                            $em->flush();
                            //return new RedirectResponse($request->getHttpHost()."/events/");
                            return $this->redirectToRoute('admin_users_index',array('success_deletion_user' => 1));
                        }
                        catch( \Doctrine\DBAL\DBALException $e ) {
                            throw  new \Symfony\Component\Config\Definition\Exception\Exception('Any error occured usere event deleting!');
                        }
                    }
            }
	    return $this->redirectToRoute('events_index');
        }
        else {
            return $this->redirectToRoute('events_index');
        }
    }
    
    
}
