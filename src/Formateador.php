<?php

namespace TrabajoTarjeta;

class Formateador {
    public function formatear (array $datos) : string {
        return
            "Costo del viaje: {$datos["costo"]}\n".
            "Saldo Restante: {$datos["tarjeta"]->obtenerSaldo()}\n";
    }
}

