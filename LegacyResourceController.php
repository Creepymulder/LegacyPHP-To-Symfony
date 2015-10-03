<?php

namespace AppBundle\Controller\Legacy;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;

class LegacyResourceController extends Controller

{

   
	/**
	 * @Route("/", defaults={"controller" = "index.php"})
	 * @Route("/{controller}"})
	 */
    public function getLegacyResourceAction($controller, Request $request)

    {
   	                		 
		 //Path to your legacy application that you have uploaded into a folder called "legacy" in the web folder
		 $path_to_legacy_code = "yourapp/web/legacy";
		 
		 
		//Grab the GET parameters, if any
		$originalController = $request->getPathInfo();
        $originalQueryString = $request->getQueryString();
		$url = "{$path_to_legacy_code}{$originalController}?{$originalQueryString}";

        //Open a CURL connexion
        $ch = curl_init();

		//CUrl will query the previously constructed url with the GET parameters
        curl_setopt($ch, CURLOPT_URL, $url);
        
		//We want the result to be stored in a variable
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		//We need to follow any "header('location:xx')" that may exist in the legacy app
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

		//We don't need the header for prod, but that can be useful for debugging
        curl_setopt($ch, CURLOPT_HEADER, false);

		//Logging the connexion is also useful for debugging
        //curl_setopt($ch, CURLOPT_VERBOSE, 1);
		//$stderr = fopen("{$this->container->getParameter('kernel.root_dir')}/logs/curl.txt", "a");
        //curl_setopt($ch, CURLOPT_STDERR, $stderr);
        
		//We set the HTTP_USER_AGENT on the CURL request
        curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        
		//May prevent infinite looping and debugging headeache
        curl_setopt($ch,  CURLOPT_MAXREDIRS, 5);
               

        $hasFile = false;
        if ($request->getMethod() == 'POST') { //Simulate a POST request

			//Grabbing the POST parameters
            $postParameters = $request->request->all();
            $postParametersString = '';
            $postParametersArray = array();

            foreach ($postParameters as $key => $value) {
            	if(is_array($value)){
            		foreach($value as $val){
            			$postParametersString .= $key . '[]=' . $val . '&';
            		}		
            	}else{
            		$postParametersString .= $key . '=' . $value . '&';
            	}
            	

            	$postParametersArray[$key] = $value;
            
            }
            
            $postParametersString = rtrim($postParametersString, '&');
            
            //Handling file upload on the legacy apps
            $file = $request->files;
                                                  
            $countfile = 0;
            foreach ($file as $parametre => $fileuploaded) {
            	if($fileuploaded == ""){
            		continue;
            	}
            	if(!is_object($fileuploaded)){
            		continue;
            	}
            	$hasFile = true;
            	$countfile++;
            	$url = $fileuploaded->getRealPath();            	
            	$fileobj = new \CURLFile($url);
  
            	//Saving the uploaded file
            	$postParametersArray[$parametre] = $fileobj;
            	
            }

			//We tell CURL we have POST data to send
            curl_setopt($ch, CURLOPT_POST, count($postParametersArray));
                       
		    //If we're POST'ing a file, we simply pass the $postParametersArray to cURL
            if($hasFile == true){
            	curl_setopt($ch, CURLOPT_POSTFIELDS, $postParametersArray);
            }else{ //If we do not have any files, we use the http_build_query function, that allow arrays in POST data
            	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postParametersArray));
            } 
        } 
        
		//Keep a unique session with the legacy app
        curl_setopt($ch, CURLOPT_COOKIE,session_name().'='.session_id());
        
    
		//We query the URl with CURL
        $result = curl_exec($ch);

        //Get Mime data
        $mime = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                
 
		//Useful to display errors
        if (false === $result) {
        	echo curl_error($ch);
        	exit;
        }
        
              
		//Close the CURL connexion
        curl_close($ch);
        
		//Create a Symfony Response Object
        $Response = new Response($result);
          
    
        //If the legacy app output file on the browser
        if($mime == "text/csv; charset=UTF-8"){
        	$Response->headers->set('Content-Type', 'application/force-download');
        	$fichier = "file.csv";
        	$Response->headers->set('Content-disposition', 'filename='. $fichier);
        }
        
        if($mime == "application/pdf"){
        	$Response->headers->set('Content-Type', 'application/pdf');
        	$fichier = "file.pdf";
        	$Response->headers->set('Content-disposition', 'filename='. $fichier);
        }
        

        $Response->headers->set('Content-Type', $mime);
       
        return $Response;
    }
    
    
}