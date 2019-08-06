<?php

namespace TrabajoTarjeta;

class Medio extends Tarjeta implements TarjetaInterface {

  protected $tipo = "Medio";

    /** Redefinimos el valor del pasaje de la clase a la mitad. Ejemplo: 6.70
     * 
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje() {
      return ($this->pasaje) / 2.0;
  }


  /**
   * Redefinimos la funcion para que, además de descontar el boleto de la tarjeta, se fije que el último viaje haya
   * sido emitido al menos 5 minutos más tarde que el anterior. Ejemplo: "Medio"
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

    if ($this->tiempo->time() == $this->horaPago) { //si la hora actual = la hora del ultimopago significa que es el primer pago que hace la tarjeta
      return $this->pagarBoleto(); //por lo tanto se cobra normalmente el boleto
    } elseif ($this->tiempo->time() - $this->horaPago >= 300) { //si pasaron 5 minutos o mas desde la ultima compra
      return $this->pagarBoleto(); //se cobra el boleto normalmente
    } else { //si no pasaron al menos 5 minutos
      return FALSE; //no puede pagar
    }
  }
}