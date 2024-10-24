<?php
/*
 Aunque no he revisado su contenido completo, 
 este archivo parece manejar el registro de 
 eventos y errores en la aplicación, lo que es 
 una buena práctica para monitorear el estado 
 de la aplicación.
*/
class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }
}