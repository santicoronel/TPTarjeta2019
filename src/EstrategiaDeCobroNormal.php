<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroNormal implements EstrategiaDeCobroInterface {

  public function tipo (){
    return "Normal";
  }

  public function valorPasaje($valorBase){
    return $valorBase;
  }

  public function tienePermitidoViajar($tiempoActual){
    return TRUE;
  }
}