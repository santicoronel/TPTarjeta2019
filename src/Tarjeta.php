<?php

namespace TrabajoTarjeta;

class Tarjeta implements TarjetaInterface {

    // NOTE: $pasaje y $valorBoleto son la misma constante?
    protected $valorBoleto = 16.8;
    protected $pasaje = 16.8;

    // NOTE: cargas es constante?
    private const cargas = ["10", "20", "30", "50", "100", "510.15", "962.59"];

    protected $saldo;
    protected $id;
    protected $tiempo;

    private $estrategiaDeCobro;
    private $manejadorPlus;
    private $manejadorTrasbordo;

    public function __construct($id, TiempoInterface $tiempo, EstrategiaDeCobroInterface $estrategiaDeCobro = null) {
        $this->estrategiaDeCobro = $estrategiaDeCobro;
        if($this->estrategiaDeCobro == null)
            $this->estrategiaDeCobro = new EstrategiaDeCobroNormal;

        // TODO?: Hacer DI sobre estos objetos???
        $this->manejadorPlus = new ChequeoPlus();
        $this->manejadorTrasbordo = new ChequeoTrasbordo();

        $this->id = $id;
        $this->saldo = 0.0;
        $this->tiempo = $tiempo;
    }

    /**
     * Recarga una tarjeta con un cierto valor de dinero.
     *
     * @param int $monto
     *    Cantidad de dinero a recargar
     *
     * @return
     *    TRUE si el monto a cargar es válido, o FALSE en caso de que no lo sea
     *
     */
    public function recargar($monto) : bool {
        // Esto comprueba si la carga esta dentro de los montos permitidos
        $cargavalida = in_array($monto, self::cargas);

        //Comprueba si la carga va a obtener un adicional y se lo suma
        if ($monto == 510.15) {
            $monto += 81.93;
        } elseif ($monto == 962.59) {
            $monto += 221.58;
        }

        if ($cargavalida) {
            $this->saldo += $monto;
        }

        return $cargavalida;
    }

    public function valorPasaje() : float {
        return $this->estrategiaDeCobro->valorPasaje($this->pasaje);
    }

    /**
     * Suma 1 a la cantidad de viajes plus hechos
     */
    public function viajePlus() {
        $this->manejadorPlus->gastarPlus();
    }

    /**
     * Devuelve el saldo que le queda a la tarjeta. Ejemplo: 37.9
     *
     * @return float
     *    Saldo
     */
    public function obtenerSaldo() : float {
        return $this->saldo;
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
    protected function pagarBoleto(ColectivoInterface $colectivo, $tiempoActual) : ?array {

        if ($this->esTrasbordo($colectivo, $tiempoActual)) {
            // DUDA: Como se relaciona esto con
            // EstrategiaDeCobroInterface::tienePermitidoViajar ?

            return [
                "tipo" => TipoDeViaje::Trasbordo,
                "costo" => 0,
                "plusPagados" => 0
            ];
        }

        $costoDeLosPlus = $this->manejadorPlus->costoAPagar($this->pasaje);
        $costoDelPasajeActual = $this->valorPasaje();
        $costoTotal = $costoDelPasajeActual + $costoDeLosPlus;

        // Puedo pagar todo?
        if($this->saldo >= $costoTotal){

            $plusGastados = $this->manejadorPlus->plusGastados();

            // Si puedo, pago, reestablezco los plus, y marco la hora
            $this->saldo -= $costoTotal;
            $this->manejadorPlus->reestablecer();

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

        } else if($this->manejadorPlus->tienePlus()) {

            // Si no puedo, me fijo si me quedan plus
            $modoPlus = $this->manejadorPlus->gastarPlus();

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
    public function intentarViaje(ColectivoInterface $colectivo) : ?array {
        $tiempoActual = $this->tiempo->time();

        $tengoPermiso = $this->estrategiaDeCobro->tienePermitidoViajar(
            $tiempoActual);

        // si no tengo permiso no viajo
        if($tengoPermiso === false)
            return null;

        $datosDeViaje = $this->pagarBoleto($colectivo, $tiempoActual);

        // si no puedo pagar no viajo
        if($datosDeViaje === null)
            return null;

        // Si viajo tengo que anotar algunas cosas antes de avisar que viajo

        $this->manejadorTrasbordo->registrarViaje($colectivo, $tiempoActual);
        $this->estrategiaDeCobro->registrarViaje($tiempoActual);

        return [
            "tipo" => $datosDeViaje["tipo"],
            "costo" => $datosDeViaje["costo"],
            "tiempo" => $tiempoActual,
            "plusPagados" => $datosDeViaje["plusPagados"]
        ];
    }


    /**
     * Devuelve el valor del boleto. Ejemplo: 18.45
     *
     * @return float
     *    Valor del boleto
     */
    public function valorDelBoleto() : float {
        return $this->valorBoleto;
    }

    /**
     * Devuelve la cantidad de viajes plus que se van a pagar en un viaje. Ejemplo: 1
     *
     * @return int
     *    Cantidad de plus a abonar
     */
    public function plusAPagar() : int {
        return $this->manejadorPlus->plusGastados();
    }

    /**
     * Devuelve la cantidad de viajes plus que tiene la tarjeta. Ejemplo: 2
     *
     * @return int
     *    Cantidad de plus en tarjeta
     */
    public function verPlus() : int {
        return $this->manejadorPlus->plusRestantes();
    }

    /**
     * Devuelve el tipo de la tarjeta que se está usando. Ejemplo: "Normal"
     *
     * @return string
     *    Tipo de tarjeta
     */
    public function obtenerTipo() : string {
        return $this->estrategiaDeCobro->tipo();
    }

    /**
     * Retorna el id único de la tarjeta. Ejemplo: 3
     *
     * @return int
     *    Número de ID de la tarjeta
     */
    public function obtenerId() : int {
        return $this->id;
    }

    /**
     * Chequea que el viaje que se quiere abonar cumpla las condiciones necesarias para
     * que sea trasbordo
     *
     * @return bool
     *    TRUE o FALSE dependiendo de si es trasbordo o no
     */
    protected function esTrasbordo($colectivo, $tiempoActual) : bool {
        return $this->manejadorTrasbordo->esTrasbordo(
            $colectivo,
            $tiempoActual,
            $this->tiempo->esFeriado()
        );
    }
}
