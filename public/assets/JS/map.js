document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('map').setView([48.8566, 2.3522], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    const marker = L.marker([48.8566, 2.3522], { draggable: true }).addTo(map);

    // Mettre à jour les champs de latitude et de longitude lorsque le marqueur est déplacé
    marker.on('dragend', () => {
        const position = marker.getLatLng();
        document.getElementById('latitude').value = position.lat;
        document.getElementById('longitude').value = position.lng;
    });

    // Mettre à jour la position du marqueur lorsqu'un utilisateur clique sur la carte
    map.on('click', (event) => {
        const position = event.latlng;
        marker.setLatLng(position);
        document.getElementById('latitude').value = position.lat;
        document.getElementById('longitude').value = position.lng;
    });
});

// Initialisez la carte et la vue...
// ...

// Ajoutez un marqueur à la carte et mettez à jour les champs de formulaire
// avec les coordonnées du marqueur et l'adresse complète
function onMapClick(e) {
    let latitudeInput = document.getElementById('latitude');
    let longitudeInput = document.getElementById('longitude');
    let adresseCompleteInput = document.getElementById('adresse_complete');

    latitudeInput.value = e.latlng.lat;
    longitudeInput.value = e.latlng.lng;

    // Utilisez un service de géocodage inverse pour obtenir l'adresse complète
    // basée sur les coordonnées du marqueur (par exemple, Nominatim, Google Maps Geocoding API, etc.)
    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${e.latlng.lat}&lon=${e.latlng.lng}&zoom=18&addressdetails=1`)
        .then(response => response.json())
        .then(data => {
            adresseCompleteInput.value = data.display_name;
        });
}

map.on('click', onMapClick);

