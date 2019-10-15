<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroNormal implements EstrategiaDeCobroInterface {

    public function tipo () : string {
        return "Normal";
    }

    public function valorPasaje(float $valorBase) : float {
        return $valorBase;
    }

    public function registrarViaje(int $tiempoActual) : void {
    }

    public function tienePermitidoViajar(int $tiempoActual) : bool {
        return TRUE;
    }
}
