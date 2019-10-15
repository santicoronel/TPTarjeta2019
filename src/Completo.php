<?php

namespace TrabajoTarjeta;

/**
 * Redefinimos el valor y tipo del pasaje de la clase.
 */
class Completo extends Tarjeta implements TarjetaInterface {

    protected $pasaje = 0.0;
    protected $tipo = "Completo";

}
