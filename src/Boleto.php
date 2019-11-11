<?php

namespace TrabajoTarjeta;

class Boleto implements BoletoInterface {

    protected $valor;
    protected $colectivo;

    /**
     * La Tarjeta con la cual se genero este Boleto
     * @var Tarjeta $tarjeta
     */
    protected $tarjeta;
    protected $tipo;
    protected $fecha;
    protected $linea;
    protected $total;
    protected $saldo;
    protected $id;
    protected $tipoBoleto;
    protected $descripcion;

    public function __construct($colectivo, $tarjeta, $informacionDeViaje) {

        $this->colectivo = $colectivo;
        $this->tarjeta = $tarjeta;

        $this->valor = $informacionDeViaje["costo"];

        $this->tipo = $tarjeta->obtenerTipo();

        $tiempoDelViaje = $informacionDeViaje["tiempo"];
        $this->fecha = date("d/m/Y H:i:s", $tiempoDelViaje);

        $this->linea = $colectivo->linea();

        $this->tipoBoleto = $informacionDeViaje["tipo"];

        $plusPagados = $informacionDeViaje["plusPagados"];
        $this->total = $this->valor + $tarjeta->valorDelBoleto() * $plusPagados;

        $this->saldo = $tarjeta->obtenerSaldo();

        $this->id = $tarjeta->obtenerId();

        $this->descripcion = $this->obtenerDescripcion();

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
