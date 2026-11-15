<?php
declare(strict_types=1);
use App\Core\{HttpException,Request,Response,Router,ValidationException};
require_once dirname(__DIR__).'/app/bootstrap.php';
header('Access-Control-Allow-Origin: '.(string)env('CORS_ALLOWED_ORIGIN','*'));
header('Access-Control-Allow-Headers: Authorization, Content-Type, Accept');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
if(($_SERVER['REQUEST_METHOD']??'GET')==='OPTIONS'){http_response_code(204);exit;}
$request=new Request();$router=new Router();require base_path('app/routes.php');
try{$router->dispatch($request);}catch(ValidationException $e){Response::json(['message'=>$e->getMessage(),'errors'=>$e->errors],422);}catch(HttpException $e){Response::json(['message'=>$e->getMessage()],$e->status);}catch(Throwable $e){$message=(bool)env('APP_DEBUG',false)?$e->getMessage():'Erro interno do servidor.';Response::json(['message'=>$message],500);}
