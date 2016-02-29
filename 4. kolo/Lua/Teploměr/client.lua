-- Promenne
require("ds18b20");
require("config");
temperature = 0;

-- Nastaveni pinu ds18b20
ds18b20.setup(dallas_pin);

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
        readTemp();
        sendData();
    end
end)

-- Odesilani dat na server
function sendData ()
    conn=net.createConnection(net.TCP, 0)
    conn:on("receive", function(conn, payload)
        if (string.find(payload, "true") ~= nil) then
            print ("Data uspesne odeslana");
        else
            print("Data byla odeslana spatne");
        end
        print("Uspavam se");
        node.dsleep(5*60*1000000); -- uspi se na 5 minut
    end )
    
    conn:on("connection", function(c)
        conn:send("GET /ctyri/registerModule.php?hashKey="..hashKey.."&temp="..temperature.." HTTP/1.1\r\nHost: malinovky5.ssinfotech.cz\r\n"
            .."Connection: keep-alive\r\nAccept: */*\r\n\r\n") 
        end)
    conn:connect(80,"malinovky5.ssinfotech.cz");
end

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
    if (wifi.sta.getip()== nil) then
        print("Nelze se pripojit k wifi");
        print("Uspavam se na 2 minuty");
        node.dsleep(2*60*1000000); -- uspi se na 2 minuty
    else
        node.restart();
    end
end

-- Kontrola funkce - pokud se vse neprovede do 20 sec, tak se restartuje, ci uspi
tmr.alarm(2,20000,0,restartNode);
