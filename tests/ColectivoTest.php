<?php

namespace TrabajoTarjeta;

use PHPUnit\Framework\TestCase;

class ColectivoTest extends TestCase {


    /**
     * Comprueba que no se pueda pagar un boleto sin el saldo suficiente
     */
    public function testPagarSaldoInsuf() {
        $colectivo = new Colectivo("102", "Negra", "Semtur", 420);

	    $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo );

        $tarjeta->recargar(10);

        $colectivo->pagarCon($tarjeta);   //hacemos los dos viajes plus para que
        $colectivo->pagarCon($tarjeta);   //se quede sin viajes y testeamos

        $this->assertEquals($tarjeta->obtenerSaldo(),10);
        $this->assertFalse($colectivo->pagarCon($tarjeta));
    }

    /**
     * Comprueba que se pueda abonar un viaje si el saldo es suficiente
     */
    public function testPagarSaldoSuf(){
        $colectivo = new Colectivo("102", "Negra", "Semtur", "420") ;

	    $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo);

        $tarjeta->recargar(20);

        //Testeamos que al pagar con la tarjeta con saldo suficiente se emite un boleto correcto
        $this->assertEquals("Normal", $colectivo->pagarCon($tarjeta)->tipoDeBoleto());
        $this->assertEquals($tarjeta->obtenerSaldo(),3.2);
    }

    /**
     * Comprueba que se emitan correctamente los viajes plus
     */
    public function testViajesPlus() {
        $colectivo = new Colectivo("102", NULL, NULL, NULL);

	    $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo);
        $tarjeta2 = new Tarjeta(2, $tiempo);

        $tarjeta->recargar(10);
        $colectivo->pagarCon($tarjeta);
        $tarjeta->recargar(10);

        $this->assertEquals(
            "Plus2", $colectivo->pagarCon($tarjeta)->tipoDeBoleto());

        // recargamos una cantidad insuficiente de dinero en la tarjeta para
        // que esta utilice los viajes plus
        $tarjeta2->recargar(10);

        //primero testeamos si se emite correctamente el primer plus
        $this->assertEquals(
            "Plus1", $colectivo->pagarCon($tarjeta2)->tipoDeBoleto());

        //y luego si se emite correctamente el boleto del ultimo plus
        $this->assertEquals(
            "Plus2", $colectivo->pagarCon($tarjeta2)->tipoDeBoleto());

    }

    /**
     * Comprueba el funcionamiento de las franquicias en todos los casos de emisión de boletos posibles
     */
    public function testFranquicias(){
        $colectivo = new Colectivo("102", NULL, NULL, NULL);

	    $tiempo = new TiempoFalso;
        $tarjeta = new Tarjeta(1, $tiempo);
        $compl = new Tarjeta(0, $tiempo, new Completo);
        $medio = new Tarjeta(2, $tiempo, new EstrategiaDeCobroMedio);
        $medioUni = new Tarjeta(3, $tiempo, new EstrategiaDeCobroMedioUniversitario);

        $medio->recargar(10);
        $medioUni->recargar(10);

        $this->assertEquals("Normal", $colectivo->pagarCon($compl)->tipoDeBoleto());
        $this->assertEquals("Normal", $colectivo->pagarCon($medio)->tipoDeBoleto());
        $this->assertEquals("Normal", $colectivo->pagarCon($medioUni)->tipoDeBoleto());
        $this->assertEquals($medio->obtenerSaldo(),1.6);
        $this->assertEquals($medioUni->obtenerSaldo(),1.6);

        // Avanzo 6 minutos para asegurarme que puedo viajar con el medio
        $tiempo->avanzar(60 * 6);

        //Genero Viaje Plus
        $colectivo->pagarCon($medio);
        $colectivo->pagarCon($medioUni);

        //Cargo como para pagar un medio
        $medio->recargar(10);
        $medioUni->recargar(10);

        // Por la misma razon de antes, avanzo 6 minutos
        $tiempo->avanzar(60 * 6);

        // Pero no puedo porque debo un plus
        $this->assertEquals("Plus2", $colectivo->pagarCon($medio)->tipoDeBoleto());
        $this->assertEquals("Plus2", $colectivo->pagarCon($medioUni)->tipoDeBoleto());

    }

    /**
     * Comprueba que se obtengan correctamente los datos de un colectivo
     */
    public function testDatosColectivo(){
        $colectivo = new Colectivo("102", "Negra", "Semtur", 2);

        $this->assertEquals($colectivo->linea(), 102);
        $this->assertEquals($colectivo->empresa(), "Semtur");
        $this->assertEquals($colectivo->numero(), 2);
    }

    /**
     * Comprueba que las deudas de los viajes plus se abonen correctamente o no en función al saldo de la tarjeta,
     * con una tarjeta de tipo "Normal"
     */
    public function testDebePlusNormal(){
        // TODO: Descomentar las lineas del boleto falso en este test

        $tiempo = new Tiempo;
        $tarjeta = new Tarjeta(1, $tiempo);
        $colectivo = new Colectivo("102", "Negra", "Semtur", 40);

        $tarjeta->recargar(10);

        //usamos un viaje plus
        $colectivo->pagarCon($tarjeta);

        // Comprobamos que nos queda un plus
        $this->assertEquals($tarjeta->verPlus(), 1);


        $tarjeta->recargar(30);
        $boletoReal = $colectivo->pagarCon($tarjeta);
        // $boletoFalso = new Boleto($colectivo, $tarjeta, "AbonaPlus", 1);

        //comprobamos que se abona un viaje plus que debia y que la descripcion coincide
        // $this->assertEquals($boletoReal, $boletoFalso);
        // $this->assertEquals($boletoReal->obtenerDescripcion(), "Abona Viajes Plus 16.8 y");

        // Comprobamos que la cantidad de plus se reinicia
        // Ahora nos deberian quedar todos los (2) plus
        $this->assertEquals($tarjeta->verPlus(), 2);
        $this->assertEquals($tarjeta->obtenerSaldo(), 6.4);

        //pagamos dos veces utilizando los viajes plus
        $colectivo->pagarCon($tarjeta);
        $colectivo->pagarCon($tarjeta);

        // Comprobamos que gastamos los 2 plus, nos quedan 0
        $this->assertEquals($tarjeta->verPlus(), 0);

        //comprobamos que con el mismo saldo no podemos viajar
        $this->assertFalse($colectivo->pagarCon($tarjeta));

        $tarjeta->recargar(30);
        // Ahora tenemos 30 + 6.4 = 36.4 de saldo

        // Para pagar 2 plus y un pasaje necesitamos 50.4 de saldo
        $this->assertFalse($colectivo->pagarCon($tarjeta));

        //solo cuando carguemos el valor de nuestro boleto + los plus que debamos, se emitira un boleto
        $tarjeta->recargar(20);
        // Ahora el saldo es 56.4, suficiente para pagar un boleto y 2 plus

        $boletoReal = $colectivo->pagarCon($tarjeta);
        // $boletoFalso = new Boleto($colectivo, $tarjeta, "AbonaPlus", 2);

        // $this->assertEquals($boletoReal, $boletoFalso);
        // $this->assertEquals($boletoFalso->obtenerDescripcion(), "Abona Viajes Plus 33.6 y");
        $this->assertEquals($tarjeta->obtenerSaldo(), 6.0 );

    }

    /**
     * Comprueba que las deudas de los viajes plus se abonen correctamente o no en función al saldo de la tarjeta,
     * con una tarjeta de tipo "Medio"
     */
    public function testDebePlusMedio(){
        $tiempo = new TiempoFalso;
        $medio = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedio);
        $colectivo = new Colectivo("102", "Negra", "Semtur", 40);

        $medio->recargar(10);
        $colectivo->pagarCon($medio); //boleto normal

        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);

        $colectivo->pagarCon($medio); //viaje plus 1


        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);

        $medio->recargar(30);  //cargamos suficiente para pagar el plus y abonamos el plus que debiamos y el medio boleto normal
        // $abono1 = new Boleto($colectivo, $medio, "AbonaPlus", 1);
        $boleto = $colectivo->pagarCon($medio);
        // $this->assertEquals($abono1, $boleto);
        $this->assertEquals($boleto->obtenerDescripcion(), "Abona Viajes Plus 16.8 y");
        $this->assertEquals($medio->obtenerSaldo(), 6.4 );

        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);
        $colectivo->pagarCon($medio);
        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);
        $colectivo->pagarCon($medio); //usamos los 2 plus

        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);
        $this->assertFalse($colectivo->pagarCon($medio)); //ahora no podremos pagar

        // Recordar que el medio solo se puede usar cada >= 5 minutos
        $tiempo->avanzar(60 * 6);
        $medio->recargar(50);
        // $abono2 = new Boleto($colectivo, $medio, "AbonaPlus", 2);
        $boleto = $colectivo->pagarCon($medio);
        // $this->assertEquals($abono2, $boleto);
        $this->assertEquals($boleto->obtenerDescripcion(), "Abona Viajes Plus 33.6 y");
        $this->assertEquals($medio->obtenerSaldo(), 14.4 );

    }

}
