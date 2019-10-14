<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedio implements EstrategiaDeCobroInterface {

    private $horaPago = null;

    public function tipo (){
        return "Medio";
    }

    /** Devuelve la mitad del costo usual.
     *
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje($valorBase) : float {
        return $valorBase / 2.0;
    }

    public function registrarViaje($tiempoActual) {
        $this->horaPago = $tiempoActual;
    }

    /**
     * Se fija que el último viaje haya sido emitido al menos 5 minutos más tarde
     * que el anterior.
     *
     * @return bool
     *    Si tiene permitido o no viajar segun las regulaciones del medio boleto
     */
    public function tienePermitidoViajar($tiempoActual) {

        // Si es el primer pago
        if ($this->horaPago === null)
            return true;

        $diferenciaDeTiempo = $tiempoActual - $this->horaPago;

        $cincoMinutos = 60 * 5;

        // Si pasaron cinco minutos o mas desde el anterior
        return $diferenciaDeTiempo >= $cincoMinutos;
    }
}
