'use strict';
window.addEventListener('load', init());

function init() {
    var videoWidth = 550;
    var videoHeight = 550;

    var c = document.getElementById("can");
    c.width = videoWidth;
    c.height = videoHeight;
    var context = c.getContext('2d');

    function animate() {
        if (context) {
            var piImage = new Image();

            piImage.onload = function() {
                context.drawImage(piImage, 0, 0, c.width, c.height);
            };

            try {
                piImage.src = "cam_pic.php?time=" + new Date().getTime();
            } catch (error) {

            }


        }
    }
    var video = window.setInterval(animate, 28);

    var url = "hodnoty.json";
    var json, jsonFile, dataFile, dataFileHum;
    var temp = document.getElementsByClassName('temp')[0];
    var pressure = document.getElementsByClassName('pressure')[0];
    var humidity = document.getElementsByClassName('humidity')[0];
    var direction = document.getElementsByClassName('direction')[0];
    var speed = document.getElementsByClassName('speed')[0];
    var lux = document.getElementsByClassName('lux')[0];
    var rain = document.getElementsByClassName('rain')[0];

    var lux_status = '';

    var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
    var today = new Date();

    var temp_average = 0,
        hum_average = 0,
        total = 0,
        total_hum = 0,
        odpovedTemp, odpovedHum, odpovedTempLength, odpovedHumLength;

    var getInterval = window.setInterval(function() {
        dataFile = new XMLHttpRequest();
        dataFile.open("GET", '/days/temp/' + days[today.getDay()] + '.txt', true);
        dataFile.send();

        dataFile.onreadystatechange = function() {
            if (dataFile.readyState === 4 && dataFile.status === 200) {

                odpovedTemp = dataFile.responseText.split(',');
                odpovedTempLength = odpovedTemp.length;

                for (var i = 0; i < odpovedTempLength - 1; i++) {
                    if (parseFloat(odpovedTemp[i]) < 70) {
                        total += parseFloat(odpovedTemp[i]);
                    }
                }
                temp_average = total / odpovedTempLength - 1;
            }
        }

        dataFileHum = new XMLHttpRequest();
        dataFileHum.open("GET", '/days/humidity/' + days[today.getDay()] + '.txt', true);
        dataFileHum.send();

        dataFileHum.onreadystatechange = function() {
            if (dataFileHum.readyState === 4 && dataFileHum.status === 200) {

                odpovedHum = dataFileHum.responseText.split(',');
                odpovedHumLength = odpovedHum.length;

                for (var i = 0; i < odpovedHumLength - 1; i++) {
                    if (parseFloat(odpovedHum[i]) < 99) {
                        total_hum += parseFloat(odpovedHum[i]);
                    }
                }
                hum_average = total_hum / odpovedHumLength - 1;
            }
        }

        jsonFile = new XMLHttpRequest();
        jsonFile.open("GET", url, true);
        jsonFile.send();

        jsonFile.onreadystatechange = function() {
            if (jsonFile.readyState === 4 && jsonFile.status === 200) {
                try {
                    json = JSON.parse(jsonFile.responseText);

                    if (json.Temperature !== undefined) {
                        temp.innerHTML = json.Temperature + '&deg;C' + '<br />Průměrná denní teplota: ' + (Math.round(temp_average * 100) / 100) + '&deg;C';
                    }

                    if (json.Pressure !== undefined) {
                        pressure.innerHTML = json.Pressure + ' hPa';
                    }

                    if (json.Humidity !== undefined) {
                        humidity.innerHTML = json.Humidity + '%' + '<br />Průměrná denní vlhkost: ' + (Math.round(hum_average * 100) / 100) + '%';
                    }

                    if (json.Direction !== undefined) {
                        switch (json.Direction) {
                            case 'west':
                                json.Direction = 'Západ';
                                break;

                            case 'north':
                                json.Direction = 'Sever';
                                break;

                            case 'east':
                                json.Direction = 'Východ';
                                break;

                            case 'south':
                                json.Direction = 'Jih';
                                break;

                            case 'nortWest':
                                json.Direction = 'Severozápad';
                                break;

                            case 'northEast':
                                json.Direction = 'Severovýchod';
                                break;

                            case 'southWest':
                                json.Direction = 'Jihozápad';
                                break;

                            case 'southEast':
                                json.Direction = 'Jihovýchod';
                                break;
                        };
                        direction.innerHTML = json.Direction;
                    }

                    if (json.Speed !== undefined) {
                        speed.innerHTML = json.Speed + ' m/s <br />(' + (Math.round(json.Speed * 3.6 * 100) / 100) + ' km/h)';
                    }

                    if (json.Lux !== undefined) {
                        if (json.Lux >= 0) {
                            lux_status = 'Hluboká noc';
                        }
                        if (json.Lux >= 1) {
                            lux_status = 'Noc';
                        }
                        if (json.Lux >= 90) {
                            lux_status = 'Západ/Východ slunce';
                        }
                        if (json.Lux >= 1000) {
                            lux_status = 'Zataženo';
                        }
                        if (json.Lux >= 10000) {
                            lux_status = 'Polojasno';
                        }
                        if (json.Lux >= 32000) {
                            lux_status = 'Jasno';
                        }
                        lux.innerHTML = json.Lux + ' luxů <br /> ' + lux_status;
                    }

                    if (json.Rain !== undefined) {
                        rain.innerHTML = json.Rain + ' mm/h';
                    }
                } catch (err) {

                }

            }
        }

        total = 0;
        total_hum = 0;
    }, 1000);

    var temperatures = [],
        humidities = [],
        temp_average_days, total_temp_days = 0,
        hum_average_days, total_hum_days = 0;
    var lineChartData, odpovedTemp_2, odpovedHum_2, odpovedTempLength_2, odpovedHumLength_2;

    for (var i = 0; i < days.length; i++) {

        (function(i) {
            window['dataFileDaysTemp' + i] = new XMLHttpRequest();
            window['dataFileDaysTemp' + i].open("GET", '/days/temp/' + days[i] + '.txt', true);

            window['dataFileDaysTemp' + i].onreadystatechange = function() {

                if (window['dataFileDaysTemp' + i].readyState === 4 && window['dataFileDaysTemp' + i].status === 200) {
                    odpovedTemp_2 = window['dataFileDaysTemp' + i].responseText.split(',');
                    odpovedTempLength_2 = odpovedTemp_2.length;

                    for (var c = 0; c < odpovedTempLength_2 - 1; c++) {
                        if (parseFloat(odpovedTemp_2[c]) < 70) {
                            total_temp_days += parseFloat(odpovedTemp_2[c]);
                        }
                    }
                    temp_average_days = total_temp_days / odpovedTempLength_2 - 1;
                    total_temp_days = 0;

                    if (temp_average_days == -1) {
                        temp_average_days = 0;
                    }
                    temperatures.push(Math.round(temp_average_days * 100) / 100);
                }
            }
            window['dataFileDaysTemp' + i].send();

            window['dataFileDaysHum' + i] = new XMLHttpRequest();
            window['dataFileDaysHum' + i].open("GET", '/days/humidity/' + days[i] + '.txt', true);

            window['dataFileDaysHum' + i].onreadystatechange = function() {

                if (window['dataFileDaysHum' + i].readyState === 4 && window['dataFileDaysHum' + i].status === 200) {
                    odpovedHum_2 = window['dataFileDaysHum' + i].responseText.split(',');
                    odpovedHumLength_2 = odpovedHum_2.length;

                    for (var c = 0; c < odpovedHumLength_2 - 1; c++) {
                        if (parseFloat(odpovedHum_2[c]) < 99) {
                            total_hum_days += parseFloat(odpovedHum_2[c]);
                        }
                    }
                    hum_average_days = total_hum_days / odpovedHumLength_2 - 1;
                    total_hum_days = 0;

                    if (hum_average_days == -1) {
                        hum_average_days = 0;
                    }
                    humidities.push(Math.round(hum_average_days * 100) / 100);

                    if (i === days.length - 1) {
                        lineChartData = {
                            labels: ['Neděle', 'Pondělí', 'Úterý', 'Středa', 'Čtvrtek', 'Pátek', 'Sobota'],
                            datasets: [{
                                label: "Teplota",
                                fillColor: "rgba(220,220,220,0.2)",
                                strokeColor: "rgba(220,220,220,1)",
                                pointColor: "rgba(220,220,220,1)",
                                pointStrokeColor: "#fff",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(220,220,220,1)",
                                data: [temperatures[0], temperatures[1], temperatures[2], temperatures[3], temperatures[4], temperatures[5], temperatures[6]]
                            }, {
                                label: "Vlhkost",
                                fillColor: "rgba(151,187,205,0.2)",
                                strokeColor: "rgba(151,187,205,1)",
                                pointColor: "rgba(151,187,205,1)",
                                pointStrokeColor: "#fff",
                                pointHighlightFill: "#fff",
                                pointHighlightStroke: "rgba(151,187,205,1)",
                                data: [humidities[0], humidities[1], humidities[2], humidities[3], humidities[4], humidities[5], humidities[6]]
                            }]

                        };

                        var ctx = document.getElementById("canvas").getContext("2d");
                        window.myLine = new Chart(ctx).Line(lineChartData, {
                            responsive: true
                        });
                    }
                }
            }

            window['dataFileDaysHum' + i].send();


        })(i);

    };

    var daysTable = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"];
    var maxTempArray = document.getElementsByClassName('maxTemp');
    var minTempArray = document.getElementsByClassName('minTemp');
    var maxHumArray = document.getElementsByClassName('maxHum');
    var minHumArray = document.getElementsByClassName('minHum');
    var odpovedTempTable, odpovedTempTableLength, odpovedHumTable, odpovedHumTableLength, maxHum = 0, minHum = 0, maxTemp = 0, minTemp = 0;

    for (var i = 0; i < maxTempArray.length; i++) {
    	(function(i) {

    		window['dataFileTableTemp' + i] = new XMLHttpRequest();
    		window['dataFileTableTemp' + i].open("GET", '/days/temp/' + daysTable[i] + '.txt', true);
    		window['dataFileTableTemp' + i].send();

    		window['dataFileTableTemp' + i].onreadystatechange = function() {

    			if (window['dataFileTableTemp' + i].readyState === 4 && window['dataFileTableTemp' + i].status === 200) {
    				odpovedTempTable = window['dataFileTableTemp' + i].responseText.split(',');
    				odpovedTempTableLength = odpovedTempTable.length;

    				for (var c = 0; c < odpovedTempTableLength - 1; c++) {
    					if (parseFloat(odpovedTempTable[c]) > maxTemp && parseFloat(odpovedTempTable[c]) < 30) {
    						maxTemp = parseFloat(odpovedTempTable[c]);
    					}
    					if (parseFloat(odpovedTempTable[c]) < maxTemp && parseFloat(odpovedTempTable[c]) < 30) {
    						minTemp = parseFloat(odpovedTempTable[c]);
    					}
    				}

    				maxTempArray[i].innerHTML = maxTemp + '&deg;C';
    				minTempArray[i].innerHTML = minTemp + '&deg;C';
    				maxTemp = 0;
    				minTemp = 0
    			}
    		}

    		window['dataFileTableHum' + i] = new XMLHttpRequest();
    		window['dataFileTableHum' + i].open("GET", '/days/humidity/' + daysTable[i] + '.txt', true);
    		window['dataFileTableHum' + i].send();

    		window['dataFileTableHum' + i].onreadystatechange = function() {

    			if (window['dataFileTableHum' + i].readyState === 4 && window['dataFileTableHum' + i].status === 200) {
    				odpovedHumTable = window['dataFileTableHum' + i].responseText.split(',');
    				odpovedHumTableLength = odpovedHumTable.length;

    				for (var c = 0; c < odpovedTempTableLength - 1; c++) {
    					if (parseFloat(odpovedHumTable[c]) > maxHum && parseFloat(odpovedHumTable[c]) < 99) {
    						maxHum = parseFloat(odpovedHumTable[c]);
    					}
    					if (parseFloat(odpovedHumTable[c]) < maxHum && parseFloat(odpovedHumTable[c]) < 99) {
    						minHum = parseFloat(odpovedHumTable[c]);
    					}
    				}

    				maxHumArray[i].innerHTML = maxHum + '%';
    				minHumArray[i].innerHTML = minHum + '%';
    				maxHum = 0;
    				minHum = 0
    			}
    		}

    	})(i);
    };

}