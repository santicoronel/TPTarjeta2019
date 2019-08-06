<?php

namespace TrabajoTarjeta;

class TiempoFalso implements TiempoInterface {

    protected $tiempo;
    protected $feriado;

    public function __construct($inicio = 0, $fer = FALSE) {
        $this->tiempo = $inicio;
        $this->feriado = $fer;
    }

    /**
     * Avanza cierta cantidad de segundos
     * @param int
     *     Tiempo a avanzar
     */
    public function avanzar($segundos) {
        $this->tiempo += $segundos;
    }

    /**
     * Devuelve el tiempo falso
     * 
     * @return int
     *     Tiempo falso
     */
    public function time() {
        return $this->tiempo;
    }

    /**
     * Devuelve si es feriado o no
     * 
     * @return bool
     *     TRUE si es feriado, FALSE en caso contrario
     */
    public function esFeriado() {
        return $this->feriado;
    }

    /**
     * Cambia el valor del campo 'feriado'
     */
    public function cambiarFeriado() {
        if ($this->feriado == FALSE) {
            $this->feriado = TRUE;
        } else {
            $this->feriado = FALSE;
        }
    }
}