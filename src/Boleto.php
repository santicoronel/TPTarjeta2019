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
    protected $id;
    protected $tipoBoleto;
    protected $descripcion;

    public function __construct($colectivo, $tarjeta, $tipoBoleto) {

        switch ($tipoBoleto) {
            case "Normal":
                $this->valor = $tarjeta->valorPasaje();
                break;
                                                                                                
            case "Trasbordo":
                $this->valor = $tarjeta->valorPasaje() * 0.33;
                break;

            case "AbonaPlus":
                $this->valor = $tarjeta->valorPasaje();                                    
                break;                
            
            default:
                $this->valor = 0.0;                                                                            
        }

        $this->colectivo = $colectivo;

        $this->tarjeta = $tarjeta;

        $this->tipo = $tarjeta->obtenerTipo();

        $this->fecha = date("d/m/Y H:i:s", $tarjeta->obtenerFecha());

        $this->linea = $colectivo->linea();

        $this->tipoBoleto = $tipoBoleto;

        $this->total = $this->valor + $tarjeta->valorDelBoleto() * $tarjeta->plusAPagar();

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
     * Devuelve los datos del boleto emitido si es 
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

}
