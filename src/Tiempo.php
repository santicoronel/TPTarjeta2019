<?php

namespace TrabajoTarjeta;

class Tiempo implements TiempoInterface {

    protected $feriado;

    public function __construct($fer = FALSE) {
        $this->feriado = $fer; 
    }
    /**
     * Devuelve el tiempo actual
     * 
     * @return int
     */
    public function time() {
        return time();
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