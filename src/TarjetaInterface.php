<?php

namespace TrabajoTarjeta;

interface TarjetaInterface {

    /**
     * Recarga una tarjeta con un cierto valor de dinero.
     *
     * @param int $monto
     *    Cantidad de dinero a recargar
     *
     * @return bool
     *    TRUE si el monto a cargar es válido, o FALSE en caso de que no lo sea
     *
     */
    public function recargar($monto) : bool;

    /**
     * Devuelve el valor de un pasaje. Ejemplo: 16.8
     *
     * @return float
     *    Valor de pasaje
     */
    public function valorPasaje() : float;

    /**
     * Devuelve el saldo que le queda a la tarjeta. Ejemplo: 37.9
     *
     * @return float
     *    Saldo
     */
    public function obtenerSaldo() : float;

    /**
     * Devuelve el valor del boleto. Ejemplo 18.45
     *
     * @return float
     *    Valor del boleto
     */
    public function valorDelBoleto() : float;

    /**
     * Devuelve la cantidad de viajes plus que se van a pagar en un viaje. Ejemplo: 1
     *
     * @return int
     *    Cantidad de plus a abonar
     */
    public function plusAPagar();

    /**
     * Devuelve la cantidad de viajes plus que tiene la tarjeta. Ejemplo: 2
     *
     * @return int
     *    Cantidad de plus en tarjeta
     */
    public function verPlus();

    /**
     * Devuelve el tipo de la tarjeta que se está usando. Ejemplo: "Normal"
     *
     * @return string
     *    Tipo de tarjeta
     */
    public function obtenerTipo();

    /**
     * Retorna el id único de la tarjeta. Ejemplo: 3
     *
     * @return int
     *    Número de ID de la tarjeta
     */
    public function obtenerId();
}
