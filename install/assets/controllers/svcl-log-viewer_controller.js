import { Controller } from 'stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    url: String
  }
  static targets = ["sourceID", "sourceType", "logLevel", "content", "sourceIDC", "sourceTypeC", "logLevelC", "country", 
    "next", "prev", "last", 
    "hideNext", "hidePrev", "firstBtn", "prevBtn", "nextBtn", "lastBtn",
    "count", "countDisplay", "from", "fromDisplay", "to", "toDisplay",
  ];



  connect() {
    this.refreshContent(this.urlValue + "?onlyData=1&offset=0");
    this.enableDisableButton();
  }

  onSubmit(event) {
    event.preventDefault();
    this.refreshContent(this.createURL(0));
  }

  first() {
    this.refreshContent(this.createURL(0));
  }

  prev() {
    this.refreshContent(this.createURL(this.prevTarget.value));
  }


  next() {
    this.refreshContent(this.createURL(this.nextTarget.value));
  }

  /**
   * go to the last record
   */
  last() {
    this.refreshContent(this.createURL(this.lastTarget.value));
  }

  createURL(offset) {
    var url = this.urlValue + "?onlyData=1&offset=" + offset;
    url += "&sourceID=" + this.sourceIDTarget.value;
    url += "&sourceIDC=" + this.sourceIDCTarget.value;
    url += "&sourceType=" + this.sourceTypeTarget.value;
    url += "&sourceTypeC=" + this.sourceTypeCTarget.value;
    url += "&logLevel=" + this.logLevelTarget.value;
    url += "&logLevelC=" + this.logLevelCTarget.value;
    url += "&country=" + this.countryTarget.value;
    return url;
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
      location.reload();
      return;
    }

    if (response.ok) {
      target.innerHTML = await response.text();
      target.style.opacity = 1;
      this.refreshCounts();
      this.enableDisableButton();


    } else {
      console.log(response.status);
      location.reload();
    }
  }

  refreshCounts() {
    this.countDisplayTarget.innerText = this.countTarget.value;
    this.fromDisplayTarget.innerText = this.fromTarget.value;
    this.toDisplayTarget.innerText = this.toTarget.value;
  }

  /**
   * enable or disable the pagination buttons
   */
  enableDisableButton() {
    if (this.hidePrevTarget.value != "1") {
      this.firstBtnTarget.classList.remove('disabled');
      this.prevBtnTarget.classList.remove('disabled');
    } else {
      this.firstBtnTarget.classList.add('disabled');
      this.prevBtnTarget.classList.add('disabled');
    }

    if (this.hideNextTarget.value != "1") {
      this.nextBtnTarget.classList.remove('disabled');
      this.lastBtnTarget.classList.remove('disabled');
    } else {
      this.nextBtnTarget.classList.add('disabled');
      this.lastBtnTarget.classList.add('disabled');
    }

  }

}