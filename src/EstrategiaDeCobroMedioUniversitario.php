<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedioUniversitario implements EstrategiaDeCobroInterface {

    private $mediosUsados = 0;
    private $horaPago = null;

    public function tipo () {
        return "Medio Universitario";
    }

    /**
     * Devuelve la mitad del valor usual, con un límite de dos boletos por día.
     *
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje($valorBase) : float {
        if ($this->mediosUsados <= 2) {
            return $valorBase / 2.0;
        } else {
            return $valorBase;
        }
    }

    /**
     * Lleva la cuenta de la cantidad de medios usados
     *
     * @return bool
     *    Si tiene permitido o no viajar segun las regulaciones del medio universitario
     */
    public function tienePermitidoViajar($tiempoActual) {
        if ($this->horaPago == null)
            $this->horaPago = $tiempoActual;

        $hoy = date("Y/m/d", $tiempoActual);
        $diaPago = date("Y/m/d", $this->horaPago);

        if ($hoy > $diaPago) {
            $this->mediosUsados = 0;
        }

        if ($this->mediosUsados <= 2) {
            $this->mediosUsados += 1;
        }

        $this->horaPago = $tiempoActual;

        return TRUE;
    }


}
