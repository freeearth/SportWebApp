<?php

namespace AppBundle\Controller;


use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Cookie;
use RecursiveArrayIterator;

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
       
        $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user_new)) {
            $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (!empty($user_new)) {
            return $this->redirectToRoute('events_index');
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
                if (!empty($request->get("remote_remember_me"))) {
                    $time_expired = time() + (3600 * 24 * 7);//cookie
                    $domain = $request->getHost();//cookie domain
                    //$response->headers->setCookie(new Cookie('SessionSportWebAppPID', $serialized, $time_expired,"/",$domain ));
                    setcookie("SessionSportWebAppPID_KK",$serialized, $time_expired,"/",$domain);
                }
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
    
    
    
    /**
     * @Route("/remote/", name="remote_register")
     */
    public function remoteRegisterAction(Request $request, UserPasswordEncoderInterface $passwordEncoder)
    {
            $em = $this->getDoctrine()->getManager();
            
            
            $user_new = $em->getRepository('AppBundle:User')->checkSession($request);
            if (empty($user_new)) {
                $user_new = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
            }
            if (!empty($user_new)) {
                return $this->json([
                    'error' =>array('value' => "You are logged in! <br\> Please logout and try again!"),
                ]);
            }
            $params = json_decode($request->request->all()['regiter_remote_form_data']);
            $params=new RecursiveArrayIterator($params);
            foreach ($params as $key=>$value) {
                switch ($value->name) {
                    case "remote_email":
                        $remote_email=trim($value->value);
                    break;
                    case "remote_password[first]":
                        $password_1_planned=trim($value->value);
                    break;
                    case "remote_password[second]":
                        $password_2_planned=trim($value->value);
                    break;
                    case "remote_username":
                        $user_name=trim($value->value);
                    break;
                    case "remote_roles":
                        $user_roles=$value->value;
                    break;
                    case "remote_remember_me":
                        $user_remember_me = $value->value;
                    default:
                    break;
                }
            }
            
            $user_email    = $em->getRepository("AppBundle:User")->findBy(array('email' => $remote_email));
            if(!empty($user_email )){
                //echo $e->getMessage();
                return $this->json([
                    'error' =>array('value' => "User with email ".$remote_email." already exist!"),
                ]);
            }
            if (empty($password_1_planned) || strlen($password_1_planned)<4) {
                //echo $e->getMessage();
                return $this->json([
                    'error' =>array('value' => "Wrong password!")
                ]);
            }
            if ($password_1_planned !== $password_2_planned) {
                //echo $e->getMessage();
                return $this->json([
                    'error' =>array('value' => "Your entered passwords are not the same!<br\> Please enter it correctly and try.")
                ]);
            }
            
            $user = new User();
            
            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, trim($password_1_planned));
            
            if (!in_array($user_roles, array('ROLE_PUBLISHER','ROLE_USER'))) {
                //echo $e->getMessage();
                return $this->json([
                    'error' =>array('value' => "Incorrect role!")
                ]);
            }
            $user->setPassword($password);
            $user->setEmail($remote_email);
            $user->setUsername($user_name);
            $user->setRoles($user_roles);
            try {            
                // 4) save the User!
                $em->persist($user);
                $em->flush();
                $serialized = $user->serialize();
                $session = $request->getSession();
                // set and get session attributes
                $session->set('SessionSportWebAppPID', $serialized);
                if (!empty($user_remember_me)) {
                    $time_expired = time() + (3600 * 24 * 7);//cookie
                    $domain = $request->getHost();//cookie domain
                    //$response->headers->setCookie(new Cookie('SessionSportWebAppPID', $serialized, $time_expired,"/",$domain ));
                    setcookie("SessionSportWebAppPID_KK",$serialized, $time_expired,"/",$domain);
                    
                    
                }
                return $this->json([
                    'error' =>array(),
                ]);
            }
            catch( \Doctrine\DBAL\DBALException $e ) {
                //echo $e->getMessage();
                return $this->json([
                    'error' =>'Database error occured!',
                ]);
            }
        
    }
    
    
    }
    
