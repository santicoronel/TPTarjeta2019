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
    public function recargar($monto);

    /**
     * Devuelve el valor de un pasaje. Ejemplo: 16.8
     *
     * @return float
     *    Valor de pasaje
     */
    public function valorPasaje();

    /**
     * Suma 1 a la cantidad de viajes plus hechos
     */
    public function viajePlus();

    /**
     * Devuelve el saldo que le queda a la tarjeta. Ejemplo: 37.9
     *
     * @return float
     *    Saldo
     */
    public function obtenerSaldo();

    /**
     * Descuenta el boleto del saldo de la tarjeta. Ejemplo: 'AbonaPlus'
     *
     * @param ColectivoInterface $colectivo
     *
     * @return string|bool
     *    El tipo de pago o FALSE si el saldo es insuficiente
     */
    public function descontarSaldo(ColectivoInterface $colectivo);

    /**
     * Se abonan los viajes plus en función a los que tiene la tarjeta. Ejemplo: 33.6
     *
     * @return float
     *    Valor total de viajes plus a pagar
     */
    public function abonaPlus();

    /**
     * Devuelve el valor del boleto. Ejemplo 18.45
     *
     * @return float
     *    Valor del boleto
     */
    public function valorDelBoleto();

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
     * Devuelve la hora en la que se abonó un pasaje. Ejemplo: 543
     *
     * @return int
     *    Hora en la que se efectuó el pago del boleto
     */
    public function obtenerFecha();

    /**
     * Retorna el id único de la tarjeta. Ejemplo: 3
     *
     * @return int
     *    Número de ID de la tarjeta
     */
    public function obtenerId();

    /**
     * Llama a una función del tiempo que hace al día feriado o no, dependiendo su valor anterior
     */
    public function cFeriado();

    /**
     * Llama a una función del tiempo que indica si un día es feriado o no
     *
     * @return bool
     *    TRUE si el día es feriado o FALSE si no lo es
     */
    public function eFeriado();


}
