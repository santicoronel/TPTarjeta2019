<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedio implements EstrategiaDeCobroInterface {

    private $horaPago;

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

    /**
     * Se fija que el último viaje haya sido emitido al menos 5 minutos más tarde
     * que el anterior.
     *
     * @return bool
     *    Si tiene permitido o no viajar segun las regulaciones del medio boleto
     */
    public function tienePermitidoViajar($tiempoActual) {
        if ($this->horaPago == null)
            $this->horaPago = $tiempoActual;

        $diferenciaDeTiempo = $horaActual - $this->horaPago;

        $this->horaPago = $tiempoActual;
        $cincoMinutos = 60 * 5;

        // Puedo pagar si es el primer pago
        // o si pasaron cinco minutos o mas desde el anterior
        return $diferenciaDeTiempo == 0 || $diferenciaDeTiempo >= $cincoMinutos;
    }
}
