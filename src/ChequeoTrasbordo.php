<?php

namespace TrabajoTarjeta;

class ChequeoTrasbordo {
    private const una_hora_y_media = 5400;
    private const una_hora = 3600;
    private const sabado = 6;
    private const domingo = 0;

    private $tiempoAnterior = null;
    private $colectivoAnterior = null;

    public function __construct ($colectivo = null, $tiempo = null) {
        $tiempoAnterior = $tiempo;
        $colectivoAnterior = $colectivo;
    }

    public function registrarViaje ($colectivo, $tiempoActual) {
        $this->tiempoAnterior = $tiempoActual;
        $this->colectivoAnterior = $colectivo;
    }

    public function esTrasbordo ($colectivo, $tiempoActual, $esFeriado) {
        return $this->chequeoGeneral($tiempoActual, $esFeriado, $this->colectivosDiferentes($colectivo));
    }

    private function chequeoGeneral ($tiempoActual, $esFeriado, $colectivosDiferentes) {
        $hora = date("G", $tiempoActual);
        $dia = date("w", $tiempoActual);

        if (!$colectivosDiferentes)
            return false;

        // Si nunca viaje, imposible que sea trasbordo
        if($this->tiempoAnterior === null)
            return false;

        $tiempoTranscurrido = $tiempoActual - $this->tiempoAnterior;

        // Si es de noche (10pm a 6am)
        if ($hora < 6 || 22 <= $hora)
            return $this->chequeoNocturno($tiempoTranscurrido, $hora);

        // Los sabados
        if ($dia == self::sabado)
            return $this->chequeoSabados($tiempoTranscurrido, $hora);

        if ($dia == self::domingo || $esFeriado)
            return $this->chequeoDomingosYFeriados($tiempoTranscurrido, $hora);
        
        return $this->chequeoNormal($tiempoTranscurrido, $hora);
    }

    private function chequeoNormal ($tiempoTranscurrido, $hora) {
        return $tiempoTranscurrido <= self::una_hora;
    }

    private function chequeoNocturno ($tiempoTranscurrido, $hora) {
        return $tiempoTranscurrido <= self::una_hora_y_media;
    }

    private function chequeoSabados ($tiempoTranscurrido, $hora) {
        // de 6 a 14
        if ($hora < 14) {
            return $tiempoTranscurrido <= self::una_hora;
        } else {
            return $tiempoTranscurrido <= self::una_hora_y_media;
        }
    }

    private function chequeoDomingosYFeriados ($tiempoTranscurrido, $hora) {
        return $tiempoTranscurrido <= self::una_hora_y_media;
    }

    // Esto quizas deberia ser un metodo de la clase Colectivo
    // NOTE: Creeria que se puede simplificar a solamente comparar por numero
    protected function colectivosDiferentes($colectivo) {
        if ($this->colectivoAnterior == null)
            return true;

        $linea1 = $this->colectivoAnterior->linea();
        $linea2 = $colectivo->linea();

        $bandera1 = $this->colectivoAnterior->bandera();
        $bandera2 = $colectivo->bandera();

        $numero1 = $this->colectivoAnterior->numero();
        $numero2 = $colectivo->numero();

        return $linea1 != $linea2 || $bandera1 != $bandera2 || $numero1 != $numero2;
    }
};

