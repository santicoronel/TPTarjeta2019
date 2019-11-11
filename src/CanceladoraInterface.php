<?php

namespace TrabajoTarjeta;

interface CanceladoraInterface {
    /**
     * Toma datos de viaje y los "muestra". devuelve los datos formateados
     */
    public function mostrarDatos (array $datos) : string;
}
