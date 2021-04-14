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

    fetch('ajax', {method: 'POST', body: JSON.stringify(data)})
        .then(function (response) {
            return response.json();
        }).then(function (data) {

        places = data.places
        html = "";

        for (const [key, value] of Object.entries(places)) {
            html = html + "<option value='" + `${key}` + "'>" + `${value.name}` + "</option>" + "\n";
        }
        document.getElementById('event[place]').innerHTML = html;
    })

    displayInformations();
}


function displayInformations() {

    //Récupération de l'id du lieu
    let place_id = document.getElementById('event[place]')

    console.log(place_id.options.length);

    if (place_id.options.length !== 0){
        let data = {'placeId' : place_id.options[place_id.selectedIndex].value}
        console.log(data)
    }else{
        console.log('no data')
    }


}


