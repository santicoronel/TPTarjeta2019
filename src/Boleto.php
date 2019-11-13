<?php

namespace TrabajoTarjeta;

class Boleto implements BoletoInterface {

    protected $valor;
    protected $colectivo;
    protected $tarjeta;
    protected $tipo;
    protected $fecha;
    protected $linea;
    protected $total;
    protected $saldo;
    protected $tipoBoleto;

    public function __construct($colectivo, $tarjeta, $informacionDeViaje) {

        $this->colectivo = $colectivo;
        $this->tarjeta = $tarjeta;

        $this->valor = $informacionDeViaje["costo"];
        $this->tipoBoleto = $informacionDeViaje["tipo"];
        $tiempoDelViaje = $informacionDeViaje["tiempo"];
        $plusPagados = $informacionDeViaje["plusPagados"];

        $this->linea = $colectivo->linea();

        $this->tipo = $tarjeta->obtenerTipo();
        $this->saldo = $tarjeta->obtenerSaldo();

        $this->fecha = date("d/m/Y H:i:s", $tiempoDelViaje);

        $this->total = $this->valor + $tarjeta->valorDelBoleto() * $plusPagados;
    }


    /**
     * Devuelve el valor del boleto.
     *
     * @return int
     *     Valor del boleto
     */
    public function obtenerValor() {
        return $this->valor;
    }

    /**
     * Devuelve un objeto que respresenta el colectivo donde se viajó.
     *
     * @return ColectivoInterface
     *     Colectivo donde se viajó
     */
    public function obtenerColectivo() {
        return $this->colectivo;
    }

    /**
     * Devuelve el adicional por viaje plus formateado en un formato legible
     *
     * @return string|NULL
     */
    public function obtenerDescripcion() {
        if ($this->tipoBoleto == "AbonaPlus") {
            $base = "Abona Viajes Plus ";
            $extraPlus = $this->total - $this->valor;
            $final = $base . $extraPlus . " y";
            return $final;
        }
        return NULL;
    }

    public function tipoDeBoleto () {
        return $this->tipoBoleto;
    }
}
