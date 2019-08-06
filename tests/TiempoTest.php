<?php

namespace TrabajoTarjeta;

use PHPUnit\Framework\TestCase;

class TiempoTest extends TestCase {

    /**
     * Comprueba que funcionen las distintas funcionalidades de feriado, en el tipo Tiempo
     */
    public function testTiempoFeriados() {
        $tiempo = new Tiempo;

        //Comprobamos que esFeriado devuelve FALSE, o sea, no es feriado
        $this->assertFalse($tiempo->esFeriado());

        //Ahora, que cambiarFeriado cambie a feriado correctamente
        $tiempo->cambiarFeriado();
        $this->assertTrue($tiempo->esFeriado());

        //Dejamos que sea feriado
        $tiempo->cambiarFeriado();

        //Y comprobamos que efectivamente no lo sea
        $this->assertFalse($tiempo->esFeriado());
    }

    /**
     * Comprueba que funcionen las distintas funcionalidades de feriado, en el tipo TiempoFalso
     */
    public function testTiempoFalsoFeriados() {
        $tiempo = new TiempoFalso;

        //Comprobamos que esFeriado devuelve FALSE, o sea, no es feriado
        $this->assertFalse($tiempo->esFeriado());

        //Ahora, que cambiarFeriado cambie a feriado correctamente
        $tiempo->cambiarFeriado();
        $this->assertTrue($tiempo->esFeriado());

        //Dejamos que sea feriado
        $tiempo->cambiarFeriado();

        //Y comprobamos que efectivamente no lo sea
        $this->assertFalse($tiempo->esFeriado());
    }
}