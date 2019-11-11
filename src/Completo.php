<?php

namespace TrabajoTarjeta;

/**
 */
class Completo implements EstrategiaDeCobroInterface {

    public function tipo () : string {
        return "Completo";
    }

    public function valorPasaje(float $valorBase) : float {
        return 0;
    }

    public function tienePermitidoViajar(int $tiempoActual) : bool {
        return true;
    }

    public function registrarViaje(int $tiempoActual) : void {
    }

}
