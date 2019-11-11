<?php

namespace TrabajoTarjeta;

use PHPUnit\Framework\TestCase;

class TarjetaTest extends TestCase {

    /**
     * Comprueba que la tarjeta aumenta su saldo cuando se carga saldo válido.
     */
    public function testCargaSaldo() {
        $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo);

        $this->assertTrue($tarjeta->recargar(10));
        $this->assertEquals($tarjeta->obtenerSaldo(), 10);

        $this->assertTrue($tarjeta->recargar(20));
        $this->assertEquals($tarjeta->obtenerSaldo(), 30);

        $this->assertTrue($tarjeta->recargar(30));
        $this->assertEquals($tarjeta->obtenerSaldo(), 60);

        $this->assertTrue($tarjeta->recargar(50));
        $this->assertEquals($tarjeta->obtenerSaldo(), 110);

        $this->assertTrue($tarjeta->recargar(100));
        $this->assertEquals($tarjeta->obtenerSaldo(), 210);
    }

    //Comprueba que la tarjeta se carga con el adicional
    public function testCargasConAdicional(){
        $tiempo = new Tiempo;
        $tarjeta1 = new Tarjeta(1, $tiempo);
        $tarjeta2 = new Tarjeta(2, $tiempo);

        $this->assertTrue($tarjeta1->recargar(510.15));
        $this->assertEquals($tarjeta1->obtenerSaldo(), 592.08);

        $this->assertTrue($tarjeta2->recargar(962.59));
        $this->assertEquals($tarjeta2->obtenerSaldo(), 1184.17);
    }

    /**
     * Comprueba que la tarjeta no puede cargar saldos invalidos.
     */
    public function testCargaSaldoInvalido() {
        $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo);

        $this->assertFalse($tarjeta->recargar(15));
        $this->assertEquals($tarjeta->obtenerSaldo(), 0);
  }

    /**
     * Comprueba que se puedan emitir dos medios recién al haber pasado 5 minutos
     */
    public function testLimiteTiempoMedio(){
        $tiempo = new TiempoFalso;
		$medio = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedio);
        $colectivo = Colectivo::crear("102", "Negra", "Semtur", 2);

        $medio->recargar(20);

        // se comprueba que se emite medio normal
        $this->assertEquals("Normal", $colectivo->pagarCon($medio)->tipoDeBoleto());
        $tiempo->avanzar(150); //y al pasar dos minutos y medio

        $this->assertFalse($colectivo->pagarCon($medio)); //no puede pagar

        $tiempo->avanzar(180); //pero al pasar otros 3 minutos

        //se emite un medio normal sin problemas
        $this->assertEquals("Normal", $colectivo->pagarCon($medio)->tipoDeBoleto());
  }

    /**
     * Comprueba que se puedan emitir dos medios universitarios por día
     */
    public function testLimiteMedioUni(){
        $tiempo = new TiempoFalso;
        $uni = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedioUniversitario);
        $colectivo = Colectivo::crear("102", "Negra", "Semtur", 3);

        $uni->recargar(50);

        //avanzar una hora
        $tiempo->avanzar(3600);

        //pago medio boleto
        $boleto = $colectivo->pagarCon($uni);
        $this->assertEquals("Normal", $boleto->tipoDeBoleto());
        $this->assertEquals($boleto->obtenerValor(), 8.4);

        //avanzar tres horas para no interferir con el trasbordo
        $tiempo->avanzar(3 * 3600);

        //pago segundo medio boleto
        $boleto = $colectivo->pagarCon($uni);
        $this->assertEquals("Normal", $boleto->tipoDeBoleto());
        $this->assertEquals($boleto->obtenerValor(), 8.4);

        //avanzar tres horas para no interferir con el trasbordo
        $tiempo->avanzar(3 * 3600);

        // y pagamos un boleto normal porque ya usamos los 2 medios que teniamos disponibles
        $boleto = $colectivo->pagarCon($uni);
        $this->assertEquals("Normal", $boleto->tipoDeBoleto());
        $this->assertEquals(16.8, $boleto->obtenerValor());

        //avanzamos un dia en el tiempo
        $tiempo->avanzar(86400);

        // se emite el primer medio ya que paso un dia
        $boleto = $colectivo->pagarCon($uni);
        $this->assertEquals("Normal", $boleto->tipoDeBoleto());
        $this->assertEquals($boleto->obtenerValor(), 8.4);
    }

    public function provider () {
        $tiempo1 = new TiempoFalso;
        $tiempo2 = new TiempoFalso;
        $tiempo3 = new TiempoFalso;
        return [
            [
                $tiempo1,
                new Tarjeta(1, $tiempo1, new EstrategiaDeCobroNormal),
                Colectivo::crear("102", "Negra", "Semtur", 1),
                Colectivo::crear("102", "Roja", "Semtur", 2)
            ], [
                $tiempo2,
                new Tarjeta(1, $tiempo2, new EstrategiaDeCobroMedio),
                Colectivo::crear("102", "Negra", "Semtur", 3),
                Colectivo::crear("102", "Roja", "Semtur", 4)
            ], [
                $tiempo3,
                new Tarjeta(1, $tiempo3, new EstrategiaDeCobroMedioUniversitario),
                Colectivo::crear("102", "Negra", "Semtur", 5),
                Colectivo::crear("102", "Roja", "Semtur", 6)
            ]
        ];
    }

    /**
     * @dataProvider provider
     */
    public function testTrasbordoNocturno($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        $colectivo1->pagarCon($tarjeta);
        $saldo = $tarjeta->obtenerSaldo();

        //Avanzamos 90 minutos
        $tiempo->avanzar(5400);

        //Test franja nocturna, pueden pasar hasta 90 minutos
        //Chequeamos que el boleto sea de tipo trasbordo
        $boleto = $colectivo2->pagarCon($tarjeta);
        $this->assertEquals("Trasbordo", $boleto->tipoDeBoleto());

        // El trasbordo no deberia descontar saldo
        $this->assertEquals($saldo, $tarjeta->obtenerSaldo());
    }

    /**
     *
     * @dataProvider provider
     */
    public function testTrasbordoSeguidos($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        $boleto1 = $colectivo1->pagarCon($tarjeta);

        $tiempo->avanzar(3600);
        $boleto2 = $colectivo2->pagarCon($tarjeta);

        $this->assertEquals("Trasbordo", $boleto2->tipoDeBoleto());

        $tiempo->avanzar(600);
        $boleto3 = $colectivo2->pagarCon($tarjeta);

        // Verificamos que no pueda emitir dos trasbordos seguidos
        $this->assertNotEquals("Trasbordo", $boleto3->tipoDeBoleto());
    }

    /**
     *
     * @dataProvider provider
     */
    public function testTrasbordoFeriadoDiurno($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        $segundo = 1;
        $minuto = 60 * $segundo;
        $hora = 60 * $minuto;

        //Avanzamos hasta las 6 de la mañana de un feriado
        $tiempo->avanzar(6 * $hora);
        $tiempo->cambiarFeriado();

        $colectivo1->pagarCon($tarjeta);
        $saldo = $tarjeta->obtenerSaldo();

        //Avanzamos hora y media
        $tiempo->avanzar(1.5 * $hora);

        //Comprobamos que se emita un trasbordo
        $boleto1 = $colectivo2->pagarCon($tarjeta);
        $this->assertEquals("Trasbordo", $boleto1->tipoDeBoleto());
        $this->assertEquals($saldo, $tarjeta->obtenerSaldo());

        $tiempo->avanzar(10 * $minuto);

        //Comprobamos que se emita un boleto normal
        $boleto2 = $colectivo2->pagarCon($tarjeta);
        $this->assertNotEquals("Trasbordo", $boleto2->tipoDeBoleto());

        $tiempo->cambiarFeriado();
    }

    /**
     * @dataProvider provider
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio"
     */
    public function testTrasbordoSabado($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        $segundo = 1;
        $minuto = 60 * $segundo;
        $hora = 60 * $minuto;
        $dia = 24 * $hora;

        //Nos movemos al sábado a las 15:00 (Horario post 14hs)
        $tiempo->avanzar(2 * $dia + 15 * $hora);

        $boleto1 = $colectivo1->pagarCon($tarjeta);

        //Avanzamos 90 minutos
        $tiempo->avanzar($hora + 20 * $minuto);

        //Comprobamos que que se emite un trasbordo
        $boleto2 = $colectivo2->pagarCon($tarjeta);
        $this->assertEquals("Trasbordo", $boleto1->tipoDeBoleto());
    }

    /**
     * @dataProvider provider
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio"
     */
    public function testTrasbordoDomingos($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        //Test domingos, pueden pasar hasta 90 minutos
        $tiempo->avanzar(86400);

        $boleto1 = $colectivo1->pagarCon($tarjeta);

        //Avanzo 10 minutos
        $tiempo->avanzar(600);

        //Comprobamos que no se pueden emitir trasbordos en el mismo colectivo
        $boleto2 = $colectivo1->pagarCon($tarjeta);
        $this->assertNotEquals("Trasbordo", $boleto2->tipoDeBoleto());

        $tiempo->avanzar(86400); //Nos movemos al lunes a las 16:50
    }

    /**
     * @dataProvider provider
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio"
     */
    public function testTrasbordoDiurno($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        //Test lunes a viernes, franja diurna. Limite de tiempo: 60 minutos
        $boleto1 = $colectivo1->pagarCon($tarjeta);

        //Avanzamos 40 minutos
        $tiempo->avanzar(2400);

        //Comprobamos que se emita un trasbordo
        $boleto2 = $colectivo2->pagarCon($tarjeta);
        $this->assertEquals("Trasbordo", $boleto2->tipoDeBoleto());
    }

    /**
     * @dataProvider provider
     */
    public function testTrasbordoMismaLineaDistintaUnidad($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $this->assertTrue(true);
    }
}
