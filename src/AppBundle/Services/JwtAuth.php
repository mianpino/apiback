<?php
namespace AppBundle\Services;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\RequestStack;
use Firebase\JWT\JWT;

class JwtAuth {
	public $manager;
	public $container;
	public $key;
	public $requestStack;
	public $request;
	
	public function __construct($manager, Container $container, RequestStack $requestStack) {
		$this->manager = $manager;
		$this->key = "clave-secreta";
		$this->container = $container;
		$this->requestStack = $requestStack;
		$this->request = $this->requestStack->getCurrentRequest() ;
	}
	
	public function signup($email, $password, $getHash = NULL){
		$key = $this->key;
		
		$user = $this->manager->getRepository('BackendBundle:User')->findOneBy(
					array(
						"email" => $email,
						"password" => $password
					)
				);
		
		$signup = false;
		if(is_object($user)){
			$signup = true;
		}
		
		if($signup == true){
			$token = array(
				"sub" => $user->getId(),
				"email" => $user->getEmail(),
				"name"	=> $user->getName(),
				"surname"	=> $user->getSurname(),
				"password"	=> $user->getPassword(),
				"image"	=> $this->request->getSchemeAndHttpHost()  . '/uploads/users/' .$user->getImage(),
				"iat" => time(),
				"exp" => time() + (7 * 24 * 60 * 60)
			);
			
			$jwt = JWT::encode($token, $key, 'HS256');
			$decoded = JWT::decode($jwt, $key, array('HS256'));
			
			if($getHash != null){
				return $jwt;
			}else{
				return $decoded;
			}
			
		}else{
			return array("status" => "error", "data" => "Login failed !!");
		}
	}
	
	public function checkToken($jwt, $getIdentity = false){
		$key = $this->key;
		$auth = false;
		
		try{
			$decoded = JWT::decode($jwt, $key, array('HS256'));
			
		}catch(\UnexpectedValueException $e){
			$auth = false;
		}catch(\DomainException $e){
			$auth = false;
		}
		
		if(isset($decoded->sub)){
			$auth = true;
		}else{
			$auth = false;
		}
		
		if($getIdentity == true){
			return $decoded;
		}else{
			return $auth;
		}
	}
	
}
