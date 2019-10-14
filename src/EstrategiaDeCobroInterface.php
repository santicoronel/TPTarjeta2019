<?php

namespace TrabajoTarjeta;

interface EstrategiaDeCobroInterface {

    public function tipo ();
    public function valorPasaje($valorBase) : float;
    public function tienePermitidoViajar($tiempoActual);
    public function registrarViaje($tiempoActual);
}
