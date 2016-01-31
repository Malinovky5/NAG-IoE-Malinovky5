//////////////////////////////////////
////           MALINOVKY5         ////
////          Meteostanice        ////
//////////////////////////////////////

#include <Timer.h>
#include <StopWatch.h>
#include <SFE_BMP180.h>
#include <Wire.h>
#include <Servo.h>
#include <dht11.h>
#include <OneWire.h>
#include <BH1750FVI.h>

 // Piny
 int directionPin = A0;
 int speedPin = 2;
 int rainPin = 3;
 int servoPin = 4;
 int dhtPin = 5;
 int oneWirePin = 6;

 // Promenne
 String directionString = "unknow"; // Aktualni svetova strana 
 float rainPerHour = 0; // Pocet mm srazek za posledni hodinu
 int rainCount = 0; // Preklopeni srazkomeru za posledni hodinu
 int rainValuePrev = 1;
 float speedActual = 0; // rychlost vetru v metrech za sekundu
 int speedValuePrev = 0;
 float pressureValue = 0; // tlak v hPa
 int pressureInitEvent, pressureGetEvent;
 int servoPosition = 80; // Pozice serva
 bool servoDirection = 0;
 int servoTimeUpdate = 100; // cas pro pohyb
 int servoMoveEvent, servoPauseEvent;
 float dsTemp = 0; // Teplota z cidla DS18B20
 int dsGetEvent;
 byte addr[8] = { 0x28, 0xFF, 0xE6, 0x5B, 0x65, 0x15, 0x02, 0x7D }; // adresa naseho DS18B20 (zjistena predem)
 uint16_t lux; // Intenzita svetla z cidla BH1750

 // Instance
 Timer t;
 StopWatch sw_millis;
 SFE_BMP180 pressure;
 Servo servo;
 dht11 DHT;
 OneWire ds(oneWirePin);
 BH1750FVI lightSensor;

#define ALTITUDE 291.0 // Nadmorska vyska ve Frydku-Mistku (v metrech)

 void setup() {
  // Inicializace pinu
  pinMode(speedPin, INPUT);
  digitalWrite(speedPin, HIGH); // Internal pull-up

  pinMode(rainPin, INPUT);
  digitalWrite(rainPin, HIGH);

  // Inicializace pinu serva
  servo.attach(servoPin);
  
  // Nastaveni casovacu
  t.every(3600000, sumUpRain); // Na hodinu pro srazky
  t.every(100, sendData); // Na posilani dat
  servoMoveEvent = t.every(servoTimeUpdate, servoMove); // Pohyb serva
  t.every(3000, tempHumidityLuxDecode); // Ziskava data z DHT11, BH1750 a DS18B20 kazdych 10 sekund

  // Prvni aktivace stopek pro rychlost vetru
  sw_millis.start();

  // Inicializace svetelneho cidla
  lightSensor.begin();
  lightSensor.SetAddress(Device_Address_L);
  lightSensor.SetMode(Continuous_H_resolution_Mode);
  
  // Prvni ziskani dat z DHT11 a DS18B20
  tempHumidityLuxDecode();

  // Inicializace seriove komunikace
  Serial.begin(9600);

  // Inicializace BM180
  if (pressure.begin())
  {
    pressureDecode(); // Prvni zavolani ziskani tlaku
    pressureGetEvent = t.every(5000, pressureDecode); // Kazdych 5 sekund ziskame data
  }
  else
    pressureInitEvent = t.every(500, reinitPressure);
}

void loop() {
  // Cteme hodnoty senzoru
  int directionValue = analogRead(directionPin);
  int speedValue = digitalRead(speedPin);
  int rainValue = digitalRead(rainPin);

  // Volani dekodovacich funkci
  directionDecode(directionValue);
  rainDecode(rainValue);
  speedDecode(speedValue);

  // Aktualizace timeru
  t.update();
}

/// <summary>
/// Dekoduje hodnoty svetovych stran
/// </summary>
/// <param name="directionValue">Dekodovana hodnota</param>
void directionDecode(int directionValue) {
  // Tolerance nahoru a dolu
  int tolerance = 10;

  // Hodnoty jednotlivych svetovych stran
  int north = 234;
  int nortWest = 133;
  int northEast = 560;
  int south = 735;
  int southWest = 390;
  int southEast = 838;
  int west = 75;
  int east = 930;

  // Dekodovani
  if (directionValue >= (north - tolerance) && directionValue <= (north + tolerance))
    directionString = "north";
  if (directionValue >= (nortWest - tolerance) && directionValue <= (nortWest + tolerance))
    directionString = "nortWest";
  if (directionValue >= (northEast - tolerance) && directionValue <= (northEast + tolerance))
    directionString = "northEast";
  if (directionValue >= (south - tolerance) && directionValue <= (south + tolerance))
    directionString = "south";
  if (directionValue >= (southWest - tolerance) && directionValue <= (southWest + tolerance))
    directionString = "southWest";
  if (directionValue >= (southEast - tolerance) && directionValue <= (southEast + tolerance))
    directionString = "southEast";
  if (directionValue >= (west - tolerance) && directionValue <= (west + tolerance))
    directionString = "west";
  if (directionValue >= (east - tolerance) && directionValue <= (east + tolerance))
    directionString = "east";
  if (directionValue == 0)
    directionString = "unconnected";
}

/// <summary>
/// Dekoduje preklopeni u srazkomeru
/// </summary>
/// <param name="rainValue">Dekodovana hodnota</param>
void rainDecode(int rainValue) {
  // Pokud se promenna zmenila do preklopeneho stavu
  if (rainValue != rainValuePrev && rainValue == 1)
    rainCount++; // Pricteme preklopeni (0,41 mm)

  // Ulozime tuto hodnotu
  rainValuePrev = rainValue;
}

/// <summary>
/// Spocte pocet mm srazek za danou hodinu
/// </summary>
void sumUpRain() {
  rainPerHour = rainCount * 0.41; // Pocet preklopeni * 0,41 mm (= jedno preklopeni)
  rainCount = 0; // Vynulovani stavu
}

/// <summary>
/// Dekoduje rychlost vetru
/// </summary>
/// <param name="speedValue">Dekodovana hodnota</param>
void speedDecode(int speedValue) {
  // Pokud se zmenila hodnota
  if (speedValue != speedValuePrev && speedValue == 1) {
    float millisElapsed = sw_millis.elapsed(); // Uplynuly cas
    
    if (millisElapsed != 0)
      speedActual = 282.5 / millisElapsed; // Polovina otocky (celkova draha 0,565m) / trvani v milisekundach
    
    sw_millis.reset(); // Restartujeme casovac pro dalsi pocty
    sw_millis.start(); // Opet nastartujeme casovac
  }

  // Ulozime tuto hodnotu
  speedValuePrev = speedValue;

  // Overeni, zda-li je senzor vubec pripojen (je spojen se smerovym), pokud ne, dava 0
  if (directionString == "unconnected")
    speedActual = 0;
}

/// <summary>
/// Reinicializace tlakoveho cidla
/// </summary>
void reinitPressure() {
  if (pressure.begin())
  {
    t.stop(pressureInitEvent); // Pokud je uspech, zastavi automaticke obnovovani
    pressureDecode(); // Prvni zavolani ziskani tlaku
    pressureGetEvent = t.every(5000, pressureDecode); // Zapne automaticke zjistovani dat
  }
}

/// <summary>
/// Ziskava tlak v hPa
/// </summary>
void pressureDecode() {
  char status;
  double P,T;
  
  status = pressure.startTemperature();
  if (status != 0)
  {
    // Cekani na dokonceni mereni
    delay(status);

    // Musime prvne vycist teplotu, abychom mohli ziskat tlak
    status = pressure.getTemperature(T);
    if (status != 0)
    {
      status = pressure.startPressure(3); // 0-3 (3 = nejvetsi presnost, ale i delka)
      if (status != 0)
      {
        // Cekani na dokonceni mereni
        delay(status);

        status = pressure.getPressure(P,T);
        if (status != 0)
        {
          pressureValue = pressure.sealevel(P,ALTITUDE); // hodnota relativni k nadmorske vysce v hPa
        }
        else pressureValue = 0; // chyba ziskani tlaku
      }
      else pressureValue = 0; // chyba zahajeni ziskani tlaku
    }
    else pressureValue = 0; // chyba ziskani teploty
  }
  else pressureValue = 0; // chyba zahajeni ziskani teploty
}

/// <summary>
/// Pohyb serva
/// </summary>
void servoMove() {
  if (servoPosition == 125 || servoPosition == 35) { // Pokud jsme jiz na konci (180deg nebo 0deg)
    servoDirection = !servoDirection; // Otoceni smeru
    servoPause(10000);
  }

  if (servoPosition == 80) // Pozastaveni uprostred
    servoPause(10000);

  // Pohyb serva
  if (servoDirection)
    servoPosition ++;
  else
    servoPosition --;

  // Odesleme pohyb
  servo.write(servoPosition);
}

/// <summary>
/// Pozastavuje na dany cas servo
/// </summary>
/// <param name="pauseMillis">Cas v milisekundach, po ktery ma servo zustat stat</param>
void servoPause(int pauseMillis) {
  t.stop(servoMoveEvent); // pozastavi pohyb serva
  servoPauseEvent = t.every(pauseMillis, servoUnpause); // odpauzuje servo za dany cas v ms
}

/// <summary>
/// Vraci servo do provozu
/// </summary>
void servoUnpause() {
  t.stop(servoPauseEvent); // vypne pauzu
  servoMoveEvent = t.every(servoTimeUpdate, servoMove); // Pohyb serva
}

/// <summary>
/// Ziska hodnoty z cidla DHT11 a DS18B20
/// </summary>
void tempHumidityLuxDecode() {
  // DHT 11
  DHT.read(dhtPin);

  //BH1750
  lux = lightSensor.GetLightIntensity();

  // DS18B20
  ds.reset();
  ds.select(addr);
  ds.write(0x44, 1); // Zahaji konverzaci s DS18B20

  // Je treba alespon 1000ms delay, ktery kvuli realtime musime provest pomoci timeru
  dsGetEvent = t.every(1000, dallasDecode);
}

void dallasDecode() {
  t.stop(dsGetEvent); // Zastaveni casovace

  byte i;
  byte present = 0;
  byte type_s;
  byte data[12];
  
  present = ds.reset();
  ds.select(addr);    
  ds.write(0xBE);

  for ( i = 0; i < 9; i++) {    // we need 9 bytes
    data[i] = ds.read();
  }

  int16_t raw = (data[1] << 8) | data[0];
  if (type_s) {
    raw = raw << 3; // 9 bit resolution default
    if (data[7] == 0x10) {
      // "count remain" gives full 12 bit resolution
      raw = (raw & 0xFFF0) + 12 - data[6];
    }
  } else {
    byte cfg = (data[4] & 0x60);
    // at lower res, the low bits are undefined, so let's zero them
    if (cfg == 0x00) raw = raw & ~7;  // 9 bit resolution, 93.75 ms
    else if (cfg == 0x20) raw = raw & ~3; // 10 bit res, 187.5 ms
    else if (cfg == 0x40) raw = raw & ~1; // 11 bit res, 375 ms
    //// default is 12 bit resolution, 750 ms conversion time
  }

  dsTemp = (float)raw / 16.0; // Nase finalni hodnota
}

/// <summary>
/// Odesilani dat pres seriovou linku ve forme json
/// </summary>
void sendData() {
  String jsonData = "{\"Direction\": \"" + directionString + "\",\"Rain\": \"" + rainPerHour + "\",\"Speed\": \"" + speedActual + "\",\"Pressure\": \"" + pressureValue + "\",\"Humidity\": \"" + DHT.humidity + "\",\"Temperature\": \"" + dsTemp + "\",\"Lux\": \"" + lux + "\"}";
  Serial.println(jsonData);
}
