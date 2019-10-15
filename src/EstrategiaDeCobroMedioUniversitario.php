<?php

namespace TrabajoTarjeta;

class EstrategiaDeCobroMedioUniversitario implements EstrategiaDeCobroInterface {

    /** @var int $mediosUsados La cantidad de boletos usados en el dia actual */
    private $mediosUsados = 0;

    /** @var ?int $horaPago el momento del ultimo boleto */
    private $horaPago = null;

    public function tipo () : string {
        return "Medio Universitario";
    }

    /**
     * Devuelve la mitad del valor usual, con un límite de dos boletos por día.
     * Pasado el limite, devuelve el valor base.
     *
     * @param float $valorBase
     *     Valor de un pasaje normal
     *
     * @return float
     *     Valor del pasaje
     */
    public function valorPasaje(float $valorBase) : float {
        if ($this->mediosUsados <= 2) {
            return $valorBase / 2.0;
        } else {
            return $valorBase;
        }
    }

    public function registrarViaje(int $tiempoActual) : void {
        if($this->horaPago !== null){
            $hoy = date("Y/m/d", $tiempoActual);
            $diaPago = date("Y/m/d", $this->horaPago);

            if ($hoy > $diaPago) {
                $this->mediosUsados = 0;
            }
        }

        $this->horaPago = $tiempoActual;

        if ($this->mediosUsados <= 2)
            $this->mediosUsados += 1;
    }

    public function tienePermitidoViajar(int $tiempoActual) : bool {
        return TRUE;
    }
}
