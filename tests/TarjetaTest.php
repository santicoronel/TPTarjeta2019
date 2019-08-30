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

    /**
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Normal" 
     */
    public function testTrasbordoNormal(){
        $tiempo = new TiempoFalso;
        $tarjeta = new Tarjeta(1, $tiempo);
        
        $negra102 = new Colectivo ("102", "Negra", "Semtur", 2);
        $roja102 = new Colectivo ("102", "Roja", "Semtur", 3);
        $negra103 = new Colectivo ("103", "Negra", "Semtur", 23);
        $negra102diferente = new Colectivo ("102", "Negra", "Semtur", 65);

        $tarjeta->recargar(100);
        $negra102->pagarCon($tarjeta);

        $tiempo->avanzar(5400); //Avanzamos 90 minutos

        //Test franja nocturna, pueden pasar hasta 90 minutos
        $this->assertEquals($roja102->pagarCon($tarjeta), new Boleto($roja102, $tarjeta, "Trasbordo")); //Chequeamos que el boleto sea de tipo trasbordo
        $this->assertEquals($tarjeta->obtenerSaldo(), 77.66);
        $this->assertNotEquals($roja102->pagarCon($tarjeta), new Boleto($roja102, $tarjeta, "Trasbordo")); //Verificamos que no pueda emitir dos trasbordos seguidos
        
        $tiempo->avanzar(16200); //Avanzamos hasta las 6 de la mañana
        
        //Test feriado, límite de tiempo: 90 minutos
        $negra102->pagarCon($tarjeta);
        
        $tiempo->avanzar(5400); //Avanzamos hora y media

        $tarjeta->cFeriado();
        $this->assertEquals($negra103->pagarCon($tarjeta), new Boleto($negra103, $tarjeta, "Trasbordo")); //Comprobamos que se emita un trasbordo
        $tarjeta->cFeriado();
        $this->assertEquals($tarjeta->obtenerSaldo(), 38.52);
        $this->assertNotEquals($negra103->pagarCon($tarjeta), new Boleto($negra103, $tarjeta, "Trasbordo")); //Comprobamos que se emita un boleto normal

        $tiempo->avanzar(6000); //Hacemos pasar 100 minutos

        $this->assertNotEquals($negra103->pagarCon($tarjeta), new Boleto($negra103, $tarjeta, "Trasbordo")); //Y verificamos que no se emita un trasbordo fuera del limte de tiempo
        
        $tarjeta->recargar(510.15); //le cargamos un total de 597 pesos

        //Test sábados, distintas franjas horarias
        $tiempo->avanzar(172800); //Avanzamos al sabado a las 9:10

        $roja102->pagarCon($tarjeta);
        
        $tiempo->avanzar(3600); //Una hora después...

        $this->assertEquals($negra102->pagarCon($tarjeta), new Boleto($negra102, $tarjeta, "Trasbordo")); //Comprobamos que se emita un trasbordo
    
        $tiempo->avanzar(18900); //Nos movemos al sábado a las 15:25
        
        $negra102->pagarCon($tarjeta);

        $tiempo->avanzar(4500); //Avanzamos 90 minutos

        $this->assertEquals($negra103->pagarCon($tarjeta), new Boleto($negra103, $tarjeta, "Trasbordo")); //Comprobamos que no se emita trasbordo en colectivos distintos con la misma bandera

        //Test domingos, pueden pasar hasta 90 minutos
        $tiempo->avanzar(86400);

        $negra102->pagarCon($tarjeta);

        $tiempo->avanzar(600); //Avanzo 10 minutos
        
        $this->assertNotEquals($negra102->pagarCon($tarjeta), new Boleto($negra102, $tarjeta, "Trasbordo")); //Comprobamos que se puede emitir el trasbordo
        
        $tiempo->avanzar(86400); //Nos movemos al lunes a las 16:50

        //Test lunes a viernes, franja diurna. Limite de tiempo: 60 minutos
        $negra102->pagarCon($tarjeta);
        
        $tiempo->avanzar(2400); //Avanzamos 40 minutos

        $this->assertEquals($negra103->pagarCon($tarjeta), new Boleto($negra103, $tarjeta, "Trasbordo")); //Comprobamos que se emita un trasbordo

        $negra102->pagarCon($tarjeta);

        $tiempo->avanzar(600);

        $this->assertNotEquals($negra102diferente->pagarCon($tarjeta), new Boleto($negra102diferente, $tarjeta, "Trasbordo")); //Comprobamos que se puede emitir el trasbordo en un colectivo con la misma linea y bandera que el anterior

    }

    /**
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio" 
     */
    public function testTrasbordoMedio(){
        $tiempo = new TiempoFalso;
        $medio = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedio);
                                                                                                                            
        $negra102 = new Colectivo ("102", "Negra", "Semtur", 2);
        $roja102 = new Colectivo ("102", "Roja", "Semtur", 3);
        $negra103 = new Colectivo ("103", "Negra", "Semtur", 23);
        $negra102diferente = new Colectivo ("102", "Negra", "Semtur", 65);

        $medio->recargar(100);
        $negra102->pagarCon($medio);

        $tiempo->avanzar(5400); //Avanzamos 90 minutos

        //Test franja nocturna, pueden pasar hasta 90 minutos
        $this->assertEquals($roja102->pagarCon($medio), new Boleto($roja102, $medio, "Trasbordo")); //Chequeamos que el boleto sea de tipo trasbordo
        $this->assertEquals($medio->obtenerSaldo(), 88.83);
        $this->assertNotEquals($roja102->pagarCon($medio), new Boleto($roja102, $medio, "Trasbordo")); //Verificamos que no pueda emitir dos trasbordos seguidos
        
        $tiempo->avanzar(16200); //Avanzamos hasta las 6 de la mañana
        
        //Test feriado, límite de tiempo: 90 minutos
        $negra102->pagarCon($medio);
        
        $tiempo->avanzar(5400); //Avanzamos hora y media

        $medio->cFeriado();
        $this->assertEquals($negra103->pagarCon($medio), new Boleto($negra103, $medio, "Trasbordo")); //Comprobamos que se emita un trasbordo
        $medio->cFeriado();
        $this->assertEquals($medio->obtenerSaldo(), 69.26);
        $this->assertNotEquals($negra103->pagarCon($medio), new Boleto($negra103, $medio, "Trasbordo")); //Comprobamos que se emita un boleto normal

        $tiempo->avanzar(6000); //Hacemos pasar 100 minutos

        $this->assertNotEquals($negra103->pagarCon($medio), new Boleto($negra103, $medio, "Trasbordo")); //Y verificamos que no se emita un trasbordo fuera del limte de tiempo
        
        $medio->recargar(510.15); //le cargamos un total de 597 pesos

        //Test sábados, distintas franjas horarias
        $tiempo->avanzar(172800); //Avanzamos al sabado a las 9:10

        $roja102->pagarCon($medio);
        
        $tiempo->avanzar(3600); //Una hora después...

        $this->assertEquals($negra102->pagarCon($medio), new Boleto($negra102, $medio, "Trasbordo")); //Comprobamos que se emita un trasbordo
    
        $tiempo->avanzar(18900); //Nos movemos al sábado a las 15:25

        $negra102->pagarCon($medio);

        $tiempo->avanzar(4500); //Avanzamos 90 minutos

        $this->assertEquals($negra103->pagarCon($medio), new Boleto($negra103, $medio, "Trasbordo")); //Comprobamos que que se emite un trasbordo

        //Test domingos, pueden pasar hasta 90 minutos
        $tiempo->avanzar(86400);

        $negra102->pagarCon($medio);

        $tiempo->avanzar(600); //Avanzo 10 minutos
        
        $this->assertNotEquals($negra102->pagarCon($medio), new Boleto($negra102, $medio, "Trasbordo")); //Comprobamos que no se pueden emitir trasbordos en el mismo colectivo
        
        $tiempo->avanzar(86400); //Nos movemos al lunes a las 16:50

        //Test lunes a viernes, franja diurna. Limite de tiempo: 60 minutos
        $negra102->pagarCon($medio);
        
        $tiempo->avanzar(2400); //Avanzamos 40 minutos

        $this->assertEquals($negra103->pagarCon($medio), new Boleto($negra103, $medio, "Trasbordo")); //Comprobamos que se emita un trasbordo   
        
        $negra102->pagarCon($medio);

        $tiempo->avanzar(600);

        $this->assertNotEquals($negra102diferente->pagarCon($medio), new Boleto($negra102diferente, $medio, "Trasbordo")); //Comprobamos que se puede emitir el trasbordo en un colectivo con la misma linea y bandera que el anterior

    }

    /**
     * Comprueba el funcionamiento del trasbordo en todos los casos posibles, con una tarjeta de tipo "Medio Universitario" 
     */
    public function testTrasbordoMedioUni(){
        $tiempo = new TiempoFalso;
        $medioUni = new Tarjeta(1, $tiempo, new EstrategiaDeCobroMedioUniversitario);
                                                                                                                            
        $negra102 = new Colectivo ("102", "Negra", "Semtur", 2);
        $roja102 = new Colectivo ("102", "Roja", "Semtur", 3);
        $negra103 = new Colectivo ("103", "Negra", "Semtur", 23);
        $negra102diferente = new Colectivo ("102", "Negra", "Semtur", 65);

        $medioUni->recargar(100);
        $negra102->pagarCon($medioUni);

        $tiempo->avanzar(5400); //Avanzamos 90 minutos

        //Test franja nocturna, pueden pasar hasta 90 minutos
        $this->assertEquals($roja102->pagarCon($medioUni), new Boleto($roja102, $medioUni, "Trasbordo")); //Chequeamos que el boleto sea de tipo trasbordo
        $this->assertEquals($medioUni->obtenerSaldo(), 88.83);
        $this->assertNotEquals($roja102->pagarCon($medioUni), new Boleto($roja102, $medioUni, "Trasbordo")); //Verificamos que no pueda emitir dos trasbordos seguidos
        
        $tiempo->avanzar(16200); //Avanzamos hasta las 6 de la mañana
        
        //Test feriado, límite de tiempo: 90 minutos
        $negra102->pagarCon($medioUni);
        
        $tiempo->avanzar(5400); //Avanzamos hora y media

        $medioUni->cFeriado();
        $this->assertEquals($negra103->pagarCon($medioUni), new Boleto($negra103, $medioUni, "Trasbordo")); //Comprobamos que se emita un trasbordo
        $medioUni->cFeriado();
        $this->assertEquals($medioUni->obtenerSaldo(), 49.69);
        $this->assertNotEquals($negra103->pagarCon($medioUni), new Boleto($negra103, $medioUni, "Trasbordo")); //Comprobamos que se emita un boleto normal

        $tiempo->avanzar(6000); //Hacemos pasar 100 minutos

        $this->assertNotEquals($negra103->pagarCon($medioUni), new Boleto($negra103, $medioUni, "Trasbordo")); //Y verificamos que no se emita un trasbordo fuera del limte de tiempo
        
        $medioUni->recargar(510.15); //le cargamos un total de 597 pesos

        //Test sábados, distintas franjas horarias
        $tiempo->avanzar(172800); //Avanzamos al sabado a las 9:10

        $roja102->pagarCon($medioUni);
        
        $tiempo->avanzar(3600); //Una hora después...

        $this->assertEquals($negra102->pagarCon($medioUni), new Boleto($negra102, $medioUni, "Trasbordo")); //Comprobamos que se emita un trasbordo
    
        $tiempo->avanzar(18900); //Nos movemos al sábado a las 15:25

        $negra102->pagarCon($medioUni);

        $tiempo->avanzar(4500); //Avanzamos 90 minutos

        $this->assertEquals($negra103->pagarCon($medioUni), new Boleto($negra103, $medioUni, "Trasbordo")); //Comprobamos que que se emite un trasbordo

        //Test domingos, pueden pasar hasta 90 minutos
        $tiempo->avanzar(86400);

        $negra102->pagarCon($medioUni);

        $tiempo->avanzar(600); //Avanzo 10 minutos
        
        $this->assertNotEquals($negra102->pagarCon($medioUni), new Boleto($negra102, $medioUni, "Trasbordo")); //Comprobamos que no se pueden emitir trasbordos en el mismo colectivo
        
        $tiempo->avanzar(86400); //Nos movemos al lunes a las 16:50

        //Test lunes a viernes, franja diurna. Limite de tiempo: 60 minutos
        $negra102->pagarCon($medioUni);
        
        $tiempo->avanzar(2400); //Avanzamos 40 minutos

        $this->assertEquals($negra103->pagarCon($medioUni), new Boleto($negra103, $medioUni, "Trasbordo")); //Comprobamos que se emita un trasbordo   
        
        $negra102->pagarCon($medioUni);

        $tiempo->avanzar(600);

        $this->assertNotEquals($negra102diferente->pagarCon($medioUni), new Boleto($negra102diferente, $medioUni, "Trasbordo")); //Comprobamos que se puede emitir el trasbordo en un colectivo con la misma linea y bandera que el anterior

    }
    
    
   
}
