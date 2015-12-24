
#include <Adafruit_NeoPixel.h>
#ifdef __AVR__
  #include <avr/power.h>
#endif

#define PIN            6
#define NUMPIXELS      56

Adafruit_NeoPixel pixels = Adafruit_NeoPixel(NUMPIXELS, PIN, NEO_GRB + NEO_KHZ800);

int delay_1 = 50;

void setup() {
  pixels.begin(); 
  pixels.setBrightness(30);
  Serial.begin(9600);
  randomSeed(62);   
}

void loop() {
  if (Serial.available() > 0) {
    int val = Serial.parseInt();
    Serial.println(val);
    if(val == 0){
      clearAnimation();
    }
    
    if(val == 1){
      animation_1(255, 255, 255, delay_1);
    }

    if(val == 2){
      animation_2(75, 255, 20, delay_1);
    }

    if(val == 3){
      animation_3(255, 0, 0, 100, 5);
    }

    if(val == 4){
      animation_4(255, 179, 60, 100);
    }

    if(val == 5){
      animation_5(25, 179, 60, 100, 300);
    }

    if(val == 6){
      animation_6();
    }
  }
}

void clearAnimation(){
  for(int i = 0; i < NUMPIXELS; i++){
    pixels.setPixelColor(i, pixels.Color(0, 0, 0));
    pixels.show();
  }
  
}

void animation_1(int red, int green, int blue, int delayTime){
  clearAnimation();
  for(int i = 0; i < NUMPIXELS; i++){
    pixels.setPixelColor(i, pixels.Color(red, green, blue));

    pixels.show();

    delay(delayTime);
  }
  
}



void animation_2(int red, int green, int blue, int delayTime){
  clearAnimation();
  
  for(int i = 0; i < NUMPIXELS; i++){
    pixels.setPixelColor(i, pixels.Color(red, green,blue));

    pixels.show();

    delay(delayTime);
  }

  for (int i = NUMPIXELS; i >= 0; i--) {
    pixels.setPixelColor(i, pixels.Color(0, 0, 0));

    pixels.show();

    delay(delayTime);
  }
  
}

void animation_3(int red, int green, int blue, int delayTime, int count){
  clearAnimation();

  for(int i = 0; i < count; i++){
    for(int i = 0; i < NUMPIXELS; i++){
      pixels.setPixelColor(i, pixels.Color(red, green,blue));

      pixels.show();
    }
    delay(delayTime);

    for(int i = 0; i < NUMPIXELS; i++){
      pixels.setPixelColor(i, pixels.Color(0, 0, 0));

      pixels.show();
    }
    delay(delayTime);
  }
  
}

void animation_4(int red, int green, int blue, int delayTime){
  clearAnimation();
  
  for(int i = 0; i < NUMPIXELS; i++){
    if(i % 2 == 0){
      pixels.setPixelColor(i, pixels.Color(red, green,blue));

      pixels.show();

      delay(delayTime);
    }
    
  }

  delay(delayTime);

  for (int i = NUMPIXELS; i >= 0; i--) {
    if(i % 2 != 0){
      pixels.setPixelColor(i, pixels.Color(red - 50, green - 50, blue - 50));
      pixels.show();
      delay(delayTime);
    }   
  }
  
}

void animation_5(int red, int green, int blue, int delayTime, int count){
  clearAnimation();

  for(int i = 0; i < count; i++){
    
    for(int i = 0; i < NUMPIXELS; i++){
      red = random(0, 255);
      green = random(0, 255);
      blue = random(0, 255);
    
      pixels.setPixelColor(i, pixels.Color(red, green,blue));
      pixels.show();
    }
    delay(delayTime);

    for(int i = 0; i < NUMPIXELS; i++){
      red = random(10, 255);
      green = random(10, 255);
      blue = random(10, 255);
    
      pixels.setPixelColor(i, pixels.Color(red, green,blue));
      pixels.show();
    }
    delay(delayTime);
  }

  delay(delayTime);
}

void animation_6(){
  while(Serial.available() == 0){
    animation_1(255, 255, 255, delay_1);
    delay(300);
    animation_2(0, 255, 0, delay_1);
    delay(300);
    animation_3(255, 0, 0, 100, 5);
    delay(300);
    animation_4(255, 179, 60, 100);
  } 
}


