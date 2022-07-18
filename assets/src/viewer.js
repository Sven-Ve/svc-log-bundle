import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
  static values = {
    url: String,
    showFilter: Boolean,
    defaultSourceId: String,
    defaultSourceType: String,
    defaultLogLevel: String,
    hideSourceCols: Boolean
  }
  static targets = ["sourceID", "sourceType", "logLevel", "content", "sourceIDC", "sourceTypeC", "logLevelC", "country",
    "next", "prev", "last",
    "hideNext", "hidePrev", "firstBtn", "prevBtn", "nextBtn", "lastBtn",
    "count", "countDisplay", "from", "fromDisplay", "to", "toDisplay",
  ];


  connect() {
    this.refreshContent(this.createURL("0"));
  }

  onSubmit(event) {
    event.preventDefault();
    this.refreshContent(this.createURL("0"));
  }

  first() {
    this.refreshContent(this.createURL("0"));
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

  /**
   * create the ajax url with all filter parameter
   * @param {string} offset - the pagination offset
   */
  createURL(offset) {
    let url = this.urlValue + "?offset=" + offset;
    if (this.showFilterValue) {
      url += "&sourceID=" + this.sourceIDTarget.value;
      url += "&sourceIDC=" + this.sourceIDCTarget.value;
      url += "&sourceType=" + this.sourceTypeTarget.value;
      url += "&sourceTypeC=" + this.sourceTypeCTarget.value;
      url += "&logLevel=" + this.logLevelTarget.value;
      url += "&logLevelC=" + this.logLevelCTarget.value;
      url += "&country=" + this.countryTarget.value;
    } else {
      url += "&sourceID=" + this.defaultSourceIdValue;
      url += "&sourceType=" + this.defaultSourceTypeValue;
      url += "&logLevel=" + this.defaultLogLevelValue;
    }

    if (this.hideSourceColsValue) {
      url += "&hideSourceCols=1";
    }

    return url;
  }

  /**
   * refresh (ajax) a given url and show the result in the target container
   *
   * @param {string} url
   * @returns
   */
  async refreshContent(url) {

    const target = this.contentTarget;
    target.style.opacity = .5;

    let response;
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
      target.innerHTML = await response.text();
      target.style.opacity = 1;
      this.refreshCounts();
      this.enableDisableButton();


    } else {
      alert('Error during load. Please dry again. (' + response.status + ')');
      console.log(response.status);
      location.reload();
    }
  }

  /**
   * refresh the count information (row 1 to 10 from 100)
   */
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
