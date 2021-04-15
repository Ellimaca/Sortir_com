function init() {
    addListeners();
}

function addListeners() {

    var city = document.getElementById('event_city');

    city.addEventListener("change", updatePlaceField);

    //Récupération du lieu choisi
    var place = document.getElementById('event[place]');

    place.addEventListener('change', displayInformations);

}

function updatePlaceField() {

    let city_id = document.getElementById('event_city')
    let data = {"eventCity": city_id.value}

    console.log(data)

    fetch('/Sortir_com/public/ajaxCity', {method: 'POST', body: JSON.stringify(data)})
        .then(function (response) {
            return response.json();
        }).then(function (data) {

        places = data.places
        html = "<option value=\"\" selected value=\"disabled\" disabled >Définir un lieu</option>" + "\n";
        first = true;

        for (const [key, value] of Object.entries(places)) {
            if (first){
                html = "<option selected value='" + `${key}` + "'>" + `${value.name}` + "</optionselected>" + "\n";
            }else{
                html = html + "<option value='" + `${key}` + "'>" + `${value.name}` + "</option>" + "\n";
            }
        }

        document.getElementById('event[place]').innerHTML = html;
        displayInformations();
    })

}

function displayInformations() {

    //Récupération de l'id du lieu
    let place_id = document.getElementById('event[place]')

    console.log(place_id.options.length);

    if (place_id.options.length !== 0 && place_id.options.item(0).value !== '') {
        let data = {'placeId': place_id.options[place_id.selectedIndex].value}

        fetch('/Sortir_com/public/ajaxPlace', {method: 'POST', body: JSON.stringify(data)})
            .then(function (response) {
                return response.json();
            }).then(function (data) {
            placeStreet = data.placeStreet;
            placeLongitude = data.placeLongitude;
            placeLatitude = data.placeLatitude;

            document.getElementById("streetPlace").innerText = placeStreet;
            document.getElementById("latitudePlace").innerText = placeLatitude;
            document.getElementById("longitudePlace").innerText = placeLongitude;

            console.log(data)
        })
    } else {
        console.log('Place - no data')
    }

}


