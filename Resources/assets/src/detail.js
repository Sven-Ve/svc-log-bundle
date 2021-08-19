import { Controller } from 'stimulus';
import { Modal } from 'bootstrap';


/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    url: String
  }


  connect() {
//    console.log("connected" + this.idValue);
  }

  showDetail() {
    console.log("Detail " + this.urlValue);
    this.loadData(this.urlValue);
  }

  async loadData(url) {

    var response;
    try {
      response = await fetch(url);
    }
    catch (err) {
      console.log(err.message);
      alert('Error during load. Please dry again.')
      location.reload();
      return;
    }

    if (response.ok) {
      const result = await response.json();
      this.showModal(result);


    } else {
      alert('Error during load. Please dry again. (' + response.status + ')');
      console.log(response.status);
      location.reload();
    }
  }

  showModal(data) {
    var model =  document.getElementById('showDetail')
    var myModal = new Modal(model);
    myModal.show();


    model.querySelector('.mc-sourceID').textContent = data.sourceID;
    model.querySelector('.mc-sourceType').textContent = data.sourceType;
    model.querySelector('.mc-message').textContent = data.message;
    model.querySelector('.mc-date').textContent = data.logDate;
    model.querySelector('.mc-logLevel').textContent = data.logLevel;
}

}