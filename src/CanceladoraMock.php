<?php

namespace TrabajoTarjeta;

class CanceladoraMock implements CanceladoraInterface {

    private $hacerPrint = false;
    private $hacerLog = false;

    private $log = [];
    private $mensaje = null;

    private $formateador;

    public function __construct () {
        $this->formateador = new Formateador;
    }

    public function mostrarDatos (array $datos) : string {
        $mensaje = $this->formateador->formatear($datos);
        $this->mostrarMensaje($mensaje);
        return $mensaje;
    }

    public function mostrarMensaje (string $mensaje) {
        $this->mensaje = $mensaje;

        if($this->hacerPrint){
            echo $mensaje;
        }

        if($this->hacerLog){
            $this->log[] = $mensaje;
        }
    }

    public function invertirLog () {
        $this->hacerLog = !$this->hacerLog;
    }

    public function asignarLog ($hacerLog) {
        $this->hacerLog = $hacerLog;
    }

    public function invertirPrint () {
        $this->hacerPrint = !$this->hacerPrint;
    }

    public function asignarPrint ($hacerPrint) {
        $this->hacerPrint = $hacerPrint;
    }
}
