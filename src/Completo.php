<?php

namespace TrabajoTarjeta;

class Completo extends Tarjeta implements TarjetaInterface {

    /**
     * Redefinimos el valor y tipo del pasaje de la clase.
     */
    protected $pasaje = 0.0;
    protected $tipo = "Completo";

}
