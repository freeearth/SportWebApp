<?php

namespace AppBundle\Controller;


use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;

/**
     * @Route("register")
     */
class RegistrationController extends Controller
{
    /**
     * @Route("/", name="register")
     */
    public function registerAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
        
        
        
        $em = $this->getDoctrine()->getManager();
            
        $session = $request->getSession();
        $serialized = $session->get("SessionSportWebAppPID");
        if (!empty($serialized)) {
            $user_old = new User();
            $user_old->unserialize($serialized);

            $user_new = $em->getRepository('AppBundle:User')->findOneBy(array('id' => $user_old->getId()));
            if (!empty($user_new)) {
                $password_old = $user_old->getPassword();
                $password_new = $user_new->getPassword();
        
                if ($password_old == $password_new) {
                    return $this->redirectToRoute('events_index');
                }
            }
        }
        
        // 1) build the form
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $user_email    = $entityManager->getRepository("AppBundle:User")->findBy(array('email' => $form->get("email")->getData()));
            if(!empty($user_email )){
                //echo $e->getMessage();
                $error = new FormError("User with email ".$form->get("email")->getData()." already exist!");
                $form->get('email')->addError($error);
                return $this->render('registration/register.html.twig',
                ['form' => $form->createView()]);
            }
            $user_name   = $entityManager->getRepository("AppBundle:User")->findBy(array('username' => $form->get("username")->getData()));
            if(!empty($user_name )){
                //echo $e->getMessage();
                $error = new FormError("User with name ".$form->get("username")->getData()." already exist!");
                $form->get('username')->addError($error);
                return $this->render('registration/register.html.twig',
                ['form' => $form->createView()]);
            }
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, trim($user->getPlainPassword()));
            $user->setPassword($password);
            try {            
                // 4) save the User!
                $entityManager->persist($user);
                $entityManager->flush();
                // ... do any other work - like sending them an email, etc
                // maybe set a "flash" success message for the user
                $serialized = $user->serialize();
                $session = $request->getSession();
                // set and get session attributes
                $session->set('SessionSportWebAppPID', $serialized);
                return $this->redirectToRoute('events_index');
            }
            catch( \Doctrine\DBAL\DBALException $e ) {
                //echo $e->getMessage();
                $error = new FormError("Any error occured!");
                $form->addError($error);
                return $this->render('registration/register.html.twig',
                ['form' => $form->createView()]);
            }
        }
        
        return $this->render('registration/register.html.twig',
                ['form' => $form->createView()]);
    }

    
    
    }
    
