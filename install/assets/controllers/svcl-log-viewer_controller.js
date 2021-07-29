import { Controller } from 'stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    url: String
  }
  static targets = ["sourceID", "sourceType" , "logLevel", "content", "sourceIDC", "sourceTypeC" , "logLevelC", "next", "prev", "last"];



  /*   connect() {
      console.log("connected");
      console.log(this.urlValue);
    } */

  onSubmit(event) {
    event.preventDefault();


    url = this.createURL(0);
    this.refreshContent(url);
  }

  first() {
    console.log("first");
    this.refreshContent(this.createURL(0));
  }

  prev() {
    console.log("prev");
    console.log(this.prevTarget.value);
    const url = this.createURL(this.prevTarget.value);
    this.refreshContent(url);
  }


  next() {
    console.log("next");
    console.log(this.nextTarget.value);
    const url = this.createURL(this.nextTarget.value);
    this.refreshContent(url);
  }

  last() {
    console.log("last");
    console.log(this.lastTarget.value);
    const url = this.createURL(this.lastTarget.value);
    this.refreshContent(url);
  }

  createURL(offset) {
    var url = this.urlValue + "?onlyData=1&offset=" + offset;
    url += "&sourceID=" + this.sourceIDTarget.value;
    url += "&sourceIDC=" + this.sourceIDCTarget.value;
    url += "&sourceType=" + this.sourceTypeTarget.value;
    url += "&sourceTypeC=" + this.sourceTypeCTarget.value;
    url += "&logLevel=" + this.logLevelTarget.value;
    url += "&logLevelC=" + this.logLevelCTarget.value;
    return url;
  }

  async refreshContent(url) {
    console.log(url);

    const target = this.contentTarget;
    target.style.opacity = .5;

    var response;
    try {
      response = await fetch(url);
    }
    catch (err) {
      console.log(err.message);
      location.reload();
      return;
    }

    if (response.ok) {
      target.innerHTML = await response.text();
      target.style.opacity = 1;
    } else {
      console.log(response.status);
      location.reload();
    }
  }

}