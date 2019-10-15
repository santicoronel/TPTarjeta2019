<?php

namespace TrabajoTarjeta;

interface EstrategiaDeCobroInterface {

    /**
     * @return string El nombre de la estrategia de cobro
     */
    public function tipo () : string;

    /**
     * @return float El valor del pasaje segun la estrategia de cobro
     */
    public function valorPasaje(float $valorBase) : float;

    /**
     * @return bool Si se cumplen los requisitos de esta estrategia de cobro
     */
    public function tienePermitidoViajar(int $tiempoActual) : bool;

    /**
     * Se notifica de un viaje exitoso, y actualiza los datos de la estrategia
     * para el proximo viaje
     *
     * @param int $tiempoActual
     *     El momento en el cual se realiza el viaje, en segundos desde 1/1/1970
     */
    public function registrarViaje(int $tiempoActual) : void;
}
