<?php
    include_once "./models/Cuenta.php";
    /*
    $rutaBancoJson = './ArchivosJson/banco.json';
    $nombreImagen = $_FILES['imagen']['tmp_name'];
    $directorioImagenesAlta = "ImagenesDeCuentas/2023/";
    // echo "alta cuenta";
    // B- CuentaAlta.php: (por POST) se ingresa Nombre y Apellido, Tipo Documento, Nro. Documento, Email, Tipo de Cuenta (CA – caja de ahorro o CC – cuenta corriente), Moneda ($ o U$S), Saldo Inicial (0 por defecto).
    // Se guardan los datos en el archivo $rutaBancoJson, tomando un id autoincremental de 6 dígitos como Nro. de Cuenta (emulado). 

    //Sí el nombre y tipo ya existen , se actualiza el precio y se suma al stock existente.
    // completar el alta con imagen/foto del usuario/cliente, guardando la imagen con Nro y Tipo de Cuenta (ej.: NNNNNNTT) como identificación en la carpeta: /ImagenesDeCuentas/2023.

    if(Cuenta::ValidarParametrosPost()){
        $tipoCuenta= null;
        $moneda = null;
        $nroCuentaNueva = null;
        if(Cuenta::ValidarTipoCuenta($_POST["tipoCuenta"])){
            $tipoCuenta = $_POST["tipoCuenta"];
        }else{
            echo "tipo de cuenta incorrecto";
        }
        if(Cuenta::ValidarMoneda($_POST["moneda"])){
            $moneda = $_POST["moneda"];
        }else{
            echo "moneda ingresada incorrecta";
        }
        if(!Cuenta::ValidarTipoCuentaConMoneda($tipoCuenta,$moneda)){
            echo "<br>El tipo de cuenta no coincide con la moneda ingresada";
        }else{
        if($moneda !== null && $tipoCuenta !== null){
            echo "<br>ALTA DE CUENTA:<br>";
            if(isset($_POST["saldoInicial"])){
                $saldoInicial = $_POST["saldoInicial"];
            }else{
                $saldoInicial = 0;
            }
            #Asignar nroCuenta unico
            if(isset($_POST['nroCuenta'])){
                if(!Cuenta::ValidarNroCuentaEnJson($_POST['nroCuenta'], $rutaBancoJson)){
                    $nroCuentaNueva = $_POST['nroCuenta'];
                }else{
                    echo "<br>El nro de cuenta ya existe";
                }
            }else{
                do{
                    $nroCuentaNueva = rand(100000, 999999);
                } while (Cuenta::ValidarNroCuentaEnJson($nroCuentaNueva, $rutaBancoJson));
                
            }

            $nroDocumento = $_POST["nroDocumento"];
            $nombre = $_POST["nombre"];
            $apellido = $_POST["apellido"];

            if($nroCuentaNueva !== null && Cuenta::ValidarUsuarioEnJson($nroDocumento,$nombre,$apellido,$rutaBancoJson)){
                $cuentaNueva = new Cuenta($nombre, $apellido, $_POST["tipoDocumento"],$nroDocumento,$_POST["email"], $_POST["tipoCuenta"], $_POST["moneda"], $saldoInicial,$nroCuentaNueva,"activa");
                
                $cuentaJson = Cuenta::ValidarUsuarioYTipoEnJson($cuentaNueva->GetNombre(),$cuentaNueva->GetApellido(),$cuentaNueva->GetNroDocumento(),$cuentaNueva->GetTipoCuenta(),$cuentaNueva->GetMoneda(),$rutaBancoJson);
                if($cuentaJson !== null){
                    echo "<br>existe la cuenta <br>" ;
                    if($cuentaJson->ActualizarSaldoCuentaJson($saldoInicial,$rutaBancoJson)){
                        echo "<br>Saldo Actualizando ";
                    }            
                }else if(Cuenta::GuardarCuentaJSON($cuentaNueva,$rutaBancoJson)){
                    echo "Cuenta guardada en json <br> " . $cuentaNueva->__toString();

                    if($cuentaNueva->GuardarImagen($nombreImagen,$directorioImagenesAlta)){
                        echo "<br>El archivo ha sido cargado correctamente en ". $directorioImagenesAlta ."<br>";
                    }else{
                        echo "<br>Ocurrió algún error al subir el fichero. No pudo guardarse.<br>";
                    }

                }else{
                    echo "Error de sistema";
                }
            }else{
                echo "El dni ya existe pero el nombre y apellido no coinciden!";
            }
        }
        }
    }else{
        echo "faltan parametros";
    }
*/

?>