document.addEventListener('DOMContentLoaded', function() {
    const pseudoInput = document.getElementById('registration_form_pseudo');
    const pseudoFeedback = document.createElement('div');
    pseudoInput.parentNode.insertBefore(pseudoFeedback, pseudoInput.nextSibling);

    if (pseudoInput) {
        pseudoInput.addEventListener('blur', function() {
            const pseudo = this.value;
            if (pseudo.length > 0) {
                const checkPseudoUrl = pseudoInput.dataset.checkPseudoUrl;
                fetch(`${checkPseudoUrl}?pseudo=${pseudo}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.isUnique) {
                            pseudoFeedback.style.color = 'green';
                            pseudoFeedback.textContent = data.message;
                        } else {
                            pseudoFeedback.style.color = 'red';
                            pseudoFeedback.textContent = data.message;
                        }
                    })
                    .catch(error => {
                        console.error('Error checking pseudo:', error);
                        pseudoFeedback.style.color = 'orange';
                        pseudoFeedback.textContent = 'Erreur lors de la vérification du pseudo.';
                    });
            } else {
                pseudoFeedback.textContent = '';
            }
        });
    }
});