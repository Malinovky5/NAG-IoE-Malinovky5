-- Promenne
require("ds18b20");
require("config");
temperature = 0;
ledStatus = "OFF";

-- Nastaveni pinu ds18b20
ds18b20.setup(dallas_pin);

--Nastaveni LED
gpio.write(led_pin, gpio.LOW);

-- Pripojeni k wifi
wifi.setmode(wifi.STATION);
wifi.sta.config(ssid,password)
wifi.sta.connect()
tmr.alarm(1, 100, 1, function()
    if wifi.sta.getip()== nil then
        print("IP neni znama, cekejte")
    else
        tmr.stop(1);
        print("Status: "..wifi.sta.status());
        print("IP, maska, vychozi brana");
        print(wifi.sta.getip());
    end
end)

tmr.alarm(2, 5000, 1, function()
    readTemp();
end)

srv=net.createServer(net.TCP)
srv:listen(80,function(conn)
  conn:on("receive",function(conn,payload)
  
  -- Parsovani post dat
  postparse={string.find(payload,"led=")};
  if postparse[2]~=nil then
    post = string.sub(payload,postparse[2]+1,#payload);
    if (post == "ON") then
        gpio.write(led_pin, gpio.HIGH);
        ledStatus = "ON";
    else
        gpio.write(led_pin, gpio.LOW);
        ledStatus = "OFF";
    end
  end

  -- Web
    -- HTML Header Stuff
    conn:send('HTTP/1.1 200 OK\n\n')
    conn:send('<!DOCTYPE HTML>\n')
    conn:send('<html>\n')
    conn:send('<head><meta content="text/html; charset=utf-8">\n')
    conn:send('<title>Malinovky5 LED + teplota</title></head>\n')
    conn:send('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">')
    conn:send('<body>')
        
    conn:send('<div class="container-fluid">')
    conn:send('<div class="col-md-6 col-md-offset-3">')
    conn:send('<img src="http://malinovky5.ssinfotech.cz/ctyri/design/images/logo_malinovky.png" width="300">')
    conn:send('<div class="panel panel-default">')
    conn:send('<div class="panel-body">')
    conn:send(" Teplota: "..temperature.." °C<br>")
    conn:send("</div>")
    conn:send("</div>")
    conn:send('<div class="panel panel-default">')
    conn:send('<div class="panel-body">')
    conn:send("<form method='POST'>")
    conn:send(" LED: "..ledStatus.."<br>")
    conn:send(' <div class="btn-group" role="group">');
    conn:send(" <button class='btn btn-default' type='submit' name='led' value='ON'>ON</button>")
    conn:send(" <button class='btn btn-default' type='submit' name='led' value='OFF'>OFF</button>")
    conn:send("</div>")
    conn:send("</form>")
    conn:send("</div>")
    conn:send("</div>")
    conn:send("</div>")
    conn:send("</div>")
    conn:send("</body>")
  end)
  conn:on("sent",function(conn) conn:close() end)
end)

-- Vycte teplotu
function readTemp ()
    temperature = ds18b20.read();
    temperature = ds18b20.read();
    tmr.delay(300000);
    
    if (temperature ~= 85 and temperature ~= nil) then -- Obcasna chyba 85 stupnu
        print("Teplota: "..temperature.."C");
    else
        node.restart();
    end
end

function restartNode ()
    node.restart();
end
