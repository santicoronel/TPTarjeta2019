<?php

namespace TrabajoTarjeta;

interface BoletoInterface {

    /**
     * Devuelve el valor del boleto.
     *
     * @return int
     *     Valor del boleto
     */
    public function obtenerValor();

    /**
     * Devuelve un objeto que respresenta el colectivo donde se viajó.
     *
     * @return ColectivoInterface
     *     Colectivo donde se viajó
     */
    public function obtenerColectivo();

    /**
     * Devuelve los datos del boleto emitido si es 
     * 
     * @return string|NULL
     */
    public function obtenerDescripcion();
}
