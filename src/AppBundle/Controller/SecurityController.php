<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Session\Session;


//use Symfony\Component\Security\Core\User;
//use Symfony\Component\Security\Core\Encoder\EncoderFactory;
//use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;



class SecurityController extends Controller
{
    
    
    
    /**
     * @Route("login", name="login")
     */
    public function loginAction(UserPasswordEncoderInterface $passwordEncoder, Request $request)
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
        
        
        $form = $this->createFormBuilder()
        ->add('email', EmailType::class)
        ->add('password', PasswordType::class)
        ->getForm();
        
        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em= $this->getDoctrine()->getManager();
            $email = $form->get('email')->getData();
            $user  = $em->getRepository("AppBundle:User")->findOneBy(array('email' => $email));
            if (empty($user)) {
                $error = new FormError("There is no user with email".$email."!");
                $form->addError($error);
                return $this->render('security/login.html.twig',
                ['form' => $form->createView()]);
            }
            
            /*
            $defaultEncoder = new MessageDigestPasswordEncoder('sha512', true, 5000);
            $weakEncoder = new MessageDigestPasswordEncoder('md5', true, 1);

            $encoders = [
                User::class       => $defaultEncoder,
                LegacyUser::class => $weakEncoder,
                // ...
            ];
            $encoderFactory = new EncoderFactory($encoders);
            $encoder = $encoderFactory->getEncoder($user);

            // returns $weakEncoder (see above)
            $encodedPassword = $encoder->encodePassword($plainPassword, $user->getSalt());
    
             * 
             * 
             * 
             *              */
            
            $password = $form->get("password")->getData();
            $validPassword = $passwordEncoder->isPasswordValid($user, $password);
            if ($validPassword == true) {
                $serialized = $user->serialize();
                $session = $request->getSession();
                // set and get session attributes
                $session->set('SessionSportWebAppPID', $serialized);
                return $this->redirectToRoute('events_index');
            }
            else {
                $error = new FormError("Wrong password!");
                $form->addError($error);
                return $this->render('security/login.html.twig',
                ['form' => $form->createView()]);
            }
        }
        return $this->render('security/login.html.twig',
                ['form' => $form->createView()]);
    }
    
    
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {   
        $session = $request->getSession();
        $session->clear();
        return $this->redirectToRoute('login');
    }    
    
    /**
     * @Route("remote_login", name="remote_login")
     */
    public function remoteLoginAction(UserPasswordEncoderInterface $passwordEncoder, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $email  = $request->request->get('log');
        $user  = $em->getRepository("AppBundle:User")->findOneBy(array('email' => json_decode($email)));
        if (empty($user)) {
            return $this->json([
                'error' =>array('value' => "There is no user with email ".$email."!"),
            ]);
        }
        $password= $request->request->get('pas');
        $validPassword = $passwordEncoder->isPasswordValid($user, json_decode($password));
        if ($validPassword == true) {
            $serialized = $user->serialize();
            $session = $request->getSession();
            // set and get session attributes
            $session->set('SessionSportWebAppPID', $serialized);
            return $this->json([
                'error' =>array(),
                'SessionSportWebAppPID' => $serialized,
            ]);
        }
        else {
            return $this->json([
                'error' =>array('value' => "Wrong password!"),
            ]);
        }
    }

}
