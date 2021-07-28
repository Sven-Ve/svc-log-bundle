import { Controller } from 'stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    url: String
  }
  static targets = ["sourceid", "content"];



  /*   connect() {
      console.log("connected");
      console.log(this.urlValue);
    } */

  onSubmit(event) {
    event.preventDefault();

    var url = this.urlValue + "?onlyData=1";
    url += "&sourceID=" + this.sourceidTarget.value;
    console.log(url);
    this.refreshContent(url);
  }


  async refreshContent(url) {

    const target = this.contentTarget;
    target.style.opacity = .5;

    var response;
    try {
      response = await fetch(url);
    }
    catch (err) {
      console.log(err.message);
      return;
    }

    if (response.ok) {
      target.innerHTML = await response.text();
      target.style.opacity = 1;
    } else {
      console.log(response.status);
    }
  }

}