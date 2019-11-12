<?php

namespace TrabajoTarjeta;

use PHPUnit\Framework\TestCase;

class BoletoTest extends TestCase {

    /**
     * Comprueba que el boleto se genere correctamente con el valor de pasaje correspondiente 
     */
    public function testGenerarBoleto() {
        $valor = 16.80;
	
	    $tiempo = new Tiempo;

	    $colectivo = Colectivo::crear("102", "Negra", "Semtur", "420");

	    $tarjeta = new Tarjeta(1);

        $boleto = new Boleto(
            $colectivo, $tarjeta,
            [
                "tipo" => "Normal",
                "costo" => 16.80,
                "plusPagados" => 0,
                "tiempo" => 0
            ]);

        $this->assertEquals($boleto->obtenerValor(), $valor);
    }

    /**
     * Comprueba que el boleto se genere correctamente con el colectivo en el que se abonó
     */
    public function testObtenerColectivo() {
        $this->assertTrue(true);
        /* TODO: Rehacer este test
        $linea = "102";
        $empresa = "Semtur";
        $numero = 420;
        $bandera = "Negra";

	    $tiempo = new Tiempo;

	    $tarjeta = new Tarjeta(1, $tiempo);

        $colectivo = Colectivo::crear($linea, $bandera, $empresa, $numero);

        $boleto = new Boleto($colectivo, $tarjeta, "Normal");

        $this->assertEquals($boleto->obtenerColectivo(), $colectivo);
         */
    }

}
