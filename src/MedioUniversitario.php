<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedioUniversitario implements EstrategiaDeCobroInterface {

    private $mediosUsados = 0;

    public function tipo () {
        return "Medio Universitario";
    }

    /**
     * Devuelve la mitad del valor usual, con un límite de dos boletos por día.
     *
     * @return float
     *    Valor del pasaje
     */
    public function valorPasaje($valorBase) {
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
        // FIXME: comparar por YMD en vez de DMY
        $hoy = date("d/m/Y", $this->tiempo->time());
        $diaPago = date("d/m/Y", $this->horaPago);

        if ($hoy > $diaPago) {
            $this->mediosUsados = 0;
        }

        if ($this->mediosUsados <= 2) {
            $this->mediosUsados += 1;
        }

        return TRUE;
    }


}
