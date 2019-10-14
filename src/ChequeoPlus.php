<?php

namespace TrabajoTarjeta;

class ChequeoPlus {
    private const PLUS_TOTAL = 2;

    private $plusRestantes;

    /**
     *
     * intenta viajar.
     * CostoTotal <- Cuanto tiene que pagar?
     *
     * Puede pagar CostoTotal?
     * si:
     *   Paga CostoTotal y se le reestablecen los plus
     * no:
     *   Le queda plus?
     *   si:
     *     paga con plus
     *   no:
     *     rechazado
     *
     */

    __construct () {
            $this->plusRestantes = self::PLUS_TOTAL;
    }

    /**
     * Const
     * float -> float
     */
    public function costoAPagar ($valorBasePasaje) {
        $plusGastados = self::PLUS_TOTAL - $this->plusRestantes;
        return $valorBasePasaje * $plusGastados;
    }

    /**
     * Mutable
     * void -> void
     */
    public function reestablecer () {
        $this->plusRestantes = self::PLUS_TOTAL;
    }

    /**
     * Const
     * void -> bool
     */
    public function tienePlus () {
        return $this->plusRestantes > 0;
    }

    /**
     * Mutable
     * void -> string
     */
    public function gastarPlus () {
        if($this->plusRestantes <= 0)
            throw new Exception("Intenta usar viaje Plus cuando no le quedan");

        $this->plusRestantes = $this->plusRestantes - 1;
        $plusGastados = self::PLUS_TOTAL - $this->plusRestantes;

        return "Plus$plusGastados";
    }
}

