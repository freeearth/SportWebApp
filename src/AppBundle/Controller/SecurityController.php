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
use Symfony\Component\HttpFoundation\Response;


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
        $em= $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user)) {
            $user = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (empty($user)) {
            $form = $this->createFormBuilder()
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class)
            ->getForm();
            // 2) handle the submit (will only happen on POST)
            $form->handleRequest($request);
            $errors = $form->getErrors();
            if ($form->isSubmitted() && $form->isValid()) {
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
                    if (!empty($request->get("remote_remember_me"))) {
                        $time_expired = time() + (3600 * 24 * 7);//cookie
                        $domain = $request->getHost();//cookie domain
                        //$response->headers->setCookie(new Cookie('SessionSportWebAppPID', $serialized, $time_expired,"/",$domain ));
                        setcookie("SessionSportWebAppPID_KK",$serialized, $time_expired,"/",$domain);
                    }
                    $user_role = $user -> getRoles();
                    if ($user_role == "ROLE_ADMIN"){
                        return $this->redirectToRoute('admin_users_index');
                    }
                    else {
                        return $this->redirectToRoute('events_index');
                    }
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
        else {
            return $this->redirectToRoute("events_index");
        }
    }
    
    
    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {   
        $session = $request->getSession();
        $session->clear();
        $response = new Response();
        $response->headers->clearCookie('SessionSportWebAppPID_KK');
        $response->send();
        return $this->redirectToRoute('login');
    }    
    
    /**
     * @Route("remote_login", name="remote_login")
     */
    public function remoteLoginAction(UserPasswordEncoderInterface $passwordEncoder, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->checkSession($request);
        if (empty($user)) {
            $user = $em->getRepository('AppBundle:User')->checkAuthCookie($request);
        }
        if (empty($user)) {
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
                if (!empty($request->get("remote_remember_me"))) {
                    $time_expired = time() + (3600 * 24 * 7);//cookie
                    $domain = $request->getHost();//cookie domain
                    //$response->headers->setCookie(new Cookie('SessionSportWebAppPID', $serialized, $time_expired,"/",$domain ));
                    setcookie("SessionSportWebAppPID_KK",$serialized, $time_expired,"/",$domain);
                }
                return $this->json([
                    'error' =>array(),
                ]);
            }
            else {
                return $this->json([
                    'error' =>array('value' => "Wrong password!"),
                ]);
            }
        }
        else {
            return $this->json([
                    'error' =>array('value' => "You are logged in!"),
                ]);
        }
    }

}
