<?php

namespace TrabajoTarjeta;

/**
 * Se encarga de realizar los chequeos necesarios para saber cuando se puede
 * cobrar un viaje plus.
 */
class ChequeoPlus {
    private const PLUS_TOTAL = 2;

    private $cantidad;

    public function __construct () {
            $this->cantidad = self::PLUS_TOTAL;
    }

    /**
     * @return int
     *     La cantidad de plus restantes
     */
    public function plusRestantes () : int {
        return $this->cantidad;
    }

    /**
     * @return int
     *     La cantidad de plus gastados
     */
    public function plusGastados () : int {
        return self::PLUS_TOTAL - $this->cantidad;
    }

    /**
     * Calcula el costo de pagar todos los viajes plus
     *
     * @param float $valorBasePasaje
     *     El costo de un solo pasaje
     *
     * @return float
     *     El costo de todos los viajes plus
     */
    public function costoAPagar (float $valorBasePasaje) : float {
        return $valorBasePasaje * $this->plusGastados();
    }

    /**
     * [[mutates]] Repone los viajes plus
     */
    public function reestablecer () : void {
        $this->cantidad = self::PLUS_TOTAL;
    }

    /**
     * @return bool
     *     Si le quedan viajes plus
     */
    public function tienePlus () : bool {
        return $this->cantidad > 0;
    }

    /**
     * [[mutates]] Gasta un viaje plus
     *
     * @throws Exception
     *     Cuando no hay mas viajes plus
     *
     * @return string
     *     Un identificador para el viaje plus que se uso
     */
    public function gastarPlus () : string {
        if($this->cantidad <= 0)
            throw new Exception("Intenta usar viaje Plus cuando no le quedan");

        $this->cantidad = $this->cantidad - 1;
        $plusGastados = $this->plusGastados();

        return "Plus$plusGastados";
    }
}

