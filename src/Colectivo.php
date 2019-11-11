<?php

namespace TrabajoTarjeta;

class Colectivo implements ColectivoInterface {

    protected $linea;
    protected $empresa;
    protected $numero;
    protected $bandera;

    public function __construct($linea, $bandera, $empresa, $numero) {
        $this->linea = $linea;
        $this->bandera = $bandera;
        $this->empresa = $empresa;
        $this->numero = $numero;
    }

    /**
     * Devuelve el nombre de la linea. Ejemplo "142"
     *
     * @return string
     *     Nombre de la linea
     */
    public function linea() {
        return $this->linea;
    }

    /**
     * Devuelve la bandera de la unidad. Ejemplo: "Negra"
     *
     * @return string
     *     Bandera de la unidad
     */
    public function bandera() {
        return $this->bandera;
    }

    /**
     * Devuelve el nombre de la empresa. Ejemplo "Semtur"
     *
     * @return string
     *     Nombre de la empresa
     */
    public function empresa() {
        return $this->empresa;
    }

    /**
     * Devuelve el numero de unidad. Ejemplo: 12
     *
     * @return int
     *     Numero de unidad
     */
    public function numero() {
        return $this->numero;
    }

    /**
     * Paga un viaje en el colectivo con una tarjeta en particular
     *
     * @param TarjetaInterface $tarjeta
     *
     * @return BoletoInterface|FALSE
     *  El boleto generado por el pago del viaje o FALSE si no hay saldo
     *  suficiente en la tarjeta.
     */
    public function pagarCon(TarjetaInterface $tarjeta) {

        $plusAPagar = $tarjeta->plusAPagar();
        $tipoDeViaje = $tarjeta->descontarSaldo($this);

        if(!$tipoDeViaje)
            return false;

        return new Boleto($this, $tarjeta, $tipoDeViaje, $plusAPagar);
    }

    public function esMismoValor (ColectivoInterface $otro) {
        return $this->numero() == $otro->numero()
            || $this->bandera() == $otro->bandera()
            || $this->linea() == $otro->linea()
            || $this->empresa() == $otro->empresa();
    }
}
