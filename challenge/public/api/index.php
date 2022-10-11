<?php

use DI\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

// DIC
$container = new Container();
$container->set('redisClient', function () {
    return new Predis\Client(['host' => 'redis']);
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->setBasePath('/api');
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(true, true, true);


/** TODO
 * Here you can write your own API endpoints.
 * You can use Redis and/or cookies for data persistence. */

 
 //Soft login for storing initial information of User 
$app->post("/login/{name}",function(Request $request, Response $response, $args){
    
    $loggedName = $args["name"];
    $redisClient = $this->get("redisClient");
    $userData = $redisClient->hgetall($loggedName);
    $reqBody = $request->getParsedBody();
    $timeStarted = $reqBody["timeStart"];
    
     if($userData["userName"]){
        $response->getBody()->write(json_encode([$loggedName=>$userData,"salutation"=>"Hello $loggedName"], JSON_THROW_ON_ERROR));
    
    }else{
        $redisClient->hmset($loggedName,
        "userName", $loggedName,
        "startReadTime",$timeStarted,"currentReadTime","00:00:00","endReadTime","00:00:00");
        
        $response->getBody()->write(json_encode(["response"=>"Thank you for logging in"]));
        
    };
    return $response->withHeader("Content-Type","application/json");
});
// Storing info for session time in case of not full read
$app->post("/sessionOver/{name}",function(Request $request,Response $response, $args){
    
});
// Providing details for startTime, endTime, overallTimeSpent
$app->get("/finnishedRead/{name}",function(Request $request,Response $response,$args){
    
});



/*Find below an example of a GET endpoint that uses redis to temporarily store a name,
 and cookies to keep track of an event date and time. */


 $app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    // Redis usage example:
    /** @var \Predis\Client $redisClient */
    $redisClient = $this->get('redisClient');
    $oldName = $redisClient->get('name');
    if (is_string($oldName)) {
        $name = $oldName;
    } else {
        $redisClient->set('name', $args['name'], 'EX', 10);
        $name = $args['name'];
    }

    // Setting a cookie example:
    $cookieValue = '';
    if (empty($_COOKIE["FirstSalutationTime"])) {
        $cookieName = "FirstSalutationTime";
        $cookieValue = (string)time();
        $expires = time() + 60 * 60 * 24 * 30; // 30 days.
        setcookie($cookieName, $cookieValue, $expires, '/');
    }

    // Response example:
    $response->getBody()->write(json_encode([
        'name' => $name,
        'salutation' => "Hello, $name!",
        'first_salutation_time' => $_COOKIE["FirstSalutationTime"] ?? $cookieValue,
    ], JSON_THROW_ON_ERROR));

    return $response->withHeader('Content-Type', 'application/json');
});



$app->run();
