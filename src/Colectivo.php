<?php

namespace TrabajoTarjeta;

class Colectivo implements ColectivoInterface {

    protected $linea;
    protected $empresa;
    protected $numero;
    protected $bandera;

    private $canceladora;

    public function __construct(
        $linea,
        $bandera,
        $empresa,
        $numero,
        CanceladoraInterface $canceladora
    ) {
        $this->linea = $linea;
        $this->bandera = $bandera;
        $this->empresa = $empresa;
        $this->numero = $numero;

        $this->canceladora = $canceladora;
    }

    public static function crear ($linea, $bandera, $empresa, $numero) {
        return new Colectivo(
            $linea, $bandera, $empresa, $numero, new CanceladoraMock(new TiempoFalso));
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
        $datos = $this->canceladora->intentarViaje($this, $tarjeta);

        return new Boleto($this, $tarjeta, $datos);
    }

    public function esMismoValor (ColectivoInterface $otro) {
        return $this->numero() == $otro->numero()
            || $this->bandera() == $otro->bandera()
            || $this->linea() == $otro->linea()
            || $this->empresa() == $otro->empresa();
    }
}
