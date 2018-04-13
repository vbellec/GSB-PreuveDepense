window.onload = function () {
  const formPreuve = document.querySelector('#form-preuve');

  formPreuve.addEventListener('submit', (evt) => {
    evt.preventDefault();
    ajaxPromise('POST', formPreuve.action, new FormData(formPreuve)).then(() => {
      alert('Envoyé !')
      this.window.location =  '#accueil';
    }).catch(err => {
      alert('Erreur')
      this.console.error(err);
    });
  })
}