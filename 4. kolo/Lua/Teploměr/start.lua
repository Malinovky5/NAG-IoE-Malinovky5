print("started");

-- Promenne
dallas_pin = 3;
sw_pin = 4;

-- Nastaveni GPIO
gpio.mode(sw_pin, gpio.INPUT);


if gpio.read(sw_pin) == gpio.LOW then
    print("server");
    dofile('server.lua'); -- AP
else
    print("klient");
    dofile('client.lua'); -- klient
end
