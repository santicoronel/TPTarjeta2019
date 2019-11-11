<?php

namespace TrabajoTarjeta;

class TipoDeViaje {
    const Normal = "Normal";
    const Trasbordo = "Trasbordo";
    const Plus1 = "Plus1";
    const Plus2 = "Plus2";
    const AbonaPlus = "AbonaPlus";

    public static function descripcion ($tipo) {
        switch($tipo){
            case self::Normal: return "Viaje Normal";
            case self::Trasbordo: return "Trasbordo";
            case self::Plus1: return "Viaje Plus";
            case self::Plus2: return "Ultimo Plus";
            case self::AbonaPlus: return "Abona Viaje Plus";
            default: return null;
        }
    }
}
