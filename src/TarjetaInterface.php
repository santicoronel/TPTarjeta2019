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
     * Suma 1 a la cantidad de viajes plus hechos
     */
    public function viajePlus();

    /**
     * Devuelve el saldo que le queda a la tarjeta. Ejemplo: 37.9
     *
     * @return float
     *    Saldo
     */
    public function obtenerSaldo() : float;

    /**
     * Descuenta el boleto del saldo de la tarjeta. Ejemplo: 'AbonaPlus'
     *
     * @param ColectivoInterface $colectivo
     *
     * @return array:[tipo:string, costo:float, tiempo:int, plusPagados:int] | null
     *    Informacion sobre el viaje o null si no es posible viajar
     */
    public function intentarViaje(ColectivoInterface $colectivo) : ?array;

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

    /**
     * Llama a una función del tiempo que indica si un día es feriado o no
     *
     * @return bool
     *    TRUE si el día es feriado o FALSE si no lo es
     */
    public function eFeriado();


}
