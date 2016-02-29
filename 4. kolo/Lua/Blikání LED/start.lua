print("started");

-- Promenne
dallas_pin = 3;
led_pin = 4;

-- Nastaveni GPIO
gpio.mode(led_pin, gpio.OUTPUT);

dofile("client.lua");
