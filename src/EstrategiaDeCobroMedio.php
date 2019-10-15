<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedio implements EstrategiaDeCobroInterface {
    private const cincoMinutos = 60 * 5;

    private $horaPago = null;

    public function tipo () : string {
        return "Medio";
    }

    /**
     * Devuelve la mitad del costo usual.
     *
     * @param float $valorBase
     *     Valor de un pasaje normal
     *
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje(float $valorBase) : float {
        return $valorBase / 2.0;
    }

    public function registrarViaje(int $tiempoActual) : void {
        $this->horaPago = $tiempoActual;
    }

    /**
     * Se fija que el último viaje haya sido emitido al menos 5 minutos más
     * tarde que el anterior.
     *
     * @return bool
     *     Si tiene permitido o no viajar segun las regulaciones del medio
     *     boleto
     */
    public function tienePermitidoViajar(int $tiempoActual) : bool {

        // Si es el primer pago
        if ($this->horaPago === null)
            return true;

        $diferenciaDeTiempo = $tiempoActual - $this->horaPago;

        // Si pasaron cinco minutos o mas desde el anterior
        return $diferenciaDeTiempo >= self::cincoMinutos;
    }
}
