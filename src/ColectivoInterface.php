<?php

namespace TrabajoTarjeta;

interface ColectivoInterface {

    /**
     * Devuelve el nombre de la linea. Ejemplo "142"
     *
     * @return string
     *     Nombre de la linea
     */
    public function linea();

    /**
     * Devuelve la bandera de la unidad. Ejemplo: "Negra"
     *
     * @return string
     *     Bandera de la unidad
     */
    public function bandera();

    /**
     * Devuelve el nombre de la empresa. Ejemplo "Semtur"
     *
     * @return string
     *     Nombre de la empresa
     */
    public function empresa();

    /**
     * Devuelve el numero de unidad. Ejemplo: 12
     *
     * @return int
     *     Numero de unidad
     */
    public function numero();

    /**
     * Paga un viaje en el colectivo con una tarjeta en particular.
     *
     * @param TarjetaInterface $tarjeta
     *
     * @return BoletoInterface|FALSE
     *  El boleto generado por el pago del viaje. O FALSE si no hay saldo
     *  suficiente en la tarjeta.
     */
    public function pagarCon(TarjetaInterface $tarjeta);

}
