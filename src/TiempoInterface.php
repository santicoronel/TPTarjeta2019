<?php

namespace TrabajoTarjeta;

interface TiempoInterface {

    /**
     * Devuelve el tiempo actual
     *
     * @return int
     *     Tiempo actual
     */
    public function time();

    /**
     * Devuelve si es feriado o no
     *
     * @return bool
     *     TRUE si es feriado, FALSE en caso contrario
     */
    public function esFeriado();

    /**
     * Cambia el valor del campo 'feriado'
     */
    public function cambiarFeriado();
}
