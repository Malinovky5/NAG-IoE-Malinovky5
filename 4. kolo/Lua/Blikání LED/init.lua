print("setup");

function startup()
    dofile('start.lua');
end

tmr.alarm(0,1000,0,startup);

function debug ()
    tmr.stop(0);
end
