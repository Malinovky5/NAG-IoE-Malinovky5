var myApp = new Framework7();

var $$ = Dom7;

$$('.form-from-json').on('click', function () {
    $$.getJSON('http://malinovky5.ssinfotech.cz/hodnoty.json', function (data) {
        var smer;
        switch(data.Direction){
                case 'west':
                    smer = 'Západ';
                    break;

                case 'north':
                    smer = 'Sever';
                    break;

                case 'east':
                    smer = 'Východ';
                    break;

                case 'south':
                    smer = 'Jih';
                    break;

                case 'nortWest':
                    smer = 'Severozápad';
                    break;

                case 'northEast':
                    smer = 'Severovýchod';
                    break;

                case 'southWest':
                    smer = 'Jihozápad';
                    break;

                case 'southEast':
                    smer = 'Jihovýchod';
                    break;
            };
        var formData = {
            'Direction': smer,
            'Rain': data.Rain+"  mm/h",
            'Speed': data.Speed+"  m/s",
            'Pressure': data.Pressure+"  hPa",
            'Humidity': data.Humidity+"  %",
            'Temperature': data.Temperature+"  °C",
            'Lux': data.Lux+"  lux"
        }
        myApp.formFromJSON('#my-form', formData);
    });
    
});