<?php

namespace TrabajoTarjeta;

class CanceladoraMock implements CanceladoraInterface {

    private $valorBoleto = 16.8;
    private $tiempo;

    public function __construct (TiempoInterface $tiempo) {
        $this->tiempo = $tiempo;
    }

    /**
     *
     * Verifica que se puede viejar y, de ser asi, descuenta el boleto del saldo
     * de la tarjeta.
     * Ejemplo: 'AbonaPlus'
     *
     * @param ColectivoInterface $colectivo
     *    Colectivo que intentamos tomar
     *
     * @return array:[tipo:string, costo:float, tiempo:int, plusPagados:int] | null
     *    Informacion sobre el viaje o null si no es posible viajar
     */
    public function intentarViaje (
        ColectivoInterface $colectivo,
        TarjetaInterface $tarjeta
    ) {
        $tiempoActual = $this->tiempo->time();

        $tengoPermiso = $tarjeta->estrategiaDeCobro->tienePermitidoViajar(
            $tiempoActual);

        // si no tengo permiso no viajo
        if($tengoPermiso === false)
            return null;

        $datosDeViaje = $this->pagarBoleto(
            $colectivo, $tarjeta, $tiempoActual);

        // si no puedo pagar no viajo
        if($datosDeViaje === null)
            return null;

        // Si viajo tengo que registrar algunas cosas

        $tarjeta->registrarViaje(
            $colectivo,
            $tiempoActual,
            $datosDeViaje["costo"],
            $datosDeViaje["plusPagados"]
        );

        return [
            "colectivo" => $colectivo,
            "tarjeta" => $tarjeta,
            "tipo" => $datosDeViaje["tipo"],
            "costo" => $datosDeViaje["costo"],
            "tiempo" => $tiempoActual,
            "plusPagados" => $datosDeViaje["plusPagados"]
        ];
    }

    /**
     * @brief Determina que tipo de viaje se va a realizar y resta el saldo correspondiente
     *
     * ```
     * intenta viajar.
     *
     * es trasbordo?
     *     si: No paga. fin.
     *
     * CostoTotal <- Cuanto tiene que pagar?
     *
     * Puede pagar CostoTotal?
     *     si: Paga CostoTotal y se le reestablecen los plus
     *     no: Le queda plus?
     *         si: paga con plus
     *         no: rechazado
     * ```
     *
     * @param ColectivoInterface $colectivo
     * @param int $tiempoActual
     *
     * @return array:[tipo:string, valor:float, plusPagados:int] | null
     *    Informacion sobre el pago o null si no es posible pagar
     */
    protected function pagarBoleto(
        ColectivoInterface $colectivo,
        TarjetaInterface $tarjeta,
        $tiempoActual
    ) : ?array {

        $datosTarjeta = $tarjeta->obtenerDatos();

        $manejadorTrasbordo = new ChequeoTrasbordo(
            $datosTarjeta->colectivoDelUltimoViaje,
            $datosTarjeta->tiempoDelUltimoViaje
        );

        if ($manejadorTrasbordo->esTrasbordo($colectivo, $tiempoActual, $this->tiempo->esFeriado())) {
            // DUDA: Como se relaciona esto con
            // EstrategiaDeCobroInterface::tienePermitidoViajar ?

            return [
                "tipo" => TipoDeViaje::Trasbordo,
                "costo" => 0,
                "plusPagados" => 0
            ];
        }

        $manejadorPlus = new ChequeoPlus(
            $datosTarjeta->plusRestantes
        );

        $costoDeLosPlus = $manejadorPlus->costoAPagar($this->valorBoleto);
        $costoDelPasajeActual = $tarjeta->valorPasaje();
        $costoTotal = $costoDelPasajeActual + $costoDeLosPlus;

        // Puedo pagar todo?
        if($datosTarjeta->saldo >= $costoTotal){

            $plusGastados = $manejadorPlus->plusGastados();

            if($costoDeLosPlus > 0) {
                return [
                    "tipo" => TipoDeViaje::AbonaPlus,
                    "costo" => $costoTotal,
                    "plusPagados" => $plusGastados
                ];
            } else {
                return [
                    "tipo" => TipoDeViaje::Normal,
                    "costo" => $costoTotal,
                    "plusPagados" => 0
                ];
            }

        } else if($manejadorPlus->tienePlus()) {

            // Si no puedo, me fijo si me quedan plus
            $modoPlus = $manejadorPlus->gastarPlus();

            return [
                "tipo" => $modoPlus,
                "costo" => 0,
                "plusPagados" => 0
            ];

        } else {
            // Si no tengo ni plata ni plus, no puedo viajar
            return null;
        }
    }

}
