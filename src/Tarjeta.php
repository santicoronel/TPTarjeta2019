<?php

namespace TrabajoTarjeta;

class DatosDeTarjeta {
    public $saldo;
    public $id;

    public $plusRestantes;

    public $tiempoDelUltimoViaje;
    public $colectivoDelUltimoViaje;
}

class Tarjeta implements TarjetaInterface {

    // TODO: Mover esto a otro lado
    protected $valorBoleto = 16.8;
    private const cargas = ["10", "20", "30", "50", "100", "510.15", "962.59"];

    protected $datos;

    public $estrategiaDeCobro;

    public function __construct(
        $id,
        EstrategiaDeCobroInterface $estrategiaDeCobro = null
    ) {

        $this->estrategiaDeCobro =
            $estrategiaDeCobro ?? new EstrategiaDeCobroNormal;

        $this->datos = new DatosDeTarjeta;
        $this->datos->id = $id;
        $this->datos->saldo = 0.0;
        $this->datos->plusRestantes = ChequeoPlus::PLUS_TOTAL;
        $this->datos->tiempoDelUltimoViaje = null;
        $this->datos->colectivoDelUltimoViaje = null;
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
            $this->datos->saldo += $monto;
        }

        return $cargavalida;
    }

    public function registrarViaje (
        $colectivo,
        $tiempoActual,
        $costo,
        $plusPagados
    ) {
        $this->datos->colectivoDelUltimoViaje = $colectivo;
        $this->datos->tiempoDelUltimoViaje = $tiempoActual;
        $this->datos->saldo -= $costo;
        $this->datos->plusRestantes += $plusPagados;

        $this->estrategiaDeCobro->registrarViaje($tiempoActual);
    }

    public function valorPasaje() : float {
        return $this->estrategiaDeCobro->valorPasaje($this->valorBoleto);
    }

    /**
     * Devuelve el saldo que le queda a la tarjeta. Ejemplo: 37.9
     *
     * @return float
     *    Saldo
     */
    public function obtenerSaldo() : float {
        return $this->datos->saldo;
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
        return $this->datos->plusRestantes;
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
        return $this->datos->id;
    }


    public function obtenerDatos () {
        return clone $this->datos;
    }
}
