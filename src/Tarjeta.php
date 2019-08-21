<?php

namespace TrabajoTarjeta;

class Tarjeta implements TarjetaInterface {
    protected $valorBoleto = 16.8;
    protected $pasaje = 16.8;
    protected $saldo;
    protected $cargas = array("10", "20", "30", "50", "100", "510.15", "962.59");
    protected $plus = 0;
    protected $tipo = "Normal";
    protected $id;
    protected $horaPago;
    protected $actualColectivo;
    protected $anteriorColectivo = NULL;
    protected $fueTrasbordo = FALSE;
    protected $plusPPagar;
    protected $tiempo;
    private $estrategiaDeCobro;

    public function __construct($id, TiempoInterface $tiempo, EstrategiaDeCobroInterface $estrategiaDeCobro = null) {
      $this->estrategiaDeCobro = $estrategiaDeCobro;
      if($this->estrategiaDeCobro == null)
        $this->estrategiaDeCobro = new EstrategiaDeCobroNormal;

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
      $this->plus += 1;
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
    protected function pagarBoleto() {

      if ($this->esTrasbordo()) {  //Si es trasbordo

        $this->saldo -= round($this->valorPasaje() * 0.33, 2); //Se cobra un 33% del valor del pasaje
        $this->horaPago = $this->tiempo->time(); //guarda la hora en la que se realizo el pago
        $this->fueTrasbordo = TRUE;
        $this->plusPPagar = 0;
        return "Trasbordo";
      }

      if ($this->saldo >= $this->valorPasaje()) {   //se verifica si tiene saldo
        if ($this->plus == 0) {                     //despues se comprueba que no deba ningun plus
          $this->saldo -= $this->valorPasaje(); //si no debe ninguno, se descuenta normalmente el saldo
          $this->horaPago = $this->tiempo->time(); //guarda la hora en la que se realizo el pago
          $this->fueTrasbordo = FALSE;
          $this->plusPPagar = 0;
          return "PagoNormal";
        } elseif ($this->plus == 1) {//si debe uno se descuenta el valor del boleto + el valor del plus que debe
          if ($this->saldo >= $this->valorPasaje() + $this->valorBoleto) {//Le alcanza?
            $this->saldo -= $this->valorPasaje() + $this->abonaPlus(); //se resta el valor del pasaje de la tarjeta, la cantidad de plus que deba y se reinicia el contador de plus
            $this->horaPago = $this->tiempo->time();
            $this->fueTrasbordo = FALSE;
            $this->plusPPagar = 1;
            return "AbonaPlus";
          } else {//si no puede pagar el valor del boleto + el del plus que debe, no puede abonar el pasaje
            $this->viajePlus();
            $this->horaPago = $this->tiempo->time();
            $this->fueTrasbordo = FALSE;
            $this->plusPPagar = 0;
            return "Plus2";
          }

        } elseif ($this->plus == 2) {//si debe dos se descuenta el valor del boleto + el valor de los plus que debe
          if ($this->saldo >= $this->valorPasaje() + $this->valorBoleto * 2) {
            $this->saldo -= $this->valorPasaje() + $this->abonaPlus(); //aca se resta el valor del pasaje de la tarjeta, la cantidad de plus que deba y se reinicia el contador de plus
            $this->horaPago = $this->tiempo->time();
            $this->fueTrasbordo = FALSE;
            $this->plusPPagar = 2;
            return "AbonaPlus";
          } else {//si no puede pagar el valor del boleto + el de los plus que debe, no puede abonar el pasaje
          return FALSE;
        }

        }

      } elseif ($this->plus < 2) {//si no tiene saldo suficiente se verifica si le quedan plus disponibles
        switch ($this->plus) {
          case 0:
            $this->viajePlus(); //dependiendiendo de la cantidad de viajes plus que le queden hace 1 o 2 viajes
            $this->horaPago = $this->tiempo->time();
            return "Plus1";
          
          case 1:
            $this->viajePlus();
            $this->horaPago = $this->tiempo->time();
            return "Plus2";

        }
      }

        return FALSE; //si no le queda saldo ni plus, no puede pagar
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
      if ($this->anteriorColectivo == NULL) { 
        $this->anteriorColectivo = $colectivo;
      } else {
        $this->anteriorColectivo = $this->actualColectivo;
      }
      $this->actualColectivo = $colectivo;

      if($this->estrategiaDeCobro->tienePermitidoViajar($this->tiempo->time()))
        return $this->pagarBoleto();
      else
        return FALSE;
    }


    /**
     * Se abonan los viajes plus en función a los que tiene la tarjeta. Ejemplo: 33.6
     * 
     * @return float
     *    Valor total de viajes plus a pagar
     */
    public function abonaPlus() {
      $pagoPlus = $this->valorBoleto * $this->plus;
      $this->plus = 0;
      return $pagoPlus;
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
      return $this->plusPPagar;  
    }

    /**
     * Devuelve la cantidad de viajes plus que tiene la tarjeta. Ejemplo: 2
     * 
     * @return int
     *    Cantidad de plus en tarjeta
     */
    public function verPlus() {
      return $this->plus;
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
    protected function esTrasbordo() {
      $tiempoActual = $this->tiempo->time();
      $hora = date("G", $tiempoActual);
      $dia = date("w", $tiempoActual);

      if ($this->colectivosDiferentes()) { //Si el colectivo en el que se esta usando la tarjeta ahora es diferente al anterior

        if ($hora >= 22 || $hora < 6) { //Todos los dias de 22 a 6
          if ($tiempoActual - $this->obtenerFecha() <= 5400) { //Si pasaron 90 minutos o menos
            $this->fueTrasbordo = TRUE;
            return TRUE; //Paga trasbordo
          }
        } elseif ($dia == 6) { //Si es sábado
          if ($hora >= 6 && $hora < 14) { //De 6 a 14
            if ($tiempoActual - $this->obtenerFecha() <= 3600) { //Si pasaron 60 minutos o menos
              $this->fueTrasbordo = TRUE;
              return TRUE; //Paga trasbordo
            }
          } else {
            if ($tiempoActual - $this->obtenerFecha() <= 5400) { //Si pasaron 90 minutos o menos
              $this->fueTrasbordo = TRUE;
              return TRUE; //Paga trasbordo
            }
          }
        } elseif ($dia == 0 || $this->eFeriado()) { //Si es domingo o feriado
          if ($hora >= 6 && $hora < 22) { //De 6 a 22
            if ($tiempoActual - $this->obtenerFecha() <= 5400) { //Si pasaron 90 minutos o menos
              $this->fueTrasbordo = TRUE;
              return TRUE; //Paga trasbordo
            }
          } 
        } else { //De lunes a viernes de 6 a 22
            if ($tiempoActual - $this->obtenerFecha() <= 3600) { //Si pasó una hora o menos
              $this->fueTrasbordo = TRUE;
              return TRUE; //Paga trasbordo
            }
          }
      }

      return FALSE;
    }


    /**
     * Se fija si el colectivo en donde se está pagando es distinto al
     * del viaje anterior. Se comparan líneas y banderas
     * 
     * @return bool
     *    TRUE o FALSE dependiendo de si son iguales o no
     */
    protected function colectivosDiferentes() {
     
      $linea1 = $this->anteriorColectivo->linea();
      $linea2 = $this->actualColectivo->linea();

      $bandera1 = $this->anteriorColectivo->bandera();
      $bandera2 = $this->actualColectivo->bandera();
    
      if ($linea1 != $linea2 || $bandera1 != $bandera2) {
        return TRUE;        
      }
    

      return FALSE;                  
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