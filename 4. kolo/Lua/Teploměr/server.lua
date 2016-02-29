-- Nastaveni AP
wifi.setmode(wifi.SOFTAP);
cfg={};
cfg.ssid="Malinovky5 IoE Temp";
wifi.ap.config(cfg);

-- Nastaveni IP
cfgIp={};
cfgIp.ip="192.168.1.1";
cfgIp.netmask="255.255.255.0";
cfgIp.gateway="192.168.1.1";
wifi.ap.setip(cfgIp);

-- Promenne
require("config");

-- Http server
srv=net.createServer(net.TCP)
srv:listen(80,function(conn)
  conn:on("receive",function(conn,payload)
  
  -- Parsovani post dat
  postparse={string.find(payload,"hashKey=")};
  if postparse[2]~=nil then
    post = string.sub(payload,postparse[2]+1,#payload);
    hashKeyPost, ssidPost, passwordPost = post:match("([^,]+)&([^,]+)&([^,]+)");
    ssidPost = string.sub(ssidPost, 6);
    passwordPost = string.sub(passwordPost, 10);

    -- Zapsani dat
    file.remove("config.lua");
    file.open("config.lua","w+");
    file.writeline('hashKey = "'..hashKeyPost..'";');
    file.writeline('ssid = "'..ssidPost..'";');
    file.writeline('password = "'..passwordPost..'";');
    file.close();
    dofile("config.lua");
  end

  -- Web
    -- HTML Header Stuff
    conn:send('HTTP/1.1 200 OK\n\n')
    conn:send('<!DOCTYPE HTML>\n')
    conn:send('<html>\n')
    conn:send('<head><meta content="text/html; charset=utf-8">\n')
    conn:send('<title>Malinovky5 Temp Modul</title></head>\n')
    conn:send('<body>')
        
    conn:send("<h1>Malinovky5</h1>")
    conn:send("<form method='POST'>")
    conn:send(" Hash klic:<br>")
    conn:send(" <input type='text' name='hashKey' value="..hashKey.."><br>")
    conn:send(" SSID:<br>")
    conn:send(" <input type='text' name='ssid' value="..ssid.."><br>")
    conn:send(" Heslo:<br>")
    conn:send(" <input type='password' name='password' value="..password.."><br>")
    conn:send(" <input type='submit' value='Uložit'>")
    conn:send("</form>")
    conn:send("</body>")
  end)
  conn:on("sent",function(conn) conn:close() end)
end)
