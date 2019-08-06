<?php

namespace TrabajoTarjeta;

class MedioUniversitario extends Medio implements TarjetaInterface {

    protected $tipo = "Medio Universitario";
    private $mediosUsados = 0;

    /** Redefinimos el valor del pasaje de la clase y agregamos un límite de dos boletos por día. Ejemplo: 6.70
     * 
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje() {
        if ($this->mediosUsados <= 2) {
            return ($this->pasaje) / 2.0;
        } else {
            return $this->pasaje;
        }
      }

    /**
     * Redefinimos la funcion para que, además de descontar el boleto de la tarjeta, se fije que no se hayan
     * emitido más de dos medios boletos en el día. De ser así, se abona un boleto común. Ejemplo: "Medio Universitario"
     * 
     * @param ColectivoInterface $colectivo
     * 
     * @return string|bool
     *    El tipo de pago o FALSE si el saldo es insuficiente
     */
    public function descontarSaldo(ColectivoInterface $colectivo) {

        if ($this->anteriorColectivo == NULL) { 
            $this->anteriorColectivo = $colectivo;
          } else {
            $this->anteriorColectivo = $this->actualColectivo;
          }

        $this->actualColectivo = $colectivo;
        
        $hoy = date("d/m/Y", $this->tiempo->time());
        $diaPago = date("d/m/Y", $this->horaPago);
        if ($hoy > $diaPago) {
          $this->mediosUsados = 0;
        }
        if ($this->mediosUsados <= 2) {
            $this->mediosUsados += 1;
            return $this->pagarBoleto();
        } else {
            return $this->pagarBoleto();
        }
        
    }


}