int red = 5;
int green = 6;
int blue = 3;

void setup() {
  pinMode(red, OUTPUT);
  pinMode(green, OUTPUT);
  pinMode(blue, OUTPUT);

  randomSeed(1023);
}

void loop() {
  while (true) {
    analogWrite(red, random(0, 1023));
    analogWrite(green, random(0, 1023));
    analogWrite(blue, random(0, 1023));

    delay(200);
  }

}
