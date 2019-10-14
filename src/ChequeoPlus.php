<?php

namespace TrabajoTarjeta;

class ChequeoPlus {
    private const PLUS_TOTAL = 2;

    private $cantidad;

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

    public function __construct () {
            $this->cantidad = self::PLUS_TOTAL;
    }

    public function plusRestantes () {
        return $this->cantidad;
    }

    public function plusGastados () {
        return self::PLUS_TOTAL - $this->cantidad;
    }

    /**
     * Const
     * float -> float
     */
    public function costoAPagar ($valorBasePasaje) {
        return $valorBasePasaje * $this->plusGastados();
    }

    /**
     * Mutable
     * void -> void
     */
    public function reestablecer () {
        $this->cantidad = self::PLUS_TOTAL;
    }

    /**
     * Const
     * void -> bool
     */
    public function tienePlus () {
        return $this->cantidad > 0;
    }

    /**
     * Mutable
     * void -> string
     */
    public function gastarPlus () {
        if($this->cantidad <= 0)
            throw new Exception("Intenta usar viaje Plus cuando no le quedan");

        $this->cantidad = $this->cantidad - 1;
        $plusGastados = $this->plusGastados();

        return "Plus$plusGastados";
    }
}

