<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroNormal implements EstrategiaDeCobroInterface {

    public function tipo (){
        return "Normal";
    }

    public function valorPasaje($valorBase) : float {
        return $valorBase;
    }

    public function registrarViaje($tiempoActual) {
    }

    public function tienePermitidoViajar($tiempoActual){
        return TRUE;
    }
}
