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

$app->options('/{routes:.+}', function ($request, $response, $args) {
    return $response;
});

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

/** TODO
 * Here you can write your own API endpoints.
 * You can use Redis and/or cookies for data persistence. */
 
 //CORS setup 
 $app->add(function ($request, $handler) {
    $response = $handler->handle($request);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*') 
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
            ->withHeader('Access-Control-Allow-Methods', 'GET');
});
 
 
 //Format date based on timezone 
function formatDateTime($timestamp, $zone="Asia/Singapore", $format = "H:i:s d/m/Y") {
    $date = new \DateTime($zone);
    $date->setTimeZone(new \DateTimeZone($zone));
    $date->setTimeStamp($timestamp);
    $result = $date->format($format);
    return $result;
}

 //Soft login for storing initial info of User 
 $app->get("/login/{name}",function(Request $request, Response $response, $args){
     $redisClient = $this->get("redisClient");
     $timeStarted = time();
     $loggedName = $args["name"];
     $userData = $redisClient->hGetAll($loggedName);
    
    //Check for previous logins for current user or create new entry if none
    if($userData["userName"]){
        $redisClient->hmSet($loggedName,"sessionStart","$timeStarted");
        $response->getBody()->write(json_encode([$loggedName=>$userData,"salutation"=>"Welcome back $loggedName"], JSON_THROW_ON_ERROR));
    }else{
        //Initialize user data in Redis if none present
        $redisClient->hmSet($loggedName,
        "userName", $loggedName,
        "startReadTime",formatDateTime($timeStarted),"sessionStart","$timeStarted","overallTimeSpent","0","endReadTime","00:00:00");
        $userData = $redisClient->hGetAll($loggedName);
        
        $response->getBody()->write(json_encode([
            $loggedName=>$userData,
            "salutation"=>"Thank you for logging in"],JSON_THROW_ON_ERROR));
    };
    return $response;
    
});

// Storing info for session time in case of not full read
$app->get("/endSession/{name}",function(Request $request,Response $response, $args){
    $redisClient = $this->get("redisClient");
    $loggedName = $args["name"];
    
    //Possible middleware to avoid redundance
    $now = time(); //current timestamp
    $currentSessionStart = (int)$redisClient->hGet($loggedName,"sessionStart");
    $previousOverallTime = (int)$redisClient->hGet($loggedName,"overallTimeSpent");
    $currentSessionTime = ($now-$currentSessionStart);
    $newOverallTimeSpent= $previousOverallTime+$currentSessionTime;
    
    //<-
    
    $redisClient->hSet($loggedName,"overallTimeSpent","$newOverallTimeSpent");
    
    $response->getBody()->write(json_encode([
    "status"=>"200 OK"        
    ]));
    return $response;
});

// Providing details for startTime, endTime, overallTimeSpent
$app->get("/finishRead/{name}",function(Request $request,Response $response,$args){
    $redisClient = $this->get("redisClient");
    $loggedName = $args["name"];
    $endReadTime = time();
    
    //Possible middleware to avoid redundance
    $now = time(); //current timestamp
    $currentSessionStart = (int)$redisClient->hGet($loggedName,"sessionStart");
    $previousOverallTime = (int)$redisClient->hGet($loggedName,"overallTimeSpent");
    $currentSessionTime = ($now-$currentSessionStart) ;
    $newOverallTimeSpent= $previousOverallTime+$currentSessionTime;
    $formatOverallTime=date("H:i:s",$newOverallTimeSpent);
    //<-
    
    $redisClient->hSet($loggedName, "endReadTime", formatDateTime($endReadTime),"overallTimeSpent","$formatOverallTime");

    $userData = $redisClient->hgetall($loggedName);
    
    $response->getBody()->write(json_encode([$loggedName=>$userData]));
    
    return $response;
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
    
    return $response;
});



$app->run();
