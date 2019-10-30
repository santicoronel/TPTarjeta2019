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
        $colectivo = new Colectivo("102", "Negra", "Semtur", 2);

        $medio->recargar(20);

        $this->assertEquals($colectivo->pagarCon($medio), new Boleto($colectivo, $medio, "Normal")); // se comprueba que se emite medio normal
        $tiempo->avanzar(150); //y al pasar dos minutos y medio

        $this->assertFalse($colectivo->pagarCon($medio)); //no puede pagar

        $tiempo->avanzar(180); //pero al pasar otros 3 minutos

        $this->assertEquals($colectivo->pagarCon($medio), new Boleto($colectivo, $medio, "Normal")); //se emite un medio normal sin problemas
  }

    /**
     * Comprueba que se puedan emitir dos medios universitarios por día
     */
    public function testLimiteMedioUni(){
        $tiempo = new TiempoFalso;
        $uni = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedioUniversitario);
        $colectivo = new Colectivo("102", "Negra", "Semtur", 3);

        $uni->recargar(50);


        $this->assertEquals($colectivo->pagarCon($uni), $medio1 = new Boleto($colectivo, $uni, "Normal"));
        $this->assertEquals($medio1->obtenerValor(), 8.4);  //pago medio boleto

        $tiempo->avanzar(3600); //avanzar una hora

        $this->assertEquals($colectivo->pagarCon($uni), $medio2 = new Boleto($colectivo, $uni, "Normal"));
        $this->assertEquals($medio2->obtenerValor(), 8.4); //pago segundo medio boleto


        $tiempo->avanzar(3600); //avanzamos una hora en el tiempo

        $this->assertEquals($colectivo->pagarCon($uni), $boleto = new Boleto($colectivo, $uni, "Normal"));
        $this->assertEquals($boleto->obtenerValor(), 16.8); // y pagamos un boleto normal porque ya usamos los 2 medios que teniamos disponibles

        $tiempo->avanzar(86400);//avanzamos un dia en el tiempo

        $this->assertEquals($colectivo->pagarCon($uni), $boleto = new Boleto($colectivo, $uni, "Normal"));
        $this->assertEquals($boleto->obtenerValor(), 8.4); // se emite el primer medio ya que paso un dia
    }

    public function provider () {
        $tiempo1 = new TiempoFalso;
        $tiempo2 = new TiempoFalso;
        $tiempo3 = new TiempoFalso;
        return [
            [
                $tiempo1,
                new Tarjeta(1, $tiempo1, new EstrategiaDeCobroNormal),
                new Colectivo("102", "Negra", "Semtur", 1),
                new Colectivo("102", "Roja", "Semtur", 2)
            ], [
                $tiempo2,
                new Tarjeta(1, $tiempo2, new EstrategiaDeCobroMedio),
                new Colectivo("102", "Negra", "Semtur", 3),
                new Colectivo("102", "Roja", "Semtur", 4)
            ], [
                $tiempo3,
                new Tarjeta(1, $tiempo3, new EstrategiaDeCobroMedioUniversitario),
                new Colectivo("102", "Negra", "Semtur", 5),
                new Colectivo("102", "Roja", "Semtur", 6)
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
        $tarjeta->cFeriado();

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

        $tarjeta->cFeriado();
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
        $tiempo->avanzar($hora + 30 * $minuto);

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

        $colectivo1->pagarCon($tarjeta);

        //Avanzo 10 minutos
        $tiempo->avanzar(600);

        //Comprobamos que no se pueden emitir trasbordos en el mismo colectivo
        $this->assertNotEquals($colectivo1->pagarCon($tarjeta), new Boleto($colectivo1, $tarjeta, "Trasbordo"));

        $tiempo->avanzar(86400); //Nos movemos al lunes a las 16:50
    }

    /**
     * @dataProvider provider
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio"
     */
    public function testTrasbordoDiurno($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $tarjeta->recargar(100);

        //Test lunes a viernes, franja diurna. Limite de tiempo: 60 minutos
        $colectivo1->pagarCon($tarjeta);

        //Avanzamos 40 minutos
        $tiempo->avanzar(2400);

        //Comprobamos que se emita un trasbordo
        $this->assertEquals(
            $colectivo2->pagarCon($tarjeta),
            new Boleto($colectivo2, $tarjeta, "Trasbordo"));
    }

    /**
     * @dataProvider provider
     */
    public function testTrasbordoMismaLineaDistintaUnidad($tiempo, $tarjeta, $colectivo1, $colectivo2){
        $this->assertTrue(true);
        /* TODO: Hacer este test.
        $tarjeta->recargar(100);

        $negra102->pagarCon($medio);
        $tiempo->avanzar(600);

        // Comprobamos que se puede emitir el trasbordo en un colectivo con la
        // misma linea y bandera que el anterior
        $this->assertEquals($negra102diferente->pagarCon($medio), new Boleto($negra102diferente, $medio, "Trasbordo"));
         */
    }
}
