<?php

namespace TrabajoTarjeta;

interface CanceladoraInterface {

    /**
     * Paga un viaje en el colectivo con una tarjeta en particular
     *
     * @param ColectivoInterface $colectivo
     * @param TarjetaInterface $tarjeta
     *
     * @return array | null
     *     datos sobre el viaje
     */
    public function intentarViaje (
        ColectivoInterface $colectivo,
        TarjetaInterface $tarjeta
    );
}
