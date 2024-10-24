<?php
/*
 Define una interfaz para la API, con métodos como 
 CargarUno, TraerUno, TraerTodos, ModificarUno y 
 BorrarUno. 
 
 Estos métodos deben ser implementados por cualquier 
 clase que utilice esta interfaz (como UsuarioController).
*/
interface IApiUsable
{
	public function TraerUno($request, $response, $args);
	public function TraerTodos($request, $response, $args);
	public function CargarUno($request, $response, $args);
	public function BorrarUno($request, $response, $args);
	public function ModificarUno($request, $response, $args);
}
