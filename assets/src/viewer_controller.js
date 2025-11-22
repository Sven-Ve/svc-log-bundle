import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'eager' */
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
    this.selectedRowIndex = -1;
    this.lastKnownSelectedIndex = -1; // Backup of selection
    this.dialogBackupIndex = -1; // Backup specifically for dialog operations
    this.refreshContent(this.createURL("0"));
    this.setupKeyboardNavigation();
    this.setupDialogHandlers();
  }

  onSubmit(event) {
    event.preventDefault();
    this.resetSelection();
    this.refreshContent(this.createURL("0"));
  }

  first() {
    this.resetSelection();
    this.refreshContent(this.createURL("0"));
  }

  prev() {
    this.resetSelection();
    this.refreshContent(this.createURL(this.prevTarget.value));
  }

  next() {
    this.resetSelection();
    this.refreshContent(this.createURL(this.nextTarget.value));
  }

  /**
   * go to the last record
   */
  last() {
    this.resetSelection();
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

  setupKeyboardNavigation() {
    // Bind the method to this instance and store the reference
    this.boundHandleKeydown = this.handleKeydown.bind(this);
    document.addEventListener('keydown', this.boundHandleKeydown);
  }

  disconnect() {
    if (this.boundHandleKeydown) {
      document.removeEventListener('keydown', this.boundHandleKeydown);
    }
    if (this.selectionCheckInterval) {
      clearInterval(this.selectionCheckInterval);
    }
  }

  handleKeydown(event) {
    // Don't interfere when user is typing in input fields
    if (event.target.tagName === 'INPUT' || event.target.tagName === 'SELECT' || event.target.tagName === 'TEXTAREA') {
      return;
    }

    // Don't interfere when a dialog is open
    // Note: Scroll prevention is now handled by the modal controller in svc-util-bundle
    const openDialog = document.querySelector('dialog[open]');
    if (openDialog) {
      return;
    }

    const dataRows = this.getDataRows();

    switch(event.key) {
      case 'ArrowUp':
      case 'k':
        event.preventDefault();
        this.navigateRows(dataRows, -1);
        break;
      
      case 'ArrowDown':
      case 'j':
        event.preventDefault();
        this.navigateRows(dataRows, 1);
        break;
      
      case 'ArrowLeft':
        if (!this.prevBtnTarget.classList.contains('disabled')) {
          event.preventDefault();
          this.prev();
        }
        break;
      
      case 'ArrowRight':
        if (!this.nextBtnTarget.classList.contains('disabled')) {
          event.preventDefault();
          this.next();
        }
        break;
      
      case 'Home':
        if (!this.firstBtnTarget.classList.contains('disabled')) {
          event.preventDefault();
          this.first();
        }
        break;
      
      case 'End':
        if (!this.lastBtnTarget.classList.contains('disabled')) {
          event.preventDefault();
          this.last();
        }
        break;
      
      case 'Enter':
      case ' ':
        if (this.selectedRowIndex >= 0 && dataRows[this.selectedRowIndex]) {
          event.preventDefault();
          // Save the current selection before opening dialog in separate backup
          this.dialogBackupIndex = this.selectedRowIndex;
          dataRows[this.selectedRowIndex].click();
        }
        break;
      
      case 'r':
      case 'R':
        event.preventDefault();
        this.refreshContent(this.createURL("0"));
        break;
      
      case 'f':
      case 'F':
        event.preventDefault();
        if (this.showFilterValue && this.sourceIDTarget) {
          this.sourceIDTarget.focus();
        }
        break;
      
      case 'Escape':
        event.preventDefault();
        this.clearSelection(dataRows, true); // explicitly reset index
        break;
    }
  }

  navigateRows(dataRows, direction) {
    if (dataRows.length === 0) return;

    // Clear visual selection but don't reset index yet
    dataRows.forEach(row => {
      row.classList.remove('table-active');
    });

    // Try to restore from backup if main index was reset
    // Only restore from dialog backup if we haven't explicitly reset all selections
    if (this.selectedRowIndex < 0) {
      if (this.dialogBackupIndex >= 0 && this.lastKnownSelectedIndex >= 0) {
        // Only restore dialog backup if regular backup also exists (means no page change)
        this.selectedRowIndex = this.dialogBackupIndex;
        // Clear the dialog backup after using it
        this.dialogBackupIndex = -1;
      } else if (this.lastKnownSelectedIndex >= 0) {
        this.selectedRowIndex = this.lastKnownSelectedIndex;
      }
    }

    // If no row is selected yet, start at the beginning or end based on direction
    if (this.selectedRowIndex < 0 || this.selectedRowIndex >= dataRows.length) {
      if (direction > 0) {
        // Arrow down: start at first row
        this.selectedRowIndex = 0;
      } else {
        // Arrow up: start at last row
        this.selectedRowIndex = dataRows.length - 1;
      }
    } else {
      // Update selected index
      this.selectedRowIndex += direction;
      
      // Wrap around
      if (this.selectedRowIndex >= dataRows.length) {
        this.selectedRowIndex = 0;
      } else if (this.selectedRowIndex < 0) {
        this.selectedRowIndex = dataRows.length - 1;
      }
    }

    // Highlight selected row
    if (dataRows[this.selectedRowIndex]) {
      dataRows[this.selectedRowIndex].classList.add('table-active');
      dataRows[this.selectedRowIndex].scrollIntoView({ 
        behavior: 'smooth', 
        block: 'nearest' 
      });
      // Always backup the successful selection
      this.lastKnownSelectedIndex = this.selectedRowIndex;
    }
  }

  getDataRows() {
    // Get only actual data rows (tr elements that contain td elements, not th elements)
    // and exclude the "no records found" row
    const rows = this.contentTarget.querySelectorAll('tr');
    const dataRows = Array.from(rows).filter(row => {
      const cells = row.querySelectorAll('td');
      // Must have td elements and not be the "no records" row
      return cells.length > 0 && !row.textContent.includes('no log records found') && !row.textContent.includes('Loading...');
    });
    
    return dataRows;
  }

  resetSelection() {
    // Reset all selection indices completely (used when changing pages)
    this.selectedRowIndex = -1;
    this.lastKnownSelectedIndex = -1;
    this.dialogBackupIndex = -1;
  }

  clearSelection(dataRows, resetIndex = true) {
    const rows = dataRows || this.getDataRows();
    
    rows.forEach(row => {
      row.classList.remove('table-active');
    });
    
    if (resetIndex) {
      this.selectedRowIndex = -1;
      this.lastKnownSelectedIndex = -1; // Also reset backup
    }
  }

  async refreshContent(url) {
    // Clear selection when refreshing content (indices already reset by calling methods)
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
      this.selectedRowIndex = -1; // Reset selection after content refresh

    } else {
      alert('Error during load. Please dry again. (' + response.status + ')');
      console.log(response.status);
      location.reload();
    }
  }

  setupDialogHandlers() {
    // Store original focus handling
    document.addEventListener('click', (event) => {
      // If clicking outside the table, don't reset selection
      if (!this.element.contains(event.target)) {
        return;
      }
    });

    // Listen for dialog close events
    // Note: Native dialog 'close' event bubbles to document
    document.addEventListener('close', (event) => {
      // Only handle dialog close events
      if (event.target.tagName === 'DIALOG') {
        setTimeout(() => this.restoreSelection(), 100);
      }
    }, true); // Use capture phase to ensure we catch it

    // Turbo integration
    document.addEventListener('turbo:before-cache', () => {
      setTimeout(() => this.restoreSelection(), 100);
    });

    // More aggressive fallback: check frequently if selection is lost
    this.selectionCheckInterval = setInterval(() => {
      if (this.selectedRowIndex >= 0) {
        const dataRows = this.getDataRows();
        if (dataRows[this.selectedRowIndex] && !dataRows[this.selectedRowIndex].classList.contains('table-active')) {
          // Check if no dialog is currently open (native dialog element)
          const openDialog = document.querySelector('dialog[open]');
          if (!openDialog) {
            this.restoreSelection();
          }
        }
      }
    }, 200);
  }

  restoreSelection() {
    // Restore visual selection after dialog closes
    let indexToRestore = -1;

    if (this.selectedRowIndex >= 0) {
      indexToRestore = this.selectedRowIndex;
    } else if (this.dialogBackupIndex >= 0 && this.lastKnownSelectedIndex >= 0) {
      // Only restore dialog backup if regular backup also exists (means no page change)
      indexToRestore = this.dialogBackupIndex;
    } else if (this.lastKnownSelectedIndex >= 0) {
      indexToRestore = this.lastKnownSelectedIndex;
    }
    
    if (indexToRestore >= 0) {
      const dataRows = this.getDataRows();
      if (dataRows[indexToRestore]) {
        // Clear any existing selection first
        dataRows.forEach(row => row.classList.remove('table-active'));
        // Restore the selection
        dataRows[indexToRestore].classList.add('table-active');
        // Update both indices
        this.selectedRowIndex = indexToRestore;
        this.lastKnownSelectedIndex = indexToRestore;
      }
    }
  }

}
