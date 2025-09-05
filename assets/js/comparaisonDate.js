document.addEventListener('DOMContentLoaded', function() {
    const dateLimiteInscription = document.getElementById('add_sortie_form_dateLimiteInscription');
    const dateHeureDebut = document.getElementById('add_sortie_form_dateHeureDebut');
    const error = document.getElementById('error_date'); // Déclarée en dehors de la fonction

    function validateDates() {
        if (dateHeureDebut.value && dateLimiteInscription.value) {
            console.log(dateHeureDebut.value);
            if (new Date(dateHeureDebut.value) < new Date(dateLimiteInscription.value)) {
                error.innerText = "La date limite d'inscription doit être antérieure à la date de la sortie";
            } else {
                error.innerText = ""; // Maintenant accessible
            }
        } else {
            // Optionnel : vider l'erreur si un des champs est vide
            error.innerText = "";
        }
    }

    dateLimiteInscription.addEventListener('input', validateDates);
    dateHeureDebut.addEventListener('input', validateDates);
});