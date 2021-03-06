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

    if (city_id != null && city_id.value != ""){
        let data = {"eventCity": city_id.value}

        fetch('/Sortir_com/public/ajaxCity', {method: 'POST', body: JSON.stringify(data)})
            .then(function (response) {
                return response.json();
            }).then(function (data) {

            //Génération du html pour remplir le select de places
            places = data.places
            html = "<option value=\"\" selected value=\"disabled\" disabled >Définir un lieu</option>" + "\n";
            first = true;
            for (const [key, value] of Object.entries(places)) {
                if (first){
                    html = "<option selected value='" + `${key}` + "'>" + `${value.name}` + "</optionselected>" + "\n";
                    first = false;
                }else{
                    html = html + "<option value='" + `${key}` + "'>" + `${value.name}` + "</option>" + "\n";
                }
            }

            //Modification du text dans le HTML
            document.getElementById('event[place]').innerHTML = html;

            //Selection de l'endroit initial de la sortie
            let oldPlaceId = document.getElementById('oldPlaceId');
            let oldCityId = document.getElementById('oldCityId');

            if (oldCityId != null){
                console.log("oldplaceValue :" + oldPlaceId.value);
                console.log("oldcityValue :" + oldCityId.value);

                if(oldCityId.value != 0){
                    let selectPlace = document.getElementById('event[place]');
                    selectPlace.value = oldPlaceId.value;
                    console.log("Selected Value :" +selectPlace.value);
                    oldPlaceId.value = 0;
                    oldCityId.value = 0;
                }
            }

            //Mise à jour des informations de l'endroit
            displayInformations();
        })
    }else{
        //Modification du text dans le HTML
        html = "<option value=\"\" selected value=\"disabled\" disabled >Définir un lieu</option>" + "\n";
        document.getElementById('event[place]').innerHTML = html;
    }



}

function displayInformations() {
    const NOT_ADVISE = "Non renseignée";

    //Récupération de l'id du lieu
    let place_id = document.getElementById('event[place]')


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
        document.getElementById("streetPlace").innerText = NOT_ADVISE;
        document.getElementById("latitudePlace").innerText = NOT_ADVISE;
        document.getElementById("longitudePlace").innerText = NOT_ADVISE;
        console.log('Place - no data')
    }

}
