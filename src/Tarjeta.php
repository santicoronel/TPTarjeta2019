<?php

namespace TrabajoTarjeta;

class Tarjeta implements TarjetaInterface {

    // NOTE: $pasaje y $valorBoleto son la misma constante?
    protected $valorBoleto = 16.8;
    protected $pasaje = 16.8;

    // NOTE: $cargas es constante?
    protected $cargas = array("10", "20", "30", "50", "100", "510.15", "962.59");

    protected $saldo;
    protected $id;
    protected $horaPago;
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
     * @return bool
     *    TRUE si el monto a cargar es válido, o FALSE en caso de que no lo sea
     *
     */
    public function recargar($monto) {
        // Esto comprueba si la carga esta dentro de los montos permitidos
        $cargavalida = in_array($monto, $this->cargas);

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
    public function obtenerSaldo() {
        return $this->saldo;
    }

    /**
     * Descuenta el saldo. Ejemplo: 'PagoNormal'
     *
     * @return string|bool
     *    El tipo de pago o FALSE si el saldo es insuficiente
     */
    protected function pagarBoleto($colectivo, $tiempoActual) {

        if ($this->esTrasbordo($colectivo, $tiempoActual)) {
            // FIXME: Si es trasbordo no se fija si le alcanza

            //Se cobra un 33% del valor del pasaje
            $this->saldo -= round($this->valorPasaje() * 0.33, 2);

            return "Trasbordo";
        }

        $costoDeLosPlus = $this->manejadorPlus->costoAPagar($this->pasaje);
        $costoDelPasajeActual = $this->valorPasaje();
        $costoTotal = $costoDelPasajeActual + $costoDeLosPlus;

        // Puedo pagar todo?
        if($this->saldo >= $costoTotal){

            // Si puedo, pago, reestablezco los plus, y marco la hora
            $this->saldo -= $costoTotal;
            $this->manejadorPlus->reestablecer();

            if($costoDeLosPlus > 0)
                return "AbonaPlus";
            else
                return "PagoNormal";

            // Si no puedo, me fijo si me quedan plus
        }
        
        // Si me quedan plus, gasto un plus
        if($this->manejadorPlus->tienePlus())
            return $this->manejadorPlus->gastarPlus();

        // Si no tengo ni plata ni plus, no puedo viajar
        return false;
    }


    /**
     * Descuenta el boleto del saldo de la tarjeta. Ejemplo: 'AbonaPlus'
     *
     * @param ColectivoInterface $colectivo
     *    Colectivo anterior
     *
     * @return string|bool
     *    El tipo de pago o FALSE si el saldo es insuficiente
     */
    public function descontarSaldo(ColectivoInterface $colectivo) {
        $tiempoActual = $this->tiempo->time();

        $tengoPermiso = $this->estrategiaDeCobro->tienePermitidoViajar(
            $tiempoActual);

        // si no tengo permiso no viajo
        if($tengoPermiso === false)
            return false;

        $tipoDeViaje = $this->pagarBoleto($colectivo, $tiempoActual);

        // si no puedo pagar no viajo
        if($tipoDeViaje === false)
            return false;

        // Si viajo tengo que anotar algunas cosas antes de avisar que viajo

        $this->manejadorTrasbordo->registrarViaje($colectivo, $tiempoActual);
        $this->estrategiaDeCobro->registrarViaje($tiempoActual);
        $this->horaPago = $tiempoActual;

        return $tipoDeViaje;
    }


    /**
     * Devuelve el valor del boleto. Ejemplo: 18.45
     *
     * @return float
     *    Valor del boleto
     */
    public function valorDelBoleto() {
        return $this->valorBoleto;
    }

    /**
     * Devuelve la cantidad de viajes plus que se van a pagar en un viaje. Ejemplo: 1
     *
     * @return int
     *    Cantidad de plus a abonar
     */
    public function plusAPagar() {
        return $this->manejadorPlus->plusGastados();
    }

    /**
     * Devuelve la cantidad de viajes plus que tiene la tarjeta. Ejemplo: 2
     *
     * @return int
     *    Cantidad de plus en tarjeta
     */
    public function verPlus() {
        return $this->manejadorPlus->plusRestantes();
    }

    /**
     * Devuelve el tipo de la tarjeta que se está usando. Ejemplo: "Normal"
     *
     * @return string
     *    Tipo de tarjeta
     */
    public function obtenerTipo() {
        return $this->estrategiaDeCobro->tipo();
    }

    /**
     * Devuelve la hora en la que se abonó un pasaje. Ejemplo: 543
     *
     * @return int
     *    Hora en la que se efectuó el pago del boleto
     */
    public function obtenerFecha() {
        return $this->horaPago;
    }

    /**
     * Retorna el id único de la tarjeta. Ejemplo: 3
     *
     * @return int
     *    Número de ID de la tarjeta
     */
    public function obtenerId() {
        return $this->id;
    }

    /**
     * Chequea que el viaje que se quiere abonar cumpla las condiciones necesarias para
     * que sea trasbordo
     *
     * @return bool
     *    TRUE o FALSE dependiendo de si es trasbordo o no
     */
    protected function esTrasbordo($colectivo, $tiempoActual) {
        return $this->manejadorTrasbordo->esTrasbordo(
            $colectivo,
            $tiempoActual,
            $this->eFeriado()
        );
    }

    /**
     * Llama a una función del tiempo que hace al día feriado o no, dependiendo su valor anterior
     */
    public function cFeriado() {
        $this->tiempo->cambiarFeriado();
    }

    /**
     * Llama a una función del tiempo que indica si un día es feriado o no
     *
     * @return bool
     *    TRUE si el día es feriado o FALSE si no lo es
     */
    public function eFeriado() {
        return $this->tiempo->esFeriado();
    }
}
