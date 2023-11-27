<?php
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class AuthFechaMW
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $queryParams = $request->getQueryParams();
        $fechas = ['fecha', 'fechaInicio', 'fechaFinal'];

        foreach ($fechas as $fechaParam) {
            if (isset($queryParams[$fechaParam])) {
                $fecha = $queryParams[$fechaParam];
                $fechaValida = DateTime::createFromFormat('d-m-Y', $fecha);

                if (!$fechaValida || $fechaValida->format('d-m-Y') !== $fecha) {
                    $response = new Response();
                    $response->getBody()->write(json_encode(["error" => "Formato de fecha invalida. Usar el formato d-m-Y y valores validos"]));
                }else{
                    $response =$handler->handle($request);
                }
            }
        }

        return $response;
    }
}
